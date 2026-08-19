<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Acesso a temas e competidores.
 *
 * Um tema é visível se for público ou se pertencer a quem está autenticado.
 * Só o dono pode alterá-lo.
 */

/** Temas marcados como públicos, disponíveis a toda a gente. */
function temasPublicos(): array
{
    return obterTodas('SELECT id, nome FROM tema WHERE publico = 1 ORDER BY nome');
}

/** Temas pertencentes a um utilizador. */
function temasDoUtilizador(int $utilizadorId): array
{
    return obterTodas(
        'SELECT id, nome FROM tema WHERE utilizadorId = ? ORDER BY nome',
        [$utilizadorId]
    );
}

/**
 * Grupos do selector de temas, por esta ordem.
 *
 * O tema não tem coluna de categoria na base de dados, e acrescentar uma
 * obrigava a mexer no schema por causa de uma dúzia de nomes que são sempre
 * os mesmos — os temas públicos vêm todos do seed. Um tema que não esteja
 * aqui (um renomeado, um criado à mão) não desaparece: cai no último grupo.
 */
const GRUPOS_TEMAS = [
    'Animals'         => ['Animals', 'Cat Breeds', 'Dog Breeds', 'Dinosaurs'],
    'Food & drink'    => ['Food in Portugal', 'International Food', 'Portuguese Desserts', 'Cocktails', 'Beers'],
    'Screen & play'   => ['Movies', 'TV Shows', 'Anime', 'Video Games', 'Superheroes'],
    'Music & sport'   => ['Rock Bands', 'Rap Artists', 'Footballers'],
    'History & art'   => ['Historical Figures', 'Generals', 'Paintings'],
    'Places & things' => ['European Capitals', 'World Landmarks', 'Classic Cars', 'Aircraft'],
];

/** Nome do grupo a que um tema pertence. */
function grupoDoTema(string $nome): string
{
    foreach (GRUPOS_TEMAS as $grupo => $nomes) {
        if (in_array($nome, $nomes, true)) {
            return $grupo;
        }
    }

    return 'More themes';
}

/**
 * Distribui uma lista de temas pelos grupos, sem grupos vazios.
 *
 * Devolve ['Animals' => [tema, ...], ...] pela ordem de GRUPOS_TEMAS, com
 * 'More themes' no fim quando houver alguma coisa lá.
 */
function agruparTemas(array $temas): array
{
    $grupos = [];

    foreach (array_keys(GRUPOS_TEMAS) as $grupo) {
        $grupos[$grupo] = [];
    }
    $grupos['More themes'] = [];

    foreach ($temas as $tema) {
        $grupos[grupoDoTema((string) $tema['nome'])][] = $tema;
    }

    return array_filter($grupos, static function (array $lista): bool {
        return $lista !== [];
    });
}

/**
 * Temas com o que o selector precisa para os mostrar: quantos competidores
 * têm e uma imagem que sirva de capa.
 *
 * A capa é o MIN(imagem) e não uma à sorte de propósito: assim o cartão de um
 * tema é sempre o mesmo de visita para visita, e o browser reaproveita a
 * imagem que já tem em cache em vez de descarregar outra a cada abertura.
 *
 * O LEFT JOIN é o que faz um tema vazio — acabado de criar, ainda sem
 * imagens — aparecer na lista com "0 competitors" em vez de desaparecer.
 */
function temasParaEscolher(?int $utilizadorId = null): array
{
    $sql = 'SELECT t.id, t.nome, COUNT(c.id) AS competidores, MIN(c.imagem) AS capa
              FROM tema t
              LEFT JOIN competidor c ON c.TemaId = t.id
             WHERE ' . ($utilizadorId === null ? 't.publico = 1' : 't.utilizadorId = ?') . '
             GROUP BY t.id, t.nome
             ORDER BY t.nome';

    return obterTodas($sql, $utilizadorId === null ? [] : [$utilizadorId]);
}

/** Devolve um tema pelo id, ou null. */
function obterTema(int $temaId): ?array
{
    return obterLinha('SELECT id, nome, utilizadorId, publico FROM tema WHERE id = ?', [$temaId]);
}

/** Devolve um tema do utilizador autenticado pelo nome, ou null. */
function obterTemaDoUtilizadorPorNome(int $utilizadorId, string $nome): ?array
{
    return obterLinha(
        'SELECT id, nome, utilizadorId, publico FROM tema WHERE utilizadorId = ? AND nome = ?',
        [$utilizadorId, $nome]
    );
}

/** True se o tema puder ser consultado por quem faz o pedido. */
function podeVerTema(?array $tema): bool
{
    if ($tema === null) {
        return false;
    }
    return (int) $tema['publico'] === 1
        || (utilizadorId() !== null && (int) $tema['utilizadorId'] === utilizadorId());
}

/** True se o tema pertencer a quem faz o pedido. */
function podeEditarTema(?array $tema): bool
{
    return $tema !== null
        && utilizadorId() !== null
        && (int) $tema['utilizadorId'] === utilizadorId();
}

/**
 * Carrega um tema garantindo que o utilizador o pode ver.
 * Devolve null se não existir ou se o acesso for negado — do ponto de vista
 * de quem pede, os dois casos são indistinguíveis.
 */
function temaVisivelOuNull(int $temaId): ?array
{
    $tema = obterTema($temaId);
    return podeVerTema($tema) ? $tema : null;
}

/** Competidores de um tema, com as estatísticas. */
function competidoresDoTema(int $temaId): array
{
    return obterTodas(
        'SELECT id, nome, imagem, nBatalhasVencidas, nBatalhasPerdidas, nTorneiosVencidos
           FROM competidor
          WHERE TemaId = ?
          ORDER BY nTorneiosVencidos DESC, nBatalhasVencidas DESC, nome',
        [$temaId]
    );
}

/** Número de competidores de um tema. */
function contarCompetidores(int $temaId): int
{
    return (int) obterValor('SELECT COUNT(*) FROM competidor WHERE TemaId = ?', [$temaId]);
}
