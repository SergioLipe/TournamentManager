<?php
declare(strict_types=1);

require_once __DIR__ . '/comum.php';

/**
 * Descarrega as imagens de um tema a partir da Wikipédia.
 *
 * Uso:
 *   php tools/buscar-imagens.php                     todas as listas
 *   php tools/buscar-imagens.php Animais             só tools/temas/Animais.txt
 *   php tools/buscar-imagens.php Doces --lang=pt     procurar na Wikipédia PT
 *   php tools/buscar-imagens.php --dry-run           mostrar sem descarregar
 *   php tools/buscar-imagens.php Animais --refazer   voltar a descarregar tudo
 *
 * Normalmente a pasta em Imagens/ sai do nome do tema (o de #tema=, ou o nome
 * do ficheiro da lista). Quando o tema já existe com uma pasta de nome
 * diferente — caso de 'Movies', cuja pasta ficou 'Filmes' para não reenviar
 * as imagens por FTP — usa-se --pasta= para acrescentar à pasta certa:
 *
 *   php tools/buscar-imagens.php "More Movies" --pasta=Filmes
 *
 * Só faz sentido com uma lista de cada vez. Essa lista tem de conter a sua
 * própria linha #tema= com o nome a mostrar (aqui, 'Movies'), senão o nome do
 * ficheiro da lista ('More Movies') passava a ser o nome do tema.
 *
 * Cada lista é um ficheiro de texto em tools/temas/, um competidor por linha:
 *
 *     #tema=Raças de Cães             <- opcional: nome do tema com acentos
 *     Leão | Lion                      <- à esquerda o nome a mostrar,
 *     Tigre | Tiger                       à direita o artigo a procurar
 *     T. rex | File:Trex.jpg           <- ou um ficheiro do Commons, quando a
 *                                         imagem principal do artigo não serve
 *     # linhas com cardinal são notas
 *
 * Sem a linha #tema, o nome do tema é o nome do ficheiro da lista. As imagens
 * ficam em Imagens/<Tema>/ com nomes só ASCII, e os nomes bonitos vão para
 * tools/nomes/<Tema>.csv, que o gerar-seed.php lê a seguir.
 *
 * Os títulos são pedidos em lotes de LOTE de cada vez, e não um a um: a API da
 * Wikimedia responde 429 a quem faça uma dezena de pedidos seguidos, e um tema
 * de 25 nomes esgotava a quota a meio. Assim é um pedido por tema.
 *
 * Nota sobre direitos: a imagem principal de um artigo é quase sempre do
 * Wikimedia Commons, com licença livre, mas em filmes e videojogos costuma ser
 * o cartaz ou a capa, que a Wikipédia usa ao abrigo de fair use e não é livre.
 * Vale a pena confirmar antes de publicar um tema desses.
 */

const API_TIMEOUT   = 30;
const TAMANHO_THUMB = 800;

/** Títulos por pedido. A API aceita 50; fica-se abaixo para o URL não crescer demais. */
const LOTE = 40;

/** Pausa entre pedidos, em microssegundos. */
const PAUSA = 600000;

/** Tentativas por pedido, quando a resposta é 429 ou 5xx. */
const TENTATIVAS = 6;

/** Espera máxima entre tentativas, em segundos. */
const ESPERA_MAX = 30;

$argumentos = argumentos($argv);
$lingua     = opcao($argv, 'lang', 'en');
$seco       = temFlag($argv, 'dry-run');
$refazer    = temFlag($argv, 'refazer');
$agente     = opcao($argv, 'ua', 'TournamentManager/1.0 (ferramenta local de seeding; PHP ' . PHP_VERSION . ')');
$pastaForcada = opcao($argv, 'pasta', '');

if (!preg_match('/^[a-z]{2,3}(-[a-z]+)?$/i', $lingua)) {
    erro('Língua inválida: ' . $lingua);
    exit(2);
}

