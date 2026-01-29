#!/bin/bash

# VoIP System Test Script
# Tests Kamailio call processing

set -e

KAMAILIO_IP="127.0.0.1"
KAMAILIO_PORT="5060"
TEST_DIR="/opt/voip-tests"
RESULTS_DIR="/opt/voip-tests/results"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "==========================================="
echo "VoIP System Test Suite"
echo "==========================================="
echo ""

mkdir -p $RESULTS_DIR

# Test 1: Check Kamailio is running
echo -n "Test 1: Kamailio is running... "
if pgrep -x kamailio > /dev/null; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC} - Kamailio not running"
    exit 1
fi

# Test 2: Check Kamailio is listening on port 5060
echo -n "Test 2: Kamailio listening on UDP 5060... "
if ss -uln | grep -q ":5060 "; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC} - Not listening on 5060"
    exit 1
fi

# Test 3: Basic SIP OPTIONS request
echo -n "Test 3: SIP OPTIONS response... "
OPTIONS_RESPONSE=$(sipp -sn uac $KAMAILIO_IP:$KAMAILIO_PORT -m 1 -timeout 5s -trace_err 2>&1 || true)
if echo "$OPTIONS_RESPONSE" | grep -q "Successful call"; then
    echo -e "${GREEN}PASS${NC}"
else
    # Try manual OPTIONS
    echo ""
    echo "  Trying manual OPTIONS request..."
    MANUAL_OPTIONS=$(cat <<EOF | nc -u -w 2 $KAMAILIO_IP $KAMAILIO_PORT
OPTIONS sip:$KAMAILIO_IP:$KAMAILIO_PORT SIP/2.0
Via: SIP/2.0/UDP 127.0.0.1:12345;branch=z9hG4bK-test
From: <sip:test@127.0.0.1>;tag=test123
To: <sip:$KAMAILIO_IP:$KAMAILIO_PORT>
Call-ID: test-options-123@127.0.0.1
CSeq: 1 OPTIONS
Max-Forwards: 70
Content-Length: 0

EOF
)
    if echo "$MANUAL_OPTIONS" | grep -q "SIP/2.0 "; then
        RESPONSE_CODE=$(echo "$MANUAL_OPTIONS" | head -1 | awk '{print $2}')
        echo -e "  ${GREEN}PASS${NC} - Got response $RESPONSE_CODE"
    else
        echo -e "  ${YELLOW}SKIP${NC} - No response (carrier may not be reachable)"
    fi
fi

# Test 4: Check Redis connectivity
echo -n "Test 4: Redis connectivity... "
if redis-cli ping 2>/dev/null | grep -q "PONG"; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC} - Redis not responding"
fi

# Test 5: Check MySQL connectivity
echo -n "Test 5: MySQL connectivity... "
if mysql -u root voip -e "SELECT 1" > /dev/null 2>&1; then
    echo -e "${GREEN}PASS${NC}"
else
    echo -e "${RED}FAIL${NC} - MySQL not responding"
fi

# Test 6: Check customer IP authorization
echo -n "Test 6: Customer IP authorization configured... "
CUSTOMER_IPS=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM customer_ips WHERE active = 1")
if [ "$CUSTOMER_IPS" -gt 0 ]; then
    echo -e "${GREEN}PASS${NC} ($CUSTOMER_IPS IPs configured)"
else
    echo -e "${YELLOW}WARN${NC} - No customer IPs configured"
fi

# Test 7: Check carriers configured
echo -n "Test 7: Carriers configured... "
CARRIERS=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM carriers WHERE state = 'active'")
if [ "$CARRIERS" -gt 0 ]; then
    echo -e "${GREEN}PASS${NC} ($CARRIERS active carriers)"
else
    echo -e "${YELLOW}WARN${NC} - No active carriers"
fi

# Test 8: Check rates configured
echo -n "Test 8: Destination rates configured... "
RATES=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM carrier_rates WHERE active = 1")
if [ "$RATES" -gt 0 ]; then
    echo -e "${GREEN}PASS${NC} ($RATES carrier rates)"
else
    echo -e "${YELLOW}WARN${NC} - No rates configured"
fi

# Test 9: SIP INVITE test (will get 403 or route to carrier)
echo ""
echo "Test 9: SIP INVITE processing test..."
INVITE_TEST=$(sipp -sf $TEST_DIR/uac_basic.xml $KAMAILIO_IP:$KAMAILIO_PORT \
    -s 34666123456 \
    -i 127.0.0.1 \
    -p 15060 \
    -m 1 \
    -timeout 10s \
    -trace_err \
    -trace_msg \
    -message_file $RESULTS_DIR/invite_${TIMESTAMP}.log \
    2>&1 || true)

# Check last response
if [ -f "$RESULTS_DIR/invite_${TIMESTAMP}.log" ]; then
    RESPONSE=$(grep "SIP/2.0" $RESULTS_DIR/invite_${TIMESTAMP}.log | tail -1 | awk '{print $2}')
    case $RESPONSE in
        100|180|183|200)
            echo -e "  ${GREEN}PASS${NC} - Call processed, response: $RESPONSE"
            ;;
        403)
            echo -e "  ${YELLOW}INFO${NC} - Forbidden (IP auth failed as expected for external test)"
            ;;
        404)
            echo -e "  ${YELLOW}INFO${NC} - Not Found (no route or carrier)"
            ;;
        408)
            echo -e "  ${YELLOW}INFO${NC} - Timeout (carrier not reachable)"
            ;;
        480|486|487|503|603)
            echo -e "  ${GREEN}PASS${NC} - Call rejected normally: $RESPONSE"
            ;;
        *)
            echo -e "  ${YELLOW}INFO${NC} - Response: $RESPONSE"
            ;;
    esac
else
    echo -e "  ${YELLOW}SKIP${NC} - No response captured"
fi

# Test 10: Check Kamailio statistics
echo ""
echo "Test 10: Kamailio statistics..."
if command -v kamctl &> /dev/null; then
    echo "  Active dialogs: $(kamctl stats dialog 2>/dev/null | grep active_dialogs | awk -F= '{print $2}' || echo 'N/A')"
    echo "  RX requests: $(kamctl stats core 2>/dev/null | grep rcv_requests | awk -F= '{print $2}' || echo 'N/A')"
    echo "  TX replies: $(kamctl stats core 2>/dev/null | grep fwd_replies | awk -F= '{print $2}' || echo 'N/A')"
else
    echo -e "  ${YELLOW}SKIP${NC} - kamctl not available"
fi

echo ""
echo "==========================================="
echo "Test Summary"
echo "==========================================="
echo "Results saved to: $RESULTS_DIR"
echo ""

# Final check - look for any CDRs created
CDRS=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM cdrs WHERE start_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE)")
echo "CDRs created in last 5 minutes: $CDRS"

ACTIVE_CALLS=$(mysql -u root voip -N -e "SELECT COUNT(*) FROM active_calls")
echo "Active calls: $ACTIVE_CALLS"

echo ""
echo "Done!"
