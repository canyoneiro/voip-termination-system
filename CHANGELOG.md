# Changelog

## [1.0.0] - 2026-01-31 - Producción

### Sistema Completo
- Kamailio SIP proxy con autenticación por IP
- Panel web Laravel 11 con dashboard en tiempo real
- API REST completa con Swagger/OpenAPI
- Integración fail2ban con notificaciones
- Bot Telegram para alertas (@tellmetelecom_bot)

### Funcionalidades
- LCR (Least Cost Routing) con tarifas
- QoS (calidad de servicio)
- Reportes programados
- Detección de fraude
- Dialing Plans
- Normalización de números
- Portal multi-tenant

### Seguridad
- 5 jails de fail2ban activos
- Sincronización automática BD ↔ iptables
- Alertas en tiempo real por Telegram/Email
- SSL/TLS con Let's Encrypt

---

## [0.9.0] - 2026-01-30

- Observers para auto-sync con Kamailio
- Sistema de normalización de números
- Sección de ayuda integral
- 133 tests pasando

## [0.8.0] - 2026-01-29

- Dialing Plans implementados
- API Swagger/OpenAPI
- Vistas CRUD completas
- Fase 2: LCR, QoS, Fraude, Reportes, Portal