/* -------------------------------------------------------------------------- */
/*  HTTP                                                                      */
/* -------------------------------------------------------------------------- */

/** Handle de cURL reutilizado em todos os pedidos. */
function curlPartilhado(string $agente)
{
    static $ch = null;

    if ($ch === null) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => API_TIMEOUT,
            // A Wikimedia bloqueia pedidos sem User-Agent identificável.
            CURLOPT_USERAGENT      => $agente,
            CURLOPT_ENCODING       => '',
        ]);
    }

    return $ch;
}

/**
 * GET com espera crescente enquanto a resposta for 429 ou 5xx.
 *
 * A Wikimedia responde 429 a rajadas de pedidos anónimos, e um tema de 25
 * imagens é exactamente isso. A espera duplica a cada tentativa; sem ela,
 * metade de um tema falhava a meio do download.
 *
 * Devolve o corpo, ou null. $motivo fica com o que correu mal.
 */
function obter(string $url, string $agente, ?string &$motivo = null): ?string
{
    $ch     = curlPartilhado($agente);
    $espera = 2;
    $motivo = null;

    for ($tentativa = 1; $tentativa <= TENTATIVAS; $tentativa++) {
        curl_setopt($ch, CURLOPT_URL, $url);

        $corpo  = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if (is_string($corpo) && $codigo === 200) {
            usleep(PAUSA);
            return $corpo;
        }

        $motivo = $codigo !== 0
            ? 'HTTP ' . $codigo
            : (curl_error($ch) !== '' ? curl_error($ch) : 'sem resposta');

        // 404 e afins não melhoram por se insistir.
        if ($codigo !== 429 && $codigo < 500 && $codigo !== 0) {
            return null;
        }

        if ($tentativa < TENTATIVAS) {
            sleep($espera);
            $espera = min($espera * 2, ESPERA_MAX);
        }
    }

    $motivo .= ', ' . TENTATIVAS . ' tentativas';

    return null;
}

/** GET a devolver JSON descodificado. */
function obterJson(string $url, string $agente): ?array
{
    $corpo = obter($url, $agente);
    if ($corpo === null) {
        return null;
    }

    $dados = json_decode($corpo, true);

    return is_array($dados) ? $dados : null;
}

/** URL da API da Wikipédia com os parâmetros dados. */
function urlApi(string $lingua, array $parametros): string
{
    return 'https://' . $lingua . '.wikipedia.org/w/api.php?'
        . http_build_query(['format' => 'json', 'formatversion' => '2'] + $parametros);
}

/* -------------------------------------------------------------------------- */
/*  Wikipédia                                                                 */
/* -------------------------------------------------------------------------- */

/**
 * URLs das imagens principais de vários artigos, num só pedido por lote.
 *
 * Pede-se a miniatura em vez do original de propósito: os originais do Commons
 * chegam facilmente aos vários MB e a grelha de cartões do site mostra-os com
 * algumas centenas de pixéis.
 *
 * Devolve um mapa `título pedido => URL`, sem as entradas que não deram nada.
 */
