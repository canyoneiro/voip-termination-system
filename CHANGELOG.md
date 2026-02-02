# Changelog

## [1.3.0] - 2026-02-02 - Implementación Completa de Umbrales

### Nuevos Jobs Implementados
- **CheckThresholdsJob**: Verifica umbrales del sistema y genera alertas automáticamente
  - `channels_warning_pct`: Alerta cuando cliente usa >= X% de canales (default: 80%)
  - `minutes_warning_pct`: Alerta cuando cliente consume >= X% de minutos (default: 80%)
  - `min_asr_global`: Alerta cuando ASR de últimas 4h cae del umbral (default: 40%)
  - `options_timeout`: Alerta cuando carrier no responde OPTIONS (default: 90s)
- **SyncSettingsToRedisJob**: Sincroniza settings de BD a Redis para Kamailio
  - Permite a Kamailio leer configuración dinámica sin reinicio
  - TTL de 5 minutos para seguridad ante fallos

### Kamailio - Implementación de Límites Globales
- **CHECK_GLOBAL_LIMITS route**: Nueva ruta para verificar límites globales
  - `global_max_channels`: Máximo de llamadas simultáneas en todo el sistema
  - `global_max_cps`: CPS máximo global del sistema
  - Genera alertas cuando se alcanzan los límites
- **Contador voip:global_calls**: Tracking de llamadas activas globales
  - Incremento en PROCESS_CALL
  - Decremento en todos los puntos de finalización (BYE, FAILURE, dialog:end)

### Kamailio - Implementación de Seguridad Dinámica
- **CHECK_WHITELIST route**: IPs que nunca serán bloqueadas
  - Lee de Redis SET `voip:whitelist`
  - Skip de todas las verificaciones de seguridad
- **ANTIFLOOD mejorado**: Ahora usa configuración dinámica
  - `flood_threshold`: CPS por IP para detectar flood (leído de Redis)
  - `blacklist_duration`: Duración del bloqueo automático (leído de Redis)
  - Inserta en BD ip_blacklist además de Redis

### Documentación
- **Help page actualizada**: Nueva sección "Umbrales y Configuración del Sistema"
  - Explica todos los settings de alertas, límites y seguridad
  - Documenta valores por defecto y funcionamiento

### Scheduler Actualizado
- Añadido `CheckThresholdsJob` cada minuto
- Añadido `SyncSettingsToRedisJob` cada minuto

### Settings Implementados (9 total)
| Categoría | Setting | Default | Descripción |
|-----------|---------|---------|-------------|
| alerts | channels_warning_pct | 80 | % uso canales para warning |
| alerts | minutes_warning_pct | 80 | % uso minutos para warning |
| alerts | min_asr_global | 40 | ASR mínimo global |
| alerts | options_timeout | 90 | Timeout OPTIONS en segundos |
| limits | global_max_channels | 0 | Canales máximos globales (0=sin límite) |
| limits | global_max_cps | 0 | CPS máximo global (0=sin límite) |
| security | flood_threshold | 50 | CPS por IP para flood |
| security | blacklist_duration | 3600 | Duración blacklist automático |
| security | whitelist_ips | (vacío) | IPs que nunca se bloquean |

### Archivos Creados/Modificados
- `app/Jobs/CheckThresholdsJob.php` (nuevo)
- `app/Jobs/SyncSettingsToRedisJob.php` (nuevo)
- `/etc/kamailio/kamailio.cfg` - Nuevas rutas CHECK_WHITELIST, CHECK_GLOBAL_LIMITS, ANTIFLOOD mejorado
- `routes/console.php` - 2 nuevos jobs en scheduler
- `resources/views/help/index.blade.php` - Nueva sección de documentación

---

## [1.2.0] - 2026-02-02 - Correcciones Críticas y Robustez

### Kamailio - Correcciones Críticas
- **HTABLE blacklist size**: Aumentado de 256 a 1024 entradas (size=8→10)
- **Límites de carrier**: Ahora verifica max_channels y max_cps antes de enviar llamadas
- **Bug DECR negativo**: Corregido en HANDLE_BYE y MANAGE_FAILURE (verifica GET > 0 antes de DECR)
- **dialog:end handler**: Implementado cleanup completo de llamadas huérfanas con creación de CDR

### Laravel - Correcciones de Base de Datos
- **CleanupTraces.php**: Corregido nombre de columna `timestamp` → `time_stamp` (tabla Kamailio)
- **CdrController.php**: Corregido `call_id` → `callid` y `timestamp` → `time_stamp` para SipTrace

### Laravel - Correcciones de Lógica
- **SendAlertNotificationJob.php**: Añadido escape de caracteres Markdown para Telegram (evita errores de parsing)
- **Invoice.php**: Mejorada comparación de fechas para `is_overdue` usando `endOfDay()->isPast()`
- **CustomerRate.php**: Añadido null check para `billing_increment` y `min_duration`
- **DialingPlanRule.php**: Corregida generación de regex para wildcards (* y ?)

### Nuevas Funcionalidades
- **CleanupStaleCalls command**: `php artisan calls:cleanup-stale` - Limpia llamadas huérfanas y sincroniza contadores Redis
- **API Rate Limiting**: Implementado en middleware ApiTokenAuth con headers estándar (X-RateLimit-*)
- **Scheduler**: Añadida tarea `calls:cleanup-stale` cada 5 minutos

### Archivos Modificados
- `/etc/kamailio/kamailio.cfg` - 4 correcciones críticas
- `app/Console/Commands/CleanupTraces.php`
- `app/Console/Commands/CleanupStaleCalls.php` (nuevo)
- `app/Http/Controllers/Api/CdrController.php`
- `app/Http/Middleware/ApiTokenAuth.php`
- `app/Jobs/SendAlertNotificationJob.php`
- `app/Models/CustomerRate.php`
- `app/Models/DialingPlanRule.php`
- `app/Models/Invoice.php`
- `routes/console.php`

---

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
