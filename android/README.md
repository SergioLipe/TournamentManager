# android/ — a aplicação Android

A app é uma **TWA** (Trusted Web Activity): um `.apk` a sério, com ícone no
telemóvel e lugar na Play Store, que abre o site em ecrã inteiro pelo motor do
Chrome — sem barra de endereço e sem parecer um browser.

Não há aqui código Java nem Kotlin para escrever. O projecto Android é gerado
pelo **Bubblewrap** a partir do `manifest.webmanifest` que está na raiz do site,
e é isso que estes passos fazem. A app aponta ao site: o que muda no site muda
na app sem republicar nada, desde que não mudes o `manifest.webmanifest`.

O que a app abre é o **`app.php`**, e não o `index.php` — a mesma bracket sem
login, registo, criação de temas nem estatísticas.

Esta pasta está excluída do `deploy.sh`: nada disto vai para o servidor.

## Aviso: o alojamento actual não deixa a verificação passar

O InfinityFree põe um desafio de JavaScript à frente de tudo o que não seja
imagem ou CSS. Quem chega sem o cookie `__test` recebe **200 OK com uma página
HTML de 866 bytes** que define o cookie e recarrega — em vez do ficheiro.

Um browser resolve isso sozinho e nem dá por ela. O problema é que o Chrome
verifica uma TWA indo buscar o `/.well-known/assetlinks.json` com um pedido
HTTPS simples: **não corre JavaScript e não segue redireccionamentos**. Nesse
pedido o que chega é a página de desafio, não o JSON — por isso a verificação
não pode passar, e a app fica com a barra do endereço por cima.

Não é teoria: já houve quem tentasse exactamente isto no InfinityFree e
desistisse — «*has to be a web browser that accesses the files no matter
what*» ([fórum deles][dal]). O sistema é o `testcookie-nginx-module`, corre ao
nível do NGINX à frente do Apache, e **no plano gratuito não há como o
desligar** — o `.htaccess` nem sequer chega a ser consultado.

[dal]: https://forum.infinityfree.com/t/digital-asset-link-verification-issue/36930

O que isto quer dizer na prática:

- **Instalar a partir do Chrome funciona hoje**, sem nada disto. Abre
  `torneio.site/app.php` no telemóvel, menu > *Instalar aplicação*. Fica com
  ícone próprio, ecrã inteiro e sem barra nenhuma.
- **A versão da Play Store precisa de outro alojamento.** Os passos abaixo
  estão certos e continuam válidos; é o servidor que tem de mudar primeiro.
- Sideloar o `.apk` na mesma resulta numa app funcional, mas com a barra do
  endereço à vista.

## Mudar de alojamento

O que é preciso do alojamento novo: PHP 8 com `pdo_mysql`, `fileinfo`,
`mbstring` e **`gd`**; MySQL ou MariaDB; HTTPS no domínio próprio; ~100 MB de
espaço (o `Imagens/` são 93 MB); e **nenhum interstício de JavaScript**.

### A recomendação: iFastNet Super Premium

É o premium da própria InfinityFree, ~5 USD/mês (confirma se o preço é mensal
ou anual antes de pagares). Escolhe-se por ser a mudança mais pequena que
resolve o problema:

- **O `deploy.sh` continua igual.** É cPanel e FTP na mesma — muda só o
  `.deploy.env`.
- **O `.htaccess` continua a valer.** Continua a ser Apache. Toda a defesa do
  site — negar o `.env` e os `.sql`, forçar HTTPS, esconder o `includes/` —
  vive em `.htaccess`, e num servidor que não seja Apache **nada disso é lido**
  e o `.env` com a password da base de dados passa a ser descarregável. Não é
  detalhe: é a razão principal para não saltar para nginx sem reescrever tudo.
- **O domínio fica.** O `torneio.site` é teu, muda-se para onde quiseres.
- Sem desafio de JavaScript: a própria InfinityFree diz que o sistema existe
  no gratuito e não no premium.

### A alternativa gratuita: Oracle Cloud Always Free

