#!/bin/bash
# Sync fail2ban bans to VoIP panel blacklist and create alerts
# Run periodically via cron

set -e

# Load database credentials from Laravel .env
ENV_FILE="/var/www/voip-panel/.env"
if [ -f "$ENV_FILE" ]; then
    DB_HOST=$(grep "^DB_HOST=" "$ENV_FILE" | cut -d '=' -f2)
    DB_NAME=$(grep "^DB_DATABASE=" "$ENV_FILE" | cut -d '=' -f2)
    DB_USER=$(grep "^DB_USERNAME=" "$ENV_FILE" | cut -d '=' -f2)
    DB_PASS=$(grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d '=' -f2)
else
    echo "Error: .env file not found at $ENV_FILE"
    exit 1
fi

# MySQL command with credentials
MYSQL_CMD="mysql -h${DB_HOST:-127.0.0.1} -u${DB_USER} -p${DB_PASS} ${DB_NAME}"

# Log file
LOG_FILE="/var/log/voip/sync-blacklist.log"
mkdir -p "$(dirname "$LOG_FILE")"

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

log "Starting blacklist sync"

# Get all currently banned IPs from fail2ban jails
BANNED_IPS=""

# Check kamailio jail
if fail2ban-client status kamailio &>/dev/null; then
    BANNED_IPS+=$(fail2ban-client status kamailio 2>/dev/null | grep "Banned IP" | sed 's/.*Banned IP list:\s*//' | tr ' ' '\n')
    BANNED_IPS+=$'\n'
fi

# Check kamailio-aggressive jail
if fail2ban-client status kamailio-aggressive &>/dev/null; then
    BANNED_IPS+=$(fail2ban-client status kamailio-aggressive 2>/dev/null | grep "Banned IP" | sed 's/.*Banned IP list:\s*//' | tr ' ' '\n')
    BANNED_IPS+=$'\n'
fi

# Check nginx jail if exists
if fail2ban-client status nginx-http-auth &>/dev/null; then
    BANNED_IPS+=$(fail2ban-client status nginx-http-auth 2>/dev/null | grep "Banned IP" | sed 's/.*Banned IP list:\s*//' | tr ' ' '\n')
    BANNED_IPS+=$'\n'
fi

# Process each banned IP
ADDED_COUNT=0
for IP in $BANNED_IPS; do
    if [ -n "$IP" ] && [ "$IP" != " " ]; then
        # Check if IP already in blacklist
        EXISTS=$($MYSQL_CMD -N -e "SELECT COUNT(*) FROM ip_blacklist WHERE ip_address='$IP'" 2>/dev/null)

        if [ "$EXISTS" = "0" ]; then
            # Add to blacklist with 24-hour expiry
            $MYSQL_CMD -e "INSERT INTO ip_blacklist (ip_address, reason, source, attempts, expires_at, created_at)
                VALUES ('$IP', 'Blocked by fail2ban', 'fail2ban', 1, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
                ON DUPLICATE KEY UPDATE attempts = attempts + 1, expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)" 2>/dev/null

            # Create security alert for this blocked IP
            ALERT_UUID=$(cat /proc/sys/kernel/random/uuid)
            $MYSQL_CMD -e "INSERT INTO alerts (uuid, type, severity, source_type, title, message, metadata, created_at)
                VALUES ('$ALERT_UUID', 'security_ip_blocked', 'warning', 'system',
                'IP bloqueada por fail2ban',
                'La IP $IP ha sido bloqueada automaticamente por fail2ban debido a multiples intentos de acceso fallidos.',
                '{\"ip\": \"$IP\", \"source\": \"fail2ban\", \"duration\": \"24h\"}',
                NOW())" 2>/dev/null

            log "Added $IP to blacklist and created alert"
            ((ADDED_COUNT++))
        fi
    fi
done

# Clean up expired entries
DELETED=$($MYSQL_CMD -N -e "SELECT COUNT(*) FROM ip_blacklist WHERE expires_at < NOW() AND permanent = 0" 2>/dev/null)
$MYSQL_CMD -e "DELETE FROM ip_blacklist WHERE expires_at < NOW() AND permanent = 0" 2>/dev/null

if [ "$DELETED" -gt "0" ]; then
    log "Cleaned up $DELETED expired blacklist entries"
fi

# Reload Kamailio permissions if any changes were made
if [ "$ADDED_COUNT" -gt "0" ]; then
    if kamcmd permissions.addressReload 2>/dev/null; then
        log "Kamailio permissions reloaded"
    else
        log "Warning: Failed to reload Kamailio permissions"
    fi
fi

log "Sync complete. Added $ADDED_COUNT new IPs"
