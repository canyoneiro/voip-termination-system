# Changelog

## [1.1.0] - 2026-02-01 - Accounting Preciso

### Sistema de Accounting Mejorado
- **PDD** (Post Dial Delay) con precisión de milisegundos
- **Progress Time** - Captura del timestamp 180/183
- **Ring Time** - Tiempo de timbrado (progress → answer)
- **Billable Duration** - Tiempo facturable preciso (answer → end)
- Actualización automática de minutos del customer
- Actualización automática de stats del carrier (daily_calls, daily_minutes, daily_failed)

### Mejoras en Kamailio
- Cálculo de PDD usando microsegundos ($TV(u))
- Almacenamiento de progress_time en Redis y CDR
- Corrección del cálculo de duración billable
- Estadísticas de carrier actualizadas en tiempo real

### Vistas Web Actualizadas (8 archivos)
- `cdrs/index` - Columnas Billable, Ring, PDD con colores
- `cdrs/show` - Timeline completo con todos los tiempos
- `customers/show` - Tabla CDRs con métricas de tiempo
- `carriers/show` - Tabla CDRs con métricas de tiempo
- `qos/index` - Columna Duration añadida
- `qos/customer` - Columna Duration añadida
- `qos/carrier` - Columna Duration añadida
- `portal/cdrs/index` - Columnas Billable, Ring añadidas

### Código de Colores
- 🟢 Verde: Tiempo facturable (billable)
- 🟡 Amarillo: Tiempo de timbrado (ring)
- 🟣 Morado: PDD

### Códigos SIP Descriptivos
- Ahora muestran código + razón (ej: "200 OK", "487 Request Terminated")

### Modelo Cdr
- Nuevo accessor `ring_time` (answer_time - progress_time)
- Nuevo accessor `total_time` (end_time - start_time)

---

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
