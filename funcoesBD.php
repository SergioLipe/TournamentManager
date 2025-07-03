<?php
declare(strict_types=1);

/**
 * Carrega automaticamente as dependências (por ex. vlucas/phpdotenv),
 * se existirem instaladas via Composer. Se não usares Composer,
 * podes remover este bloco sem afectar o restante código.
 */
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    if (class_exists(\Dotenv\Dotenv::class)) {
        // Lê as variáveis de ambiente definidas no ficheiro .env
        \Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
    }
}

/* -------------------------------------------------------------------------- */
/*  Utilitários                                                               */
/* -------------------------------------------------------------------------- */

function ativarErros(): void
{
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

/* -------------------------------------------------------------------------- */
/*  Ligação à Base de Dados                                                   */
/* -------------------------------------------------------------------------- */

function ativarLigacaoBaseDados()
{
    $servidor            = getenv('DB_HOST') ?: 'localhost';
    $utilizadorBaseDados = getenv('DB_USER') ?: 'root';
    $passwordBaseDados   = getenv('DB_PASS') ?: '';
    $baseDados           = getenv('DB_NAME') ?: 'torneio_db';

    global $link;
    $link = mysqli_connect($servidor, $utilizadorBaseDados, $passwordBaseDados, $baseDados);

    if (!$link) {
        throw new RuntimeException(
            'Falha na ligação à base de dados: ' .
            mysqli_connect_errno() . ' – ' .
            mysqli_connect_error()
        );
    }

    $GLOBALS['ligacaoBaseDados'] = $link;
    return $link;
}

function fecharLigacaoBaseDados(): void
{
    if (!empty($GLOBALS['ligacaoBaseDados'])) {
        mysqli_close($GLOBALS['ligacaoBaseDados']);
    }
}

function testarLigacaoBaseDados(): bool
{
    return !empty($GLOBALS['ligacaoBaseDados']) &&
           mysqli_ping($GLOBALS['ligacaoBaseDados']);
}

/* -------------------------------------------------------------------------- */
/*  Operações de BD                                                           */
/* -------------------------------------------------------------------------- */

function guardarDadosBaseDados(string $sqlString)
{
    return mysqli_query($GLOBALS['ligacaoBaseDados'], $sqlString);
}

function obterDadosBaseDados(string $sqlString)
{
    return mysqli_query($GLOBALS['ligacaoBaseDados'], $sqlString);
}

/* -------------------------------------------------------------------------- */
/*  Inicialização automática                                                  */
/* -------------------------------------------------------------------------- */

ativarErros();
ativarLigacaoBaseDados();
