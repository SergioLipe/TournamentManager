#!/usr/bin/env bash
# Exercita os controlos de segurança contra o servidor local.
BASE="${BASE:-http://127.0.0.1:8765}"
M="${MYSQL:-/c/xampp/mysql/bin/mysql.exe}"
JAR=$(mktemp)
pass=0; fail=0
BD="${BD:-torneio_db}"

# Nem o id do tema nem o do competidor são fixos: dependem da ordem por que a
# base de dados foi semeada. Descobrem-se, em vez de se assumir que são 1.
TEMA=$($M -u root -N -e "SELECT id FROM $BD.tema WHERE publico=1 ORDER BY id LIMIT 1;" 2>/dev/null | tr -d '\r')
TEMA="${TEMA:-1}"
COMP=$($M -u root -N -e "SELECT id FROM $BD.competidor WHERE TemaId=$TEMA ORDER BY id LIMIT 1;" 2>/dev/null | tr -d '\r')
COMP="${COMP:-1}"
# O mesmo vale para o dono do tema privado da secção 6: a chave estrangeira
# fk_tema_utilizador exige um utilizador que exista mesmo. Numa base semeada
# de fresco o admin pode não ser o id 1.
DONO=$($M -u root -N -e "SELECT id FROM $BD.utilizador ORDER BY id LIMIT 1;" 2>/dev/null | tr -d '\r')
DONO="${DONO:-1}"

ok()   { printf "  \033[32mPASS\033[0m %s\n" "$1"; pass=$((pass+1)); }
bad()  { printf "  \033[31mFAIL\033[0m %s — %s\n" "$1" "$2"; fail=$((fail+1)); }
check(){ [ "$2" = "$3" ] && ok "$1" || bad "$1" "esperado '$3', obtido '$2'"; }

