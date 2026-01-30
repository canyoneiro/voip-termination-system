
## 2026-01-30 - Corrección de Integridad del Sistema

### Problema Crítico 1: Kamailio no bloqueaba clientes suspendidos
**Resuelto:** Nueva ruta `CHECK_CUSTOMER_STATUS` en kamailio.cfg que verifica:
- Redis key `customer:{id}:blocked` - Bloqueos por fraude
- Campo `suspended_at` en DB - Suspensiones por saldo
- Redis key `customer:{id}:throttled_cps` - Throttling de CPS

### Problema Crítico 2: Sistema de alertas no enviaba notificaciones
**Resuelto:** Implementado sistema completo de notificaciones:
- `SendAlertNotificationJob.php` - Job para enviar email y Telegram
- `AlertMail.php` - Mailable con diseño responsive
- `alert.blade.php` - Template HTML para emails
- `AlertObserver` modificado para despachar job automáticamente
- Soporte para múltiples destinatarios según tipo de alerta

### Problema Crítico 3: Fail2ban no generaba alertas
**Resuelto:** Script `sync-blacklist.sh` actualizado para:
- Crear alertas `security_ip_blocked` al bloquear IPs
- Cargar credenciales desde .env de Laravel
- Logging mejorado en /var/log/voip/sync-blacklist.log

### Mejora: Scheduler de reportes
**Resuelto:** El scheduler ahora respeta `send_time` y `next_run_at` del modelo
- Se ejecuta cada minuto para verificar reportes pendientes
- Calcula automáticamente el próximo envío

### Mejora: BillingService con Redis
**Resuelto:** Sincronización DB ↔ Redis al suspender/reactivar clientes
- `suspendCustomer()` setea Redis key para bloqueo rápido
- `unsuspendCustomer()` limpia la key de Redis

### Configuración
- Mail cambiado de SMTP a sendmail para evitar problemas de TLS local
- Admin email configurado en system_settings

---

## 2026-01-29 - SIP Traces y UI Improvements

### Trazas SIP
- Configurado módulo siptrace en Kamailio para capturar todos los mensajes SIP
- Modificado kamailio.cfg para guardar flag de trazas en Redis y capturar BYE/ACK
- Modelo SipTrace actualizado para mapear campos de tabla Kamailio (callid, time_stamp, etc.)
- Nuevo diagrama ladder en detalle de CDR con:
  - 3 columnas visuales: Cliente, Kamailio, Carrier
  - Flechas direccionales coloreadas por tipo de mensaje
  - Detección automática de entidad por IP/puerto
  - Click para expandir mensaje SIP completo
- Toggle "Capturar Trazas SIP" añadido en edición de customer
- Badge "Trazas" visible en detalle de customer cuando está habilitado

### Sistema de Administración
- Nuevo SystemController con vistas de estado, logs y base de datos
- Menú "Sistema" en navegación con submenús
- Campo probing_enabled añadido a carriers

### UI/CSS
- Tema claro aplicado consistentemente en todo el panel
- Fondo gris claro (#f1f5f9), cards blancas
- Texto oscuro legible
- Bloques de código mantienen fondo oscuro

### Kamailio
- Desactivado probing automático (ds_probing_mode=0) para pruebas locales
- Trazas SIP guardadas en tabla sip_trace de Kamailio
