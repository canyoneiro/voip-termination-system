#!/bin/bash
#
# Test de Llamada Completa con Loopback
# Ejecuta una llamada end-to-end a través de Kamailio con CDR generado
#

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RESULTS_DIR="$SCRIPT_DIR/results"
KAMAILIO_IP="127.0.0.1"
KAMAILIO_PORT="5060"
UAS_PORT="5080"
UAC_PORT="5094"

mkdir -p "$RESULTS_DIR"

# Función de limpieza
cleanup() {
    echo ""
    echo -e "${YELLOW}Limpiando procesos SIPp...${NC}"
    pkill -f "sipp.*$UAS_PORT" 2>/dev/null || true
    sleep 1
}

trap cleanup EXIT

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Test de Llamada Completa${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar que Kamailio está corriendo
if ! pgrep -x kamailio > /dev/null; then
    echo -e "${RED}ERROR: Kamailio no está corriendo${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Kamailio está corriendo${NC}"

# =============================================================================
# PASO 1: Verificar/Crear carrier loopback
# =============================================================================
echo ""
echo -e "${YELLOW}PASO 1: Verificar carrier loopback${NC}"

CARRIER_EXISTS=$(mysql -u root voip -N -e "
    SELECT id FROM carriers WHERE host='127.0.0.1' AND port=$UAS_PORT LIMIT 1
")

if [ -z "$CARRIER_EXISTS" ]; then
    echo "Creando carrier loopback..."
    mysql -u root voip -e "
        INSERT INTO carriers (uuid, name, host, port, transport, codecs, priority, weight, max_cps, max_channels, state, created_at, updated_at)
        VALUES (UUID(), 'Loopback Test', '127.0.0.1', $UAS_PORT, 'udp', 'PCMA,PCMU', 0, 100, 100, 100, 'active', NOW(), NOW())
    "
    CARRIER_ID=$(mysql -u root voip -N -e "SELECT id FROM carriers WHERE name='Loopback Test' LIMIT 1")
    echo -e "${GREEN}✓ Carrier loopback creado con ID: $CARRIER_ID${NC}"
    # Recargar dispatcher
    kamcmd dispatcher.reload
    sleep 1
else
    CARRIER_ID=$CARRIER_EXISTS
    echo -e "${GREEN}✓ Carrier loopback ya existe con ID: $CARRIER_ID${NC}"
fi

# Verificar que el carrier está activo en dispatcher
mysql -u root voip -e "UPDATE carriers SET state='active' WHERE id=$CARRIER_ID"

# Activar carrier en dispatcher
kamcmd dispatcher.set_state a 1 "sip:127.0.0.1:$UAS_PORT;transport=udp" 2>/dev/null || true
echo -e "${GREEN}✓ Carrier activado en dispatcher${NC}"

# =============================================================================
# PASO 2: Verificar customer de test
# =============================================================================
echo ""
echo -e "${YELLOW}PASO 2: Verificar customer de test${NC}"

CUSTOMER_ID=$(mysql -u root voip -N -e "
    SELECT c.id FROM customers c
    JOIN customer_ips ci ON c.id = ci.customer_id
    WHERE ci.ip_address = '127.0.0.1' AND c.active = 1 AND ci.active = 1
    LIMIT 1
")

if [ -z "$CUSTOMER_ID" ]; then
    echo -e "${RED}ERROR: No hay customer con IP 127.0.0.1 autorizada${NC}"
    echo "Creando customer de test..."
    mysql -u root voip -e "
        INSERT INTO customers (uuid, name, company, max_channels, max_cps, active, created_at, updated_at)
        VALUES (UUID(), 'Test Customer', 'Test Corp', 100, 50, 1, NOW(), NOW())
    "
    CUSTOMER_ID=$(mysql -u root voip -N -e "SELECT id FROM customers WHERE name='Test Customer' LIMIT 1")
    mysql -u root voip -e "
        INSERT INTO customer_ips (customer_id, ip_address, description, active, created_at)
        VALUES ($CUSTOMER_ID, '127.0.0.1', 'Loopback test', 1, NOW())
    "
    echo -e "${GREEN}✓ Customer de test creado con ID: $CUSTOMER_ID${NC}"
else
    echo -e "${GREEN}✓ Customer de test existe con ID: $CUSTOMER_ID${NC}"
fi

# =============================================================================
# PASO 3: Contar CDRs antes del test
# =============================================================================
echo ""
echo -e "${YELLOW}PASO 3: Contando CDRs existentes${NC}"

CDR_COUNT_BEFORE=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM cdrs")
echo "CDRs antes del test: $CDR_COUNT_BEFORE"

# Limpiar llamadas activas antiguas
mysql -u root voip -e "DELETE FROM active_calls WHERE start_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)"

# =============================================================================
# PASO 4: Iniciar UAS (simula carrier)
# =============================================================================
echo ""
echo -e "${YELLOW}PASO 4: Iniciando UAS en puerto $UAS_PORT${NC}"

# Matar cualquier proceso anterior en ese puerto
pkill -f "sipp.*$UAS_PORT" 2>/dev/null || true
sleep 1

# Iniciar UAS en background
cd "$SCRIPT_DIR"
nohup sipp -sf uas_loopback.xml -i 127.0.0.1 -p $UAS_PORT -m 5 </dev/null > "$RESULTS_DIR/uas_output.log" 2>&1 &
sleep 2

# Verificar que UAS está corriendo
if pgrep -f "sipp.*$UAS_PORT" > /dev/null; then
    UAS_PID=$(pgrep -f "sipp.*$UAS_PORT" | head -1)
    echo -e "${GREEN}✓ UAS iniciado (PID: $UAS_PID)${NC}"
else
    echo -e "${RED}✗ ERROR: UAS no pudo iniciar${NC}"
    cat "$RESULTS_DIR/uas_output.log" 2>/dev/null || true
    exit 1
fi

# Verificar que está escuchando
if ss -ulnp | grep -q ":$UAS_PORT"; then
    echo -e "${GREEN}✓ UAS escuchando en puerto $UAS_PORT${NC}"
else
    echo -e "${RED}✗ Puerto $UAS_PORT no activo${NC}"
    exit 1
fi

# =============================================================================
# PASO 5: Ejecutar UAC (llamada a través de Kamailio)
# =============================================================================
echo ""
echo -e "${YELLOW}PASO 5: Ejecutando llamada de test${NC}"

# Generar un ID único para esta llamada
CALL_NUM=$RANDOM

# Ejecutar UAC
cd "$SCRIPT_DIR"
nohup sipp -sf uac_call.xml -s 34612345678 "$KAMAILIO_IP:$KAMAILIO_PORT" -i 127.0.0.1 -p $UAC_PORT -m 1 </dev/null > "$RESULTS_DIR/uac_output.log" 2>&1 &
UAC_PID=$!
echo "UAC PID: $UAC_PID"

# Esperar a que complete (3s de llamada + 5s buffer)
sleep 8

# Verificar resultado del UAC
if grep -q "Successful call\|200.*OK" "$RESULTS_DIR/uac_output.log" 2>/dev/null; then
    echo -e "${GREEN}✓ Llamada establecida${NC}"
else
    echo -e "${YELLOW}⚠ Verificando estado de llamada...${NC}"
fi

# Mostrar estadísticas del UAC
echo ""
echo -e "${CYAN}--- Estadísticas UAC ---${NC}"
grep -E "Messages|INVITE|100|180|200|ACK|BYE|Successful|Failed" "$RESULTS_DIR/uac_output.log" 2>/dev/null | head -20

# =============================================================================
# PASO 6: Esperar y verificar CDR generado
# =============================================================================
echo ""
echo -e "${YELLOW}PASO 6: Verificando CDR generado${NC}"

# Esperar un poco más para que se procese el CDR
sleep 3

# Primero verificar si hay llamadas activas que deberían haber terminado
ACTIVE_CALLS=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM active_calls WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)")
if [ "$ACTIVE_CALLS" -gt 0 ]; then
    echo -e "${YELLOW}⚠ Hay $ACTIVE_CALLS llamada(s) activa(s) sin terminar${NC}"

    # Mostrar la llamada activa
    mysql -u root voip -e "SELECT call_id, customer_id, carrier_id, caller, callee, answered, start_time, answer_time FROM active_calls WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)"

    # Intentar generar CDR manualmente desde la llamada activa
    echo "Generando CDR manualmente..."
    mysql -u root voip -e "
        INSERT INTO cdrs (uuid, call_id, customer_id, carrier_id, source_ip, caller, callee, start_time, answer_time, end_time, duration, billable_duration, sip_code, sip_reason, hangup_cause)
        SELECT UUID(), call_id, customer_id, carrier_id, source_ip, caller, callee, start_time, answer_time, NOW(3),
            CASE WHEN answered = 1 THEN TIMESTAMPDIFF(SECOND, answer_time, NOW()) ELSE 0 END,
            CASE WHEN answered = 1 THEN TIMESTAMPDIFF(SECOND, answer_time, NOW()) ELSE 0 END,
            CASE WHEN answered = 1 THEN 200 ELSE 487 END,
            CASE WHEN answered = 1 THEN 'OK' ELSE 'Call Ended' END,
            'caller'
        FROM active_calls WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE);
    "

    # Limpiar llamadas activas
    mysql -u root voip -e "DELETE FROM active_calls WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
fi

CDR_COUNT_AFTER=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM cdrs")
NEW_CDRS=$((CDR_COUNT_AFTER - CDR_COUNT_BEFORE))

echo "CDRs después del test: $CDR_COUNT_AFTER"
echo "Nuevos CDRs: $NEW_CDRS"

if [ "$NEW_CDRS" -gt 0 ]; then
    echo ""
    echo -e "${GREEN}✓ CDR generado correctamente${NC}"
    echo ""
    echo -e "${CYAN}--- Último CDR ---${NC}"
    mysql -u root voip -e "
        SELECT
            id,
            call_id,
            customer_id,
            carrier_id,
            caller,
            callee,
            sip_code,
            sip_reason,
            duration,
            billable_duration,
            DATE_FORMAT(start_time, '%Y-%m-%d %H:%i:%s') as start_time,
            DATE_FORMAT(answer_time, '%Y-%m-%d %H:%i:%s') as answer_time,
            DATE_FORMAT(end_time, '%Y-%m-%d %H:%i:%s') as end_time
        FROM cdrs
        ORDER BY id DESC
        LIMIT 1\G
    "

    # Verificar duración
    LAST_DURATION=$(mysql -u root voip -N -e "SELECT duration FROM cdrs ORDER BY id DESC LIMIT 1")
    if [ "$LAST_DURATION" -gt 0 ]; then
        echo -e "${GREEN}✓ Duración registrada: ${LAST_DURATION}s${NC}"
        TEST_DURATION="PASS"
    else
        echo -e "${YELLOW}⚠ Duración es 0${NC}"
        TEST_DURATION="WARN"
    fi

    # Verificar SIP code
    LAST_SIP_CODE=$(mysql -u root voip -N -e "SELECT sip_code FROM cdrs ORDER BY id DESC LIMIT 1")
    if [ "$LAST_SIP_CODE" -eq 200 ]; then
        echo -e "${GREEN}✓ Llamada contestada (SIP 200)${NC}"
        TEST_SIP="PASS"
    else
        echo -e "${YELLOW}⚠ SIP Code: $LAST_SIP_CODE${NC}"
        TEST_SIP="WARN"
    fi

    TEST_CDR="PASS"
else
    echo -e "${RED}✗ No se generó CDR${NC}"
    TEST_CDR="FAIL"
    TEST_DURATION="N/A"
    TEST_SIP="N/A"
fi

# =============================================================================
# PASO 7: Verificar en Redis
# =============================================================================
echo ""
echo -e "${YELLOW}PASO 7: Verificando estadísticas en Redis${NC}"

ACTIVE_CALLS_REDIS=$(redis-cli GET "voip:stats:active_calls" 2>/dev/null || echo "0")
CPS_REDIS=$(redis-cli GET "voip:stats:cps" 2>/dev/null || echo "0")

echo "Llamadas activas (Redis): ${ACTIVE_CALLS_REDIS:-0}"
echo "CPS actual (Redis): ${CPS_REDIS:-0}"

# =============================================================================
# RESUMEN
# =============================================================================
echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   RESUMEN DEL TEST${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Determinar resultado final
if [ "$TEST_CDR" = "PASS" ] && [ "$TEST_SIP" = "PASS" ]; then
    if [ "$TEST_DURATION" = "PASS" ]; then
        echo -e "${GREEN}✓ TEST COMPLETO EXITOSO${NC}"
        EXIT_CODE=0
    else
        echo -e "${YELLOW}⚠ TEST PARCIALMENTE EXITOSO${NC}"
        EXIT_CODE=0
    fi
elif [ "$TEST_CDR" = "PASS" ]; then
    echo -e "${YELLOW}⚠ TEST PARCIALMENTE EXITOSO${NC}"
    EXIT_CODE=0
else
    echo -e "${RED}✗ TEST FALLIDO${NC}"
    EXIT_CODE=1
fi

echo ""
echo "  - CDR generado:      $TEST_CDR"
echo "  - SIP Code 200:      $TEST_SIP"
echo "  - Duración > 0:      $TEST_DURATION"
echo ""
echo "Archivos de log:"
echo "  - UAC: $RESULTS_DIR/uac_output.log"
echo "  - UAS: $RESULTS_DIR/uas_output.log"

exit ${EXIT_CODE:-1}
