<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/temas-view.php';

// Consulta por GET: assim a página fica ligável e pode ser recarregada sem
// reenviar um formulário.
$temaId = inteiro($_GET, 'temaId');
$tema   = $temaId > 0 ? temaVisivelOuNull($temaId) : null;

$competidores = $tema !== null ? competidoresDoTema((int) $tema['id']) : [];

$listaPublicos = temasParaEscolher();
$listaPessoais = autenticado() ? temasParaEscolher((int) utilizadorId()) : [];

$tituloPagina = 'Statistics';
require __DIR__ . '/includes/header.php';
?>

<div class="container form-largo">
    <h1 class="h2 mb-4">Statistics</h1>

    <!--
        A escolha do tema é a mesma grelha de cartões do torneio, mas de
        ligações em vez de botões — aqui a página recarrega com ?temaId=.

        Com um tema aberto a grelha fecha-se: dezoito cartões por cima das
        estatísticas empurravam a tabela para fora do ecrã. O <details> faz
        isso sem JavaScript nenhum, e mantém-se aberto enquanto não houver
        tema escolhido, que é quando é a única coisa a fazer na página.
    -->
    <details class="escolher-tema" <?= $tema === null ? 'open' : '' ?>>
        <summary class="escolher-tema__resumo">
            <?= $tema === null ? 'Choose a theme' : 'Change theme' ?>
        </summary>

        <div class="escolher-tema__corpo">
            <?php seccaoDeTemas('Your themes', $listaPessoais, 'Estatisticas.php?temaId='); ?>

            <?php if ($listaPublicos === []) { ?>
                <p class="selector__vazio">No public themes yet.</p>
            <?php } ?>

            <?php seccoesDeTemasPublicos($listaPublicos, 'Estatisticas.php?temaId='); ?>
        </div>
    </details>

    <?php if ($temaId > 0 && $tema === null) { ?>
        <div class="alert alert-warning">That theme does not exist, or you cannot see it.</div>
    <?php } elseif ($tema !== null) { ?>
        <h2 class="h4 mt-4 mb-3"><?= e($tema['nome']) ?></h2>

        <?php if ($competidores === []) { ?>
            <p class="text-muted">This theme has no competitors yet.</p>
        <?php } else { ?>
            <div class="grelha-cartoes">
                <?php foreach ($competidores as $competidor) {
                    $vitorias = (int) $competidor['nBatalhasVencidas'];
                    $derrotas = (int) $competidor['nBatalhasPerdidas'];
                    $total    = $vitorias + $derrotas;
                    // Sem batalhas jogadas não há percentagem que faça sentido.
                    $taxa     = $total > 0 ? (int) round($vitorias / $total * 100) : null;
                    ?>
                    <article class="cartao">
                        <img class="cartao__img" loading="lazy"
                             src="<?= urlImagem($competidor['imagem']) ?>"
                             alt="<?= e($competidor['nome']) ?>">
                        <div class="cartao__corpo">
                            <h3 class="cartao__titulo"><?= e($competidor['nome']) ?></h3>
                            <dl class="cartao__stats">
                                <div><dt>Won</dt><dd><?= $vitorias ?></dd></div>
                                <div><dt>Lost</dt><dd><?= $derrotas ?></dd></div>
                                <div><dt>Titles</dt><dd><?= (int) $competidor['nTorneiosVencidos'] ?></dd></div>
                            </dl>
                            <?php if ($taxa !== null) { ?>
                                <div class="barra" title="<?= $taxa ?>% win rate">
                                    <div class="barra__preenchida" style="width: <?= $taxa ?>%"></div>
                                </div>
                                <p class="cartao__taxa"><?= $taxa ?>% win rate over <?= $total ?> battles</p>
                            <?php } else { ?>
                                <p class="cartao__taxa text-muted">No battles yet</p>
                            <?php } ?>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p class="text-muted mt-4">Pick a theme to see how its competitors have been doing.</p>
    <?php } ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
