# VoIP Termination System - Technical Notes

## System Overview

**Server:** sw1.tellmetelecom.com (165.22.130.17)
**SIP Port:** 9060 (changed from default 5060 for security)
**Panel:** https://sw1.tellmetelecom.com

## Current Carrier Configuration

### TellmeSwitch (ID: 2)
- **Host:** 35.195.222.129:5060
- **State:** active
- **Priority:** 5
- **Format:** `national_es` (sin prefijo 34)
- **Tech Prefix:** 15584
- **Function:** Carrier principal (intermediario hacia TalkQ)

### TalkQ (ID: 1)
- **Host:** 51.94.102.123:7447
- **State:** active (pero marcado inactivo por probing)
- **Priority:** 2
- **Format:** `international` (con prefijo 34)
- **Tech Prefix:** 18355
- **Function:** Carrier de destino final
- **Nota:** No acepta tráfico directo desde nuestra IP. Solo funciona a través de TellmeSwitch.

## Routing Logic

### Algoritmo de Selección
- **Algoritmo:** 9 (selección por prioridad)
- **Probing:** Habilitado (OPTIONS cada 30 segundos)
- **Failover:** Automático cuando carrier no responde

### Flujo de Llamada
```
Customer (82.223.64.58)
    → Kamailio (165.22.130.17:9060)
        → TellmeSwitch (35.195.222.129:5060)
            → TalkQ (51.94.102.123:7447)
                → Destino final
```

## SIP Message Format

### Para TellmeSwitch (national_es)
```
INVITE sip:15584680696867@35.195.222.129:5060 SIP/2.0
From: <sip:912013022@165.22.130.17:9060>
To: <sip:15584680696867@35.195.222.129:5060>
```
- **Destino:** tech_prefix + número (sin 34)
- **CLI:** Sin prefijo 34 (TellmeSwitch lo añade)

### Para TalkQ (international)
```
INVITE sip:1835534680696867@51.94.102.123:7447 SIP/2.0
From: <sip:34912013022@165.22.130.17:9060>
To: <sip:1835534680696867@51.94.102.123:7447>
```
- **Destino:** tech_prefix + 34 + número
- **CLI:** Con prefijo 34

## CLI (Caller ID) Handling

### Customer force_cli
- Almacenado en formato internacional: `34912013022`
- Campo: `customers.force_cli`

### Transformación según carrier
```php
// national_es: quitar 34
if ($carrier->number_format == 'national_es') {
    $cli = ltrim($cli, '34'); // 34912013022 → 912013022
}

// international: mantener 34
if ($carrier->number_format == 'international') {
    // 34912013022 → 34912013022 (sin cambio)
}
```

## Auto-Reload de Kamailio

### Modelos que disparan reload
| Modelo | Comando | Descripción |
|--------|---------|-------------|
| Carrier | `dispatcher.reload` | Cambios en carriers |
| CarrierIp | `dispatcher.reload` | IPs de carriers |
| Customer | `permissions.addressReload` | Estado activo/inactivo |
| CustomerIp | `permissions.addressReload` | IPs autorizadas |
| IpBlacklist | `htable.reload` | Lista negra |

### Archivos clave
- `/app/Services/KamailioService.php` - Servicio centralizado
- `/app/Traits/ReloadsKamailio.php` - Trait para modelos

### Comando manual
```bash
php artisan kamailio:dispatcher-reload
kamcmd dispatcher.reload
kamcmd permissions.addressReload
```

## Database Views para Kamailio

### kamailio_dispatcher
```sql
CREATE VIEW kamailio_dispatcher AS
SELECT
    1 AS setid,
    id,
    CONCAT('sip:', host, ':', port, ';transport=', transport) AS destination,
    8 AS flags,  -- Probing enabled
    priority,
    CONCAT('weight=', weight, ';duid=', id) AS attrs,
    name AS description
FROM carriers
WHERE state = 'active'
ORDER BY priority, id
```

### kamailio_address
```sql
CREATE VIEW kamailio_address AS
SELECT
    1 AS grp,
    ci.ip_address AS ip_addr,
    32 AS mask,
    5060 AS port,
    '' AS tag
FROM customer_ips ci
JOIN customers c ON ci.customer_id = c.id
WHERE ci.active = 1 AND c.active = 1
```

## Dispatcher FLAGS

| Flag | Significado |
|------|-------------|
| A | Active |
| I | Inactive |
| T | Trying (probing in progress) |
| P | Probing enabled |
| X | Probing disabled |

Ejemplos:
- `AP` = Active + Probing (carrier funcionando)
- `IP` = Inactive + Probing (carrier caído, se está probando)
- `TP` = Trying + Probing (carrier en proceso de verificación)

## Kamailio Config Highlights

**Archivo:** `/etc/kamailio/kamailio.cfg`
**Copia en repo:** `/var/www/voip-panel/config/kamailio/kamailio.cfg`

### Parámetros importantes
```
#!define LISTEN_PORT 9060
modparam("dispatcher", "ds_probing_mode", 1)
modparam("dispatcher", "ds_ping_interval", 30)
modparam("dispatcher", "ds_probing_threshold", 3)
```

### Manipulación de headers (líneas ~648-680)
- `$rU` = Request-URI user
- `$rd` = Request-URI domain
- `$rp` = Request-URI port
- `uac_replace_to()` = Modifica To header
- `uac_replace_from()` = Modifica From header

## Troubleshooting

### Carrier no recibe llamadas
1. Verificar estado: `kamcmd dispatcher.list`
2. Si FLAGS = IP/TP, el carrier no responde OPTIONS
3. Verificar conectividad: `nc -zuv IP PORT`

### CLI rechazado por carrier
1. Verificar `force_cli` del customer
2. Verificar `number_format` del carrier
3. El CLI debe estar autorizado en el carrier de destino

### Cambios no se aplican
1. Verificar que el modelo usa `ReloadsKamailio` trait
2. Manual: `kamcmd dispatcher.reload`
3. Ver logs: `tail -f /var/log/syslog | grep kamailio`

### Ver trazas SIP
```sql
SELECT * FROM sip_trace
WHERE callid = 'xxx'
ORDER BY id;
```

## Security Notes

- SSH: Solo acceso por clave (puerto 22)
- SIP: Puerto 9060 (no estándar)
- Fail2ban: Configurado para SSH, Kamailio, Nginx
- Probing: Detecta y desactiva carriers caídos automáticamente

## Files Reference

| Archivo | Descripción |
|---------|-------------|
| `/etc/kamailio/kamailio.cfg` | Config principal Kamailio |
| `/var/www/voip-panel/` | Aplicación Laravel |
| `/var/log/syslog` | Logs de Kamailio |
| `/var/www/voip-panel/storage/logs/` | Logs de Laravel |
| `/root/CLAUDE.md` | Especificaciones originales del proyecto |
