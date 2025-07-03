var tema = ''; // variavel que vai conter o tema escolhido
var temas = document.querySelectorAll('.escolheTema');  // variavel que contem todos os temas
var temasU =  document.querySelectorAll('.escolheTemaU');  // variavel que contem os temas do utilizador


// vai buscar os temas no dropdown menu e para cada um vai criar um evento
temas.forEach(item => {
    item.addEventListener('click', function (event) {  // quando sao clicados
       
        event.preventDefault(); // prevem quando o botao e selecionado envia logo o formulario da navbar
        document.getElementById('dropdownMenuButton').innerText = event.target.innerText;   // trocar o texto "escolhe um tema" para o tema escolhido
        if( document.getElementById('dropdownMenuButtonU')){
        document.getElementById('dropdownMenuButtonU').innerText = "Escolhe o teu tema";   // trocar o texto do outro dropdown menu
        }
        tema = this.value; // o tema fica o valor do botao
        carregaImagens(tema); // funcao para carregar as imagens

    });
});

// vai buscar os temas do utilizador no dropdown menu e para cada um vai criar um evento
temasU.forEach(item => {
    item.addEventListener('click', function (event) {  // quando sao clicados
       
        event.preventDefault(); // prevem quando o botao e selecionado envia logo o formulario da navbar
        document.getElementById('dropdownMenuButtonU').innerText = event.target.innerText;   // trocar o texto "escolhe um tema" para o tema escolhido
        document.getElementById('dropdownMenuButton').innerText = "Escolhe um tema";   // trocar o texto do outro dropdown menu
        tema = this.value; // o tema fica o valor do botao
        carregaImagens(tema); // funcao para carregar as imagens

    });
});



document.addEventListener('click', function (event) { // carrega quando um qualquer botao e clicado
    if (event.target.id === 'carregar') {  // se for o botao de carregar as imagens   
        if (tema === '') {    // se um tema nao for selecionado
            var randomIndex = Math.floor(Math.random() * temas.length); // escolhe um numero a sorte dentro do tamanho dos temas
            tema = temas[randomIndex].value;   //seleciona um tema a sorte
        }
        carregaImagens(tema); // funcao para carregar as imagens
    }
});


//funcao para carregar as imagens
function carregaImagens(tema) {

    reset(); // primeiro da reset a todas as imagens

    // enviar a variavel tema para o carregarimagens.php
    fetch('Carregar_Imagens.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'tema=' + encodeURIComponent(tema)
    })
        // vai buscar as imagens no ficheiro carregarimagens.php
        .then(response => response.json())
        .then(imagens => {


            //vai buscar a div que contem  todas as imagens que vao ser trocadas que estao identificadas pela class trocar
            var imagem_texto = document.querySelectorAll('.trocar');

            // percorre as imagens que vao ser trocadas
            for (let i = 0; i < imagem_texto.length; i++) {
                var imagem = imagem_texto[i].querySelector('.trocar_img'); // pega na imagem
                var nome = imagem_texto[i].querySelector('.descricao'); // pega na descrição

                if (imagens[i]) {    
                    imagem.src = imagens[i].imagem; //troca as imagens
                    nome.innerHTML = capitalizeFirstLetter(imagens[i].nome);//troca a descriçao
                }

            }
        })
        .catch(error => console.error('Error:', error));   // apanha os erros
}


//Funçao para carregar os  Nomes
function carregarNomes() {
    event.preventDefault();
    reset();  //primeiro da reset a todas as imagens

    var numeroNomes = prompt("Introduz o número de jogadores: ");
    numeroNomes = parseInt(numeroNomes);                      // numero de nomes que vao ser introduzidos


    if (isNaN(numeroNomes) || numeroNomes < 2 || numeroNomes > 16) {   //verifica se é um numero e se é entre 2 e 16
        alert("introduza um número entre 2 a 16.");
        return; 
    } 
   
       
    var nomes = [];   //array onde vao ser guardados os nomes

  
    for (var i = 1; i <= numeroNomes; i++) {

        var name = prompt("Introduza o nome para o competidor " + i + ":");
        if (name === null) {  // se o botao cancel for clicado
            return;  //acaba o prompt
        }

        if (name.length === 0) {  // se tiver vazio
            alert("O nome não pode ser vazio");
            i--;   
        }

        if (name) {              
            nomes.push(name);     //insere o nome no array
        }
    }
    nomes=shuffleArray(nomes);

    //vai buscar a div que contem  todas as imagens que vao ser trocadas que estao identificadas pela class trocar
    var imagem_texto = document.querySelectorAll('.trocar');

    // percorre as imagens que vao ser trocadas
    for (let i = 0; i < imagem_texto.length; i++) {
        var imagem = imagem_texto[i].querySelector('.trocar_img'); // pega na imagem
        var nome = imagem_texto[i].querySelector('.descricao'); // pega na descrição

        imagem.src = createNameImage(capitalizeFirstLetter(nomes[i])); //troca as imagens pelas imagens criadas pela funçao 
        nome.innerHTML = capitalizeFirstLetter(nomes[i]); //troca a descriçao


    }
}

//Funçao que fui buscar para criar uma imagem "canvas" com apenas um string
function createNameImage(name) {
    var canvas = document.createElement('canvas');
    canvas.width = 250;
    canvas.height = 150;

    var ctx = canvas.getContext('2d');
    ctx.fillStyle = "white"; 
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.font = "45px Arial";
    ctx.fillStyle = "black"; 
    ctx.textAlign = "center";
    ctx.fillText(name, canvas.width / 2, canvas.height / 2 + 10);

    return canvas.toDataURL("image/png");
}


// funcao para dar reset as imagens e ás descriçoes todas
function reset() {

    var imagens = document.getElementsByTagName('img');
    var descricoes = document.getElementsByClassName('descricao');
    var rondas = document.querySelectorAll('.ronda');
    for (var i = 0; i < imagens.length; i++) {


        imagens[i].src = "Imagens/interrogaçao_food.jpg";

        if (descricoes[i]) {
            descricoes[i].innerHTML = "";
            descricoes[i].style.backgroundColor = "";
        }
    }
    // da reset a cor verde 
    rondas.forEach(ronda => {
        ronda.style.backgroundColor = "";
        ronda.style.border = "";
    });


}


// funçao para por a primeira letra maiuscula
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

//funçao que fui buscar para baralhar o array
function shuffleArray(array) {
    // Loop through the array from the last element to the first
    for (let i = array.length - 1; i > 0; i--) {
        // Generate a random index from 0 to i
        const j = Math.floor(Math.random() * (i + 1));
        
        // Swap elements at index i and j
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}