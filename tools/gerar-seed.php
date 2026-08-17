<?php
declare(strict_types=1);

require_once __DIR__ . '/comum.php';

/**
 * Reescreve database/seed-temas-publicos.sql a partir do que está em Imagens/.
 *
 * Uso:
 *   php tools/gerar-seed.php                  reescreve o ficheiro
 *   php tools/gerar-seed.php --dono=admin     dono dos temas (por omissão admin)
 *   php tools/gerar-seed.php --stdout         imprime em vez de gravar
 *
 * Cada subpasta de Imagens/ (excepto temas/, que é onde ficam os uploads dos
 * utilizadores) vira um tema público. Os nomes a mostrar vêm de
 * tools/nomes/<Pasta>.csv; para os ficheiros que lá não estiverem, o nome é
 * deduzido do nome do ficheiro, como no upload pelo site.
 *
 * O SQL resultante pode correr-se as vezes que forem precisas: os temas que já
 * existam mantêm o id — e portanto os links para as estatísticas continuam a
 * funcionar — e só os competidores são substituídos.
 */

$dono   = opcao($argv, 'dono', 'admin');
$saida  = temFlag($argv, 'stdout');
$destino = dirname(__DIR__) . '/database/seed-temas-publicos.sql';

if (!preg_match('/^[A-Za-z0-9_.-]{1,30}$/', $dono)) {
    erro('Username inválido: ' . $dono);
    exit(2);
}

/** Escapa uma string para dentro de plicas no SQL. */
function sql(string $valor): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "''"], $valor) . "'";
}

/* -------------------------------------------------------------------------- */
/*  Recolher os temas                                                         */
/* -------------------------------------------------------------------------- */

$pastas = [];

foreach ((array) scandir(UPLOAD_DIR) as $entrada) {
    if ($entrada === '.' || $entrada === '..' || $entrada[0] === '.') {
        continue;
    }
    if (in_array($entrada, PASTAS_IGNORADAS, true) || !is_dir(UPLOAD_DIR . '/' . $entrada)) {
        continue;
    }

    $pastas[] = $entrada;
}

sort($pastas, SORT_NATURAL | SORT_FLAG_CASE);

if ($pastas === []) {
    erro('Não há nenhuma pasta de tema em ' . UPLOAD_DIR);
    exit(1);
}

$temas = [];

foreach ($pastas as $pasta) {
    $ficheiros = imagensDaPasta(UPLOAD_DIR . '/' . $pasta);

    if ($ficheiros === []) {
        erro('Aviso: Imagens/' . $pasta . '/ não tem imagens — ignorado.');
        continue;
    }

    $csv  = lerNomes($pasta);
    $nome = $csv['tema'] ?? temaAPartirDaPasta($pasta);

    if (mb_strlen($nome) > 25) {
        erro('Aviso: o tema "' . $nome . '" passa dos 25 caracteres — ignorado.');
        continue;
    }

    if (count($ficheiros) < MIN_COMPETITORS) {
        erro('Aviso: "' . $nome . '" só tem ' . count($ficheiros)
            . ' imagem(ns); o torneio precisa de ' . MIN_COMPETITORS . '.');
    }

    $competidores = [];

    foreach ($ficheiros as $ficheiro) {
        $competidores[] = [
            'nome'   => $csv['nomes'][$ficheiro] ?? nomeAPartirDoFicheiro($ficheiro),
            'imagem' => 'Imagens/' . $pasta . '/' . $ficheiro,
        ];
    }

    $temas[] = ['nome' => $nome, 'pasta' => $pasta, 'competidores' => $competidores];
}

if ($temas === []) {
    erro('Nenhum tema utilizável.');
    exit(1);
}

// Duas pastas com o mesmo nome de tema dariam um SQL silenciosamente errado:
// o segundo bloco começa por DELETE FROM competidor WHERE TemaId = @t e
// apagava os competidores que o primeiro tinha acabado de inserir.
$vistos = [];

foreach ($temas as $tema) {
    $chave = mb_strtolower($tema['nome']);

    if (isset($vistos[$chave])) {
        erro(sprintf(
            'Erro: as pastas %s/ e %s/ dão as duas o tema "%s". Muda o #tema= num dos CSVs.',
            $vistos[$chave],
            $tema['pasta'],
            $tema['nome']
        ));
        exit(1);
    }

    $vistos[$chave] = $tema['pasta'];
}

