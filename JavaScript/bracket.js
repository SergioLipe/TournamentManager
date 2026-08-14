/**
 * Construção e desenho de brackets de eliminatória simples.
 *
 * Substitui os oito ficheiros Extras/Np_Brackets.html escritos à mão. Um
 * torneio de N competidores tem sempre N-1 batalhas; quando N não é potência
 * de dois, os lugares que sobram na grelha ficam vazios e quem calha a esses
 * lugares passa directamente à ronda seguinte (bye).
 */
window.Bracket = (function () {
  'use strict';

  var MIN = 2;
  var MAX = 16;

  /* ---------------------------------------------------------------------- */
  /*  Estrutura                                                             */
  /* ---------------------------------------------------------------------- */

  /**
   * Ordem de colocação dos seeds numa grelha, de forma a que os byes fiquem
   * distribuídos e não caiam todos no mesmo ramo.
   * Para 8 lugares devolve [1, 8, 4, 5, 2, 7, 3, 6].
   */
  function ordemSeeds(tamanho) {
    var ordem = [1];

    while (ordem.length < tamanho) {
      var soma = ordem.length * 2 + 1;
      var proxima = [];
      for (var i = 0; i < ordem.length; i++) {
        proxima.push(ordem[i]);
        proxima.push(soma - ordem[i]);
      }
      ordem = proxima;
    }

    return ordem;
  }

  function limitar(valor) {
    if (isNaN(valor)) {
      return MIN;
    }
    return Math.min(MAX, Math.max(MIN, valor));
  }

  /**
   * Monta a árvore de batalhas para N competidores.
   *
   * Cada batalha guarda de onde vêm os seus dois lugares e para onde vai o
   * vencedor, por referência directa ao objecto — sem os ids em string
   * ("A", "AA", "AAA") que a versão antiga usava para se orientar no DOM.
   */
  function criarEstrutura(numCompetidores) {
    var n = limitar(parseInt(numCompetidores, 10));
    var tamanho = Math.pow(2, Math.ceil(Math.log(n) / Math.LN2));
    var ordem = ordemSeeds(tamanho);

    // Lugar por lugar: índice do competidor, ou null quando é um bye.
    var lugares = ordem.map(function (seed) {
      return seed <= n ? seed - 1 : null;
    });

    var numRondas = Math.round(Math.log(tamanho) / Math.LN2);
    var rondas = [];
    var proximoId = 0;

    for (var r = 0; r < numRondas; r++) {
      var porRonda = tamanho / Math.pow(2, r + 1);
      var ronda = [];

      for (var m = 0; m < porRonda; m++) {
        ronda.push({
          id: proximoId++,
          ronda: r,
          indice: m,
          // Só a primeira ronda conhece os lugares à partida.
          lugares: r === 0 ? [lugares[m * 2], lugares[m * 2 + 1]] : [null, null],
          competidores: [null, null],
          vencedor: null,
          // Resultado a enviar para o servidor no fim do torneio.
          resultado: null,
          bye: false,
          destino: null,
          elementos: null
        });
      }

      rondas.push(ronda);
    }

    // Liga cada batalha à seguinte.
    for (var i = 0; i < rondas.length - 1; i++) {
      for (var j = 0; j < rondas[i].length; j++) {
        rondas[i][j].destino = {
          batalha: rondas[i + 1][Math.floor(j / 2)],
          posicao: j % 2
        };
      }
    }

    return {
      numCompetidores: n,
      tamanho: tamanho,
      rondas: rondas,
      final: rondas[rondas.length - 1][0]
    };
  }

  /**
   * Coloca os competidores na primeira ronda e resolve os byes, propagando
   * quem passa sem jogar até à primeira batalha a sério.
   */
  function preencher(estrutura, competidores) {
    var primeira = estrutura.rondas[0];

    primeira.forEach(function (batalha) {
      batalha.competidores = batalha.lugares.map(function (indice) {
        return indice === null ? null : (competidores[indice] || null);
      });

      var a = batalha.competidores[0];
      var b = batalha.competidores[1];
      batalha.bye = (a === null) !== (b === null);

      if (batalha.bye) {
        // Um lugar vazio: o outro competidor avança sem batalha, e sem
        // resultado para contabilizar.
        avancar(batalha, a === null ? 1 : 0);
      }
    });

    return estrutura;
  }

  /** Marca o vencedor de uma batalha e escreve-o na batalha seguinte. */
  function avancar(batalha, posicaoVencedor) {
    var vencedor = batalha.competidores[posicaoVencedor];
    batalha.vencedor = vencedor;

    if (batalha.destino) {
      batalha.destino.batalha.competidores[batalha.destino.posicao] = vencedor;
    }

    return vencedor;
  }

  /** True se ambos os lugares da batalha já estiverem preenchidos. */
  function jogavel(batalha) {
    return !batalha.bye
      && batalha.competidores[0] !== null
      && batalha.competidores[1] !== null;
  }

  /* ---------------------------------------------------------------------- */
  /*  Desenho                                                               */
  /* ---------------------------------------------------------------------- */

  /**
   * Divide as rondas em colunas, com as duas metades a convergir para a
   * final ao centro: [E0][E1]…[FINAL]…[D1][D0]
   */
  function colunas(estrutura) {
    var rondas = estrutura.rondas;
    var ultima = rondas.length - 1;
    var esquerda = [];
    var direita = [];

    for (var r = 0; r < ultima; r++) {
      var metade = rondas[r].length / 2;
      esquerda.push({ ronda: r, lado: 'esq', batalhas: rondas[r].slice(0, metade) });
      direita.push({ ronda: r, lado: 'dir', batalhas: rondas[r].slice(metade) });
    }

    direita.reverse();

    return esquerda
      .concat([{ ronda: ultima, lado: 'final', batalhas: rondas[ultima] }])
      .concat(direita);
  }

  /** Nome da ronda a partir do número de batalhas que ainda tem. */
  function nomeRonda(totalBatalhas) {
    if (totalBatalhas === 1) {
      return 'Final';
    }
    if (totalBatalhas === 2) {
      return 'Semi-finals';
    }
    if (totalBatalhas === 4) {
      return 'Quarter-finals';
    }
    return 'Round of ' + totalBatalhas * 2;
  }

  /**
   * Largura dos slots: quanto mais batalhas na coluna, mais pequenos.
   *
   * O factor por tamanho de bracket existe porque o número de colunas cresce
   * com o torneio — 16 competidores dão sete colunas. Sem o encolhimento, a
   * bracket de 16 media 2304px e só cabia num ecrã grande à custa de uma
   * redução tão agressiva que já não se via nada.
   */
  function factorCompacto(tamanho) {
    if (tamanho >= 16) {
      return 0.72;
    }
    if (tamanho >= 8) {
      return 0.88;
    }
    return 1;
  }

  function larguraSlot(coluna, tamanho) {
    var base;

    if (coluna.lado === 'final') {
      base = 170;
    } else if (coluna.batalhas.length >= 4) {
      base = 92;
    } else if (coluna.batalhas.length >= 2) {
      base = 118;
    } else {
      base = 140;
    }

    return Math.round(base * factorCompacto(tamanho));
  }

  /**
   * Encolhe a bracket até caber na largura disponível, para não haver
   * scroll horizontal.
   *
   * O transform não ocupa espaço no layout: a altura do contentor tem de ser
   * acertada à mão, senão fica um vazio por baixo do tamanho por escalar.
   */
  // Baixo de propósito: o pedido foi que a bracket coubesse sempre, e a
  // alternativa a encolher é voltar ao scroll lateral. Só serve de travão
  // contra um contentor medido a zero.
  var ESCALA_MINIMA = 0.12;

  function ajustarAoEcra(contentor) {
    var grelha = contentor.querySelector('.bracket__grelha');
    if (!grelha) {
      return;
    }

    // Medir sempre no estado neutro, ou cada ajuste partia do resultado do
    // anterior em vez do tamanho real.
    grelha.style.transform = 'none';
    contentor.style.height = '';
    contentor.style.overflowX = 'auto';

    var disponivel = contentor.clientWidth;
    var necessario = grelha.offsetWidth;

    if (!disponivel || !necessario) {
      return;
    }

    var escala = disponivel / necessario;

    if (escala >= 1) {
      contentor.style.overflowX = 'auto';
      return; // já cabe: não vale a pena encolher
    }

    // Abaixo de certo ponto as imagens ficam ilegíveis; aí é preferível
    // deixar rolar do que mostrar uma bracket que ninguém consegue ver.
    var coube = escala >= ESCALA_MINIMA;
    escala = Math.max(escala, ESCALA_MINIMA);

    grelha.style.transform = 'scale(' + escala + ')';

    // O transform é só visual: em termos de layout a grelha continua com a
    // largura por escalar, e o contentor mostraria barra de scroll por causa
    // de conteúdo que, no ecrã, já cabe. Só se deixa rolar quando a escala
    // mínima não chegou.
    contentor.style.overflowX = coube ? 'hidden' : 'auto';

    var estilo = window.getComputedStyle(contentor);
    var extra = parseFloat(estilo.paddingTop) + parseFloat(estilo.paddingBottom);
    contentor.style.height = Math.ceil(grelha.offsetHeight * escala + extra) + 'px';
  }

  function criarElemento(tag, classe) {
    var el = document.createElement(tag);
    if (classe) {
      el.className = classe;
    }
    return el;
  }

  /** Desenha um dos dois lados de uma batalha. */
  function desenharSlot(placeholder) {
    var slot = criarElemento('div', 'slot');

    var img = criarElemento('img', 'slot__img');
    img.src = placeholder;
    img.alt = '';
    img.loading = 'lazy';

    var nome = criarElemento('span', 'slot__nome');

    slot.appendChild(img);
    slot.appendChild(nome);

    return { raiz: slot, img: img, nome: nome };
  }

  /**
   * Desenha a estrutura dentro do contentor.
   * Guarda as referências dos elementos em cada batalha, para que actualizar
   * um resultado seja escrever num nó já conhecido.
   */
  function desenhar(contentor, estrutura, opcoes) {
    opcoes = opcoes || {};
    var placeholder = opcoes.placeholder || '';
    var aoEscolher = opcoes.aoEscolher || function () {};

    contentor.textContent = '';
    contentor.setAttribute('data-tamanho', String(estrutura.tamanho));

    var grelha = criarElemento('div', 'bracket__grelha');

    colunas(estrutura).forEach(function (coluna) {
      var elColuna = criarElemento('div', 'coluna coluna--' + coluna.lado);
      elColuna.style.setProperty("--slot", larguraSlot(coluna, estrutura.tamanho) + "px");

      var titulo = criarElemento('div', 'coluna__titulo');
      titulo.textContent = nomeRonda(estrutura.rondas[coluna.ronda].length);
      elColuna.appendChild(titulo);

      var lista = criarElemento('div', 'coluna__batalhas');

      coluna.batalhas.forEach(function (batalha) {
        lista.appendChild(desenharBatalha(batalha, placeholder, aoEscolher));
      });

      elColuna.appendChild(lista);
      grelha.appendChild(elColuna);
    });

    contentor.appendChild(grelha);

    return estrutura;
  }

  function desenharBatalha(batalha, placeholder, aoEscolher) {
    var raiz = criarElemento('div', 'batalha');

    var slotA = desenharSlot(placeholder);
    var slotB = desenharSlot(placeholder);

    var meio = criarElemento('div', 'batalha__meio');
    var vs = criarElemento('span', 'batalha__vs');
    vs.textContent = 'VS';

    var botao = criarElemento('button', 'batalha__botao');
    botao.type = 'button';
    botao.textContent = 'Pick';
    botao.addEventListener('click', function () {
      aoEscolher(batalha);
    });

    meio.appendChild(vs);
    meio.appendChild(botao);

    raiz.appendChild(slotA.raiz);
    raiz.appendChild(meio);
    raiz.appendChild(slotB.raiz);

    batalha.elementos = {
      raiz: raiz,
      slots: [slotA, slotB],
      botao: botao,
      vs: vs
    };

    actualizarBatalha(batalha, placeholder);

    return raiz;
  }

  /** Reflecte no DOM o estado actual de uma batalha. */
  function actualizarBatalha(batalha, placeholder) {
    if (!batalha.elementos) {
      return;
    }

    batalha.elementos.slots.forEach(function (slot, i) {
      var competidor = batalha.competidores[i];
      var vazio = competidor === null;

      slot.img.src = vazio ? placeholder : competidor.imagem;
      slot.img.alt = vazio ? '' : competidor.nome;
      slot.nome.textContent = vazio ? '' : competidor.nome;

      slot.raiz.classList.toggle('slot--vazio', vazio);
      slot.raiz.classList.toggle('slot--venceu', batalha.vencedor !== null && competidor === batalha.vencedor);
      slot.raiz.classList.toggle(
        'slot--perdeu',
        batalha.vencedor !== null && competidor !== null && competidor !== batalha.vencedor
      );
    });

    var pronta = jogavel(batalha);
    var decidida = batalha.vencedor !== null;

    batalha.elementos.raiz.classList.toggle('batalha--bye', batalha.bye);
    batalha.elementos.raiz.classList.toggle('batalha--pronta', pronta && !decidida);
    batalha.elementos.raiz.classList.toggle('batalha--decidida', decidida);

    batalha.elementos.botao.disabled = !pronta;
    batalha.elementos.botao.textContent = decidida ? 'Redo' : 'Pick';
    batalha.elementos.botao.hidden = batalha.bye;
    batalha.elementos.vs.hidden = batalha.bye;

    if (batalha.bye) {
      batalha.elementos.botao.disabled = true;
    }
  }

  /** Redesenha todas as batalhas de uma estrutura. */
  function actualizarTudo(estrutura, placeholder) {
    estrutura.rondas.forEach(function (ronda) {
      ronda.forEach(function (batalha) {
        actualizarBatalha(batalha, placeholder);
      });
    });
  }

  /**
   * Limpa o resultado de uma batalha e de tudo o que dela dependia.
   * Necessário para poder corrigir uma escolha sem recomeçar o torneio.
   */
  function limparAPartirDe(batalha) {
    var afectadas = [];

    (function limpar(actual) {
      if (actual.vencedor === null && !actual.bye) {
        return;
      }

      if (!actual.bye) {
        actual.vencedor = null;
        actual.resultado = null;
        afectadas.push(actual);
      }

      if (actual.destino) {
        var seguinte = actual.destino.batalha;
        seguinte.competidores[actual.destino.posicao] = null;
        if (afectadas.indexOf(seguinte) === -1) {
          afectadas.push(seguinte);
        }
        limpar(seguinte);
      }
    })(batalha);

    return afectadas;
  }

  return {
    MIN: MIN,
    MAX: MAX,
    criarEstrutura: criarEstrutura,
    preencher: preencher,
    desenhar: desenhar,
    avancar: avancar,
    jogavel: jogavel,
    actualizarBatalha: actualizarBatalha,
    actualizarTudo: actualizarTudo,
    ajustarAoEcra: ajustarAoEcra,
    limparAPartirDe: limparAPartirDe
  };
})();
