<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

// Só por POST e com token: caso contrário bastava um <img src="Logout.php">
// noutro site qualquer para terminar a sessão de quem lá passasse.
if (!ehPost()) {
    redirecionar('index.php');
}

exigirCsrf();

$_SESSION = [];

// Invalida também o cookie de sessão no browser.
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $parametros['path'],
            'domain'   => $parametros['domain'],
            'secure'   => $parametros['secure'],
            'httponly' => $parametros['httponly'],
            'samesite' => $parametros['samesite'] ?? 'Lax',
        ]
    );
}

session_destroy();

iniciarSessao();
guardarMensagem('success', 'Signed out.');
redirecionar('index.php');
