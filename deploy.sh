#!/usr/bin/env bash
#
# Publica o site por FTP (InfinityFree ou qualquer outro alojamento partilhado).
#
#   ./deploy.sh              envia só o que mudou desde a última publicação
#   ./deploy.sh --full       envia tudo
#   ./deploy.sh --dry-run    mostra o que enviaria, sem enviar nada
#
# As credenciais vêm de .deploy.env, que está no .gitignore e nunca é enviado.
# O script nunca imprime a password.

set -uo pipefail

RAIZ="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$RAIZ" || exit 1

CONFIG="$RAIZ/.deploy.env"
ESTADO="$RAIZ/.deploy-state"

# Ficheiros que existem no repositório mas não pertencem ao servidor.
#
# A lista de envio vem do `git ls-files`, por isso qualquer ficheiro criado na
# raiz do projecto e adicionado ao índice seria publicado. Já aconteceu: uma
# pasta backup-live-* com a versão antiga do site foi parar ao servidor, e com
# ela os endpoints vulneráveis que esta reescrita tinha removido. O .gitignore
# trata disso, e esta lista é a segunda barreira caso alguém faça `git add -f`.
EXCLUIR_REGEX='^(deploy-obsolete\.txt|README\.md|\.gitignore|\.env\.example|deploy\.sh|database/|tests/|backup-live-|\.last-backup|\.deploy)'

FULL=0
DRY=0
for arg in "$@"; do
    case "$arg" in
        --full)    FULL=1 ;;
        --dry-run) DRY=1 ;;
        -h|--help) sed -n '2,12p' "$0" | sed 's/^# \?//'; exit 0 ;;
        *) echo "Opção desconhecida: $arg" >&2; exit 2 ;;
    esac
done

# --- Credenciais ------------------------------------------------------------

if [[ ! -f "$CONFIG" ]]; then
    cat >&2 <<'AJUDA'
Falta o ficheiro .deploy.env.

Cria-o na raiz do projecto com este conteúdo (sem aspas à volta dos valores):

    FTP_HOST=ftpupload.net
    FTP_USER=if0_XXXXXXXX
    FTP_PASS=a-tua-password-de-ftp
    FTP_DIR=/htdocs

Os valores estão em InfinityFree > Client Area > Accounts > FTP Details.
O ficheiro está no .gitignore, por isso não vai para o repositório.
AJUDA
    exit 1
fi

# shellcheck disable=SC1090
source "$CONFIG"

for var in FTP_HOST FTP_USER FTP_PASS; do
    if [[ -z "${!var:-}" ]]; then
        echo "Falta $var no .deploy.env" >&2
        exit 1
    fi
done
FTP_DIR="${FTP_DIR:-/htdocs}"

# --- Que ficheiros enviar ---------------------------------------------------

# A lista sai do git: o que está no repositório é o que o site precisa.
#
# -z e core.quotePath=false são obrigatórios: por omissão o git escapa os
# nomes com acentos (Imagens/interroga\303\247ao_food.jpg) e o caminho
# chegaria aqui com as barras invertidas literais.
FICHEIROS=()
while IFS= read -r -d '' caminho; do
    [[ "$caminho" =~ $EXCLUIR_REGEX ]] && continue
    FICHEIROS+=("$caminho")
done < <(git -c core.quotePath=false ls-files -z)

