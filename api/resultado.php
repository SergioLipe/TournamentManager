<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/temas.php';

/**
 * Regista os resultados de um torneio completo.
 *
 * POST: resultados (JSON), csrf
 *   resultados = [{"v": vencedorId, "p": perdedorId, "f": 0|1}, ...]
 *
 * Chega tudo de uma vez no fim, e não batalha a batalha, porque a bracket
 * deixa refazer uma escolha já feita — enviar cada resultado no momento
 * contaria duas vezes a mesma batalha sempre que alguém mudasse de ideias.
 *
 * Os competidores são identificados por id e validados contra a base de
 * dados. A versão antiga recebia caminhos de imagem em texto livre, o que
 * permitia a qualquer pessoa inflacionar as estatísticas de qualquer
 * competidor com um único POST.
 *
 * O torneio é jogável sem sessão iniciada, por isso não se exige login —
 * mas o token CSRF garante que o pedido nasce numa página deste site.
 */

if (!ehPost()) {
    erroJson('Método não permitido.', 405);
}

exigirCsrf();

/* -------------------------------------------------------------------------- */
/*  Ler e validar a forma do pedido                                           */
/* -------------------------------------------------------------------------- */

$resultados = json_decode((string) ($_POST['resultados'] ?? ''), true);

if (!is_array($resultados) || $resultados === []) {
    erroJson('Nenhum resultado recebido.');
}

// Um torneio tem no máximo MAX_COMPETITORS - 1 batalhas.
if (count($resultados) > MAX_COMPETITORS - 1) {
    erroJson('Resultados a mais para um único torneio.');
}

$batalhas = [];
$ids      = [];
$finais   = 0;

foreach ($resultados as $linha) {
    if (!is_array($linha)) {
        erroJson('Resultado mal formado.');
    }

    $vencedor = filter_var($linha['v'] ?? null, FILTER_VALIDATE_INT);
    $perdedor = filter_var($linha['p'] ?? null, FILTER_VALIDATE_INT);
    $ehFinal  = !empty($linha['f']);

    if ($vencedor === false || $perdedor === false
        || $vencedor <= 0 || $perdedor <= 0 || $vencedor === $perdedor
    ) {
        erroJson('Competidores inválidos.');
    }

    if ($ehFinal) {
        $finais++;
    }

    $batalhas[] = ['vencedor' => $vencedor, 'perdedor' => $perdedor, 'final' => $ehFinal];
    $ids[] = $vencedor;
    $ids[] = $perdedor;
}

// Exactamente uma final: sem isto seria possível enviar o mesmo competidor
// como campeão várias vezes no mesmo pedido.
if ($finais !== 1) {
    erroJson('Um torneio tem exactamente uma final.');
}

/* -------------------------------------------------------------------------- */
/*  Validar os competidores contra a base de dados                            */
/* -------------------------------------------------------------------------- */

$ids = array_values(array_unique($ids));

$marcadores  = implode(',', array_fill(0, count($ids), '?'));
$encontrados = obterTodas(
    "SELECT id, TemaId FROM competidor WHERE id IN ($marcadores)",
    $ids
);

if (count($encontrados) !== count($ids)) {
    erroJson('Competidores inválidos.');
}

// Todos têm de ser do mesmo tema — sem isto seria possível registar batalhas
// entre competidores que nunca se poderiam encontrar.
$temaIds = array_unique(array_map(static function (array $linha): int {
    return (int) $linha['TemaId'];
}, $encontrados));

if (count($temaIds) !== 1) {
    erroJson('Competidores de temas diferentes.');
}

if (!podeVerTema(obterTema((int) reset($temaIds)))) {
    erroJson('Tema não encontrado.', 404);
}

/* -------------------------------------------------------------------------- */
/*  Gravar                                                                    */
/* -------------------------------------------------------------------------- */

$pdo = db();
$pdo->beginTransaction();

try {
    foreach ($batalhas as $batalha) {
        query(
            'UPDATE competidor SET nBatalhasVencidas = nBatalhasVencidas + 1 WHERE id = ?',
            [$batalha['vencedor']]
        );
        query(
            'UPDATE competidor SET nBatalhasPerdidas = nBatalhasPerdidas + 1 WHERE id = ?',
            [$batalha['perdedor']]
        );

        // A final conta como batalha ganha e ainda como torneio ganho. Na
        // versão antiga o vencedor da final não recebia a vitória de batalha.
        if ($batalha['final']) {
            query(
                'UPDATE competidor SET nTorneiosVencidos = nTorneiosVencidos + 1 WHERE id = ?',
                [$batalha['vencedor']]
            );
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Falha ao registar resultados: ' . $e->getMessage());
    erroJson('Não foi possível registar os resultados.', 500);
}

responderJson(['ok' => true, 'batalhas' => count($batalhas)]);
