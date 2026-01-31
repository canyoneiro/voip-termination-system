# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-01-31

### Added

#### Core System
- Kamailio SIP proxy with IP-based authentication
- MariaDB database for CDRs and configuration
- Redis for real-time statistics and caching
- Laravel 11 web panel and API

#### Security
- Fail2ban integration with database sync
- IP blacklist with automatic blocking
- Flood detection (>50 CPS triggers ban)
- Rate limiting on API endpoints
- Telegram and email notifications for security alerts

#### Customer Management
- Customer CRUD with IP authorization
- Channel, CPS, and minutes limits
- Dialing plans for destination restrictions
- Number normalization per customer
- Prepaid/postpaid billing modes

#### Carrier Management
- Carrier CRUD with health monitoring
- Dispatcher with priority-based failover
- OPTIONS probing every 30 seconds
- Automatic state detection (active/inactive/probing)
- Codec manipulation and tech prefixes

#### LCR (Least Cost Routing)
- Destination prefixes with country/region
- Carrier rates with time-based pricing
- Rate plans assignable to customers
- CSV import for bulk rate updates

#### CDR & Traces
- Complete call detail records
- SIP trace capture for debugging
- Ladder diagram visualization
- CSV export with filters

#### QoS (Quality of Service)
- MOS calculation from RTP statistics
- PDD (Post Dial Delay) tracking
- Daily aggregated statistics
- Quality alerts and trends

#### Fraud Detection
- Pattern-based rule engine
- Wangiri detection (short calls)
- Traffic spike detection
- Off-hours traffic alerts
- High-cost destination alerts
- Automatic incident creation

#### Scheduled Reports
- Daily/weekly/monthly reports
- PDF and CSV formats
- Email delivery
- Multiple report types

#### Customer Portal
- Self-service access for customers
- CDR viewing and export
- IP management requests
- Usage statistics

#### API
- RESTful API with Bearer token auth
- Swagger/OpenAPI documentation
- Webhook system for events
- Rate limiting per token

#### Notifications
- Telegram bot integration
- Email notifications via SMTP
- Configurable per alert type
- Admin and customer notifications

### Changed
- ProcessPendingAlertsJob now handles Kamailio-inserted alerts
- CarrierObserver creates alerts for state changes
- Fail2ban script reads credentials from .env

### Fixed
- FraudController relation 'rule' changed to 'fraudRule'
- Carbon setTimeFromTimeString() replaced with setTime()
- Carbon next() day calculation for weekly reports
- SQL syntax in fail2ban-sync.sh for ip_blacklist INSERT

## [0.9.0] - 2026-01-30

### Added
- Kamailio sync observers (CustomerIpObserver, CarrierObserver)
- KamailioAddress and KamailioDispatcher models
- Help section with complete documentation
- Number normalization service

## [0.8.0] - 2026-01-29

### Added
- Dialing Plans for destination restrictions
- Swagger/OpenAPI documentation
- Complete CRUD views for rates, reports, fraud

## [0.7.0] - 2026-01-29

### Added
- Phase 2 features: LCR, QoS, Reports, Fraud Detection
- Customer Portal (multi-tenant)
- 16 new migrations, 15 new models
- CdrObserver for automatic job dispatch

---

For detailed progress tracking, see [docs/PROGRESS.md](docs/PROGRESS.md).
