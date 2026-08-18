#!/usr/bin/env bash
#
# Verifica o que o servidor está mesmo a servir em torneio.site.
#
#   bash tools/verificar-publicado.sh                 # os ficheiros da app
#   bash tools/verificar-publicado.sh CSS/style.css   # um caminho à escolha
#
# Porque é que isto não é um curl simples:
#
# O InfinityFree põe um desafio de JavaScript à frente do site. A um cliente
# sem o cookie __test responde 200 com uma página HTML de ~866 bytes que
# define o cookie e recarrega — em vez do ficheiro pedido. Um `curl -I` vê
# 200 e parece que está tudo bem, mas o que veio não era o ficheiro. Pior:
# `curl` com a identificação de origem (`curl/8.x`) nem sequer é atendido, a
# ligação é cortada e parece que o site está em baixo.
#
# Este script resolve o desafio como o browser faz — decifra o cookie com
# AES-128-CBC — e só depois pede o ficheiro, mostrando o que ele é de facto.
#
# Ficheiros .css e imagens são entregues sem desafio nenhum; .js, .json,
# .webmanifest, .php e .html são sempre desafiados.

set -uo pipefail

BASE="${BASE:-https://torneio.site}"
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36"

PREDEFINIDOS=(
    app.php
    manifest.webmanifest
    sw.js
    offline.html
    JavaScript/pwa.js
    icons/icon-192.png
    icons/icon-512.png
    icons/icon-maskable-512.png
    .well-known/assetlinks.json
)

# Devolve o valor do cookie __test a partir da página de desafio, ou vazio.
resolver_desafio() {
    local corpo="$1"
    local chave iv cifra
    chave=$(grep -oE 'toNumbers\("[0-9a-f]{32}"\)' <<<"$corpo" | sed -n 1p | grep -oE '[0-9a-f]{32}')
    iv=$(grep -oE 'toNumbers\("[0-9a-f]{32}"\)' <<<"$corpo" | sed -n 2p | grep -oE '[0-9a-f]{32}')
    cifra=$(grep -oE 'toNumbers\("[0-9a-f]{32}"\)' <<<"$corpo" | sed -n 3p | grep -oE '[0-9a-f]{32}')

    [[ -z "$chave" || -z "$iv" || -z "$cifra" ]] && return 1

    printf '%s' "$cifra" | xxd -r -p \
        | openssl enc -d -aes-128-cbc -K "$chave" -iv "$iv" -nopad 2>/dev/null \
        | xxd -p | tr -d '\n'
}

verificar() {
    local caminho="$1"
    local corpo cookie cabecalhos tipo tamanho desafiado="não"
    local tmp
    tmp=$(mktemp)

    # O corpo vai para ficheiro e não para uma variável: metade destes
    # caminhos são PNG e o bash corta tudo a partir do primeiro byte nulo.
    curl -s --max-time 30 -A "$UA" -o "$tmp" "$BASE/$caminho" 2>/dev/null

    if grep -qa '__test' "$tmp" 2>/dev/null; then
        desafiado="sim"
        corpo=$(cat "$tmp")
        cookie=$(resolver_desafio "$corpo") || {
            rm -f "$tmp"
            printf '  %-32s DESAFIO NÃO RESOLVIDO\n' "$caminho"
            return 1
        }
        cabecalhos=$(curl -s -D - -o /dev/null --max-time 30 -A "$UA" \
                          --cookie "__test=$cookie" "$BASE/$caminho" 2>/dev/null)
    else
        cabecalhos=$(curl -s -D - -o /dev/null --max-time 30 -A "$UA" "$BASE/$caminho" 2>/dev/null)
    fi

    local estado
    estado=$(grep -m1 -oE 'HTTP/[0-9.]+ [0-9]+' <<<"$cabecalhos" | grep -oE '[0-9]{3}$')
    tipo=$(grep -i '^Content-Type' <<<"$cabecalhos" | tr -d '\r' | cut -d' ' -f2- | cut -d';' -f1)
    tamanho=$(grep -i '^Content-Length' <<<"$cabecalhos" | tr -d '\r' | cut -d' ' -f2-)

    printf '  %-32s %s  %-26s %8s  desafio:%s\n' \
        "$caminho" "${estado:-???}" "${tipo:-?}" "${tamanho:-?}" "$desafiado"

    [[ "$estado" == "200" ]]
}

for cmd in curl openssl xxd; do
    command -v "$cmd" > /dev/null || { echo "Falta o $cmd." >&2; exit 1; }
done

alvos=("$@")
[[ ${#alvos[@]} -eq 0 ]] && alvos=("${PREDEFINIDOS[@]}")

echo "$BASE"
echo
falhas=0
for alvo in "${alvos[@]}"; do
    verificar "$alvo" || ((falhas++))
done

echo
if [[ $falhas -eq 0 ]]; then
    echo "Tudo servido."
else
    echo "$falhas caminho(s) com problemas." >&2
    exit 1
fi