function imagensDosArtigos(array $titulos, string $lingua, string $agente): array
{
    $encontradas = [];

    foreach (array_chunk(array_values(array_unique($titulos)), LOTE) as $lote) {
        $dados = obterJson(urlApi($lingua, [
            'action'      => 'query',
            'prop'        => 'pageimages',
            'piprop'      => 'thumbnail',
            'pithumbsize' => (string) TAMANHO_THUMB,
            'redirects'   => '1',
            'titles'      => implode('|', $lote),
        ]), $agente);

        if ($dados === null) {
            continue;
        }

        $consulta = $dados['query'] ?? [];

        // A API devolve as páginas pelo título final. Para se voltar ao título
        // que foi pedido é preciso desfazer duas normalizações: a do próprio
        // texto ('leão' -> 'Leão') e a dos redireccionamentos ('Orca' ->
        // 'Killer whale').
        $passos = [];
        foreach (['normalized', 'redirects'] as $tabela) {
            foreach ($consulta[$tabela] ?? [] as $par) {
                if (isset($par['from'], $par['to'])) {
                    $passos[$par['from']] = $par['to'];
                }
            }
        }

        $porTituloFinal = [];
        foreach ($consulta['pages'] ?? [] as $pagina) {
            $url = $pagina['thumbnail']['source'] ?? null;

            if (isset($pagina['title']) && is_string($url) && $url !== '') {
                $porTituloFinal[$pagina['title']] = $url;
            }
        }

        foreach ($lote as $pedido) {
            $titulo = $pedido;

            // Segue a cadeia de normalizações até parar. O limite evita ficar
            // preso num ciclo de redireccionamentos.
            for ($salto = 0; $salto < 5 && isset($passos[$titulo]); $salto++) {
                $titulo = $passos[$titulo];
            }

            if (isset($porTituloFinal[$titulo])) {
                $encontradas[$pedido] = $porTituloFinal[$titulo];
            }
        }
    }

    return $encontradas;
}

/**
 * Imagem principal de um artigo, pelo endpoint REST em vez da API clássica.
 *
 * A prop=pageimages da action=query filtra as imagens que não são de licença
 * livre — o que chumba silenciosamente qualquer cartaz de filme ou capa de
 * jogo, que na Wikipédia quase sempre são ficheiros "fair use" locais, não do
 * Commons. O endpoint REST não aplica esse filtro. Sem batch — só serve como
 * último recurso para o punhado de títulos que a via rápida não resolveu.
 */
function imagemViaResumo(string $titulo, string $lingua, string $agente): ?string
{
    $url = 'https://' . $lingua . '.wikipedia.org/api/rest_v1/page/summary/'
        . rawurlencode(str_replace(' ', '_', $titulo)) . '?redirect=true';

    $dados = obterJson($url, $agente);
    $fonte = $dados['thumbnail']['source'] ?? ($dados['originalimage']['source'] ?? null);

    return is_string($fonte) && $fonte !== '' ? $fonte : null;
}

/**
 * Uma entrada aponta a um ficheiro do Commons em vez de a um artigo?
 *
 * Escreve-se 'Nome | File:Alguma coisa.jpg' na lista. Serve para os casos em
 * que a imagem principal do artigo não é o que se quer mostrar: nos dinossauros
 * é quase sempre um esqueleto de museu, e o que interessa é a reconstituição
 * do animal vivo, que está no Commons mas não é a imagem principal.
 */
function eFicheiroCommons(string $artigo): bool
{
    return preg_match('/^\s*(File|Image|Ficheiro|Imagem)\s*:/i', $artigo) === 1;
}

/** 'Imagem:pe_na_areia.jpg' -> 'File:Pe na areia.jpg', que é como o Commons o trata. */
function tituloCommons(string $artigo): string
{
    $nome = preg_replace('/^\s*(File|Image|Ficheiro|Imagem)\s*:\s*/i', '', trim($artigo)) ?? '';
    $nome = str_replace('_', ' ', $nome);

    return 'File:' . mb_strtoupper(mb_substr($nome, 0, 1)) . mb_substr($nome, 1);
}

/**
 * URLs de miniatura de ficheiros do Commons, num pedido por lote.
 *
 * Devolve um mapa `entrada da lista => URL`, sem as que não existem — um nome
 * de ficheiro errado desaparece do mapa e sai depois como SEM IMAGEM, em vez
 * de passar por uma imagem qualquer.
 */
