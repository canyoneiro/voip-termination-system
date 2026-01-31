# VoIP Termination System - Estado del Proyecto

**Servidor:** sw1.tellmetelecom.com (165.22.130.17)
**Repositorio:** github.com/canyoneiro/voip-termination-system
**Última actualización:** 2026-01-31

---

## Estado Actual: ✅ PRODUCCIÓN

El sistema está completamente operativo con todas las funcionalidades implementadas.

### Servicios Activos
| Servicio | Estado | Puerto |
|----------|--------|--------|
| Kamailio | ✅ | 5060/UDP |
| MariaDB | ✅ | 3306 |
| Redis | ✅ | 6379 |
| Nginx + SSL | ✅ | 443 |
| PHP-FPM 8.3 | ✅ | socket |
| Supervisor | ✅ | - |
| Fail2ban | ✅ | - |

### Workers (Supervisor)
- `voip-queue` (x2) - Procesamiento de jobs
- `voip-scheduler` - Laravel scheduler
- `voip-webhooks` - Envío de webhooks

---

## Funcionalidades Implementadas

### Core SIP (Kamailio)
- ✅ Autenticación por IP de clientes
- ✅ Control de límites (CPS, canales, minutos)
- ✅ Dispatcher con failover inteligente
- ✅ Accounting completo a MariaDB
- ✅ Monitoreo de carriers con OPTIONS
- ✅ Detección de flood (>50 CPS = ban 1h)
- ✅ Blacklist de IPs integrada

### Panel Web (Laravel 11)
- ✅ Dashboard en tiempo real
- ✅ CRUD Clientes con IPs autorizadas
- ✅ CRUD Carriers con monitoreo
- ✅ Visor de CDRs con filtros
- ✅ Trazas SIP con diagrama ladder
- ✅ Sistema de alertas
- ✅ Blacklist de IPs
- ✅ Configuración del sistema

### Funcionalidades Avanzadas
- ✅ LCR (Least Cost Routing) con tarifas
- ✅ QoS (calidad de servicio, MOS)
- ✅ Reportes programados (email PDF/CSV)
- ✅ Detección de fraude
- ✅ Dialing Plans (restricción de destinos)
- ✅ Normalización de números por cliente
- ✅ Portal multi-tenant para clientes

### API REST
- ✅ Endpoints completos documentados
- ✅ Autenticación por token
- ✅ Rate limiting
- ✅ Webhooks para eventos
- ✅ Swagger/OpenAPI en /api/documentation

### Seguridad
- ✅ Fail2ban con 5 jails activos
- ✅ Sincronización fail2ban → BD → Telegram
- ✅ Alertas por Telegram y Email
- ✅ SSL/TLS con Let's Encrypt

---

## Arquitectura

```
Clientes SIP
     │
     ▼
┌─────────────┐
│  Kamailio   │──── Redis (contadores, cache)
│  (5060/UDP) │
└─────────────┘
     │
     ├──── MariaDB (CDRs, config, alertas)
     │
     ▼
┌─────────────┐
│   Carriers  │
└─────────────┘

Panel Web (Laravel) ◄──── Nginx (443) ◄──── Usuarios
```

---

## Archivos de Configuración

Todos los archivos de sistema están en `config/system/`:

```
config/system/
├── README.md                 # Guía de instalación
├── crontab                   # Tareas programadas
├── fail2ban/
│   ├── voip.conf             # Jails
│   ├── voip-blacklist.conf   # Acción custom
│   ├── kamailio.conf         # Filtro SIP
│   └── nginx-voip-login.conf # Filtro web
├── kamailio/
│   └── kamailio.cfg          # Config completa
├── nginx/
│   └── voip-panel.conf       # Sitio HTTPS
├── scripts/
│   ├── fail2ban-sync.sh      # Sync bans → BD
│   └── sync-blacklist.sh     # Limpieza periódica
└── supervisor/
    └── voip-panel.conf       # Workers
```

---

## Notificaciones

### Telegram
- Bot: @tellmetelecom_bot
- Admin chat_id: 592944152
- Test: `php artisan notify:test-telegram`

### Tipos de Alertas
| Tipo | Severidad | Destinatario |
|------|-----------|--------------|
| carrier_down | critical | Admin |
| carrier_recovered | info | Admin |
| security_ip_blocked | warning | Admin |
| security_flood_detected | critical | Admin |
| minutes_warning (80%) | warning | Cliente |
| minutes_exhausted | critical | Admin + Cliente |
| cps_exceeded | warning | Admin + Cliente |
| channels_exceeded | warning | Admin + Cliente |

---

## Comandos Útiles

### Artisan
```bash
# Kamailio
php artisan kamailio:sync          # Recargar módulos
php artisan kamailio:status        # Ver estado

# Notificaciones
php artisan notify:test-telegram   # Test Telegram

# Limpieza
php artisan cleanup:all            # Limpiar datos antiguos
php artisan blacklist:cleanup      # Limpiar IPs expiradas
```

### Sistema
```bash
# Ver logs
tail -f /var/www/voip-panel/storage/logs/laravel.log
tail -f /var/log/syslog | grep kamailio

# Servicios
systemctl status kamailio mariadb redis nginx php8.3-fpm

# Fail2ban
fail2ban-client status
fail2ban-client set kamailio banip 1.2.3.4
fail2ban-client set kamailio unbanip 1.2.3.4
```

### Actualizar Sistema
```bash
cd /var/www/voip-panel
git pull
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan queue:restart
```

---

## Tests

```bash
# Todos los tests
php artisan test

# Solo integración Kamailio
php artisan test --filter=Kamailio

# Resultado actual: 133 tests, 374 assertions ✅
```

---

## Historial de Cambios

### 2026-01-31
- ✅ Corregido sistema de alertas (Kamailio → BD → Telegram/Email)
- ✅ Integrado fail2ban con BD y notificaciones
- ✅ Bot Telegram configurado (@tellmetelecom_bot)
- ✅ Corregidos errores 500 en vistas QoS y Fraude
- ✅ Añadidos archivos de sistema al repositorio
- ✅ Documentación completa actualizada

### 2026-01-30
- ✅ Observers para auto-sync con Kamailio
- ✅ Sistema de normalización de números
- ✅ Sección de ayuda integral
- ✅ 133 tests pasando

### 2026-01-29
- ✅ Dialing Plans implementados
- ✅ API Swagger/OpenAPI
- ✅ Vistas CRUD completas
- ✅ Fase 2 completada (LCR, QoS, Fraude, Reportes, Portal)

---

## Datos del Sistema

| Métrica | Valor |
|---------|-------|
| Customers | 1 |
| Carriers | 2 |
| CDRs | 39 |
| Tests | 133 ✅ |
| Jails Fail2ban | 5 |