// Por nome a mostrar, não por pasta: é assim que o site os lista, e evita ver
// 'Rock Bands' entre 'Animals' e 'Classic Cars' só porque a pasta ainda se
// chama Bandas-de-Rock.
usort($temas, static fn(array $a, array $b): int => strcasecmp($a['nome'], $b['nome']));

/* -------------------------------------------------------------------------- */
/*  Escrever o SQL                                                            */
/* -------------------------------------------------------------------------- */

$linhas = [];

$linhas[] = '-- ---------------------------------------------------------------------------';
$linhas[] = '-- Temas públicos e respectivos competidores.';
$linhas[] = '--';
$linhas[] = '-- GERADO por tools/gerar-seed.php a partir do conteúdo de Imagens/.';
$linhas[] = '-- Não editar à mão: para mudar um nome, muda-se o CSV em tools/nomes/ e';
$linhas[] = '-- volta-se a correr o gerador.';
$linhas[] = '--';
$linhas[] = '-- Correr DEPOIS do schema.sql, numa base de dados que já tenha uma conta de';
$linhas[] = '-- utilizador criada (ver database/criar-admin.php).';
$linhas[] = '--';
$linhas[] = '-- Os caminhos correspondem aos ficheiros publicados em Imagens/. Os nomes a';
$linhas[] = '-- mostrar mantêm os acentos originais, mesmo que o ficheiro em disco seja só';
$linhas[] = '-- ASCII: o servidor de FTP deste alojamento recusa nomes com espaços ou';
$linhas[] = '-- acentos, mas a base de dados não tem esse problema.';
$linhas[] = '--';
$linhas[] = '-- Pode correr-se as vezes que forem precisas. Os temas que já existirem são';
$linhas[] = '-- reaproveitados — mantêm o id, e portanto os links para as estatísticas';
$linhas[] = '-- continuam válidos — e só os competidores são substituídos.';
$linhas[] = '-- ---------------------------------------------------------------------------';
$linhas[] = '';
$linhas[] = 'SET NAMES utf8mb4;';
$linhas[] = '';
$linhas[] = 'SET @dono = (SELECT id FROM utilizador WHERE username = ' . sql($dono) . ');';

$totalCompetidores = 0;

foreach ($temas as $tema) {
    $nomeSql = sql($tema['nome']);

    $linhas[] = '';
    $linhas[] = '-- ---------- ' . $tema['nome'] . ' ----------';
    $linhas[] = 'INSERT INTO tema (nome, utilizadorId, publico) VALUES (' . $nomeSql . ', @dono, 1)';
    $linhas[] = '    ON DUPLICATE KEY UPDATE publico = 1;';
    $linhas[] = 'SET @t = (SELECT id FROM tema WHERE nome = ' . $nomeSql . ' AND utilizadorId = @dono);';
    $linhas[] = 'DELETE FROM competidor WHERE TemaId = @t;';
    $linhas[] = 'INSERT INTO competidor (nome, imagem, TemaId) VALUES';

    $valores = [];
    foreach ($tema['competidores'] as $competidor) {
        $valores[] = '    (' . sql($competidor['nome']) . ', ' . sql($competidor['imagem']) . ', @t)';
    }

    $linhas[] = implode(",\n", $valores) . ';';
    $totalCompetidores += count($tema['competidores']);
}

$sql = implode("\n", $linhas) . "\n";

if ($saida) {
    fwrite(STDOUT, $sql);
    exit(0);
}

if (file_put_contents($destino, $sql) === false) {
    erro('Não consegui escrever ' . $destino);
    exit(1);
}

linha('Escrito database/seed-temas-publicos.sql');
linha('  ' . count($temas) . ' temas, ' . $totalCompetidores . ' competidores, dono "' . $dono . '".');
linha();

foreach ($temas as $tema) {
    linha(sprintf('  %-28s %3d', $tema['nome'], count($tema['competidores'])));
}

linha();
linha('A seguir:');
linha('  1. correr o SQL na base de dados (phpMyAdmin ou mysql < database/seed-temas-publicos.sql)');
linha('  2. git add Imagens tools database/seed-temas-publicos.sql');
linha('  3. ./deploy.sh');