function imagensDeFicheiros(array $artigos, string $agente): array
{
    $encontradas = [];

    if ($artigos === []) {
        return $encontradas;
    }

    // O título canónico é a chave do lado do Commons; a entrada da lista é a
    // chave do lado de cá. Duas listas com o mesmo ficheiro pedem-no uma vez.
    $porTitulo = [];
    foreach (array_unique($artigos) as $artigo) {
        $porTitulo[tituloCommons($artigo)][] = $artigo;
    }

    foreach (array_chunk(array_keys($porTitulo), LOTE) as $lote) {
        $dados = obterJson('https://commons.wikimedia.org/w/api.php?' . http_build_query([
            'format'      => 'json',
            'formatversion' => '2',
            'action'      => 'query',
            'prop'        => 'imageinfo',
            'iiprop'      => 'url',
            'iiurlwidth'  => (string) TAMANHO_THUMB,
            'redirects'   => '1',
            'titles'      => implode('|', $lote),
        ]), $agente);

        if ($dados === null) {
            continue;
        }

        $consulta = $dados['query'] ?? [];

        // Como nos artigos: a resposta vem pelo título final, e é preciso
        // desfazer a normalização e os redireccionamentos para voltar ao
        // título que foi pedido.
        $passos = [];
        foreach (['normalized', 'redirects'] as $tabela) {
            foreach ($consulta[$tabela] ?? [] as $par) {
                if (isset($par['from'], $par['to'])) {
                    $passos[$par['from']] = $par['to'];
                }
            }
        }

        $urlPorTituloFinal = [];
        foreach ($consulta['pages'] ?? [] as $pagina) {
            $url = $pagina['imageinfo'][0]['thumburl'] ?? ($pagina['imageinfo'][0]['url'] ?? null);

            if (isset($pagina['title']) && is_string($url) && $url !== '') {
                $urlPorTituloFinal[$pagina['title']] = $url;
            }
        }

        foreach ($lote as $pedido) {
            $titulo = $pedido;

            for ($salto = 0; $salto < 5 && isset($passos[$titulo]); $salto++) {
                $titulo = $passos[$titulo];
            }

            if (isset($urlPorTituloFinal[$titulo])) {
                foreach ($porTitulo[$pedido] as $entrada) {
                    $encontradas[$entrada] = $urlPorTituloFinal[$titulo];
                }
            }
        }
    }

    return $encontradas;
}

/** Título do artigo que melhor corresponde a um nome, ou null. */
function procurarArtigo(string $nome, string $lingua, string $agente): ?string
{
    $dados = obterJson(urlApi($lingua, [
        'action'      => 'query',
        'list'        => 'search',
        'srsearch'    => $nome,
        'srlimit'     => '1',
        'srnamespace' => '0',
    ]), $agente);

    $titulo = $dados['query']['search'][0]['title'] ?? null;

    return is_string($titulo) && $titulo !== '' ? $titulo : null;
}

/* -------------------------------------------------------------------------- */
/*  Listas                                                                    */
/* -------------------------------------------------------------------------- */

/**
 * Lê uma lista de competidores.
 * Devolve ['tema' => ?string, 'entradas' => [['nome' => ..., 'artigo' => ...]]].
 */
function lerLista(string $caminho): array
{
    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) {
        return ['tema' => null, 'entradas' => []];
    }

    $tema     = null;
    $entradas = [];

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '') {
            continue;
        }

        if ($linha[0] === '#') {
            if (preg_match('/^#\s*tema\s*=\s*(.+)$/u', $linha, $m) === 1) {
                $tema = trim($m[1]);
            }
            continue;
        }

        // 'Nome a mostrar | Artigo da Wikipédia'
        $partes = array_map('trim', explode('|', $linha, 2));
        $nome   = $partes[0];
        $artigo = ($partes[1] ?? '') !== '' ? $partes[1] : $nome;

        if ($nome !== '') {
            $entradas[] = ['nome' => mb_substr($nome, 0, 50), 'artigo' => $artigo];
        }
    }

    return ['tema' => $tema, 'entradas' => $entradas];
}

