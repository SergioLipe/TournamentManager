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
