var numImagens = document.getElementById('numImagens');        //n de imagens na navbar


//funçao para mudar as brackets
function mudarBrackets() {

  var nImagens = numImagens.value;      // numero de imagens é igual ao numero que o utilizador mete na navbar
  var brackets = document.getElementById('bracket')                //div onde vai estar a bracket
  // se for um numero impar assume que vai ser um valor acima
  if (nImagens == 15 || nImagens == 13 || nImagens == 11 || nImagens == 9 || nImagens == 7 || nImagens == 5 || nImagens == 3 || nImagens == 1) {
    nImagens++;
  }
  // nao deixar que o utilizador insira um numero negativo
  else if (nImagens < 1) {
    nImagens = 2;
  }
  // nao deixar que o utilizador insira um numero acima de 16
  else if (nImagens > 16) {
    nImagens = 16
  }

  var diretorio = `Extras/${nImagens}p_Brackets.html`;     //diretorio do ficheiro html

  fetch(diretorio)       //vai buscar o ficheiro
    .then(res => res.text())  // converte a resposta em texto
    .then(html => {
      brackets.innerHTML = html;  // insere o html do ficheiro
      EscolheVencedor();     // carrega a funçao escolheVencedor 
    })

}
numImagens.addEventListener('input', mudarBrackets);    // criar um eventlistener para o input de numero na navbar
window.onload = mudarBrackets;                        // executa a funçao quando a pagina carrega


// funcao para escolher o vencedor em cada ronda
function EscolheVencedor() {

  var botoes = document.querySelectorAll('.botaoVS');   // escolhe todos os botoes da pagina

  //criar a nova divisao que vai ser a pagina toda e vai conter as duas imagens para escolher
  var overlay = document.createElement('div');
  overlay.className = 'overlay';                  // class da nova div
  document.body.appendChild(overlay);           //insere no documento

  // cria a div das imagens
  var imagensDiv = document.createElement('div');
  imagensDiv.className = 'imagem_decisao';            //class 
  document.body.appendChild(imagensDiv);          // insere no documento

  botoes.forEach(button => {                  //percorre todos os botoes
    button.addEventListener('click', function () {      //adiciona em cada botao o seguinte evento
      var ronda = this.closest('.ronda');               //  encontra a ronda mais proxima do botao escolhe
      var imagens = ronda.querySelectorAll('.trocar_img');    //seleciona as imagens da ronda que sao para escolher
      var descricao = ronda.querySelectorAll('.descricao');   //seleciona o nome das imagens    



      imagensDiv.innerHTML =                                  //cria o html 
        `                                             
              <div class="coluna-final">
              <div class="ronda">
                  <div class="imagem-texto">
                      <img src="${imagens[0].src}" alt="Imagem" class="img-escolhe">
                      <button class="botaoVS vencedor "  data-src="${imagens[0].src}" data-desc="${descricao[0].innerHTML}" data-index="0" >${descricao[0].innerHTML}</button>
                  </div>
                  <div class="meioImagens">
                      <span class="vs">VS</span>
                  </div>
                  <div class="imagem-texto">
                      <img src="${imagens[1].src}" alt="Imagem" class="img-escolhe">
                      <button class="botaoVS vencedor "  data-src="${imagens[1].src}" data-desc="${descricao[1].innerHTML}" data-index="1" >${descricao[1].innerHTML}</button>
                  </div>
              </div>
              </div>
              `;

      // mete visivel as imagens
      overlay.style.visibility = 'visible';
      imagensDiv.style.visibility = 'visible';

      // se for a ronda final
      if (this.id === 'final') {
        imagensDiv.querySelectorAll('.vencedor').forEach(btn => {    // percorre as div das imagens que foram criadas e cada botao
          btn.addEventListener('click', function () {                  // quando uns dos botoes é clicado

            //mete invisivel a overlay e as imagens
            mostrarVencedorFinal(this.dataset.src, this.dataset.desc);   // chama a funçao de mostrar o vencedor final usando como parametros a src e a descricao da imagem

            overlay.style.visibility = 'hidden';
            imagensDiv.style.visibility = 'hidden';

            var perdedor;
            if (this.dataset.index == 0) {
              perdedor = 1
            }
            else {
              perdedor = 0;
            }
            
            //ESTATISTICA
            // enviar imagem vencedora do torneio para UpdateEstatistica.php
            var statVencedor = this.dataset.src; // diretorio da imagem que ganha
            var statPerdedor = imagens[perdedor].src; //diretorio da imagem que perde


            // buscar so o caminho da imagens 

            var url = new URL(statVencedor);
            var diretorioVencedor = url.pathname;  //remover o localhost...
            var diretorioVencedorTorneio = diretorioVencedor.substring(1);
            var url2 = new URL(statPerdedor);
            var diretorioPerdedor = url2.pathname;  //remover o localhost...
            var diretorioPerdedor2 = diretorioPerdedor.substring(1);



            // AJAX request para mandar os diretorios para o ficheiro UpdateEstatistica.php
            fetch('UpdateEstatistica.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'vencedorTorneio=' + encodeURIComponent(diretorioVencedorTorneio) +
                '&perdedor=' + encodeURIComponent(diretorioPerdedor2)
            })
              .catch((error) => {
                console.error('Error:', error);
              });
          });






        });
      }
      else {

        // mudar a imagensDiv da ronda seguinte
        imagensDiv.querySelectorAll('.vencedor').forEach(btn => {    // percorre as div das imagens que foram criadas e percorre por cada uma delas 
          btn.addEventListener('click', function () {                  // quando uns dos botoes é clicado
            var imagemVencedora = document.getElementById(button.dataset.vencedor); // vai buscar a imagem da ronda seguinte pelo id que esta no data do html
            var descricaoVencedora = imagemVencedora.closest('.imagem-texto').querySelector('.descricao'); // encontra a descricao mais proxima

            imagemVencedora.src = this.dataset.src; // a imagem da ronda seguinta vai virar a imagem do botao que foi clicada em que o diretorio esta guardado na data do html criado ha pouco
            descricaoVencedora.innerHTML = this.dataset.desc; //atuliza a descricao com a descricao que esta do data do html criado ha pouco

            //muda a cor da ronda acabada de escolher
            var perdedor;
            if (this.dataset.index == 0) {
              perdedor = 1
            }
            else {
              perdedor = 0;
            }
            descricao[this.dataset.index].style.backgroundColor = "lightgreen";
            descricao[perdedor].style.backgroundColor = "red";
            ronda.style.backgroundColor = "#FAFAD2";
            ronda.style.border = "5px solid #4CBB17";


            //mete invisivel a overlay e as imagens

            overlay.style.visibility = 'hidden';
            imagensDiv.style.visibility = 'hidden';


            // enviar imagem vencedora e perdedora para UpdateEstatistica.php

            var statVencedor = this.dataset.src; // diretorio da imagem que ganha
            var statPerdedor = imagens[perdedor].src; //diretorio da imagem que perde

            // buscar so o caminho da imagens 

            //remover o localhost...
            var url = new URL(statVencedor);
            var diretorioVencedor = url.pathname;
            var diretorioVencedor2 = diretorioVencedor.substring(1);  //remover a primeira /

            //remover o localhost...
            var url2 = new URL(statPerdedor);
            var diretorioPerdedor = url2.pathname;
            var diretorioPerdedor2 = diretorioPerdedor.substring(1);  //remover a primeira /



            //ESTATISTICA

            // AJAX request para mandar os diretorios para o ficheiro UpdateEstatistica.php
            fetch('UpdateEstatistica.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: 'vencedor=' + encodeURIComponent(diretorioVencedor2) +
                '&perdedor=' + encodeURIComponent(diretorioPerdedor2)
            })
              .catch((error) => {
                console.error('Error:', error);
              });
          });
        });
      }
    });

    // para voltar fazer desaparecer quando clica em qualquer parte do ecra
    overlay.addEventListener('click', function () {
      overlay.style.visibility = 'hidden';
      imagensDiv.style.visibility = 'hidden';

    });


  });
}

