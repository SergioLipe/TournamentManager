#!/usr/bin/env bash
# Corre as três suites e resume o resultado.
set -uo pipefail

AQUI="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
export PROJ="$(cd "$AQUI/.." && pwd)"
BASE="${BASE:-http://127.0.0.1:8765}"

falhas=0

echo "########## 1/3  Verificações estáticas ##########"
perl "$AQUI/estatico.pl" || falhas=$((falhas+1))

echo
echo "########## 2/3  Segurança ##########"
if ! curl -s -o /dev/null --max-time 5 "$BASE/index.php"; then
    echo "  SALTADO: não há servidor em $BASE (corre 'php -S 127.0.0.1:8765')" >&2
    falhas=$((falhas+1))
else
    bash "$AQUI/seguranca.sh" || falhas=$((falhas+1))
fi

echo
echo "########## 3/3  Uploads ##########"
if ! curl -s -o /dev/null --max-time 5 "$BASE/index.php"; then
    echo "  SALTADO: não há servidor em $BASE" >&2
    falhas=$((falhas+1))
else
    bash "$AQUI/uploads.sh" || falhas=$((falhas+1))
fi

echo
if [[ $falhas -eq 0 ]]; then
    echo "TUDO OK."
else
    echo "$falhas suite(s) com problemas." >&2
fi
exit $((falhas > 0 ? 1 : 0))
