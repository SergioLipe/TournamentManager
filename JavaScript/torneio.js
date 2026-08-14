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

  var inputNumero = document.getElementById('numImagens');
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

  function tamanhoPedido() {
    return inputNumero ? parseInt(inputNumero.value, 10) : 8;
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
    var n = tamanhoPedido();

    // Com menos competidores carregados do que lugares pedidos, joga-se com
    // os que existem em vez de deixar slots por preencher para sempre.
    var disponiveis = pool.length > 0 ? Math.min(n, pool.length) : n;

    estrutura = window.Bracket.criarEstrutura(disponiveis);

    if (pool.length > 0) {
      window.Bracket.preencher(estrutura, pool.slice(0, disponiveis));
    }

    window.Bracket.desenhar(contentor, estrutura, {
      placeholder: placeholder,
      aoEscolher: abrirDuelo
    });

    window.Bracket.ajustarAoEcra(contentor);

    if (pool.length > 0 && pool.length < n) {
      dizer(
        'This theme only has ' + pool.length + ' competitor' + (pool.length === 1 ? '' : 's') +
        ', so the tournament was built for ' + disponiveis + '.',
        'aviso'
      );
    }
  }

  function reiniciar() {
    // Volta a baralhar o que já está carregado, sem ir outra vez à base de dados.
    pool = baralhar(pool.slice());
    construir();
    if (pool.length > 0) {
      dizer('Bracket reshuffled.');
    }
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

  function carregarTema(temaId, nomeTema) {
    dizer('Loading ' + nomeTema + '…');

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
      })
      .slice(0, window.Bracket.MAX);

    if (nomes.length < window.Bracket.MIN) {
      dizer('Enter at least ' + window.Bracket.MIN + ' names, one per line.', 'erro');
      return false;
    }

    temaAtual = null;
    // Sem id: um torneio de nomes não escreve estatísticas.
    pool = baralhar(nomes).map(function (nome, i) {
      var etiqueta = primeiraMaiuscula(nome);
      return { id: null, nome: etiqueta, imagem: imagemDeNome(etiqueta), ordem: i };
    });

    if (inputNumero && nomes.length < tamanhoPedido()) {
      inputNumero.value = String(nomes.length);
    }

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

  function fecharDuelo() {
    elDuelo.hidden = true;
    batalhaAberta = null;
    document.body.classList.remove('modal-aberto');
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

    fecharDuelo();

    if (ehFinal) {
      registarTorneio();
      mostrarVencedor(vencedor);
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

  Array.prototype.forEach.call(document.querySelectorAll('.js-escolhe-tema'), function (botao) {
    botao.addEventListener('click', function () {
      carregarTema(parseInt(botao.dataset.temaId, 10), botao.dataset.temaNome || 'theme');
    });
  });

  if (inputNumero) {
    inputNumero.addEventListener('change', construir);
  }

  var btnImagens = document.getElementById('btnCarregarImagens');
  if (btnImagens) {
    btnImagens.addEventListener('click', function () {
      if (temaAtual) {
        carregarTema(temaAtual.id, temaAtual.nome);
        return;
      }

      // Sem tema escolhido, arranca com um público à sorte.
      var botoes = document.querySelectorAll('.js-escolhe-tema');
      if (botoes.length === 0) {
        dizer('There are no themes to load yet.', 'aviso');
        return;
      }
      var sorteado = botoes[Math.floor(Math.random() * botoes.length)];
      carregarTema(parseInt(sorteado.dataset.temaId, 10), sorteado.dataset.temaNome || 'theme');
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

    btnNomes.addEventListener('click', function () {
      elNomes.hidden = false;
      document.body.classList.add('modal-aberto');
      campoNomes.focus();
    });

    elNomes.querySelector('#confirmarNomes').addEventListener('click', function () {
      if (usarNomes(campoNomes.value)) {
        elNomes.hidden = true;
        document.body.classList.remove('modal-aberto');
      }
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

  var botaoNav = document.getElementById('toggleNavBtn');
  if (botaoNav) {
    botaoNav.addEventListener('click', reajustar);
  }

  // Bracket vazia à espera de um tema.
  construir();
})();