Uma máquina ARM (2 OCPU, 12 GB de RAM) e 50 GB de MySQL, de graça e sem prazo.
Mas passas a ser tu o administrador de sistemas: instalar e manter o servidor
web, o PHP-FPM, o MySQL, o Let's Encrypt, a firewall, as cópias de segurança e
as actualizações de segurança. E se puseres nginx, **os `.htaccess` deixam de
ser lidos** e tens de reescrever as regras todas à mão antes de publicar, ou
expões o `.env`. Com Apache instalado, funcionam na mesma.

Vale a pena se te apetecer aprender a administrar um servidor. Se o objectivo
é só pôr a app na loja, os 5 USD/mês compram exactamente esse trabalho todo.

### O que não serve

| | |
|---|---|
| Byet.host, e o resto do grupo | mesma empresa (iFastNet), mesma infra, mesmo desafio |
| AwardSpace grátis | ainda serve PHP 7, fim de vida desde 2022 |
| AlwaysData grátis | 100 MB de espaço; o `Imagens/` sozinho são 93 MB |
| Vercel, Netlify, Cloudflare Pages | não correm PHP |

### Os passos

1. Contratar o plano e apontar o `torneio.site` para lá.
2. Exportar a base de dados da InfinityFree pelo phpMyAdmin (não há SSH nem
   MySQL remoto no gratuito, por isso é pela web) e importá-la no novo.
3. Actualizar o `.env.production` com os dados novos da base de dados.
4. Actualizar o `.deploy.env` com o FTP novo.
5. `./deploy.sh --full` — **o `--full` não é opcional aqui.** O `.deploy-state`
   guarda o que já está no servidor *antigo*; sem `--full` o script conclui que
   está tudo publicado e não envia nada para o novo.
6. Confirmar:

   ```sh
   bash tools/verificar-publicado.sh
   ```

   Todas as linhas têm de dizer `desafio:não`. É esse o teste de aceitação da
   mudança — se disserem `desafio:sim`, o alojamento novo tem o mesmo problema
   e a Play Store continua fora de alcance.

7. Só depois seguir os passos 1 a 4 aqui de baixo.

## Antes de começar

| | |
|---|---|
| Node.js 18+ | **não está instalado nesta máquina** — https://nodejs.org |
| JDK 17 e Android SDK | o Bubblewrap propõe instalá-los sozinho na primeira execução; aceita |
| O site já publicado em HTTPS | o Bubblewrap vai buscar o manifest ao servidor, não ao disco |

Publica primeiro, com o `./deploy.sh`. Confirma no browser que estes três
respondem — se algum der 403 ou 404, pára e resolve isso antes de continuar:

```
https://torneio.site/app.php
https://torneio.site/manifest.webmanifest
https://torneio.site/.well-known/assetlinks.json
```

O `.htaccess` da raiz nega tudo o que acabe em `.json`, por causa do `.env` e
dos dumps da base de dados. É por isso que o manifest se chama `.webmanifest`
e que o `.well-known/` traz um `.htaccess` só dele. Se o `assetlinks.json`
responder 403, é aí que se mexe.

## 1. Gerar o projecto

Nesta pasta:

```sh
npx @bubblewrap/cli init --manifest https://torneio.site/manifest.webmanifest
```

Vai fazer perguntas. Aceita quase tudo o que ele propõe a partir do manifest;
estas três merecem atenção:

- **Application ID** — responde **`site.torneio.twa`**. É o domínio ao
  contrário, é permanente, é único na Play Store e não dá para mudar depois
  de publicares. Já está escrito no `.well-known/assetlinks.json`, por isso
  se escolheres outro tens de o corrigir lá também.
- **Signing key** — deixa-o criar uma. Guarda a password.
- **Display mode** — `standalone`.

No fim ficas com `twa-manifest.json` e uma chave de assinatura, ambas
ignoradas pelo git. **Faz cópia da chave e da password para fora do
computador**: sem elas nunca mais consegues publicar uma actualização da app,
e não há maneira de recuperar.

## 2. Ligar a app ao site

