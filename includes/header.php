<?php
declare(strict_types=1);

require_once __DIR__ . '/temas.php';

/**
 * Cabeçalho comum a todas as páginas.
 *
 * Cada página pode definir antes do include:
 *   $tituloPagina      — título do separador do browser
 *   $controlosTorneio  — true para mostrar o selector de tema e de tamanho
 *   $modoApp           — true na aplicação Android (ver app.php)
 */
$tituloPagina     = $tituloPagina ?? 'Tournament';
$controlosTorneio = $controlosTorneio ?? false;
$modoApp          = $modoApp ?? false;

// A lista de temas já não se lê aqui: quem a mostra é o selector-temas.php,
// incluído pelo torneio-view.php, e é ele que faz a consulta — com a capa e a
// contagem, que a barra não precisa. Fazê-la também aqui era pedir os mesmos
// temas duas vezes por página.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> · Tournament</title>

    <!--
        Instalação como aplicação. O manifest é o mesmo em todas as páginas,
        por isso o site é instalável a partir de qualquer uma; o start_url lá
        dentro é que aponta ao app.php.

        A extensão é .webmanifest e não .json de propósito: o .htaccess da raiz
        nega tudo o que acabe em .json (para o .env e os dumps da base de dados
        não serem descarregáveis) e o manifest ia no meio.
    -->
    <link rel="manifest" href="manifest.webmanifest">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#2f6fed" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#101318" media="(prefers-color-scheme: dark)">
    <link rel="icon" href="icons/icon-192.png" sizes="192x192">
    <link rel="apple-touch-icon" href="icons/icon-192.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="<?= urlRecurso('CSS/style.css') ?>" rel="stylesheet">
</head>

<body>

<button id="toggleNavBtn" type="button" title="Show/hide menu" aria-label="Show/hide menu" aria-expanded="true">
    <span aria-hidden="true">&#9650;</span>
</button>

<nav id="mainNav" class="app-nav">
    <div class="app-nav__inner">

        <div class="app-nav__group app-nav__group--start">
            <?php if ($controlosTorneio) { ?>
                <!--
                    Os temas escolhem-se na janela do selector-temas.php, não
                    num menu suspenso: com dezoito temas públicos a lista já
                    não cabia no ecrã e era só texto, sem as imagens que são
                    a única coisa que distingue um tema do outro.
                -->
                <button type="button" class="tema-botao" id="btnSelectorTemas"
                        aria-haspopup="dialog" aria-expanded="false">
                    <img class="tema-botao__capa" id="capaTemaEscolhido" alt="" hidden>
                    <span class="tema-botao__texto">
                        <span class="tema-botao__rotulo">Theme</span>
                        <span class="tema-botao__nome" id="rotuloTemaEscolhido">Pick a theme</span>
                    </span>
                    <span class="tema-botao__seta" aria-hidden="true">&#9662;</span>
                </button>

                <!--
                    Só há dois tamanhos: 8 e 16. Com qualquer número entre 2 e
                    16 a maior parte das brackets saía com byes, que confundem
                    mais do que ajudam. Dois botões lado a lado dizem logo quais
                    são as opções, e as setas do teclado mudam entre eles
                    porque são rádios.
                -->
                <div class="tamanho" id="tamanhoBracket" role="radiogroup" aria-labelledby="tamanhoRotulo">
                    <span class="visually-hidden" id="tamanhoRotulo">Bracket size</span>
                    <div class="tamanho__opcoes">
                        <?php foreach ([8 => '3 rounds', 16 => '4 rounds'] as $tamanho => $rondas) { ?>
                            <label class="tamanho__opcao" title="<?= $tamanho ?> players · <?= $rondas ?>">
                                <input type="radio" name="tamanho" value="<?= $tamanho ?>" <?= $tamanho === 8 ? 'checked' : '' ?>>
                                <span class="tamanho__num"><strong><?= $tamanho ?></strong><span class="tamanho__palavra"> players</span></span>
                            </label>
                        <?php } ?>
                    </div>
                </div>

                <button type="button" class="nav-nomes" id="btnCarregarNomes" aria-haspopup="dialog">
                    <span aria-hidden="true">&#9998;</span> Use names
                </button>
            <?php } ?>
        </div>

        <div class="app-nav__group app-nav__group--center">
            <a href="<?= $modoApp ? 'app.php' : 'index.php' ?>" class="app-nav__brand">TOURNAMENT</a>
        </div>

        <!--
            Na aplicação Android só há a bracket, sem estatísticas nem página
            sobre. Isto não é só cosmética — sem links para fora, a app nunca
            sai do /app.php e nunca abre um separador do browser por cima de si.
        -->
        <div class="app-nav__group app-nav__group--end">
            <?php if (!$modoApp) { ?>
            <a href="Estatisticas.php" class="btn btn-outline-secondary">Statistics</a>
            <a href="sobre.php" class="btn btn-outline-secondary">About</a>
            <?php } ?>
        </div>
    </div>
</nav>

<main class="app-main">
