<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Ligação à base de dados via PDO.
 *
 * Todas as consultas passam por queries preparadas — nunca se concatena
 * input do utilizador dentro de SQL.
 */

/** Devolve a ligação PDO, criando-a na primeira chamada. */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        config('DB_HOST', 'localhost'),
        config('DB_PORT', '3306'),
        config('DB_NAME', 'torneio_db')
    );

    try {
        $pdo = new PDO($dsn, config('DB_USER', 'root'), config('DB_PASS', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Prepared statements reais no servidor, não emuladas pelo driver.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('Falha na ligação à base de dados: ' . $e->getMessage());
        http_response_code(500);
        exit('Erro ao ligar à base de dados.');
    }

    return $pdo;
}

/** Executa uma query preparada e devolve o statement. */
function query(string $sql, array $parametros = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($parametros);
    return $stmt;
}

/** Devolve a primeira linha do resultado, ou null. */
function obterLinha(string $sql, array $parametros = []): ?array
{
    $linha = query($sql, $parametros)->fetch();
    return $linha === false ? null : $linha;
}

/** Devolve todas as linhas do resultado. */
function obterTodas(string $sql, array $parametros = []): array
{
    return query($sql, $parametros)->fetchAll();
}

/** Devolve o valor da primeira coluna da primeira linha, ou null. */
function obterValor(string $sql, array $parametros = [])
{
    $valor = query($sql, $parametros)->fetchColumn();
    return $valor === false ? null : $valor;
}