É este passo que tira a barra do endereço de cima da aplicação. Sem ele a app
instala e funciona, mas com um browser desenhado à volta.

```sh
npx @bubblewrap/cli fingerprint list
```

Copia a impressão digital **SHA-256** para o `.well-known/assetlinks.json`,
na raiz do projecto, por cima do `SUBSTITUIR`. O `package_name` já lá está.
Depois publica e confirma que o ficheiro é mesmo servido:

```sh
cd .. && ./deploy.sh
bash tools/verificar-publicado.sh .well-known/assetlinks.json
```

Não uses `curl` à mão para isto. O InfinityFree responde 200 com uma página
de desafio de 866 bytes a quem não traz o cookie `__test`, por isso um 200
aqui não quer dizer que o ficheiro chegou. O script resolve o desafio e
mostra o que o servidor entrega de facto.

> **Se publicares na Play Store, isto tem de ser feito outra vez.** O Google
> volta a assinar a app com uma chave dele (Play App Signing), por isso a
> impressão digital que instalaste do teu computador deixa de ser a que conta.
> Vai à Play Console > Configuração > Integridade da app, copia a impressão
> SHA-256 do **certificado de assinatura da app** e acrescenta-a à lista
> `sha256_cert_fingerprints` — ao lado da tua, não em vez dela. É a razão
> número um para uma app aparecer na loja com a barra do endereço à mostra.

## 3. Construir e instalar

```sh
npx @bubblewrap/cli build
```

Dá dois ficheiros:

- `app-release-signed.apk` — para instalar directamente no telemóvel
- `app-release-bundle.aab` — o formato que a Play Store exige

Para experimentar sem loja nenhuma, com o telemóvel ligado por USB e a
depuração USB activada:

```sh
npx @bubblewrap/cli install
```

Ou copia o `.apk` para o telemóvel e abre-o (é preciso autorizar a instalação
de fontes desconhecidas).

## 4. Publicar (opcional)

Conta de programador da Play Console: 25 USD, uma vez só. Envia o `.aab`.
Vais precisar de um endereço para a política de privacidade — o `sobre.php`
serve, desde que diga o que é guardado.

Vale a pena saber que o Google rejeita apps que sejam só um site embrulhado
sem valor próprio. Uma TWA de um site que é mesmo uma aplicação passa; ajuda
que funcione bem em ecrã pequeno e que tenha ícone e splash decentes.

## Actualizar

- **Mudou o site** (temas, imagens, CSS, PHP): nada a fazer. `./deploy.sh` e
  pronto — a app carrega o site actual.
- **Mudou o `manifest.webmanifest`** (nome, ícones, cores, `start_url`):

  ```sh
  npx @bubblewrap/cli update
  npx @bubblewrap/cli build
  ```

  E envia a build nova. Sobe o `appVersion` no `twa-manifest.json` antes, ou a
  Play Store recusa.

## Quando corre mal

| Sintoma | Causa quase sempre |
|---|---|
| A app abre com a barra do endereço | `assetlinks.json` inacessível, package name errado, ou falta a impressão digital do Play App Signing |
| A app abre no browser em vez de na app | o mesmo — a verificação falhou |
| Ecrã branco a abrir | o `start_url` do manifest não responde; testa-o no browser |
| Sem estilos, só texto | o Bootstrap vem de um CDN e não há rede; o `sw.js` não guarda respostas de outros domínios |
| `bubblewrap` não encontra o Java | deixa-o instalar o JDK: `npx @bubblewrap/cli doctor` diz o que falta |
| O `init` não consegue ler o manifest | recebeu a página de desafio em vez do JSON (ver o aviso lá em cima). Serve o manifest daqui de casa — `php -S 127.0.0.1:8000` na raiz do projecto — aponta-lhe o `--manifest`, e corrige depois o `host` no `twa-manifest.json` para `torneio.site` |

Para ver o que o Chrome acha da verificação, com o telemóvel ligado:

```sh
adb shell dumpsys package site.torneio.twa | grep -A5 "Domain verification"
```
