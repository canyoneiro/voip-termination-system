#!/bin/bash
# VoIP System - Firewall Sync Script
# Synchronizes UFW rules with authorized IPs from database
# Run via: php artisan firewall:sync or cron

set -e

# Configuration
SIP_PORT="9060/udp"
MARKER="# VoIP-Auto"

# Load database credentials from Laravel .env
ENV_FILE="/var/www/voip-panel/.env"
if [ -f "$ENV_FILE" ]; then
    DB_HOST=$(grep "^DB_HOST=" "$ENV_FILE" | sed 's/^DB_HOST=//')
    DB_NAME=$(grep "^DB_DATABASE=" "$ENV_FILE" | sed 's/^DB_DATABASE=//')
    DB_USER=$(grep "^DB_USERNAME=" "$ENV_FILE" | sed 's/^DB_USERNAME=//')
    DB_PASS=$(grep "^DB_PASSWORD=" "$ENV_FILE" | sed 's/^DB_PASSWORD=//')
else
    echo "Error: .env file not found"
    exit 1
fi

# Get current UFW rules with our marker
get_current_rules() {
    ufw status numbered 2>/dev/null | grep "$MARKER" | awk -F'[][]' '{print $2}' | sort -rn
}

# Remove all VoIP auto-generated rules
remove_old_rules() {
    local rules=$(get_current_rules)
    for rule_num in $rules; do
        echo "y" | ufw delete $rule_num >/dev/null 2>&1 || true
    done
}

# Get authorized IPs from database
get_customer_ips() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
        SELECT DISTINCT ci.ip_address
        FROM customer_ips ci
        JOIN customers c ON ci.customer_id = c.id
        WHERE ci.active = 1 AND c.active = 1;
    " 2>/dev/null
}

get_carrier_ips() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
        SELECT DISTINCT host FROM carriers WHERE state != 'disabled';
    " 2>/dev/null
}

get_carrier_response_ips() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
        SELECT DISTINCT ci.ip_address
        FROM carrier_ips ci
        JOIN carriers c ON ci.carrier_id = c.id
        WHERE ci.active = 1 AND c.state != 'disabled';
    " 2>/dev/null
}

get_whitelist_ips() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
        SELECT value FROM system_settings
        WHERE category = 'security' AND name = 'whitelist_ips';
    " 2>/dev/null | tr ',' '\n' | tr -d '[]"' | grep -v '^$'
}

# Add rules for authorized IPs
add_rules() {
    local all_ips=""

    # Collect all IPs
    for ip in $(get_customer_ips); do
        all_ips="$all_ips $ip"
    done

    for ip in $(get_carrier_ips); do
        # Skip if it's a hostname (not IP)
        if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            all_ips="$all_ips $ip"
        fi
    done

    for ip in $(get_carrier_response_ips); do
        all_ips="$all_ips $ip"
    done

    for ip in $(get_whitelist_ips); do
        if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            all_ips="$all_ips $ip"
        fi
    done

    # Remove duplicates and add rules
    for ip in $(echo $all_ips | tr ' ' '\n' | sort -u); do
        if [ -n "$ip" ]; then
            echo "Adding rule for $ip"
            ufw allow from "$ip" to any port 9060 proto udp comment "$MARKER SIP" >/dev/null 2>&1 || true
        fi
    done
}

# Main
echo "VoIP Firewall Sync - $(date)"
echo "================================"

# First, remove the generic SIP rule (if exists)
ufw delete allow 9060/udp 2>/dev/null || true

echo "Removing old auto-generated rules..."
remove_old_rules

echo "Adding rules for authorized IPs..."
add_rules

echo "Reloading UFW..."
ufw reload >/dev/null 2>&1

echo "Done! Current SIP rules:"
ufw status numbered | grep -E "(9060|SIP)" || echo "No SIP rules found"