/** Listas a processar, pelos nomes pedidos na linha de comandos (ou todas). */
function listasAProcessar(array $pedidas): array
{
    if (!is_dir(DIR_LISTAS)) {
        return [];
    }

    if ($pedidas === []) {
        $todas = glob(DIR_LISTAS . '/*.txt') ?: [];
        sort($todas);

        return $todas;
    }

    $escolhidas = [];

    foreach ($pedidas as $pedida) {
        $alvo = DIR_LISTAS . '/' . preg_replace('/\.txt$/i', '', $pedida) . '.txt';

        if (is_readable($alvo)) {
            $escolhidas[] = $alvo;
        } else {
            erro('Não há lista para "' . $pedida . '" (esperava ' . $alvo . ').');
        }
    }

    return $escolhidas;
}

/* -------------------------------------------------------------------------- */
/*  Processamento                                                             */
/* -------------------------------------------------------------------------- */

$listas = listasAProcessar($argumentos);

if ($listas === []) {
    erro('Nada a fazer. Cria uma lista em ' . DIR_LISTAS . '/<Tema>.txt.');
    exit(1);
}

linha('Wikipédia: ' . $lingua . '.wikipedia.org   miniatura: ' . TAMANHO_THUMB . 'px');
$seco && linha('MODO DRY-RUN — não é descarregado nada.');
linha();

$totalGuardados = 0;
$totalFalhados  = 0;

