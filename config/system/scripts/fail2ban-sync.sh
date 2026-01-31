#!/bin/bash
# Fail2ban to VoIP Panel sync script
# Syncs banned IPs to ip_blacklist table and creates alerts
# Called by fail2ban action: voip-blacklist

ACTION=$1
IP=$2
JAIL_NAME=$3
BANTIME=$4

# Load database credentials from Laravel .env
ENV_FILE="/var/www/voip-panel/.env"
if [ -f "$ENV_FILE" ]; then
    DB_HOST=$(grep "^DB_HOST=" "$ENV_FILE" | cut -d '=' -f2)
    DB_NAME=$(grep "^DB_DATABASE=" "$ENV_FILE" | cut -d '=' -f2)
    DB_USER=$(grep "^DB_USERNAME=" "$ENV_FILE" | cut -d '=' -f2)
    DB_PASS=$(grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d '=' -f2)
else
    # Fallback to defaults if .env not found
    DB_HOST="127.0.0.1"
    DB_NAME="voip"
    DB_USER="voip_user"
    DB_PASS=""
    logger -t fail2ban-voip "Warning: .env file not found, using defaults"
fi

# Calculate expiration time and SQL value
if [ "$BANTIME" -gt 0 ] 2>/dev/null; then
    EXPIRES=$(date -d "+${BANTIME} seconds" "+%Y-%m-%d %H:%M:%S")
    EXPIRES_SQL="'$EXPIRES'"
    PERMANENT=0
else
    EXPIRES_SQL="NULL"
    PERMANENT=1
fi

case "$ACTION" in
    ban)
        # Insert into ip_blacklist
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
            INSERT INTO ip_blacklist (ip_address, reason, source, attempts, expires_at, permanent, created_at)
            VALUES ('$IP', 'Blocked by fail2ban ($JAIL_NAME)', 'fail2ban', 1, $EXPIRES_SQL, $PERMANENT, NOW())
            ON DUPLICATE KEY UPDATE
                attempts = attempts + 1,
                expires_at = $EXPIRES_SQL,
                reason = 'Blocked by fail2ban ($JAIL_NAME)';
        " 2>&1

        # Create alert
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
            INSERT INTO alerts (uuid, type, severity, source_type, title, message, metadata, created_at)
            VALUES (
                UUID(),
                'security_ip_blocked',
                'warning',
                'system',
                'IP blocked by Fail2ban',
                'IP $IP has been blocked by fail2ban ($JAIL_NAME) for suspicious activity.',
                '{\"ip\": \"$IP\", \"jail\": \"$JAIL_NAME\", \"bantime\": $BANTIME, \"source\": \"fail2ban\"}',
                NOW()
            );
        " 2>&1

        # Log
        logger -t fail2ban-voip "Banned IP $IP (jail: $JAIL_NAME) - synced to database"
        ;;

    unban)
        # Remove from ip_blacklist (only fail2ban entries)
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
            DELETE FROM ip_blacklist
            WHERE ip_address = '$IP'
            AND source = 'fail2ban'
            AND permanent = 0;
        " 2>&1

        logger -t fail2ban-voip "Unbanned IP $IP - removed from database"
        ;;
esac

exit 0
