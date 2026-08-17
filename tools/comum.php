<?php
declare(strict_types=1);

/**
 * Funções partilhadas pelas ferramentas de linha de comandos.
 *
 * Estas ferramentas correm no computador de quem mantém o site, nunca no
 * servidor: a pasta tools/ está excluída do deploy.sh.
 */

require_once dirname(__DIR__) . '/includes/config.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/** Pasta com as listas de nomes, uma por tema. */
define('DIR_LISTAS', __DIR__ . '/temas');

/** Pasta com os nomes a mostrar, um CSV por tema. */
define('DIR_NOMES', __DIR__ . '/nomes');

/** Pastas dentro de Imagens/ que não são temas públicos. */
const PASTAS_IGNORADAS = ['temas'];

/* -------------------------------------------------------------------------- */
/*  Texto                                                                     */
/* -------------------------------------------------------------------------- */

/**
 * Substitui os acentos pelas letras correspondentes.
 *
 * Não se usa o iconv com //TRANSLIT: o resultado depende da locale do sistema
 * e no Windows um 'à' chega a sair como "'a". Um mapa explícito dá sempre o
 * mesmo resultado em qualquer máquina.
 */
function semAcentos(string $texto): string
{
    static $mapa = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n', 'ý' => 'y', 'ÿ' => 'y',
        'æ' => 'ae', 'œ' => 'oe', 'ß' => 'ss', 'ð' => 'd', 'þ' => 'th',
        'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N', 'Ý' => 'Y',
        'Æ' => 'AE', 'Œ' => 'OE', 'Ð' => 'D', 'Þ' => 'TH',
        '’' => "'", '‘' => "'", '“' => '"', '”' => '"', '–' => '-', '—' => '-',
    ];

    return strtr($texto, $mapa);
}

/**
 * Converte um nome no nome de ficheiro correspondente, sem extensão.
 *
 * Só ASCII e sem espaços: o servidor de FTP do alojamento recusa nomes
 * acentuados, e já aconteceu chegarem lá ficheiros com o nome corrompido.
 * O nome bonito, com acentos, fica no CSV e vai para a base de dados.
 */
function nomeFicheiro(string $nome): string
{
    $slug = semAcentos(trim($nome));
    $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    if ($slug === '') {
        $slug = 'competidor';
    }

    return substr($slug, 0, 60);
}

/**
 * Nome a mostrar deduzido do nome do ficheiro.
 *
 * Igual ao que o upload pelo site faz (nomeCompetidorDeFicheiro), para que
 * um tema semeado e um tema enviado pelo site se comportem da mesma maneira.
 */
function nomeAPartirDoFicheiro(string $ficheiro): string
{
    $nome = pathinfo($ficheiro, PATHINFO_FILENAME);
    $nome = str_replace(['_', '-'], ' ', $nome);
    $nome = trim(preg_replace('/\s+/u', ' ', $nome) ?? '');

    return $nome === '' ? 'Competitor' : mb_substr($nome, 0, 50);
}

/** Nome da pasta em Imagens/ para um tema. Ex.: 'Bandas de Rock' -> 'Bandas-de-Rock'. */
function pastaDoTemaPublico(string $tema): string
{
    return nomeFicheiro($tema);
}

/** Nome a mostrar deduzido da pasta. Ex.: 'Bandas-de-Rock' -> 'Bandas de Rock'. */
function temaAPartirDaPasta(string $pasta): string
{
    return str_replace('-', ' ', $pasta);
}

/* -------------------------------------------------------------------------- */
/*  Nomes a mostrar (tools/nomes/<Pasta>.csv)                                 */
/* -------------------------------------------------------------------------- */

/**
 * Lê o CSV de um tema.
 *
 * Formato: `ficheiro.jpg,Nome A Mostrar`, uma linha por competidor.
 * Uma linha `#tema=Nome Do Tema` no início sobrepõe o nome deduzido da pasta,
 * que é o que permite ter um tema chamado 'Séries' numa pasta 'Series'.
 *
 * Devolve ['tema' => ?string, 'nomes' => array<string,string>].
 */
function lerNomes(string $pasta): array
{
    $caminho = DIR_NOMES . '/' . $pasta . '.csv';
    $fora    = ['tema' => null, 'nomes' => []];

    if (!is_readable($caminho)) {
        return $fora;
    }

    $handle = fopen($caminho, 'r');
    if ($handle === false) {
        return $fora;
    }

    while (($linha = fgetcsv($handle)) !== false) {
        $primeiro = trim((string) ($linha[0] ?? ''));

        if ($primeiro === '') {
            continue;
        }

        if ($primeiro[0] === '#') {
            if (preg_match('/^#\s*tema\s*=\s*(.+)$/u', $primeiro, $m) === 1) {
                $fora['tema'] = trim($m[1]);
            }
            continue;
        }

        $nome = trim((string) ($linha[1] ?? ''));
        if ($nome !== '') {
            $fora['nomes'][$primeiro] = mb_substr($nome, 0, 50);
        }
    }

    fclose($handle);

    return $fora;
}

