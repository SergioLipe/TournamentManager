<?php
declare(strict_types=1);

require_once __DIR__ . '/temas.php';

/**
 * Os cartões de tema, partilhados pelas duas páginas onde se escolhe um.
 *
 * O torneio abre-os numa janela (selector-temas.php) e as estatísticas
 * mostram-nos na página, mas o cartão é o mesmo: capa, nome e quantos
 * competidores tem. A diferença é o que acontece ao carregar — no torneio é
 * um <button> que o torneio.js apanha, nas estatísticas é um <a> para outro
 * URL — e é só isso que $urlBase decide.
 */

/**
 * Um cartão.
 *
 * $urlBase nulo devolve o botão do torneio; com URL, devolve uma ligação para
 * $urlBase . id.
 */
function cartaoTema(array $tema, ?string $urlBase = null): void
{
    $capa    = ($tema['capa'] ?? '') !== '' ? (string) $tema['capa'] : PLACEHOLDER_IMAGE;
    $quantos = (int) ($tema['competidores'] ?? 0);
    $procura = mb_strtolower((string) $tema['nome']);
    ?>
    <?php if ($urlBase === null) { ?>
        <button class="tema-cartao js-escolhe-tema" type="button"
                data-tema-id="<?= (int) $tema['id'] ?>"
                data-tema-nome="<?= e($tema['nome']) ?>"
                data-procura="<?= e($procura) ?>">
    <?php } else { ?>
        <a class="tema-cartao" href="<?= e($urlBase) . (int) $tema['id'] ?>"
           data-procura="<?= e($procura) ?>">
    <?php } ?>
        <img class="tema-cartao__img" src="<?= urlImagem($capa) ?>" alt="" loading="lazy" decoding="async">
        <span class="tema-cartao__nome"><?= e($tema['nome']) ?></span>
        <span class="tema-cartao__conta"><?= $quantos ?> competitor<?= $quantos === 1 ? '' : 's' ?></span>
    <?= $urlBase === null ? '</button>' : '</a>' ?>
    <?php
}

/** Um grupo com título e a sua grelha de cartões. */
function seccaoDeTemas(string $titulo, array $temas, ?string $urlBase = null): void
{
    if ($temas === []) {
        return;
    }
    ?>
    <section class="selector__grupo" data-grupo>
        <h3 class="selector__grupo-titulo"><?= e($titulo) ?></h3>
        <div class="selector__grelha">
            <?php foreach ($temas as $tema) { cartaoTema($tema, $urlBase); } ?>
        </div>
    </section>
    <?php
}

/** Os temas públicos, já arrumados por grupos, cada um na sua secção. */
function seccoesDeTemasPublicos(array $temas, ?string $urlBase = null): void
{
    foreach (agruparTemas($temas) as $grupo => $doGrupo) {
        seccaoDeTemas($grupo, $doGrupo, $urlBase);
    }
}
