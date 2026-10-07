<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/temas-view.php';

// Consulta por GET: assim a página fica ligável e pode ser recarregada sem
// reenviar um formulário.
$temaId = inteiro($_GET, 'temaId');
$tema   = $temaId > 0 ? temaVisivelOuNull($temaId) : null;

$competidores = $tema !== null ? competidoresDoTema((int) $tema['id']) : [];

$listaPublicos = temasParaEscolher();

$tituloPagina = 'Statistics';

// Aberta a partir da app (?app=1): a barra continua em modo app e as
// ligações daqui mantêm o parâmetro, para nunca se cair no index.php.
$modoApp  = isset($_GET['app']);
$urlTema  = 'Estatisticas.php?' . ($modoApp ? 'app=1&' : '') . 'temaId=';
require __DIR__ . '/includes/header.php';
?>

<div class="container form-largo estatisticas">
    <h1 class="h2 mb-1">Statistics</h1>
    <p class="text-muted mb-3">Every finished tournament, from everyone who plays, adds to these numbers.</p>

    <?php if ($tema === null) { ?>
        <!--
            Sem tema escolhido, escolher é a única coisa a fazer: os temas em
            cartões pequenos, a rolar com a própria página. Antes estavam numa
            caixa com barra de deslocamento própria dentro da página, e rolar
            uma dentro da outra era o que tornava isto desajeitado.
        -->
        <?php if ($temaId > 0) { ?>
            <div class="alert alert-warning">That theme does not exist.</div>
        <?php } ?>

        <?php if ($listaPublicos === []) { ?>
            <p class="selector__vazio">No themes yet.</p>
        <?php } ?>

        <?php seccoesDeTemasPublicos($listaPublicos, $urlTema); ?>
    <?php } else { ?>
        <!--
            Com um tema aberto, os outros ficam numa faixa que desliza de lado:
            mudar de tema é um toque, e não empurra as estatísticas para baixo.
        -->
        <nav class="faixa-temas" aria-label="Themes">
            <?php foreach ($listaPublicos as $opcao) {
                $esta = (int) $opcao['id'] === (int) $tema['id'];
                $capa = ($opcao['capa'] ?? '') !== '' ? (string) $opcao['capa'] : PLACEHOLDER_IMAGE;
                ?>
                <a class="faixa-temas__item<?= $esta ? ' faixa-temas__item--activo' : '' ?>"
                   href="<?= e($urlTema) . (int) $opcao['id'] ?>"<?= $esta ? ' aria-current="page"' : '' ?>>
                    <img class="faixa-temas__img" src="<?= urlImagem($capa) ?>" alt="" loading="lazy" decoding="async">
                    <span><?= e($opcao['nome']) ?></span>
                </a>
            <?php } ?>
        </nav>

        <?php
        $torneios = array_sum(array_map(static function (array $c): int {
            return (int) $c['nTorneiosVencidos'];
        }, $competidores));
        ?>
        <div class="estatisticas__cabeca">
            <h2 class="h4 m-0"><?= e($tema['nome']) ?></h2>
            <span class="text-muted">
                <?= count($competidores) ?> competitors · <?= $torneios ?> tournament<?= $torneios === 1 ? '' : 's' ?> finished
            </span>
        </div>

        <?php if ($competidores === []) { ?>
            <p class="text-muted">This theme has no competitors yet.</p>
        <?php } else { ?>
            <!--
                Uma linha por competidor, já por ordem (títulos, depois
                vitórias): a imagem pequena e os números lado a lado cabem
                várias vezes mais por ecrã do que os cartões grandes de antes.
            -->
            <ol class="ranking">
                <?php foreach ($competidores as $posicao => $competidor) {
                    $vitorias = (int) $competidor['nBatalhasVencidas'];
                    $derrotas = (int) $competidor['nBatalhasPerdidas'];
                    $titulos  = (int) $competidor['nTorneiosVencidos'];
                    $total    = $vitorias + $derrotas;
                    // Sem batalhas jogadas não há percentagem que faça sentido.
                    $taxa     = $total > 0 ? (int) round($vitorias / $total * 100) : null;
                    ?>
                    <li class="ranking__linha">
                        <span class="ranking__pos"><?= $posicao + 1 ?></span>
                        <img class="ranking__img" loading="lazy" decoding="async"
                             src="<?= urlImagem($competidor['imagem']) ?>" alt="">
                        <div class="ranking__info">
                            <span class="ranking__nome"><?= e($competidor['nome']) ?></span>
                            <?php if ($taxa !== null) { ?>
                                <div class="ranking__taxa">
                                    <div class="barra" title="<?= $taxa ?>% win rate over <?= $total ?> battles">
                                        <div class="barra__preenchida" style="width: <?= $taxa ?>%"></div>
                                    </div>
                                    <span><?= $taxa ?>%</span>
                                </div>
                            <?php } else { ?>
                                <span class="ranking__sem">No battles yet</span>
                            <?php } ?>
                        </div>
                        <dl class="ranking__numeros">
                            <div><dt>Won</dt><dd><?= $vitorias ?></dd></div>
                            <div><dt>Lost</dt><dd><?= $derrotas ?></dd></div>
                            <div class="ranking__titulos"><dt>Titles</dt><dd><?= $titulos ?></dd></div>
                        </dl>
                    </li>
                <?php } ?>
            </ol>
        <?php } ?>
    <?php } ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