if [[ ${#FICHEIROS[@]} -eq 0 ]]; then
    echo "Nada para enviar. Já correste 'git add'?" >&2
    exit 1
fi

# O .env de produção vai com o nome .env, mas guarda-se com outro nome para
# não se confundir com o .env local de desenvolvimento.
ENV_PROD=""
if [[ -f "$RAIZ/.env.production" ]]; then
    ENV_PROD=".env.production"
elif [[ -f "$RAIZ/.env" ]]; then
    echo "Aviso: não há .env.production; vai ser enviado o .env local." >&2
    ENV_PROD=".env"
else
    echo "Aviso: não há .env nem .env.production — o site não vai ligar à base de dados." >&2
fi

# --- Estado da última publicação -------------------------------------------

declare -A ANTERIOR=()
if [[ $FULL -eq 0 && -f "$ESTADO" ]]; then
    while read -r hash caminho; do
        [[ -n "${caminho:-}" ]] && ANTERIOR["$caminho"]="$hash"
    done < "$ESTADO"
fi

# --- Envio ------------------------------------------------------------------

enviar() {
    local local_path="$1" remoto="$2"
    curl --silent --show-error --fail \
         --ftp-create-dirs --ftp-pasv --connect-timeout 20 --retry 2 \
         --user "$FTP_USER:$FTP_PASS" \
         --upload-file "$local_path" \
         "ftp://$FTP_HOST$FTP_DIR/$remoto"
}

NOVO_ESTADO="$(mktemp)"
trap 'rm -f "$NOVO_ESTADO"' EXIT

enviados=0
saltados=0
falhados=0

processar() {
    local caminho="$1" destino="$2"
    local hash
    hash="$(sha1sum "$caminho" | cut -d' ' -f1)"

    if [[ "${ANTERIOR[$destino]:-}" == "$hash" ]]; then
        printf '%s %s\n' "$hash" "$destino" >> "$NOVO_ESTADO"
        ((saltados++))
        return
    fi

    if [[ $DRY -eq 1 ]]; then
        echo "  enviaria  $destino"
        printf '%s %s\n' "$hash" "$destino" >> "$NOVO_ESTADO"
        ((enviados++))
        return
    fi

    if enviar "$caminho" "$destino"; then
        echo "  ok        $destino"
        printf '%s %s\n' "$hash" "$destino" >> "$NOVO_ESTADO"
        ((enviados++))
    else
        echo "  FALHOU    $destino" >&2
        # Não se grava o hash: na próxima tentativa este ficheiro vai outra vez.
        ((falhados++))
    fi
}

echo "Destino: ftp://$FTP_HOST$FTP_DIR  (utilizador: $FTP_USER)"
[[ $DRY -eq 1 ]] && echo "MODO DRY-RUN — não é enviado nada."
[[ $FULL -eq 1 ]] && echo "Publicação completa: todos os ficheiros."
echo

for caminho in "${FICHEIROS[@]}"; do
    [[ -f "$caminho" ]] || continue
    processar "$caminho" "$caminho"
done

if [[ -n "$ENV_PROD" ]]; then
    processar "$ENV_PROD" ".env"
fi

if [[ $DRY -eq 0 ]]; then
    mv "$NOVO_ESTADO" "$ESTADO"
    trap - EXIT
fi

# --- Remover o que ficou de versões anteriores -----------------------------
#
# Publicar por FTP só acrescenta. Sem este passo, os endpoints antigos —
# com SQL injection e escritas sem sessão — continuavam acessíveis ao lado
# do código novo, e as correcções não valiam nada.

OBSOLETOS="$RAIZ/deploy-obsolete.txt"
apagados=0
naoexistiam=0

if [[ -f "$OBSOLETOS" ]]; then
    echo
    echo "A remover ficheiros obsoletos do servidor:"

    # Ficheiros primeiro, pastas no fim.
    pastas=()
    while IFS= read -r alvo; do
        alvo="${alvo%%#*}"
        alvo="$(echo "$alvo" | sed 's/[[:space:]]*$//')"
        [[ -z "$alvo" ]] && continue

        if [[ "$alvo" == */ ]]; then
            pastas+=("$alvo")
            continue
        fi

        if [[ $DRY -eq 1 ]]; then
            echo "  apagaria  $alvo"
            ((apagados++))
            continue
        fi

        if curl --silent --show-error --ftp-pasv --connect-timeout 20 \
                --user "$FTP_USER:$FTP_PASS" \
                --quote "DELE $FTP_DIR/$alvo" \
                "ftp://$FTP_HOST$FTP_DIR/" -o /dev/null 2>/dev/null; then
            echo "  apagado   $alvo"
            ((apagados++))
        else
            # Já não lá estava: é o estado desejado à mesma.
            echo "  ausente   $alvo"
            ((naoexistiam++))
        fi
    done < "$OBSOLETOS"

    for pasta in "${pastas[@]:-}"; do
        [[ -z "$pasta" ]] && continue
        if [[ $DRY -eq 1 ]]; then
            echo "  removeria $pasta"
            continue
        fi
        if curl --silent --show-error --ftp-pasv --connect-timeout 20 \
                --user "$FTP_USER:$FTP_PASS" \
                --quote "RMD $FTP_DIR/${pasta%/}" \
                "ftp://$FTP_HOST$FTP_DIR/" -o /dev/null 2>/dev/null; then
            echo "  removida  $pasta"
        else
            echo "  ausente   $pasta"
        fi
    done
fi

echo
echo "Enviados: $enviados   Sem alterações: $saltados   Falhados: $falhados"

if [[ $falhados -gt 0 ]]; then
    echo "Houve falhas. Corre outra vez para tentar só os que faltaram." >&2
    exit 1
fi

if [[ $DRY -eq 0 && $enviados -gt 0 ]]; then
    echo "Publicado."
fi
