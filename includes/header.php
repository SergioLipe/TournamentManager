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

// Prefixo próprio: as páginas têm as suas listas de temas e o include
// partilha o mesmo scope, por isso nomes genéricos atropelavam-se.
$navTemasPublicos = temasPublicos();
$navTemasPessoais = (!$modoApp && autenticado()) ? temasDoUtilizador((int) utilizadorId()) : [];
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
    <meta name="theme-color" content="#2f6fed">
    <link rel="icon" href="icons/icon-192.png" sizes="192x192">
    <link rel="apple-touch-icon" href="icons/icon-192.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="CSS/style.css" rel="stylesheet">
</head>

<body>

<button id="toggleNavBtn" type="button" title="Show/hide menu" aria-label="Show/hide menu" aria-expanded="true">
    <span aria-hidden="true">&#9650;</span>
</button>

<nav id="mainNav" class="app-nav">
    <div class="app-nav__inner">

        <div class="app-nav__group app-nav__group--start">
            <?php if ($controlosTorneio) { ?>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownTemas" data-bs-toggle="dropdown" aria-expanded="false">
                        Choose theme
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownTemas">
                        <?php if ($navTemasPublicos === []) { ?>
                            <li><span class="dropdown-item-text text-muted">No public themes yet</span></li>
                        <?php } ?>
                        <?php foreach ($navTemasPublicos as $tema) { ?>
                            <li>
                                <button class="dropdown-item js-escolhe-tema" type="button"
                                        data-tema-id="<?= (int) $tema['id'] ?>"
                                        data-tema-nome="<?= e($tema['nome']) ?>"><?= e($tema['nome']) ?></button>
                            </li>
                        <?php } ?>
                    </ul>
                </div>

                <?php if (!$modoApp && autenticado()) { ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                id="dropdownTemasUtilizador" data-bs-toggle="dropdown" aria-expanded="false">
                            Your themes
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownTemasUtilizador">
                            <?php if ($navTemasPessoais === []) { ?>
                                <li><span class="dropdown-item-text text-muted">You have no themes yet</span></li>
                            <?php } ?>
                            <?php foreach ($navTemasPessoais as $tema) { ?>
                                <li>
                                    <button class="dropdown-item js-escolhe-tema" type="button"
                                            data-tema-id="<?= (int) $tema['id'] ?>"
                                            data-tema-nome="<?= e($tema['nome']) ?>"><?= e($tema['nome']) ?></button>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <div class="qty" role="group" aria-label="Number of competitors">
                    <span class="qty__label">Competitors</span>
                    <button class="qty__btn" type="button" id="qtyMenos" aria-label="Fewer competitors">&minus;</button>
                    <input type="number" id="numImagens" class="qty__input"
                           value="8" min="<?= MIN_COMPETITORS ?>" max="<?= MAX_COMPETITORS ?>" step="1"
                           aria-label="Number of competitors">
                    <button class="qty__btn" type="button" id="qtyMais" aria-label="More competitors">+</button>
                </div>
            <?php } ?>
        </div>

        <div class="app-nav__group app-nav__group--center">
            <a href="<?= $modoApp ? 'app.php' : 'index.php' ?>" class="app-nav__brand">TOURNAMENT</a>
        </div>

        <!--
            Na aplicação Android não há contas: nem login, nem registo, nem
            criação de temas, nem estatísticas. Só temas públicos e a bracket.
            Isto não é só cosmética — sem links para fora, a app nunca sai do
            /app.php e nunca abre um separador do browser por cima de si.
        -->
        <div class="app-nav__group app-nav__group--end">
            <?php if (!$modoApp) { ?>
            <a href="CriarTema.php" class="btn btn-outline-success">Create theme</a>
            <a href="Estatisticas.php" class="btn btn-outline-secondary">Statistics</a>
            <a href="sobre.php" class="btn btn-outline-secondary">About</a>
            <?php if (autenticado()) { ?>
                <span class="app-nav__user" title="Signed in">@<?= e(utilizadorNome() ?? '') ?></span>
                <form method="post" action="Logout.php" class="d-inline">
                    <?= campoCsrf() ?>
                    <button type="submit" class="btn btn-outline-secondary">Log out</button>
                </form>
            <?php } else { ?>
                <a href="Login.php" class="btn btn-outline-secondary">Log in</a>
                <a href="Registar.php" class="btn btn-outline-secondary">Register</a>
            <?php } ?>
            <?php } ?>
        </div>
    </div>
</nav>

<main class="app-main">
<?php mostrarMensagens(); ?>
