# Security Hardening Documentation

Last updated: 2026-02-03

## Overview

This document describes the security hardening measures applied to the VoIP Termination System server.

## SSH Hardening

**File:** `/etc/ssh/sshd_config`

- Protocol 2 only
- Root login disabled (key-only via `without-password`)
- Password authentication disabled
- MaxAuthTries: 3
- MaxSessions: 2
- MaxStartups: 10:30:60
- Strong ciphers only:
  - aes256-gcm@openssh.com
  - chacha20-poly1305@openssh.com
  - aes256-ctr
- Strong MACs: hmac-sha2-512-etm, hmac-sha2-256-etm
- Strong KexAlgorithms: curve25519-sha256, diffie-hellman-group16/18-sha512
- X11Forwarding, AgentForwarding, TcpForwarding disabled

## Nginx Hardening

**Files:** `/etc/nginx/nginx.conf`, `/etc/nginx/sites-available/voip-panel`

### TLS Configuration
- TLS 1.2 and 1.3 only (TLS 1.0/1.1 disabled)
- Strong cipher suites (ECDHE with AES-GCM and ChaCha20)
- Server tokens hidden

### Security Headers
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Strict-Transport-Security: max-age=63072000; includeSubDomains; preload`
- `Content-Security-Policy` (restrictive policy)
- `Permissions-Policy` (disabled geolocation, camera, microphone, etc.)
- `Cross-Origin-Embedder-Policy: require-corp`
- `Cross-Origin-Opener-Policy: same-origin`
- `Cross-Origin-Resource-Policy: same-origin`

### Rate Limiting
**File:** `/etc/nginx/conf.d/rate-limiting.conf`

| Zone | Rate | Purpose |
|------|------|---------|
| login | 5r/m | Login page protection |
| api | 30r/s | API endpoints |
| general | 10r/s | General requests |
| conn_per_ip | 20 | Max connections per IP |

### Timeouts (DoS Prevention)
- client_body_timeout: 12s
- client_header_timeout: 12s
- keepalive_timeout: 15s
- send_timeout: 10s

## Fail2ban Configuration

**Files:** `/etc/fail2ban/jail.d/voip.conf`, `/etc/fail2ban/filter.d/kamailio*.conf`

### Active Jails

| Jail | Filter | MaxRetry | FindTime | BanTime |
|------|--------|----------|----------|---------|
| sshd | sshd | 3 | 600s | 7200s |
| kamailio | kamailio | 5 | 600s | 3600s |
| kamailio-aggressive | kamailio | 20 | 60s | 86400s |
| kamailio-scanner | kamailio-scanner | 1 | 3600s | 604800s |
| nginx-voip-login | nginx-voip-login | 5 | 300s | 1800s |
| nginx-botsearch | nginx-botsearch | 2 | 60s | 86400s |

### Kamailio Filter Patterns
- Unauthorized IP attempts
- Blacklisted IPs
- Flood detection (pike module)
- 401/403/407 SIP responses
- Scanner user-agents (SIPVicious, friendly-scanner, etc.)
- Malformed messages (sanity module)

### Integration
- Banned IPs are synced to `ip_blacklist` database table
- Alerts are created automatically
- Script: `/opt/voip-scripts/fail2ban-sync.sh`

## Firewall (UFW)

### Rules

| Port | Protocol | Access | Description |
|------|----------|--------|-------------|
| 22 | TCP | LIMIT | SSH (rate limited) |
| 80 | TCP | ALLOW | HTTP (Certbot) |
| 443 | TCP | ALLOW | HTTPS (Panel) |
| 9060 | UDP | Restricted | SIP (authorized IPs only) |

### SIP Port Restriction
The SIP port (9060/udp) is restricted to authorized IPs only:
- Customer IPs from `customer_ips` table
- Carrier hosts from `carriers` table
- Carrier response IPs from `carrier_ips` table

**Sync script:** `/opt/voip-scripts/sync-firewall.sh`

Run manually or via Laravel:
```bash
/opt/voip-scripts/sync-firewall.sh
# or
php artisan firewall:sync
```

## MariaDB Hardening

**File:** `/etc/mysql/mariadb.conf.d/99-security.cnf`

### Security Settings
- `bind-address = 127.0.0.1` (localhost only)
- `local_infile = 0` (disabled)
- `symbolic-links = 0`
- `skip-name-resolve`
- `secure_file_priv = /var/lib/mysql-files`

### Access Control
- No anonymous users
- No remote root access
- Test database removed
- voip_user has minimum required privileges

### Connection Limits
- max_connections: 100
- max_user_connections: 50
- connect_timeout: 10s
- wait_timeout: 600s

## Kamailio Security

### Modules
- **pike**: Flood detection and blocking
- **htable**: In-memory tracking (blacklist size: 1024)
- **sanity**: Message validation
- **topoh**: Topology hiding

### Protections
- Rate limiting per IP before authentication
- IP blacklist checking
- Customer/carrier IP validation
- CPS and channel limits per customer/carrier

## Verification Commands

```bash
# Check SSH config
sshd -T | grep -E "permitrootlogin|passwordauth|maxauthtries"

# Check Nginx
nginx -t && curl -I https://sw1.tellmetelecom.com 2>/dev/null | grep -E "^(X-|Strict|Content-Security)"

# Check fail2ban
fail2ban-client status

# Check firewall
ufw status numbered

# Check MariaDB
mysql -e "SHOW VARIABLES LIKE 'local_infile';"

# Check all services
systemctl is-active ssh nginx mariadb redis-server php8.3-fpm kamailio fail2ban
```

## Maintenance

### Regular Tasks
- Review fail2ban logs: `fail2ban-client status kamailio`
- Check blacklisted IPs: `mysql voip -e "SELECT * FROM ip_blacklist;"`
- Update firewall after adding customers/carriers: `/opt/voip-scripts/sync-firewall.sh`
- Monitor auth.log and syslog for suspicious activity

### Security Updates
```bash
apt update && apt upgrade -y
```
