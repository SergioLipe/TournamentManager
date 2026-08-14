#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Cria (ou actualiza) uma conta a partir da linha de comandos e passa-lhe
 * os temas públicos.
 *
 *   php database/criar-admin.php <username>
 *
 * A password é pedida de forma interactiva, para não ficar no histórico da
 * shell nem na lista de processos.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$username = $argv[1] ?? '';

if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
    fwrite(STDERR, "Uso: php database/criar-admin.php <username>\n");
    fwrite(STDERR, "O username tem 3 a 30 caracteres: letras, dígitos, ponto, traço ou underscore.\n");
    exit(1);
}

/** Lê uma password sem a mostrar no terminal, quando o sistema o permite. */
function lerPassword(string $pergunta): string
{
    fwrite(STDOUT, $pergunta);

    // Em Unix desliga-se o eco do terminal; no Windows não há equivalente
    // simples, por isso a password fica visível.
    $temStty = stripos(PHP_OS_FAMILY, 'Windows') === false && shell_exec('command -v stty') !== null;

    if ($temStty) {
        $estado = trim((string) shell_exec('stty -g'));
        shell_exec('stty -echo');
    }

    $password = trim((string) fgets(STDIN));

    if ($temStty) {
        shell_exec('stty ' . $estado);
        fwrite(STDOUT, "\n");
    }

    return $password;
}

$password = lerPassword("Password: ");
$repetir  = lerPassword("Repetir password: ");

if (strlen($password) < 8) {
    fwrite(STDERR, "A password tem de ter pelo menos 8 caracteres.\n");
    exit(1);
}

if ($password !== $repetir) {
    fwrite(STDERR, "As passwords não coincidem.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$existente = obterLinha('SELECT id FROM utilizador WHERE username = ?', [$username]);

if ($existente !== null) {
    query('UPDATE utilizador SET password = ? WHERE id = ?', [$hash, (int) $existente['id']]);
    $utilizadorId = (int) $existente['id'];
    fwrite(STDOUT, "Password de \"$username\" actualizada.\n");
} else {
    query('INSERT INTO utilizador (username, password) VALUES (?, ?)', [$username, $hash]);
    $utilizadorId = (int) db()->lastInsertId();
    fwrite(STDOUT, "Conta \"$username\" criada.\n");
}

// Os temas de base ficam públicos e à responsabilidade desta conta.
$temasBase = ['Food', 'Filmes', 'Bandas de Rock', 'VideoGames'];

foreach ($temasBase as $nome) {
    $tema = obterLinha('SELECT id FROM tema WHERE nome = ? AND utilizadorId = ?', [$nome, $utilizadorId]);

    if ($tema === null) {
        query(
            'INSERT INTO tema (nome, utilizadorId, publico) VALUES (?, ?, 1)',
            [$nome, $utilizadorId]
        );
        fwrite(STDOUT, "  tema público criado: $nome\n");
    } else {
        query('UPDATE tema SET publico = 1 WHERE id = ?', [(int) $tema['id']]);
        fwrite(STDOUT, "  tema marcado como público: $nome\n");
    }
}

fwrite(STDOUT, "Feito.\n");
