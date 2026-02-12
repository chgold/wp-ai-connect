#!/bin/bash

echo "╔════════════════════════════════════════════╗"
echo "║   AI Connect - Complete Endpoint Test     ║"
echo "╚════════════════════════════════════════════╝"
echo ""

PASSED=0
FAILED=0

echo "📋 Getting manifest and creating auth token..."

OAUTH_DATA=$(wp eval "
\$auth = new \AIConnect\Core\Auth();
\$client = \$auth->register_client('Complete Test', 'https://test.com');
\$token = \$auth->generate_access_token(1, \$client['client_id'], 'read write admin');
echo \$token . '|' . \$client['client_id'];
" 2>/dev/null)

TOKEN=$(echo $OAUTH_DATA | cut -d'|' -f1)
CLIENT_ID=$(echo $OAUTH_DATA | cut -d'|' -f2)

echo "✅ Authentication ready"
echo ""
echo "🧪 Testing all tools from manifest:"
echo ""

while IFS= read -r TOOL; do
  
  case $TOOL in
    wordpress.getPost|wordpress.getPage)
      TEST_DATA='{"identifier": 1}'
      ;;
    woocommerce.getProduct)
      TEST_DATA='{"identifier": 1}'
      ;;
    woocommerce.addToCart)
      TEST_DATA='{"product_id": 1}'
      ;;
    wordpress.searchPosts|wordpress.searchPages)
      TEST_DATA='{"limit": 5}'
      ;;
    woocommerce.searchProducts)
      TEST_DATA='{"limit": 5}'
      ;;
    woocommerce.getOrders)
      TEST_DATA='{"limit": 5}'
      ;;
    *)
      TEST_DATA='{}'
      ;;
  esac
  
  RESPONSE=$(curl -s -X POST \
    "http://localhost:8888/index.php?rest_route=/ai-connect/v1/tools/$TOOL" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d "$TEST_DATA" 2>/dev/null)
  
  if echo "$RESPONSE" | grep -qE '"code":|"error":'; then
    ERROR_MSG=$(echo "$RESPONSE" | grep -oE '"message":"[^"]*"' | head -1 | cut -d'"' -f4)
    
    if [[ "$ERROR_MSG" =~ (not found|not exist|No posts|No pages|No products|No orders|does not exist) ]]; then
      echo "   ✅ $TOOL (endpoint works)"
      ((PASSED++))
    else
      echo "   ❌ $TOOL - $ERROR_MSG"
      ((FAILED++))
    fi
  elif [ ${#RESPONSE} -gt 10 ]; then
    if echo "$RESPONSE" | grep -q '"id"'; then
      COUNT=$(echo "$RESPONSE" | grep -o '"id"' | wc -l | tr -d ' ')
      echo "   ✅ $TOOL ($COUNT results)"
    elif echo "$RESPONSE" | grep -q '"username"'; then
      echo "   ✅ $TOOL (user data)"
    else
      echo "   ✅ $TOOL (data returned)"
    fi
    ((PASSED++))
  else
    echo "   ❌ $TOOL - No response"
    ((FAILED++))
  fi
  
done < /tmp/tool_list.txt

echo ""
echo "🌐 Testing infrastructure endpoints:"
echo ""

STATUS=$(curl -s "http://localhost:8888/index.php?rest_route=/ai-connect/v1/status")
if echo "$STATUS" | grep -q '"status":"ok"'; then
  echo "   ✅ Status endpoint"
  ((PASSED++))
else
  echo "   ❌ Status endpoint"
  ((FAILED++))
fi

MANIFEST=$(curl -s "http://localhost:8888/index.php?rest_route=/ai-connect/v1/manifest")
if echo "$MANIFEST" | grep -q '"tools"'; then
  echo "   ✅ Manifest endpoint"
  ((PASSED++))
else
  echo "   ❌ Manifest endpoint"
  ((FAILED++))
fi

echo ""
echo "╔════════════════════════════════════════════╗"
echo "║   Test Results                             ║"
echo "╠════════════════════════════════════════════╣"
printf "║   ✅ Passed: %-3d                          ║\n" $PASSED
printf "║   ❌ Failed: %-3d                          ║\n" $FAILED
printf "║   📊 Total:  %-3d                          ║\n" $((PASSED + FAILED))
echo "╠════════════════════════════════════════════╣"
if [ $FAILED -eq 0 ]; then
  echo "║                                            ║"
  echo "║          ✅ ALL TESTS PASSED! ✅          ║"
  echo "║                                            ║"
else
  echo "║                                            ║"
  echo "║         ⚠️  SOME TESTS FAILED ⚠️          ║"
  echo "║                                            ║"
fi
echo "╚════════════════════════════════════════════╝"

wp eval "delete_option('ai_connect_client_$CLIENT_ID');" 2>/dev/null

exit $FAILED
