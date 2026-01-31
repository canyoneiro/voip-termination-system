# VoIP Termination System

[![CI](https://github.com/canyoneiro/voip-termination-system/actions/workflows/ci.yml/badge.svg)](https://github.com/canyoneiro/voip-termination-system/actions/workflows/ci.yml)
[![Deploy](https://github.com/canyoneiro/voip-termination-system/actions/workflows/deploy.yml/badge.svg)](https://github.com/canyoneiro/voip-termination-system/actions/workflows/deploy.yml)

Sistema completo de terminacion VoIP con panel de administracion, API REST y portal de clientes.

## Caracteristicas

### Core
- **Autenticacion por IP** - Validacion de IPs autorizadas por cliente
- **Control de Limites** - Canales simultaneos, CPS, minutos diarios/mensuales
- **LCR (Least Cost Routing)** - Enrutamiento inteligente basado en costo y prioridad
- **Failover Automatico** - Reintento con carriers alternativos
- **Accounting Completo** - CDRs detallados con trazas SIP

### Panel de Administracion
- Dashboard en tiempo real con llamadas activas
- Gestion de clientes e IPs autorizadas
- Gestion de carriers con monitoreo de estado
- Visor de CDRs con filtros avanzados
- Sistema de alertas con notificaciones
- Gestion de webhooks
- Blacklist de IPs

### Fase 2 - Funcionalidades Avanzadas
- **LCR + Tarifas** - Gestion completa de tarifas por destino, planes de precios
- **QoS (Calidad)** - Metricas MOS, PDD, alertas de degradacion
- **Reportes Programados** - Envio automatico de estadisticas por email (PDF/CSV)
- **Deteccion de Fraude** - Patrones sospechosos, Wangiri, picos de trafico
- **Portal Multi-tenant** - Acceso de clientes a sus propios datos

### API REST
- Endpoints completos para integraciones
- Autenticacion por token con rate limiting
- Webhooks para eventos en tiempo real
- Documentacion Swagger/OpenAPI

## Requisitos

- Ubuntu 22.04 / 24.04 LTS
- PHP 8.2+
- MariaDB 10.6+
- Redis 6+
- Nginx
- Kamailio 5.7+
- Composer 2.x
- Node.js 18+ (para assets)

## Instalacion

### 1. Clonar el repositorio

```bash
cd /var/www
git clone https://github.com/canyoneiro/voip-termination-system.git voip-panel
cd voip-panel
```

### 2. Instalar dependencias PHP

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con las credenciales de base de datos y Redis:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=voip
DB_USERNAME=voip_user
DB_PASSWORD=tu_password_seguro

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 4. Ejecutar migraciones

```bash
php artisan migrate --force
php artisan db:seed --class=FraudRulesSeeder --force
```

### 5. Compilar assets

```bash
npm install
npm run build
```

### 6. Configurar permisos

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 7. Configurar servicios del sistema

Las configuraciones del sistema se encuentran en `config/system/`. Ver `config/system/README.md` para instrucciones detalladas.

```bash
# Supervisor (queue workers)
sudo cp config/system/supervisor/voip-panel.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update

# Fail2ban
sudo cp config/system/fail2ban/*.conf /etc/fail2ban/jail.d/
sudo cp config/system/fail2ban/voip-blacklist.conf /etc/fail2ban/action.d/
sudo systemctl restart fail2ban

# Scripts
sudo cp config/system/scripts/*.sh /opt/voip-scripts/
sudo chmod +x /opt/voip-scripts/*.sh

# Kamailio (si es nueva instalacion)
sudo cp config/system/kamailio/kamailio.cfg /etc/kamailio/
```

### 8. Configurar scheduler

Agregar al crontab:

```bash
* * * * * cd /var/www/voip-panel && php artisan schedule:run >> /dev/null 2>&1
```

## Estructura del Sistema

```
/var/www/voip-panel/          # Aplicacion Laravel
/etc/kamailio/                # Configuracion Kamailio
/var/log/voip/                # Logs del sistema
/var/backups/voip/            # Backups automaticos
```

## Arquitectura

```
                    +-----------------+
                    |   Clientes SIP  |
                    +--------+--------+
                             |
                    +--------v--------+
                    |    Kamailio     |
                    |   (SIP Proxy)   |
                    +--------+--------+
                             |
        +--------------------+--------------------+
        |                    |                    |
+-------v-------+    +-------v-------+    +-------v-------+
|    MariaDB    |    |     Redis     |    |    Laravel    |
| (CDRs, Config)|    | (Stats, Cache)|    |  (Panel, API) |
+---------------+    +---------------+    +---------------+
                                                  |
                                          +-------v-------+
                                          |    Carriers   |
                                          +---------------+
```

## API Endpoints

### Publicos
- `GET /api/v1/health` - Estado del sistema
- `GET /api/v1/ping` - Health check simple

### Clientes
- `GET /api/v1/customers` - Listar clientes
- `POST /api/v1/customers` - Crear cliente
- `GET /api/v1/customers/{id}` - Ver cliente
- `PUT /api/v1/customers/{id}` - Actualizar cliente
- `GET /api/v1/customers/{id}/usage` - Uso actual

### Carriers
- `GET /api/v1/carriers` - Listar carriers
- `POST /api/v1/carriers` - Crear carrier
- `GET /api/v1/carriers/{id}/status` - Estado del carrier

### CDRs
- `GET /api/v1/cdrs` - Listar CDRs con filtros
- `GET /api/v1/cdrs/{uuid}` - Detalle de CDR
- `GET /api/v1/cdrs/{uuid}/trace` - Traza SIP

### Estadisticas
- `GET /api/v1/stats/realtime` - Metricas en tiempo real
- `GET /api/v1/stats/summary` - Resumen del periodo
- `GET /api/v1/stats/by-customer` - Stats por cliente

### QoS
- `GET /api/v1/qos/realtime` - Metricas QoS tiempo real
- `GET /api/v1/qos/trends` - Tendencias
- `GET /api/v1/qos/by-carrier` - QoS por carrier

### Fraude
- `GET /api/v1/fraud/incidents` - Incidentes detectados
- `GET /api/v1/fraud/rules` - Reglas de deteccion
- `GET /api/v1/fraud/risk-scores` - Puntuacion de riesgo

### Tarifas
- `GET /api/v1/rates/lcr-lookup` - Consulta LCR
- `GET /api/v1/rates/destinations` - Destinos
- `GET /api/v1/rates/carrier-rates` - Tarifas de carriers

### Reportes
- `GET /api/v1/reports` - Reportes programados
- `POST /api/v1/reports/{id}/trigger` - Ejecutar reporte

## Portal de Clientes

Acceso para clientes en `/portal`:
- Dashboard con estadisticas propias
- Historial de CDRs
- Gestion de IPs autorizadas
- Solicitud de nuevas IPs
- Gestion de perfil

## Comandos Artisan

### Clientes
```bash
php artisan customer:list
php artisan customer:create "Nombre" --ip=1.2.3.4
php artisan customer:reset-minutes {id}
```

### Carriers
```bash
php artisan carrier:list
php artisan carrier:test {id}
```

### Sistema
```bash
php artisan kamailio:reload
php artisan cleanup:all
php artisan stats:daily
```

## Webhooks

Eventos disponibles:
- `call.started` - Nueva llamada
- `call.answered` - Llamada contestada
- `call.ended` - Llamada terminada (incluye CDR)
- `customer.minutes_warning` - 80% de minutos consumidos
- `carrier.down` - Carrier caido
- `alert.created` - Nueva alerta

## Deteccion de Fraude

Tipos de deteccion:
- **Alto Costo** - Llamadas a prefijos premium (900, 901, etc.)
- **Picos de Trafico** - Aumento anormal vs media historica
- **Wangiri** - Muchas llamadas cortas (<6s)
- **Destinos Inusuales** - Paises/prefijos nunca usados
- **Alta Tasa de Fallos** - >80% de llamadas fallidas
- **Consumo Acelerado** - Uso de minutos >300% de lo normal

## Seguridad

- Autenticacion por IP para trafico SIP
- Rate limiting en API
- Fail2ban para intentos de acceso
- Blacklist automatica de IPs sospechosas
- HTTPS obligatorio para panel y API
- Audit log de acciones administrativas

## Monitoreo

### Health Check
```bash
curl https://tu-dominio.com/api/v1/health
```

### Metricas Prometheus
```bash
curl https://tu-dominio.com/api/v1/metrics
```

## Backup

Los backups se ejecutan automaticamente:
- Diario: 01:00 AM
- Ubicacion: `/var/backups/voip/daily/`

Backup manual:
```bash
php artisan db:backup --encrypt
```

## Troubleshooting

### Llamada no conecta
1. Verificar IP del cliente esta autorizada
2. Revisar logs de Kamailio: `tail -f /var/log/voip/kamailio/kamailio.log`
3. Verificar carrier activo y respondiendo

### 403 Forbidden
- IP no autorizada o en blacklist
- Verificar: `php artisan blacklist:list`

### Carrier marcado como down
- Revisar respuesta OPTIONS
- Test manual: `php artisan carrier:test {id}`

## Licencia

MIT License

## Soporte

Para reportar problemas: https://github.com/canyoneiro/voip-termination-system/issues