foreach ($listas as $caminhoLista) {
    $lista    = lerLista($caminhoLista);
    $entradas = $lista['entradas'];

    // A linha #tema= manda; sem ela, o nome do ficheiro serve de nome do tema.
    $tema  = $lista['tema'] ?? pathinfo($caminhoLista, PATHINFO_FILENAME);
    $pasta = $pastaForcada !== '' ? $pastaForcada : pastaDoTemaPublico($tema);
    $dir   = UPLOAD_DIR . '/' . $pasta;

    if ($entradas === []) {
        erro($tema . ': a lista está vazia.');
        continue;
    }

    if (mb_strlen($tema) > 25) {
        erro($tema . ': o nome do tema passa dos 25 caracteres que a base de dados aceita.');
        continue;
    }

    linha($tema . '  (' . count($entradas) . ' entradas -> Imagens/' . $pasta . '/)');

    if (!$seco && !is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        erro('  não consegui criar ' . $dir);
        continue;
    }

    // Parte-se dos nomes que já lá estão, para não perder as correcções feitas
    // à mão nem os competidores acrescentados por outra via.
    $existentes = lerNomes($pasta);
    $nomes      = $existentes['nomes'];

    // 1. O que já está em disco não se volta a buscar.
    $porBuscar = [];

    foreach ($entradas as $entrada) {
        $base = nomeFicheiro($entrada['nome']);
        $jaLa = glob($dir . '/' . $base . '.*') ?: [];

        if ($jaLa !== [] && !$refazer) {
            $nomes[basename($jaLa[0])] = $entrada['nome'];
            linha('  existe     ' . $entrada['nome']);
            continue;
        }

        $entrada['base']      = $base;
        $entrada['antigos']   = $jaLa;
        $porBuscar[]          = $entrada;
    }

    if ($porBuscar === []) {
        !$seco && escreverNomes($pasta, $tema, $nomes);
        linha();
        continue;
    }

    // 2. As entradas que apontam a um ficheiro do Commons resolvem-se lá; as
    //    outras vão à Wikipédia, num pedido por lote.
    $ficheiros = [];
    $artigos   = [];

    foreach ($porBuscar as $entrada) {
        if (eFicheiroCommons($entrada['artigo'])) {
            $ficheiros[] = $entrada['artigo'];
        } else {
            $artigos[] = $entrada['artigo'];
        }
    }

    $urls = imagensDeFicheiros($ficheiros, $agente)
        + ($artigos === [] ? [] : imagensDosArtigos($artigos, $lingua, $agente));

    // 3. Os que não deram nada tentam-se pela pesquisa, um a um: resolve os
    //    acentos, os plurais e os nomes aproximados.
    // Um ficheiro do Commons que não exista não se procura na Wikipédia: o
    // título seria 'File:...' e a pesquisa devolveria qualquer coisa. Fica
    // por resolver, e sai como SEM IMAGEM.
    $semTitulo = array_values(array_filter(
        $porBuscar,
        static fn(array $e): bool => !isset($urls[$e['artigo']]) && !eFicheiroCommons($e['artigo'])
    ));

    $encontrados = [];

    if ($semTitulo !== []) {
        foreach ($semTitulo as $entrada) {
            $titulo = procurarArtigo($entrada['artigo'], $lingua, $agente);

            if ($titulo !== null) {
                $encontrados[$entrada['artigo']] = $titulo;
            }
        }

        if ($encontrados !== []) {
            $porTitulo = imagensDosArtigos(array_values($encontrados), $lingua, $agente);

            foreach ($encontrados as $artigo => $titulo) {
                if (isset($porTitulo[$titulo])) {
                    $urls[$artigo] = $porTitulo[$titulo];
                }
            }
        }
    }

    // 3.5. O que ainda falta tenta-se pelo REST, título a título: é o caso dos
    //      cartazes de filme e capas de jogo, que a via rápida (passo 2) nunca
    //      devolve por não serem de licença livre.
    foreach ($porBuscar as $entrada) {
        if (isset($urls[$entrada['artigo']]) || eFicheiroCommons($entrada['artigo'])) {
            continue;
        }

        $titulo = $encontrados[$entrada['artigo']] ?? $entrada['artigo'];
        $url    = imagemViaResumo($titulo, $lingua, $agente);

        if ($url !== null) {
            $urls[$entrada['artigo']] = $url;
        }
    }

    // 4. Descarregar.
    $guardados = 0;

    foreach ($porBuscar as $entrada) {
        $url = $urls[$entrada['artigo']] ?? null;

        if ($url === null) {
            erro('  SEM IMAGEM ' . $entrada['nome'] . '  (tenta "Nome | Artigo Exacto" na lista)');
            $totalFalhados++;
            continue;
        }

        if ($seco) {
            linha('  buscaria   ' . $entrada['nome'] . '  <- ' . $url);
            continue;
        }

        $bytes = obter($url, $agente, $motivo);

        if ($bytes === null) {
            erro('  FALHOU     ' . $entrada['nome'] . '  (download: ' . $motivo . ')');
            $totalFalhados++;
            continue;
        }

        $extensao = extensaoDaImagem($bytes);

        if ($extensao === null) {
            erro('  FALHOU     ' . $entrada['nome'] . '  (não é uma imagem aceite)');
            $totalFalhados++;
            continue;
        }

        // Ao refazer, o formato pode mudar; apaga-se o que estava.
        foreach ($entrada['antigos'] as $antigo) {
            @unlink($antigo);
        }

        $ficheiro = $entrada['base'] . '.' . $extensao;

        if (file_put_contents($dir . '/' . $ficheiro, $bytes) === false) {
            erro('  FALHOU     ' . $entrada['nome'] . '  (escrita)');
            $totalFalhados++;
            continue;
        }

        @chmod($dir . '/' . $ficheiro, 0644);
        $nomes[$ficheiro] = $entrada['nome'];
        $guardados++;

        linha('  ok         ' . $entrada['nome'] . '  -> ' . $ficheiro);
    }

    if (!$seco) {
        escreverNomes($pasta, $tema, $nomes);
    }

    $totalGuardados += $guardados;
    linha();
}

linha('Guardados: ' . $totalGuardados . '   Falhados: ' . $totalFalhados);

if (!$seco && $totalGuardados > 0) {
    linha();
    linha('A seguir:  php tools/gerar-seed.php');
}

exit($totalFalhados > 0 ? 1 : 0);
