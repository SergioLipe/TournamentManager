/**
 * Liga a bracket à página: escolher tema, carregar competidores, jogar as
 * batalhas e registar os resultados.
 */
(function () {
  'use strict';

  var contentor = document.getElementById('bracket');
  if (!contentor || !window.Bracket) {
    return;
  }

  var placeholder = contentor.dataset.placeholder || '';
  var csrf = contentor.dataset.csrf || '';

  var grupoTamanho = document.getElementById('tamanhoBracket');
  var estado = document.getElementById('estadoTorneio');

  var elDuelo = document.getElementById('duelo');
  var elVencedor = document.getElementById('vencedor');
  var elNomes = document.getElementById('dialogoNomes');

  /** Pool de competidores actualmente carregado. */
  var pool = [];
  var temaAtual = null;
  var estrutura = null;
  var batalhaAberta = null;

  /* ---------------------------------------------------------------------- */
  /*  Utilitários                                                           */
  /* ---------------------------------------------------------------------- */

  /* --- Tamanho da bracket: 8 ou 16 --- */

  var TAMANHOS = [8, 16];
  var CHAVE_TAMANHO = 'torneio:tamanho';

  function tamanhoPedido() {
    var escolhido = grupoTamanho && grupoTamanho.querySelector('input[name="tamanho"]:checked');
    var valor = escolhido ? parseInt(escolhido.value, 10) : 8;
    return TAMANHOS.indexOf(valor) === -1 ? 8 : valor;
  }

  /** Marca o tamanho sem disparar o change: quem chama decide se reconstrói. */
  function marcarTamanho(valor) {
    if (!grupoTamanho) {
      return;
    }
    var opcao = grupoTamanho.querySelector('input[name="tamanho"][value="' + valor + '"]');
    if (opcao) {
      opcao.checked = true;
    }
  }

  function guardarTamanho(valor) {
    try {
      localStorage.setItem(CHAVE_TAMANHO, String(valor));
    } catch (erro) {
      /* Sem localStorage a escolha só dura esta visita. */
    }
  }

  /** O tamanho escolhido da última vez, ou 8. */
  function tamanhoGuardado() {
    try {
      var guardado = parseInt(localStorage.getItem(CHAVE_TAMANHO), 10);
      if (TAMANHOS.indexOf(guardado) !== -1) {
        return guardado;
      }
    } catch (erro) {
      /* Sem localStorage fica o 8. */
    }
    return 8;
  }

  // Quem joga sempre com 16 não tem de o escolher a cada visita.
  marcarTamanho(tamanhoGuardado());

  /*
   * Com nomes, a bracket é do tamanho exacto da lista — 5 nomes, 5 jogadores
   * — e o 8/16 não se aplica. Fica desligado e sem nenhum marcado, para não
   * dizer "8 players" por cima de uma bracket de 5; volta ao escolher um tema.
   */
  var modoNomes = false;

  function definirModoNomes(ligado) {
    modoNomes = ligado;
    if (!grupoTamanho) {
      return;
    }
    grupoTamanho.classList.toggle('tamanho--desligado', ligado);
    grupoTamanho.title = ligado ? 'With names, the bracket has one spot per name' : '';
    Array.prototype.forEach.call(grupoTamanho.querySelectorAll('input[name="tamanho"]'), function (opcao) {
      opcao.disabled = ligado;
      if (ligado) {
        opcao.checked = false;
      }
    });
    if (!ligado) {
      marcarTamanho(tamanhoGuardado());
    }
  }

  function dizer(texto, tipo) {
    if (!estado) {
      return;
    }
    estado.textContent = texto || '';
    estado.className = 'tournament__estado' + (tipo ? ' tournament__estado--' + tipo : '');
  }

  function primeiraMaiuscula(texto) {
    return texto.charAt(0).toUpperCase() + texto.slice(1);
  }

  /** Desenha uma imagem só com um nome, para os torneios sem imagens. */
  function imagemDeNome(nome) {
    var canvas = document.createElement('canvas');
    var escala = window.devicePixelRatio || 1;
    var largura = 250;
    var altura = 250;

    canvas.width = largura * escala;
    canvas.height = altura * escala;

    var ctx = canvas.getContext('2d');
    ctx.scale(escala, escala);

    ctx.fillStyle = '#1f2933';
    ctx.fillRect(0, 0, largura, altura);

    ctx.fillStyle = '#ffffff';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    // Encolhe o texto até caber na largura disponível.
    var tamanho = 46;
    do {
      ctx.font = '600 ' + tamanho + 'px system-ui, Arial, sans-serif';
      tamanho -= 2;
    } while (tamanho > 12 && ctx.measureText(nome).width > largura - 32);

    ctx.fillText(nome, largura / 2, altura / 2);

    return canvas.toDataURL('image/png');
  }

  /* ---------------------------------------------------------------------- */
  /*  Construção da bracket                                                 */
  /* ---------------------------------------------------------------------- */

  function construir() {
    // Com nomes, um lugar por nome; com um tema, o 8 ou 16 escolhido.
    var n = modoNomes ? pool.length : tamanhoPedido();

    // Com menos competidores carregados do que lugares pedidos, joga-se com
    // os que existem em vez de deixar slots por preencher para sempre.
    var disponiveis = pool.length > 0 ? Math.min(n, pool.length) : n;

    estrutura = window.Bracket.criarEstrutura(disponiveis);

    if (pool.length > 0) {
      window.Bracket.preencher(estrutura, pool.slice(0, disponiveis));
    }

    window.Bracket.desenhar(contentor, estrutura, {
      placeholder: placeholder,
      aoEscolher: function (batalha) {
        // Escolher uma batalha à mão sai do modo seguido: quem vai à bracket
        // buscar uma batalha em concreto não quer ser levado para outra.
        seguido = false;
        abrirDuelo(batalha);
      }
    });

    window.Bracket.ajustarAoEcra(contentor);
    actualizarBotaoProximo();

    if (pool.length > 0 && pool.length < n) {
      dizer(
        'This theme only has ' + pool.length + ' competitor' + (pool.length === 1 ? '' : 's') +
        ', so the tournament was built for ' + disponiveis + '.',
        'aviso'
      );
    }
  }

  /**
   * Recomeça com um sorteio novo. Com um tema vai buscar outra vez à base de
   * dados, que devolve competidores à sorte de entre todos os do tema — com
   * 55 animais, um torneio de 8 traz caras novas em vez dos mesmos baralhados.
   * Com nomes baralha os mesmos nomes. Sem nada carregado abre o selector.
   */
  function reiniciar() {
    seguido = false;
    if (temaAtual) {
      carregarTema(temaAtual.id, temaAtual.nome);
      return;
    }
    if (pool.length > 0) {
      pool = baralhar(pool.slice());
      construir();
      dizer('Names reshuffled.');
      return;
    }
    abrirSelector();
  }

  function baralhar(array) {
    for (var i = array.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var temp = array[i];
      array[i] = array[j];
      array[j] = temp;
    }
    return array;
  }

  /* ---------------------------------------------------------------------- */
  /*  Carregar competidores                                                 */
  /* ---------------------------------------------------------------------- */

  function carregarTema(temaId, nomeTema, capa) {
    dizer('Loading ' + nomeTema + '…');

    // O botão da barra passa a dizer o tema em vez de "Choose a theme": com a
    // barra escondida a meio de um torneio, era a única coisa que dizia qual
    // dos temas estava a jogar. A capa ajuda a reconhecê-lo sem ler.
    if (rotuloTema) {
      rotuloTema.textContent = nomeTema;
    }
    if (capaTema && capa) {
      capaTema.src = capa;
      capaTema.hidden = false;
    }
    marcarCartaoEscolhido(temaId);

    var corpo = new URLSearchParams();
    corpo.set('temaId', String(temaId));
    corpo.set('quantos', String(window.Bracket.MAX));

    fetch('api/competidores.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: corpo.toString(),
      credentials: 'same-origin'
    })
      .then(function (resposta) {
        return resposta.json().then(function (dados) {
          if (!resposta.ok) {
            throw new Error(dados.erro || 'Could not load the theme.');
          }
          return dados;
        });
      })
      .then(function (dados) {
        if (!dados.competidores || dados.competidores.length < window.Bracket.MIN) {
          throw new Error('"' + nomeTema + '" needs at least ' + window.Bracket.MIN + ' competitors.');
        }

        temaAtual = dados.tema;
        pool = dados.competidores;
        definirModoNomes(false);
        esconderBarra();
        construir();

        if (pool.length >= tamanhoPedido()) {
          dizer('Loaded ' + nomeTema + '.');
        }
      })
      .catch(function (erro) {
        dizer(erro.message, 'erro');
      });
  }

  function usarNomes(texto) {
    var nomes = texto
      .split('\n')
      .map(function (linha) {
        return linha.trim();
      })
      .filter(function (linha) {
        return linha !== '';
      });

    if (nomes.length < window.Bracket.MIN) {
      dizer('Enter at least ' + window.Bracket.MIN + ' names, one per line.', 'erro');
      return false;
    }

    // Cortar à socapa os que passam de 16 deixava alguém fora do torneio sem
    // aviso. O diálogo fica aberto para se tirar os que sobram.
    if (nomes.length > window.Bracket.MAX) {
      dizer('That is ' + nomes.length + ' names — the most is ' + window.Bracket.MAX + '.', 'erro');
      return false;
    }

    temaAtual = null;

    // O botão do tema deixa de mostrar o último tema: agora joga-se com nomes.
    if (rotuloTema) {
      rotuloTema.textContent = 'Pick a theme';
    }
    if (capaTema) {
      capaTema.hidden = true;
    }
    marcarCartaoEscolhido(null);
    // Sem id: um torneio de nomes não escreve estatísticas.
    pool = baralhar(nomes).map(function (nome, i) {
      var etiqueta = primeiraMaiuscula(nome);
      return { id: null, nome: etiqueta, imagem: imagemDeNome(etiqueta), ordem: i };
    });

    definirModoNomes(true);
    esconderBarra();
    construir();
    dizer('Playing with ' + nomes.length + ' names.');
    return true;
  }

  /* ---------------------------------------------------------------------- */
  /*  Duelo                                                                 */
  /* ---------------------------------------------------------------------- */

  function abrirDuelo(batalha) {
    if (batalha.bye) {
      return;
    }

    // Refazer uma escolha já feita anula tudo o que dependia dela.
    if (batalha.vencedor !== null) {
      var afectadas = window.Bracket.limparAPartirDe(batalha);
      afectadas.forEach(function (afectada) {
        window.Bracket.actualizarBatalha(afectada, placeholder);
      });
      dizer('That result was undone. Pick again.');
      actualizarBotaoProximo();
    }

    if (!window.Bracket.jogavel(batalha)) {
      dizer('That battle is still waiting for both competitors.', 'aviso');
      return;
    }

    batalhaAberta = batalha;

    var lados = elDuelo.querySelectorAll('.duelo__lado');
    for (var i = 0; i < lados.length; i++) {
      var competidor = batalha.competidores[i];
      lados[i].querySelector('.duelo__img').src = competidor.imagem;
      lados[i].querySelector('.duelo__img').alt = competidor.nome;
      lados[i].querySelector('.duelo__nome').textContent = competidor.nome;
    }

    elDuelo.hidden = false;
    document.body.classList.add('modal-aberto');
    lados[0].focus();
  }

  /** Esconde o duelo sem mais nada: usado depois de uma escolha. */
  function esconderDuelo() {
    elDuelo.hidden = true;
    batalhaAberta = null;
    document.body.classList.remove('modal-aberto');
  }

  /** Fechar o duelo sem escolher (X, Esc, fora da caixa) pára o modo seguido. */
  function fecharDuelo() {
    seguido = false;
    esconderDuelo();
  }

  /* --- Próxima batalha --- */

  /**
   * Com o modo seguido ligado, cada escolha abre logo a batalha seguinte.
   * Liga-se no botão "Next match" e desliga-se ao fechar o duelo ou ao
   * escolher uma batalha à mão na bracket.
   */
  var seguido = false;
  var btnProximo = document.getElementById('btnProximo');

  /**
   * A primeira batalha por jogar, ronda a ronda: acaba-se a primeira ronda
   * antes de passar às meias-finais, como num torneio a sério.
   */
  function proximaBatalha() {
    if (!estrutura) {
      return null;
    }
    for (var r = 0; r < estrutura.rondas.length; r++) {
      var ronda = estrutura.rondas[r];
      for (var i = 0; i < ronda.length; i++) {
        if (ronda[i].vencedor === null && window.Bracket.jogavel(ronda[i])) {
          return ronda[i];
        }
      }
    }
    return null;
  }

  function actualizarBotaoProximo() {
    if (btnProximo) {
      btnProximo.disabled = proximaBatalha() === null;
    }
  }

  function escolher(posicao) {
    if (!batalhaAberta) {
      return;
    }

    var batalha = batalhaAberta;
    var ehFinal = batalha === estrutura.final;
    var vencedor = batalha.competidores[posicao];
    var perdedor = batalha.competidores[posicao === 0 ? 1 : 0];

    window.Bracket.avancar(batalha, posicao);

    // Guardado na própria batalha em vez de enviado já: se este resultado
    // for refeito, o novo substitui o antigo em vez de se somar a ele.
    batalha.resultado = { v: vencedor.id, p: perdedor.id, f: ehFinal ? 1 : 0 };

    window.Bracket.actualizarBatalha(batalha, placeholder);

    if (batalha.destino) {
      window.Bracket.actualizarBatalha(batalha.destino.batalha, placeholder);
    }

    esconderDuelo();
    actualizarBotaoProximo();

    if (ehFinal) {
      seguido = false;
      registarTorneio();
      mostrarVencedor(vencedor);
      return;
    }

    // Pouco mais de um segundo antes do duelo seguinte: dá para ver o
    // vencedor avançar na bracket, em vez de a caixa só trocar de imagens.
    if (seguido) {
      setTimeout(function () {
        var seguinte = proximaBatalha();
        if (seguido && seguinte && elDuelo.hidden) {
          abrirDuelo(seguinte);
        }
      }, 1200);
    }
  }

  /* ---------------------------------------------------------------------- */
  /*  Estatísticas                                                          */
  /* ---------------------------------------------------------------------- */

  /** Envia de uma vez todas as batalhas do torneio que acabou. */
  function registarTorneio() {
    // Refazer a final depois de já ter sido gravada não pode voltar a somar
    // tudo outra vez. Cada bracket conta uma única vez.
    if (estrutura.registado) {
      return;
    }

    var resultados = [];
    var completo = true;

    estrutura.rondas.forEach(function (ronda) {
      ronda.forEach(function (batalha) {
        if (batalha.bye) {
          return;
        }
        if (batalha.resultado === null) {
          completo = false;
          return;
        }
        // Torneios de nomes não têm competidores na base de dados.
        if (batalha.resultado.v === null || batalha.resultado.p === null) {
          completo = false;
          return;
        }
        resultados.push(batalha.resultado);
      });
    });

    // Só se guarda um torneio inteiro: assim as estatísticas nunca contêm
    // meias-brackets nem torneios jogados com nomes.
    if (!completo || resultados.length === 0) {
      return;
    }

    estrutura.registado = true;

    var corpo = new URLSearchParams();
    corpo.set('resultados', JSON.stringify(resultados));
    corpo.set('csrf', csrf);

    fetch('api/resultado.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: corpo.toString(),
      credentials: 'same-origin'
    }).catch(function (erro) {
      // Falhar a gravar estatísticas não deve estragar o fim do torneio.
      console.error('Could not record the results:', erro);
    });
  }

  /* ---------------------------------------------------------------------- */
  /*  Vencedor final                                                        */
  /* ---------------------------------------------------------------------- */

  var intervaloFogo = null;

  function mostrarVencedor(competidor) {
    elVencedor.querySelector('.vencedor__img').src = competidor.imagem;
    elVencedor.querySelector('.vencedor__img').alt = competidor.nome;
    elVencedor.querySelector('.vencedor__nome').textContent = competidor.nome;

    elVencedor.hidden = false;
    document.body.classList.add('modal-aberto');
    elVencedor.querySelector('[data-fechar]').focus();

    iniciarFogo();
  }

  function fecharVencedor() {
    elVencedor.hidden = true;
    document.body.classList.remove('modal-aberto');
    pararFogo();
  }

  function iniciarFogo() {
    // Respeita quem pediu menos animação no sistema.
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    pararFogo();

    var caixa = document.createElement('div');
    caixa.className = 'fogo';
    document.body.appendChild(caixa);

    function lancarFaisca() {
      var faisca = document.createElement('span');
      faisca.className = 'fogo__faisca';
      faisca.style.left = Math.random() * 100 + '%';
      faisca.style.top = Math.random() * 80 + '%';
      faisca.style.setProperty('--cor', 'hsl(' + Math.random() * 360 + ', 100%, 60%)');
      caixa.appendChild(faisca);

      setTimeout(function () {
        faisca.remove();
      }, 900);
    }

    intervaloFogo = setInterval(function () {
      for (var i = 0; i < 8; i++) {
        lancarFaisca();
      }
    }, 420);
  }

  function pararFogo() {
    if (intervaloFogo !== null) {
      clearInterval(intervaloFogo);
      intervaloFogo = null;
    }
    var caixa = document.querySelector('.fogo');
    if (caixa) {
      caixa.remove();
    }
  }

  /* ---------------------------------------------------------------------- */
  /*  Ligações                                                              */
  /* ---------------------------------------------------------------------- */

  /* --- Selector de temas --- */

  var elSelector = document.getElementById('selectorTemas');
  var btnSelector = document.getElementById('btnSelectorTemas');
  var campoProcura = document.getElementById('selectorProcura');
  var semResultados = document.getElementById('selectorSemResultados');
  var rotuloTema = document.getElementById('rotuloTemaEscolhido');
  var capaTema = document.getElementById('capaTemaEscolhido');
  var cartoes = document.querySelectorAll('.js-escolhe-tema');
  var chips = elSelector ? elSelector.querySelectorAll('.selector__chip') : [];

  /** A categoria escolhida nas pílulas por cima da grelha; '' é todas. */
  var grupoActivo = '';

  /** Assinala no selector o tema que está a ser jogado. */
  function marcarCartaoEscolhido(temaId) {
    Array.prototype.forEach.call(cartoes, function (cartao) {
      var este = parseInt(cartao.dataset.temaId, 10) === temaId;
      cartao.classList.toggle('tema-cartao--escolhido', este);
      if (este) {
        cartao.setAttribute('aria-current', 'true');
      } else {
        cartao.removeAttribute('aria-current');
      }
    });
  }

  function abrirSelector() {
    if (!elSelector) {
      return;
    }
    elSelector.hidden = false;
    document.body.classList.add('modal-aberto');
    if (btnSelector) {
      btnSelector.setAttribute('aria-expanded', 'true');
    }

    // Abre sempre com a lista toda à vista. Sem isto, quem procurou "dino" na
    // vez anterior reabria o selector com os outros temas escondidos e sem
    // nada que explicasse porquê.
    if (campoProcura) {
      campoProcura.value = '';
    }
    escolherGrupo('');

    // Com um tema já escolhido, a grelha abre à volta dele.
    var escolhido = elSelector.querySelector('.tema-cartao--escolhido');
    if (escolhido) {
      escolhido.scrollIntoView({ block: 'center' });
    } else {
      elSelector.querySelector('.selector__corpo').scrollTop = 0;
    }

    // Num telemóvel isto abre o teclado por cima da grelha, que é o oposto do
    // que quem vai escolher pela imagem quer; no rato e teclado é o atalho
    // óbvio. O ponteiro grosseiro é o critério que separa os dois.
    if (campoProcura && window.matchMedia('(pointer: fine)').matches) {
      campoProcura.focus();
    }
  }

  function fecharSelector() {
    if (!elSelector) {
      return;
    }
    elSelector.hidden = true;
    document.body.classList.remove('modal-aberto');
    if (btnSelector) {
      btnSelector.setAttribute('aria-expanded', 'false');
      btnSelector.focus();
    }
  }

  /**
   * Mostra só os cartões que passam na procura e na categoria escolhida, e
   * esconde os grupos que ficam vazios.
   */
  function filtrar() {
    var termo = campoProcura ? campoProcura.value.trim().toLowerCase() : '';
    var visiveis = 0;

    Array.prototype.forEach.call(elSelector.querySelectorAll('[data-grupo]'), function (grupo) {
      var doGrupo = grupoActivo === '' || grupo.dataset.grupo === grupoActivo;
      var noGrupo = 0;

      Array.prototype.forEach.call(grupo.querySelectorAll('.js-escolhe-tema'), function (cartao) {
        var corresponde = doGrupo && (termo === '' || (cartao.dataset.procura || '').indexOf(termo) !== -1);
        cartao.hidden = !corresponde;
        if (corresponde) {
          noGrupo++;
        }
      });

      grupo.hidden = noGrupo === 0;
      visiveis += noGrupo;
    });

    if (semResultados) {
      semResultados.hidden = visiveis > 0;
    }
  }

  function escolherGrupo(grupo) {
    grupoActivo = grupo;
    Array.prototype.forEach.call(chips, function (chip) {
      chip.setAttribute('aria-pressed', chip.dataset.grupoFiltro === grupo ? 'true' : 'false');
    });
    filtrar();
  }

  Array.prototype.forEach.call(chips, function (chip) {
    chip.addEventListener('click', function () {
      escolherGrupo(chip.dataset.grupoFiltro || '');
      elSelector.querySelector('.selector__corpo').scrollTop = 0;
    });
  });

  if (btnSelector && elSelector) {
    btnSelector.addEventListener('click', abrirSelector);

    Array.prototype.forEach.call(elSelector.querySelectorAll('[data-fechar]'), function (el) {
      el.addEventListener('click', fecharSelector);
    });
  }

  if (campoProcura) {
    campoProcura.addEventListener('input', filtrar);

    // Enter com um só tema à vista carrega-o: procurar "dino" e carregar em
    // Enter é mais rápido do que ir buscar o cartão com o rato.
    campoProcura.addEventListener('keydown', function (evento) {
      if (evento.key !== 'Enter') {
        return;
      }
      evento.preventDefault();
      var visiveis = elSelector.querySelectorAll('.js-escolhe-tema:not([hidden])');
      if (visiveis.length > 0) {
        visiveis[0].click();
      }
    });
  }

  // "Surprise me" na barra: carrega logo um tema à sorte, sem abrir o
  // selector. Nunca o que já está a ser jogado — carregar outra vez o mesmo
  // parecia que o botão não tinha feito nada.
  var btnSurpresa = document.getElementById('btnSurpresa');
  if (btnSurpresa) {
    btnSurpresa.addEventListener('click', function () {
      var opcoes = Array.prototype.filter.call(cartoes, function (cartao) {
        return !temaAtual || parseInt(cartao.dataset.temaId, 10) !== temaAtual.id;
      });
      if (opcoes.length === 0) {
        dizer('There are no themes to pick from yet.', 'aviso');
        return;
      }
      var cartao = opcoes[Math.floor(Math.random() * opcoes.length)];
      var capa = cartao.querySelector('img');
      carregarTema(parseInt(cartao.dataset.temaId, 10), cartao.dataset.temaNome || 'theme', capa ? capa.src : '');
    });
  }

  Array.prototype.forEach.call(cartoes, function (botao) {
    botao.addEventListener('click', function () {
      fecharSelector();
      var capa = botao.querySelector('img');
      carregarTema(parseInt(botao.dataset.temaId, 10), botao.dataset.temaNome || 'theme', capa ? capa.src : '');
    });
  });

  if (grupoTamanho) {
    grupoTamanho.addEventListener('change', function () {
      guardarTamanho(tamanhoPedido());
      construir();
    });
  }

  if (btnProximo) {
    btnProximo.addEventListener('click', function () {
      var seguinte = proximaBatalha();
      if (!seguinte) {
        return;
      }
      seguido = true;
      abrirDuelo(seguinte);
    });
  }

  var btnReiniciar = document.getElementById('btnReiniciar');
  if (btnReiniciar) {
    btnReiniciar.addEventListener('click', reiniciar);
  }

  /* --- Diálogo dos nomes --- */

  var btnNomes = document.getElementById('btnCarregarNomes');
  if (btnNomes && elNomes) {
    var campoNomes = elNomes.querySelector('#campoNomes');
    var contaNomes = elNomes.querySelector('#contaNomes');
    var erroNomes = elNomes.querySelector('#erroNomes');

    /** Conta à medida que se escreve, para se saber quantos faltam ou sobram. */
    function actualizarConta() {
      var quantos = campoNomes.value.split('\n').filter(function (linha) {
        return linha.trim() !== '';
      }).length;
      var max = window.Bracket.MAX;
      contaNomes.textContent = quantos + ' name' + (quantos === 1 ? '' : 's') + ' · up to ' + max;
      contaNomes.classList.toggle('nomes__conta--demais', quantos > max);
      erroNomes.hidden = true;
    }

    btnNomes.addEventListener('click', function () {
      elNomes.hidden = false;
      document.body.classList.add('modal-aberto');
      actualizarConta();
      campoNomes.focus();
    });

    campoNomes.addEventListener('input', actualizarConta);

    elNomes.querySelector('#confirmarNomes').addEventListener('click', function () {
      if (usarNomes(campoNomes.value)) {
        elNomes.hidden = true;
        document.body.classList.remove('modal-aberto');
        return;
      }
      // O estado da página fica por trás do diálogo; o erro repete-se aqui.
      erroNomes.textContent = estado ? estado.textContent : '';
      erroNomes.hidden = false;
    });

    Array.prototype.forEach.call(elNomes.querySelectorAll('[data-fechar]'), function (el) {
      el.addEventListener('click', function () {
        elNomes.hidden = true;
        document.body.classList.remove('modal-aberto');
      });
    });
  }

  /* --- Escolha dentro do duelo --- */

  Array.prototype.forEach.call(elDuelo.querySelectorAll('.duelo__lado'), function (lado) {
    lado.addEventListener('click', function () {
      escolher(parseInt(lado.dataset.lado, 10));
    });
  });

  Array.prototype.forEach.call(elDuelo.querySelectorAll('[data-fechar]'), function (el) {
    el.addEventListener('click', fecharDuelo);
  });

  Array.prototype.forEach.call(elVencedor.querySelectorAll('[data-fechar]'), function (el) {
    el.addEventListener('click', fecharVencedor);
  });

  document.addEventListener('keydown', function (evento) {
    if (evento.key !== 'Escape') {
      return;
    }
    if (!elDuelo.hidden) {
      fecharDuelo();
    } else if (!elVencedor.hidden) {
      fecharVencedor();
    } else if (elNomes && !elNomes.hidden) {
      elNomes.hidden = true;
      document.body.classList.remove('modal-aberto');
    } else if (elSelector && !elSelector.hidden) {
      fecharSelector();
    }
  });

  // A largura disponível muda ao redimensionar a janela, ao rodar o
  // telemóvel e ao esconder a barra de navegação.
  var temporizadorEscala = null;
  function reajustar() {
    clearTimeout(temporizadorEscala);
    temporizadorEscala = setTimeout(function () {
      window.Bracket.ajustarAoEcra(contentor);
    }, 120);
  }

  window.addEventListener('resize', reajustar);
  window.addEventListener('orientationchange', reajustar);

  // A barra abre e fecha com uma transição de altura; medir a meio dava a
  // bracket com o tamanho errado, por isso reajusta-se quando ela acaba.
  var barra = document.getElementById('mainNav');
  if (barra) {
    barra.addEventListener('transitionend', function (evento) {
      if (evento.target === barra) {
        reajustar();
      }
    });
  }

  function esconderBarra() {
    if (window.TorneioNav) {
      window.TorneioNav.esconder();
    }
  }

  // Bracket vazia à espera de um tema.
  construir();
})();
