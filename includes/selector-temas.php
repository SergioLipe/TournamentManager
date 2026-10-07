<?php
declare(strict_types=1);

require_once __DIR__ . '/temas-view.php';

/**
 * O selector de temas: a janela que se abre no botão "Choose theme".
 *
 * Substitui o menu suspenso que a barra de navegação tinha. Com dezoito temas
 * públicos aquilo era uma lista de dezoito linhas de texto igual, com barra de
 * deslocamento própria, em que escolher exigia ler tudo — e o que distingue
 * um tema do outro são as imagens, que a lista não mostrava.
 *
 * Aqui cada tema é um cartão com capa, nome e quantos competidores tem,
 * arrumados por grupos (ver GRUPOS_TEMAS no temas.php) e com uma caixa de
 * procura por cima para quem já sabe o que quer.
 *
 * Espera que o header.php já tenha corrido: usa e(), urlImagem() e a
 * variável $modoApp que ele preparou.
 */

$selTemasPublicos = temasParaEscolher();
$selGrupos        = array_keys(agruparTemas($selTemasPublicos));
$selTotal         = count($selTemasPublicos);
?>

<div class="selector" id="selectorTemas" hidden>
    <div class="selector__fundo" data-fechar></div>

    <div class="selector__caixa" role="dialog" aria-modal="true" aria-labelledby="selectorTitulo">
        <div class="selector__topo">
            <div class="selector__cabeca">
                <h2 class="selector__titulo" id="selectorTitulo">Choose a theme</h2>
                <span class="selector__total"><?= $selTotal ?> theme<?= $selTotal === 1 ? '' : 's' ?></span>
                <button type="button" class="selector__fechar" data-fechar aria-label="Close">&times;</button>
            </div>

            <div class="selector__ferramentas">
                <div class="selector__procura">
                    <svg class="selector__lupa" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                        <circle cx="8.5" cy="8.5" r="5.5" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M13 13l4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <label class="visually-hidden" for="selectorProcura">Search themes</label>
                    <input type="search" id="selectorProcura" class="selector__campo"
                           placeholder="Search themes" autocomplete="off">
                </div>
            </div>

            <!--
                As categorias em pílulas: com duas dúzias de temas, saltar
                directamente para "Animals" é mais rápido do que procurar ou
                descer a grelha toda. Num telemóvel deslizam na horizontal.
            -->
            <?php if (count($selGrupos) > 1) { ?>
                <div class="selector__chips" role="group" aria-label="Categories">
                    <button type="button" class="selector__chip" data-grupo-filtro="" aria-pressed="true">All</button>
                    <?php foreach ($selGrupos as $grupo) { ?>
                        <button type="button" class="selector__chip" data-grupo-filtro="<?= e($grupo) ?>" aria-pressed="false"><?= e($grupo) ?></button>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <div class="selector__corpo">
            <?php if ($selTemasPublicos === []) { ?>
                <p class="selector__vazio">No themes yet.</p>
            <?php } ?>

            <?php seccoesDeTemasPublicos($selTemasPublicos); ?>

            <p class="selector__vazio" id="selectorSemResultados" hidden>No theme matches that search.</p>
        </div>
    </div>
</div>
