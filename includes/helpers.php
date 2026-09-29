<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/**
 * Funções de apoio às vistas.
 */

/** Escapa texto para HTML. Usar sempre que se imprime algo vindo da BD ou do utilizador. */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Escapa um caminho de imagem para usar dentro de um atributo src. */
function urlImagem(?string $caminho): string
{
    $caminho = $caminho ?? '';
    // Cada segmento é codificado à parte para não destruir as barras.
    $segmentos = array_map('rawurlencode', explode('/', $caminho));
    return e(implode('/', $segmentos));
}

/**
 * Caminho de um CSS ou JS do site com a data do ficheiro no fim (?v=...).
 *
 * Sem isto o browser guardava o style.css na cache e continuava a usá-lo
 * depois de uma publicação nova: a página chegava com o HTML novo e os
 * estilos antigos, e ficava desfeita. Com a data no URL, cada versão do
 * ficheiro é um endereço diferente e o browser vai buscá-la.
 */
function urlRecurso(string $caminho): string
{
    $ficheiro = APP_ROOT . '/' . $caminho;
    $versao   = is_file($ficheiro) ? (string) filemtime($ficheiro) : '0';
    return e($caminho . '?v=' . $versao);
}

/** Lê um inteiro de um array de input, dentro de limites. */
function inteiro(array $origem, string $chave, int $omissao = 0, ?int $min = null, ?int $max = null): int
{
    $valor = filter_var($origem[$chave] ?? null, FILTER_VALIDATE_INT);
    if ($valor === false || $valor === null) {
        $valor = $omissao;
    }
    if ($min !== null && $valor < $min) {
        $valor = $min;
    }
    if ($max !== null && $valor > $max) {
        $valor = $max;
    }
    return $valor;
}

/** True se o pedido for POST. */
function ehPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

/** Responde em JSON e termina o pedido. */
function responderJson($dados, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Responde com um erro em JSON e termina o pedido. */
function erroJson(string $mensagem, int $codigo = 400): void
{
    responderJson(['erro' => $mensagem], $codigo);
}
