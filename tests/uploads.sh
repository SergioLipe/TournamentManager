#!/usr/bin/env bash
# Exercita o upload de imagens: o caminho mais perigoso da aplicação.
BASE="${BASE:-http://127.0.0.1:8765}"
M="${MYSQL:-/c/xampp/mysql/bin/mysql.exe}"
PROJ="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
J=$(mktemp)
# O curl e um binario Windows: nao abre caminhos /tmp do Git Bash, aborta
# antes de enviar. As fixtures tem de estar dentro do projecto.
cd "$PROJ" || exit 1
TMP="$PROJ/.testfixtures"; rm -rf "$TMP"; mkdir -p "$TMP"
pass=0; fail=0
ok(){ echo "  PASS $1"; pass=$((pass+1)); }
bad(){ echo "  FAIL $1 — $2"; fail=$((fail+1)); }
check(){ [ "$2" = "$3" ] && ok "$1" || bad "$1" "esperado '$3', obtido '$2'"; }
q(){ $M -u root --default-character-set=utf8mb4 torneio_db -N -e "$1" | tr -d '\r'; }

# --- entrar ---
tok=$(curl -s -c "$J" "$BASE/Login.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
curl -s -b "$J" -c "$J" -o /dev/null -X POST "$BASE/Login.php" -d "utilizador=admin" -d "password=localtest123" -d "csrf=$tok"
tok=$(curl -s -b "$J" -c "$J" "$BASE/CriarTema.php" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
[ -n "$tok" ] && ok "autenticado, token obtido" || bad "autenticação" "sem token"

# --- ficheiros de teste ---
cp "$PROJ/Imagens/Portuguese-Food/Ramen.jpg" "$TMP/boa.jpg"
printf '<?php system($_GET["c"]); ?>' > "$TMP/malicioso.jpg"       # PHP disfarçado
printf 'GIF89a<?php echo 1; ?>' > "$TMP/falso.gif"                  # magic bytes falsos
head -c 6000000 /dev/urandom > "$TMP/enorme.jpg"                    # acima do limite

echo
echo "=== 1. criar tema com uma imagem válida ==="
curl -s -b "$J" -o /dev/null -X POST "$BASE/AdicionarCompetidor.php" \
    -F "csrf=$tok" -F "criar_tema=1" -F "nomeTema=TesteUpload" -F "imagens[]=@.testfixtures/boa.jpg"
tid=$(q "SELECT id FROM tema WHERE nome='TesteUpload';")
[ -n "$tid" ] && ok "tema criado (id=$tid)" || bad "criar tema" "não foi criado"
n=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
check "1 competidor inserido" "$n" "1"

img=$(q "SELECT imagem FROM competidor WHERE TemaId=$tid LIMIT 1;")
echo "  caminho guardado: $img"
case "$img" in
  Imagens/temas/$tid/*) ok "guardado na pasta derivada do id do tema";;
  *) bad "caminho" "esperava Imagens/temas/$tid/, obtive $img";;
esac
base=$(basename "$img")
if echo "$base" | grep -qE '^[a-f0-9]{16}\.(jpg|png|gif|webp|avif)$'; then
  ok "nome do ficheiro gerado ($base), não o do cliente"
else bad "nome do ficheiro" "'$base' não parece gerado"; fi
[ -f "$PROJ/$img" ] && ok "ficheiro existe em disco" || bad "ficheiro" "não está em disco"

echo
echo "=== 2. PHP disfarçado de .jpg é rejeitado ==="
antes=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
body=$(curl -s -b "$J" -X POST "$BASE/AdicionarCompetidor.php" \
    -F "csrf=$tok" -F "tema_existente=1" -F "temaId=$tid" -F "imagens[]=@.testfixtures/malicioso.jpg")
depois=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
check "não foi inserido" "$depois" "$antes"
find "$PROJ/Imagens/temas/$tid" -name '*.php' 2>/dev/null | grep -q . && bad "ficheiro .php" "foi gravado" || ok "nada de .php gravado"

echo
echo "=== 3. magic bytes GIF falsos com PHP dentro ==="
antes=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
curl -s -b "$J" -o /dev/null -X POST "$BASE/AdicionarCompetidor.php" \
    -F "csrf=$tok" -F "tema_existente=1" -F "temaId=$tid" -F "imagens[]=@.testfixtures/falso.gif"
depois=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
check "GIF inválido rejeitado" "$depois" "$antes"

echo
echo "=== 4. ficheiro acima do limite ==="
antes=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
curl -s -b "$J" -o /dev/null -X POST "$BASE/AdicionarCompetidor.php" \
    -F "csrf=$tok" -F "tema_existente=1" -F "temaId=$tid" -F "imagens[]=@.testfixtures/enorme.jpg"
depois=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
check "ficheiro de 6MB rejeitado" "$depois" "$antes"

echo
echo "=== 5. travessia de directório no nome do tema ==="
curl -s -b "$J" -o /dev/null -X POST "$BASE/AdicionarCompetidor.php" \
    -F "csrf=$tok" -F "criar_tema=1" -F "nomeTema=../../evil" -F "imagens[]=@.testfixtures/boa.jpg"
n=$(q "SELECT COUNT(*) FROM tema WHERE nome LIKE '%..%';")
check "nome de tema com ../ rejeitado" "$n" "0"
[ -d "$PROJ/../evil" ] && bad "travessia" "criou pasta fora do projecto" || ok "nada criado fora do projecto"

echo
echo "=== 6. tema de outro utilizador ==="
$M -u root torneio_db -e "INSERT INTO utilizador (id,username,password) VALUES (77,'outro','x') ON DUPLICATE KEY UPDATE username='outro';
INSERT INTO tema (id,nome,utilizadorId,publico) VALUES (78,'DoOutro',77,0) ON DUPLICATE KEY UPDATE utilizadorId=77;" 2>/dev/null
antes=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=78;")
curl -s -b "$J" -o /dev/null -X POST "$BASE/AdicionarCompetidor.php" \
    -F "csrf=$tok" -F "tema_existente=1" -F "temaId=78" -F "imagens[]=@.testfixtures/boa.jpg"
depois=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=78;")
check "não deixa enviar para tema alheio" "$depois" "$antes"

echo
echo "=== 7. apagar competidor remove o ficheiro ==="
cid=$(q "SELECT id FROM competidor WHERE TemaId=$tid LIMIT 1;")
cimg=$(q "SELECT imagem FROM competidor WHERE id=$cid;")
tok2=$(curl -s -b "$J" -c "$J" "$BASE/CriarTema.php?temaId=$tid" | grep -oP 'name="csrf" value="\K[a-f0-9]+' | head -1)
curl -s -b "$J" -o /dev/null -X POST "$BASE/apagar.php" -d "csrf=$tok2" -d "competidorId=$cid"
n=$(q "SELECT COUNT(*) FROM competidor WHERE id=$cid;")
check "competidor apagado da BD" "$n" "0"
[ -f "$PROJ/$cimg" ] && bad "ficheiro órfão" "$cimg ficou em disco" || ok "ficheiro removido do disco"

echo
echo "=== 8. apagar tema faz cascata ==="
curl -s -b "$J" -o /dev/null -X POST "$BASE/apagar.php" -d "csrf=$tok2" -d "temaId=$tid"
n=$(q "SELECT COUNT(*) FROM tema WHERE id=$tid;")
check "tema apagado" "$n" "0"
n=$(q "SELECT COUNT(*) FROM competidor WHERE TemaId=$tid;")
check "competidores em cascata" "$n" "0"

# limpeza
$M -u root torneio_db -e "DELETE FROM tema WHERE utilizadorId=77 OR nome IN ('TesteUpload','../../evil'); DELETE FROM utilizador WHERE id=77;" 2>/dev/null
rm -rf "$TMP" "$J"; rm -rf "$PROJ/Imagens/temas" 2>/dev/null
echo
echo "RESULTADO: $pass passaram, $fail falharam"
exit $((fail > 0 ? 1 : 0))