echo "=== 1. SQL injection no login ==="
for payload in "' OR '1'='1" "admin'--" "' OR 1=1#" "'; DROP TABLE utilizador;--"; do
  tok=$(curl -s -c "$JAR" "$BASE/Login.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
  body=$(curl -s -b "$JAR" -c "$JAR" -X POST "$BASE/Login.php" \
      --data-urlencode "utilizador=$payload" --data-urlencode "password=x" --data-urlencode "csrf=$tok")
  if echo "$body" | grep -q "Wrong username or password"; then ok "rejeitado: $payload"
  else bad "payload: $payload" "não foi rejeitado"; fi
done
still=$($M -u root $BD -N -e "SELECT COUNT(*) FROM utilizador;" 2>/dev/null)
check "tabela utilizador intacta" "$still" "1"

echo
echo "=== 2. Login legítimo ==="
tok=$(curl -s -c "$JAR" "$BASE/Login.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
code=$(curl -s -b "$JAR" -c "$JAR" -o /dev/null -w "%{http_code}" -X POST "$BASE/Login.php" \
    -d "utilizador=admin" -d "password=localtest123" -d "csrf=$tok")
check "login correcto redirecciona (302)" "$code" "302"

tok2=$(curl -s -c "$JAR" "$BASE/Login.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
code=$(curl -s -b "$JAR" -o /dev/null -w "%{http_code}" -X POST "$BASE/Login.php" \
    -d "utilizador=admin" -d "password=WRONGPASS" -d "csrf=$tok2")
check "password errada NÃO redirecciona (200)" "$code" "200"

echo
echo "=== 3. CSRF ==="
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/Login.php" -d "utilizador=admin" -d "password=localtest123")
check "login sem token CSRF" "$code" "400"
# Anónimo: o exigirLogin() corre antes do CSRF, por isso sai um 302 para o
# login. Continua negado — só não é o 400 do CSRF.
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/apagar.php" -d "competidorId=$COMP")
check "apagar anónimo redirecciona para login" "$code" "302"

# O CSRF só se consegue testar já autenticado.
LJ=$(mktemp)
ltok=$(curl -s -c "$LJ" "$BASE/Login.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
curl -s -b "$LJ" -c "$LJ" -o /dev/null -X POST "$BASE/Login.php" \
    -d "utilizador=admin" -d "password=localtest123" -d "csrf=$ltok"
code=$(curl -s -b "$LJ" -o /dev/null -w "%{http_code}" -X POST "$BASE/apagar.php" -d "competidorId=$COMP")
check "apagar autenticado mas sem CSRF" "$code" "400"
antes_csrf=$($M -u root $BD -N -e "SELECT COUNT(*) FROM competidor WHERE id=$COMP;")
check "e nao apagou nada" "$antes_csrf" "1"
rm -f "$LJ"

echo
echo "=== 4. apagar.php exige POST + sessão ==="
antes=$($M -u root $BD -N -e "SELECT COUNT(*) FROM competidor;")
curl -s -o /dev/null "$BASE/apagar.php?id=$COMP"
curl -s -o /dev/null "$BASE/apagar.php?competidorId=$COMP"
depois=$($M -u root $BD -N -e "SELECT COUNT(*) FROM competidor;")
check "GET nao apaga nada (era o bug antigo)" "$depois" "$antes"

echo
echo "=== 5. api/competidores.php ==="
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/api/competidores.php")
check "GET rejeitado" "$code" "405"
# Contar por "imagem": o "id" também aparece no objecto "tema" da resposta.
n=$(curl -s -X POST "$BASE/api/competidores.php" -d "temaId=$TEMA" -d "quantos=8" | grep -o '"imagem"' | wc -l)
check "POST devolve 8 competidores" "$n" "8"
n=$(curl -s -X POST "$BASE/api/competidores.php" -d "temaId=$TEMA" -d "quantos=999" | grep -o '"imagem"' | wc -l)
check "quantos acima do maximo limitado a 16" "$n" "16"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/api/competidores.php" -d "temaId=99999")
check "tema inexistente = 404" "$code" "404"

echo
echo "=== 6. Tema privado invisível a anónimos ==="
$M -u root $BD -e "INSERT INTO tema (id,nome,utilizadorId,publico) VALUES (99,'Privado',$DONO,0) ON DUPLICATE KEY UPDATE publico=0;"
$M -u root $BD -e "INSERT IGNORE INTO competidor (id,nome,imagem,TemaId) VALUES (9001,'p1','x.jpg',99),(9002,'p2','y.jpg',99);"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/api/competidores.php" -d "temaId=99")
check "tema privado nega anónimo" "$code" "404"

echo
echo "=== 7. api/resultado.php ==="
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/api/resultado.php" -d 'resultados=[{"v":1,"p":2,"f":1}]')
check "sem CSRF = 400" "$code" "400"

CJ=$(mktemp)
tok=$(curl -s -c "$CJ" "$BASE/index.php" | grep -oP 'data-csrf="\K[a-f0-9]+')
# A resposta traz o tema antes dos competidores ("tema":{"id":..}), por isso
# um grep solto por "id" apanhava primeiro o id do TEMA e punha-o no $A. O
# resultado.php rejeitava-o — com razão — e as três verificações seguintes
# falhavam sem que houvesse nada de errado com o servidor.
ids=$(curl -s -b "$CJ" -X POST "$BASE/api/competidores.php" -d "temaId=$TEMA" -d "quantos=2" \
      | sed 's!.*"competidores"!!' | grep -oP '"id":\K[0-9]+' | tr '\n' ' ')
A=$(echo $ids | cut -d' ' -f1); B=$(echo $ids | cut -d' ' -f2)

vA=$($M -u root $BD -N -e "SELECT nBatalhasVencidas FROM competidor WHERE id=$A;")
tA=$($M -u root $BD -N -e "SELECT nTorneiosVencidos FROM competidor WHERE id=$A;")
pB=$($M -u root $BD -N -e "SELECT nBatalhasPerdidas FROM competidor WHERE id=$B;")

resp=$(curl -s -b "$CJ" -X POST "$BASE/api/resultado.php" \
    --data-urlencode "resultados=[{\"v\":$A,\"p\":$B,\"f\":1}]" --data-urlencode "csrf=$tok")
echo "  resposta: $resp"

vA2=$($M -u root $BD -N -e "SELECT nBatalhasVencidas FROM competidor WHERE id=$A;")
tA2=$($M -u root $BD -N -e "SELECT nTorneiosVencidos FROM competidor WHERE id=$A;")
pB2=$($M -u root $BD -N -e "SELECT nBatalhasPerdidas FROM competidor WHERE id=$B;")

check "vencedor da final ganha batalha (bug antigo)" "$vA2" "$((vA+1))"
check "vencedor da final ganha torneio"              "$tA2" "$((tA+1))"
check "perdedor leva derrota"                        "$pB2" "$((pB+1))"

code=$(curl -s -b "$CJ" -o /dev/null -w "%{http_code}" -X POST "$BASE/api/resultado.php" \
    --data-urlencode "resultados=[{\"v\":$A,\"p\":9001,\"f\":1}]" --data-urlencode "csrf=$tok")
check "competidores de temas diferentes = 400" "$code" "400"

code=$(curl -s -b "$CJ" -o /dev/null -w "%{http_code}" -X POST "$BASE/api/resultado.php" \
    --data-urlencode "resultados=[{\"v\":$A,\"p\":$B,\"f\":1},{\"v\":$B,\"p\":$A,\"f\":1}]" --data-urlencode "csrf=$tok")
check "duas finais = 400" "$code" "400"

code=$(curl -s -b "$CJ" -o /dev/null -w "%{http_code}" -X POST "$BASE/api/resultado.php" \
    --data-urlencode "resultados=[{\"v\":$A,\"p\":$A,\"f\":1}]" --data-urlencode "csrf=$tok")
check "vencedor = perdedor rejeitado" "$code" "400"

echo
echo "=== 8. Registo valida ==="
tok=$(curl -s -c "$JAR" "$BASE/Registar.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
body=$(curl -s -b "$JAR" -X POST "$BASE/Registar.php" -d "utilizador=admin" -d "password=abcd1234" -d "confirmacao=abcd1234" -d "csrf=$tok")
echo "$body" | grep -q "already taken" && ok "username duplicado rejeitado" || bad "username duplicado" "aceite"
tok=$(curl -s -c "$JAR" "$BASE/Registar.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
body=$(curl -s -b "$JAR" -X POST "$BASE/Registar.php" -d "utilizador=novo" -d "password=short" -d "confirmacao=short" -d "csrf=$tok")
echo "$body" | grep -q "at least 8" && ok "password curta rejeitada" || bad "password curta" "aceite"

echo
echo "=== 9. XSS escapado ==="
$M -u root $BD -e "UPDATE competidor SET nome='<script>alert(1)</script>' WHERE id=$A;"
body=$(curl -s "$BASE/Estatisticas.php?temaId=$TEMA")
echo "$body" | grep -q "<script>alert(1)</script>" && bad "XSS" "script cru no HTML" || ok "nome perigoso escapado"
echo "$body" | grep -q "&lt;script&gt;" && ok "escapado como entidades" || bad "escape" "entidades ausentes"
$M -u root $BD -e "UPDATE competidor SET nome='restaurado' WHERE id=$A;"

echo
$M -u root $BD -e "DELETE FROM competidor WHERE id IN (9001,9002); DELETE FROM tema WHERE id=99;" 2>/dev/null
rm -f "$JAR" "$CJ"
echo "RESULTADO: $pass passaram, $fail falharam"
exit $((fail > 0 ? 1 : 0))
