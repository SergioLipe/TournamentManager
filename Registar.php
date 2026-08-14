<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

if (autenticado()) {
    redirecionar('index.php');
}

$erros = [];

if (ehPost()) {
    exigirCsrf();

    $username = trim((string) ($_POST['utilizador'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirma = (string) ($_POST['confirmacao'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
        $erros[] = 'The username must be 3 to 30 characters, using only letters, digits, dot, dash or underscore.';
    }
    if (strlen($password) < 8) {
        $erros[] = 'The password must be at least 8 characters long.';
    }
    if ($password !== $confirma) {
        $erros[] = 'The two passwords do not match.';
    }
    if ($erros === [] && obterValor('SELECT 1 FROM utilizador WHERE username = ?', [$username]) !== null) {
        $erros[] = 'That username is already taken.';
    }

    if ($erros === []) {
        query(
            'INSERT INTO utilizador (username, password) VALUES (?, ?)',
            [$username, password_hash($password, PASSWORD_DEFAULT)]
        );

        autenticar((int) db()->lastInsertId(), $username);
        guardarMensagem('success', 'Account created. Welcome!');
        redirecionar('index.php');
    }
}

$tituloPagina = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="container form-estreito">
    <h1 class="h2 mb-4">Create an account</h1>

    <?php foreach ($erros as $erro) { ?>
        <div class="alert alert-danger"><?= e($erro) ?></div>
    <?php } ?>

    <form method="post" novalidate>
        <?= campoCsrf() ?>
        <div class="mb-3">
            <label class="form-label" for="utilizador">Username</label>
            <input type="text" id="utilizador" name="utilizador" class="form-control"
                   autocomplete="username" minlength="3" maxlength="30" required
                   value="<?= e((string) ($_POST['utilizador'] ?? '')) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control"
                   autocomplete="new-password" minlength="8" required>
            <div class="form-text">At least 8 characters.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="confirmacao">Repeat password</label>
            <input type="password" id="confirmacao" name="confirmacao" class="form-control"
                   autocomplete="new-password" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
        <a href="Login.php" class="btn btn-link">I already have an account</a>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