/** Escreve o CSV de nomes de um tema, ordenado pelo nome do ficheiro. */
function escreverNomes(string $pasta, ?string $tema, array $nomes): void
{
    if (!is_dir(DIR_NOMES) && !mkdir(DIR_NOMES, 0755, true) && !is_dir(DIR_NOMES)) {
        throw new RuntimeException('Não foi possível criar ' . DIR_NOMES);
    }

    ksort($nomes, SORT_NATURAL | SORT_FLAG_CASE);

    $handle = fopen(DIR_NOMES . '/' . $pasta . '.csv', 'w');
    if ($handle === false) {
        throw new RuntimeException('Não foi possível escrever o CSV de ' . $pasta);
    }

    fwrite($handle, "# ficheiro,nome a mostrar — gerado por tools/buscar-imagens.php\n");
    if ($tema !== null && $tema !== temaAPartirDaPasta($pasta)) {
        fwrite($handle, '#tema=' . $tema . "\n");
    }

    foreach ($nomes as $ficheiro => $nome) {
        fputcsv($handle, [$ficheiro, $nome]);
    }

    fclose($handle);
}

/* -------------------------------------------------------------------------- */
/*  Imagens                                                                   */
/* -------------------------------------------------------------------------- */

/** Lista os ficheiros de imagem de uma pasta de tema, por ordem alfabética. */
function imagensDaPasta(string $caminho): array
{
    $extensoes = array_flip(ALLOWED_IMAGE_TYPES) + ['jfif' => 1, 'jpeg' => 1];
    $ficheiros = [];

    foreach ((array) scandir($caminho) as $ficheiro) {
        if ($ficheiro === '.' || $ficheiro === '..' || !is_file($caminho . '/' . $ficheiro)) {
            continue;
        }

        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (isset($extensoes[$ext])) {
            $ficheiros[] = $ficheiro;
        }
    }

    sort($ficheiros, SORT_NATURAL | SORT_FLAG_CASE);

    return $ficheiros;
}

/**
 * Confirma que os bytes descarregados são mesmo uma imagem aceite.
 *
 * As mesmas verificações que o upload pelo site faz em includes/uploads.php:
 * o tipo vem do conteúdo, nunca do URL ou do Content-Type da resposta.
 * Devolve a extensão a usar, ou null.
 */
function extensaoDaImagem(string $bytes): ?string
{
    if ($bytes === '' || strlen($bytes) > MAX_UPLOAD_BYTES) {
        return null;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
    if (!is_string($mime) || !isset(ALLOWED_IMAGE_TYPES[$mime])) {
        return null;
    }

    if ($mime !== 'image/avif') {
        $dimensoes = @getimagesizefromstring($bytes);

        if ($dimensoes === false || $dimensoes[0] < 1 || $dimensoes[1] < 1) {
            return null;
        }
        if ($dimensoes[0] * $dimensoes[1] > MAX_IMAGE_PIXELS) {
            return null;
        }
        if (isset($dimensoes['mime']) && $dimensoes['mime'] !== $mime) {
            return null;
        }
    }

    return ALLOWED_IMAGE_TYPES[$mime];
}

/* -------------------------------------------------------------------------- */
/*  Consola                                                                   */
/* -------------------------------------------------------------------------- */

/** Escreve uma linha no stdout. */
function linha(string $texto = ''): void
{
    fwrite(STDOUT, $texto . PHP_EOL);
}

/** Escreve uma linha no stderr. */
function erro(string $texto): void
{
    fwrite(STDERR, $texto . PHP_EOL);
}

/** Lê uma opção `--chave=valor` da linha de comandos. */
function opcao(array $argv, string $chave, string $omissao = ''): string
{
    foreach ($argv as $arg) {
        if (strpos($arg, "--$chave=") === 0) {
            return substr($arg, strlen($chave) + 3);
        }
    }

    return $omissao;
}

/** True se `--flag` estiver presente. */
function temFlag(array $argv, string $flag): bool
{
    return in_array("--$flag", $argv, true);
}

/** Argumentos que não são opções. */
function argumentos(array $argv): array
{
    return array_values(array_filter(
        array_slice($argv, 1),
        static fn(string $a): bool => strpos($a, '--') !== 0
    ));
}
