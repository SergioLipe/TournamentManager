<?php
declare(strict_types=1);

/**
 * Configuração da aplicação.
 *
 * Lê as definições a partir de variáveis de ambiente, com um leitor de .env
 * mínimo para não obrigar a instalar o Composer. Se o vendor/ existir e o
 * vlucas/phpdotenv estiver disponível, esse tem prioridade.
 */

/** Raiz do projecto (sem barra final). */
define('APP_ROOT', dirname(__DIR__));

/** Pasta onde as imagens dos competidores são guardadas. */
define('UPLOAD_DIR', APP_ROOT . '/Imagens');

/**
 * Imagem mostrada num slot ainda por preencher.
 *
 * O nome é propositadamente só ASCII: é a imagem mais pedida do site e
 * chega lá por FTP, onde nomes acentuados nem sempre sobrevivem intactos.
 */
define('PLACEHOLDER_IMAGE', 'Imagens/placeholder.jpg');

/** Limites do torneio. */
define('MIN_COMPETITORS', 2);
define('MAX_COMPETITORS', 16);

/** Limites de upload. */
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
define('MAX_UPLOADS_PER_REQUEST', 40);

/**
 * Máximo de pixéis por imagem (~40 megapixéis).
 *
 * Um ficheiro pequeno pode declarar dimensões enormes e rebentar com a
 * memória do PHP ao ser descodificado — a chamada bomba de descompressão.
 * As dimensões são verificadas antes de se tentar abrir a imagem.
 */
define('MAX_IMAGE_PIXELS', 40000000);

/** Extensões aceites, mapeadas a partir do MIME real do ficheiro. */
const ALLOWED_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
];

/**
 * Valores lidos do .env.
 *
 * Guardados aqui, e não só no ambiente do processo, porque muitos
 * alojamentos partilhados desactivam o putenv() através do disable_functions
 * — é o caso do InfinityFree. Nesses servidores o putenv() não faz nada e o
 * getenv() nunca devolve o que se acabou de escrever; se o config() só olhasse
 * para o ambiente, ficava tudo vazio e a ligação à base de dados caía nos
 * valores por omissão.
 */
function &registoEnv(): array
{
    static $valores = [];
    return $valores;
}

/**
 * Carrega um ficheiro .env, se existir.
 *
 * Suporta `CHAVE=valor`, comentários com # e valores entre aspas.
 * Não sobrepõe variáveis já definidas no ambiente real.
 */
function carregarEnv(string $caminho): void
{
    $registo = &registoEnv();

    if (!is_readable($caminho)) {
        return;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) {
        return;
    }

    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor);

        // Remove aspas envolventes, se existirem.
        $primeiro = $valor[0] ?? '';
        if (strlen($valor) >= 2 && ($primeiro === '"' || $primeiro === "'") && substr($valor, -1) === $primeiro) {
            $valor = substr($valor, 1, -1);
        }

        // O ambiente real tem prioridade sobre o ficheiro.
        if (getenv($chave) === false && !isset($_ENV[$chave])) {
            $registo[$chave] = $valor;
            // Best-effort: onde o putenv funcionar, fica também no ambiente,
            // para o caso de haver código de terceiros a ler de lá.
            if (function_exists('putenv')) {
                @putenv("$chave=$valor");
            }
            $_ENV[$chave] = $valor;
        }
    }
}

/**
 * Lê uma definição, com valor por omissão.
 *
 * A ordem importa: primeiro o ambiente real (permite sobrepor a configuração
 * sem tocar no ficheiro), depois o que foi lido do .env.
 */
function config(string $chave, string $omissao = ''): string
{
    $valor = getenv($chave);
    if ($valor !== false && $valor !== '') {
        return $valor;
    }

    if (isset($_ENV[$chave]) && $_ENV[$chave] !== '') {
        return (string) $_ENV[$chave];
    }

    $registo = registoEnv();
    if (isset($registo[$chave]) && $registo[$chave] !== '') {
        return (string) $registo[$chave];
    }

    return $omissao;
}

/** True quando a aplicação corre em modo de desenvolvimento. */
function emDesenvolvimento(): bool
{
    return config('APP_ENV', 'production') === 'development';
}

$autoload = APP_ROOT . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

if (class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable(APP_ROOT)->safeLoad();
} else {
    carregarEnv(APP_ROOT . '/.env');
}

/*
 * Em produção os erros nunca devem chegar ao navegador: uma stack trace do
 * PDO expõe credenciais e estrutura da base de dados.
 */
if (emDesenvolvimento()) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}
ini_set('log_errors', '1');
error_reporting(E_ALL);
