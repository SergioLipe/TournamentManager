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
$selTemasPessoais = (!$modoApp && autenticado()) ? temasParaEscolher((int) utilizadorId()) : [];
?>

<div class="selector" id="selectorTemas" hidden>
    <div class="selector__fundo" data-fechar></div>

    <div class="selector__caixa" role="dialog" aria-modal="true" aria-labelledby="selectorTitulo">
        <div class="selector__topo">
            <h2 class="selector__titulo" id="selectorTitulo">Choose a theme</h2>

            <label class="visually-hidden" for="selectorProcura">Search themes</label>
            <input type="search" id="selectorProcura" class="selector__procura form-control"
                   placeholder="Search themes" autocomplete="off">

            <button type="button" class="btn btn-outline-primary" id="btnTemaAleatorio">Surprise me</button>
            <button type="button" class="selector__fechar" data-fechar aria-label="Close">&times;</button>
        </div>

        <div class="selector__corpo">
            <?php seccaoDeTemas('Your themes', $selTemasPessoais); ?>

            <?php if ($selTemasPublicos === []) { ?>
                <p class="selector__vazio">No public themes yet.</p>
            <?php } ?>

            <?php seccoesDeTemasPublicos($selTemasPublicos); ?>

            <p class="selector__vazio" id="selectorSemResultados" hidden>No theme with that name.</p>
        </div>
    </div>
</div>
