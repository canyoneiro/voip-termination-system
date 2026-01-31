# System Configuration Files

This directory contains all system-level configuration files required for the VoIP termination system to function properly.

## Directory Structure

```
config/system/
├── fail2ban/           # Fail2ban jails, actions, and filters
├── kamailio/           # Kamailio SIP proxy configuration
├── nginx/              # Nginx web server configuration
├── scripts/            # System scripts (cron, sync, etc.)
├── supervisor/         # Supervisor process manager
├── crontab             # Root crontab entries
└── README.md           # This file
```

## Installation Paths

| Source (repo) | Destination (server) |
|---------------|----------------------|
| `fail2ban/voip.conf` | `/etc/fail2ban/jail.d/voip.conf` |
| `fail2ban/voip-blacklist.conf` | `/etc/fail2ban/action.d/voip-blacklist.conf` |
| `fail2ban/kamailio.conf` | `/etc/fail2ban/filter.d/kamailio.conf` |
| `fail2ban/nginx-voip-login.conf` | `/etc/fail2ban/filter.d/nginx-voip-login.conf` |
| `kamailio/kamailio.cfg` | `/etc/kamailio/kamailio.cfg` |
| `nginx/voip-panel.conf` | `/etc/nginx/sites-enabled/voip-panel` |
| `scripts/*.sh` | `/opt/voip-scripts/` |
| `supervisor/voip-panel.conf` | `/etc/supervisor/conf.d/voip-panel.conf` |

## Fail2ban Configuration

### Jails (`fail2ban/voip.conf`)

| Jail | Purpose | Max Retry | Ban Time |
|------|---------|-----------|----------|
| `sshd` | SSH brute force protection | 3 | 2 hours |
| `kamailio` | SIP authentication failures | 5 | 1 hour |
| `kamailio-aggressive` | SIP flood detection | 20/min | 24 hours |
| `nginx-voip-login` | Web panel login attempts | 5 | 30 min |
| `nginx-botsearch` | Bot/scanner detection | 2 | 24 hours |

### Custom Action (`fail2ban/voip-blacklist.conf`)

Syncs banned IPs to the VoIP panel database:
- Inserts IP into `ip_blacklist` table
- Creates alert in `alerts` table
- Triggers Telegram/Email notifications
- Kamailio also blocks the IP (checks `ip_blacklist`)

## Kamailio Configuration

Main configuration file with:
- IP-based authentication (customers)
- Dispatcher with failover (carriers)
- Rate limiting (CPS, channels, minutes)
- Flood detection (>50 CPS triggers ban)
- SIP trace capture
- CDR accounting to MariaDB
- Integration with Redis for real-time counters

### Key Routes

| Route | Purpose |
|-------|---------|
| `CHECK_BLACKLIST` | Rejects IPs in `ip_blacklist` |
| `AUTH_IP` | Validates customer IPs |
| `CHECK_LIMITS` | Enforces CPS/channels/minutes limits |
| `SELECT_CARRIER` | LCR routing with failover |
| `ACCOUNTING` | CDR generation |

## Nginx Configuration

- HTTPS with Let's Encrypt SSL
- PHP-FPM socket connection
- Proxy headers for real IP
- Security headers (CSP, X-Frame-Options, etc.)

## Supervisor Configuration

Manages background processes:
- `voip-queue` - Laravel queue worker (8 processes)
- `voip-schedule` - Laravel scheduler

## Scripts

### `fail2ban-sync.sh`
Called by fail2ban on ban/unban events:
```bash
/opt/voip-scripts/fail2ban-sync.sh ban <ip> <jail> <bantime>
/opt/voip-scripts/fail2ban-sync.sh unban <ip>
```

### `sync-blacklist.sh`
Syncs `ip_blacklist` table to iptables (runs every 5 minutes via cron).

## Crontab

```cron
# Laravel scheduler (every minute)
* * * * * cd /var/www/voip-panel && php artisan schedule:run

# Blacklist sync to iptables (every 5 minutes)
*/5 * * * * /opt/voip-scripts/sync-blacklist.sh
```

## Deployment

After cloning the repository, copy configuration files:

```bash
# Fail2ban
sudo cp config/system/fail2ban/voip.conf /etc/fail2ban/jail.d/
sudo cp config/system/fail2ban/voip-blacklist.conf /etc/fail2ban/action.d/
sudo cp config/system/fail2ban/kamailio.conf /etc/fail2ban/filter.d/
sudo cp config/system/fail2ban/nginx-voip-login.conf /etc/fail2ban/filter.d/
sudo systemctl restart fail2ban

# Kamailio
sudo cp config/system/kamailio/kamailio.cfg /etc/kamailio/
sudo kamctl mi reload

# Nginx
sudo cp config/system/nginx/voip-panel.conf /etc/nginx/sites-enabled/voip-panel
sudo nginx -t && sudo systemctl reload nginx

# Scripts
sudo cp config/system/scripts/*.sh /opt/voip-scripts/
sudo chmod +x /opt/voip-scripts/*.sh

# Supervisor
sudo cp config/system/supervisor/voip-panel.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update

# Crontab (review first!)
# crontab config/system/crontab
```

## Security Notes

- Database credentials in `fail2ban-sync.sh` should match Laravel `.env`
- Never expose these files publicly
- Keep repository private
- Rotate credentials regularly
