<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Sessão e protecção CSRF.
 *
 * O site deixou de ter contas: os temas são só os públicos, criados a partir
 * da linha de comandos (tools/). A sessão continua a existir por uma única
 * razão — guardar o token CSRF que o api/resultado.php exige, para que só uma
 * página deste site possa registar resultados.
 */

/** Arranca a sessão com cookies endurecidos. Seguro chamar várias vezes. */
function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $seguro = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // inacessível ao JavaScript
        'secure'   => $seguro,
        'samesite' => 'Lax',  // bloqueia envio a partir de sites terceiros
    ]);

    session_start();
}

/* -------------------------------------------------------------------------- */
/*  CSRF                                                                       */
/* -------------------------------------------------------------------------- */

/** Devolve o token CSRF da sessão, gerando-o se necessário. */
function tokenCsrf(): string
{
    iniciarSessao();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** True se o token recebido corresponder ao da sessão. */
function csrfValido(?string $token): bool
{
    iniciarSessao();
    return !empty($_SESSION['csrf'])
        && is_string($token)
        && hash_equals($_SESSION['csrf'], $token);
}

/** Interrompe o pedido se o token CSRF não for válido. */
function exigirCsrf(): void
{
    if (!csrfValido($_POST['csrf'] ?? null)) {
        http_response_code(400);
        exit('Pedido inválido.');
    }
}

iniciarSessao();