// funçao para mostrar o vencedor final
function mostrarVencedorFinal(src, desc) {
  //criar a nova divisao que vai ser a pagina toda e vai conter a imagem vencedora final
  var overlay = document.createElement('div');
  overlay.className = 'overlay';                  // class da nova div
  document.body.appendChild(overlay);           //insere no documento

  // cria a div onde vai tar a imagem
  var imagemFinalDiv = document.createElement('div');
  imagemFinalDiv.className = 'imagem_decisao';
  document.body.appendChild(imagemFinalDiv);

  //html da imagem vencedora
  imagemFinalDiv.innerHTML = `         
  <div class="vencedor_final">
  <div class="vencedor_titulo"> Winner!</div>
  <img src="${src}" class="imagem_vencedor">
  <span class="descricao_final">${desc}</span>
</div>

  `;

  overlay.style.visibility = 'visible';
  imagemFinalDiv.style.visibility = 'visible';
  triggerFireworks();

  // quando clica em qualquer parte do ecra remove as divisoes
  overlay.addEventListener('click', function () {
    document.body.removeChild(overlay);
    document.body.removeChild(imagemFinalDiv);
    stopFireworks();
  });
}






// funçao tirada da net para fazer fogo de artificio

// Global variable to store the interval ID for stopping later
var fireworksInterval;

function triggerFireworks() {
  const fireworksContainer = document.createElement('div');
  fireworksContainer.className = 'fireworks-container';
  document.body.appendChild(fireworksContainer);

  function createFirework() {
    const firework = document.createElement('div');
    firework.className = 'firework';
    firework.style.left = `${Math.random() * 100}%`;
    firework.style.top = `${Math.random() * 100}%`;
    firework.style.background = `hsla(${Math.random() * 360}, 100%, 50%, 1)`;

    fireworksContainer.appendChild(firework);

    setTimeout(() => {
      firework.remove();
    }, 800); // Slightly shorter than animation duration to keep performance smooth
  }

  function burstFireworks() {
    for (let i = 0; i < 10; i++) {
      createFirework();
    }
  }

  // Start firing fireworks at a regular interval
  fireworksInterval = setInterval(burstFireworks, 400);
}

function stopFireworks() {
  clearInterval(fireworksInterval); // Stop the fireworks
  // Remove the fireworks container
  const fireworksContainer = document.querySelector('.fireworks-container');
  if (fireworksContainer) {
    fireworksContainer.remove();
  }
}