<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Sessões, autenticação e protecção CSRF.
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

/** True se houver um utilizador autenticado. */
function autenticado(): bool
{
    return !empty($_SESSION['autenticado']) && !empty($_SESSION['userId']);
}

/** Id do utilizador autenticado, ou null. */
function utilizadorId(): ?int
{
    return autenticado() ? (int) $_SESSION['userId'] : null;
}

/** Nome do utilizador autenticado, ou null. */
function utilizadorNome(): ?string
{
    return autenticado() ? (string) ($_SESSION['username'] ?? '') : null;
}

/** Redirecciona para o login se não houver sessão iniciada. */
function exigirLogin(): void
{
    if (!autenticado()) {
        header('Location: Login.php');
        exit;
    }
}

/** Regista o utilizador na sessão, com novo id de sessão. */
function autenticar(int $id, string $username): void
{
    // Evita fixação de sessão: o id anterior deixa de ser válido.
    session_regenerate_id(true);
    $_SESSION['userId']      = $id;
    $_SESSION['username']    = $username;
    $_SESSION['autenticado'] = true;
}

/* -------------------------------------------------------------------------- */
/*  Passwords                                                                  */
/* -------------------------------------------------------------------------- */

/** True se a string já for um hash (bcrypt/argon), e não texto simples. */
function ehHash(string $valor): bool
{
    return (bool) preg_match('/^\$(2[aby]|argon2(i|d|id))\$/', $valor);
}

/**
 * Valida a password de um utilizador.
 *
 * As contas antigas foram guardadas em texto simples. Quando uma dessas
 * autentica com sucesso, a password é convertida para hash nesse momento —
 * assim a migração acontece sem obrigar ninguém a mudar de password.
 */
function verificarPassword(int $utilizadorId, string $password, string $guardada): bool
{
    if (ehHash($guardada)) {
        if (!password_verify($password, $guardada)) {
            return false;
        }
        // Re-hash se o algoritmo por omissão entretanto mudou.
        if (password_needs_rehash($guardada, PASSWORD_DEFAULT)) {
            guardarHashPassword($utilizadorId, $password);
        }
        return true;
    }

    // Conta legada em texto simples: comparação em tempo constante.
    if (!hash_equals($guardada, $password)) {
        return false;
    }

    guardarHashPassword($utilizadorId, $password);
    return true;
}

/** Grava a password em hash para um utilizador. */
function guardarHashPassword(int $utilizadorId, string $password): void
{
    query(
        'UPDATE utilizador SET password = ? WHERE id = ?',
        [password_hash($password, PASSWORD_DEFAULT), $utilizadorId]
    );
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

/** Campo escondido com o token, para colocar dentro de cada formulário. */
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') . '">';
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
