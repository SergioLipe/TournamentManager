<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

if (autenticado()) {
    redirecionar('index.php');
}

$erro = '';

if (ehPost()) {
    exigirCsrf();

    $username = trim((string) ($_POST['utilizador'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $utilizador = obterLinha(
        'SELECT id, username, password FROM utilizador WHERE username = ?',
        [$username]
    );

    if ($utilizador !== null
        && verificarPassword((int) $utilizador['id'], $password, (string) $utilizador['password'])
    ) {
        autenticar((int) $utilizador['id'], (string) $utilizador['username']);
        guardarMensagem('success', 'Signed in.');
        // O redireccionamento tem de acontecer antes de qualquer HTML sair —
        // na versão antiga o header() vinha depois de um echo e não fazia nada.
        redirecionar('index.php');
    }

    // Uma mensagem única para utilizador errado e password errada, para não
    // revelar que nomes de utilizador existem.
    $erro = 'Wrong username or password.';
    usleep(300000); // trava um pouco a tentativa por força bruta
}

$tituloPagina = 'Log in';
require __DIR__ . '/includes/header.php';
?>

<div class="container form-estreito">
    <h1 class="h2 mb-4">Log in</h1>

    <?php if ($erro !== '') { ?>
        <div class="alert alert-danger"><?= e($erro) ?></div>
    <?php } ?>

    <form method="post" novalidate>
        <?= campoCsrf() ?>
        <div class="mb-3">
            <label class="form-label" for="utilizador">Username</label>
            <input type="text" id="utilizador" name="utilizador" class="form-control"
                   autocomplete="username" required
                   value="<?= e((string) ($_POST['utilizador'] ?? '')) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control"
                   autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary">Log in</button>
        <a href="Registar.php" class="btn btn-link">Create an account</a>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
