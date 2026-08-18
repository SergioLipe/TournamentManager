<?php
declare(strict_types=1);

/**
 * O torneio em si: a bracket e os diálogos que a acompanham.
 *
 * Vive num include porque há duas páginas a servi-lo — o index.php do site e
 * o app.php que a aplicação Android abre. A única diferença entre as duas é
 * a barra de navegação, e essa é decidida no header.php pelo $modoApp.
 *
 * Espera que o header.php já tenha corrido: usa e(), urlImagem() e as
 * constantes do config.php.
 */
?>

<div class="tournament">
    <div class="tournament__toolbar">
        <button type="button" class="btn btn-primary" id="btnCarregarImagens">Load images</button>
        <button type="button" class="btn btn-outline-primary" id="btnCarregarNomes">Use names instead</button>
        <button type="button" class="btn btn-outline-secondary" id="btnReiniciar">Reset</button>
        <span class="tournament__estado" id="estadoTorneio" role="status" aria-live="polite"></span>
    </div>

    <div class="bracket" id="bracket"
         data-csrf="<?= e(tokenCsrf()) ?>"
         data-placeholder="<?= urlImagem(PLACEHOLDER_IMAGE) ?>"></div>
</div>

<!-- Ecrã de escolha entre dois competidores -->
<div class="duelo" id="duelo" hidden>
    <div class="duelo__fundo" data-fechar></div>
    <div class="duelo__caixa" role="dialog" aria-modal="true" aria-labelledby="dueloTitulo">
        <h2 class="duelo__titulo" id="dueloTitulo">Pick one</h2>
        <div class="duelo__lados">
            <button class="duelo__lado" type="button" data-lado="0">
                <img class="duelo__img" alt="">
                <span class="duelo__nome"></span>
            </button>
            <span class="duelo__vs">VS</span>
            <button class="duelo__lado" type="button" data-lado="1">
                <img class="duelo__img" alt="">
                <span class="duelo__nome"></span>
            </button>
        </div>
        <button class="duelo__fechar" type="button" data-fechar aria-label="Close">&times;</button>
    </div>
</div>

<!-- Introdução de nomes, em vez de imagens -->
<div class="modal-simples" id="dialogoNomes" hidden>
    <div class="modal-simples__fundo" data-fechar></div>
    <div class="modal-simples__caixa" role="dialog" aria-modal="true" aria-labelledby="nomesTitulo">
        <h2 class="h5" id="nomesTitulo">Play with names</h2>
        <p class="text-muted">
            One name per line, between <?= MIN_COMPETITORS ?> and <?= MAX_COMPETITORS ?> of them.
            They get shuffled before the draw.
        </p>
        <label class="visually-hidden" for="campoNomes">Competitor names</label>
        <textarea id="campoNomes" class="form-control" rows="8"
                  placeholder="Alice&#10;Bob&#10;Carol&#10;Dave"></textarea>
        <div class="modal-simples__accoes">
            <button type="button" class="btn btn-primary" id="confirmarNomes">Build bracket</button>
            <button type="button" class="btn btn-outline-secondary" data-fechar>Cancel</button>
        </div>
    </div>
</div>

<!-- Ecrã do vencedor -->
<div class="vencedor" id="vencedor" hidden>
    <div class="vencedor__fundo" data-fechar></div>
    <div class="vencedor__caixa" role="dialog" aria-modal="true" aria-labelledby="vencedorTitulo">
        <p class="vencedor__titulo" id="vencedorTitulo">Winner</p>
        <img class="vencedor__img" alt="">
        <p class="vencedor__nome"></p>
        <button class="btn btn-primary" type="button" data-fechar>Play again</button>
    </div>
</div>
