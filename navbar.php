<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link href="CSS/style2.css" rel="stylesheet">
    
    <style>
        /* --- CSS DA NAVBAR --- */
        .navbar {
            padding-top: 5px !important;
            padding-bottom: 5px !important;
            min-height: 60px;
            transition: margin-top 0.4s ease-in-out;
            z-index: 1000;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
        }

        .navbar-hidden {
            margin-top: -120px !important;
        }

        /* --- BOTÃO FLUTUANTE (MENU) --- */
        #toggleNavBtn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 2000; 
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(145deg, #212529, #495057);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        #toggleNavBtn:hover {
            transform: scale(1.1);
            background: linear-gradient(145deg, #000000, #343a40);
        }

        /* --- CONTROLO DE IMAGENS (+ INPUT -) --- */
        .input-group-custom {
            display: flex;
            align-items: center;
            width: 140px; /* Aumentei para caber bem */
        }

        .btn-qty {
            border: 1px solid #ced4da;
            background-color: #fff;
            color: #333;
            font-weight: bold;
            width: 35px; /* Botões um pouco mais largos */
            height: 38px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .btn-qty:hover {
            background-color: #e9ecef;
        }

        /* O input no meio (Agora editável) */
        #numImagens {
            text-align: center;
            width: 50px;
            height: 38px;
            padding: 0;
            margin: 0;
            border: 1px solid #ced4da;
            border-left: none;
            border-right: none;
            -moz-appearance: textfield; /* Esconde setas nativas Firefox */
        }
        /* Esconde setas nativas Chrome/Safari */
        #numImagens::-webkit-outer-spin-button,
        #numImagens::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Ajustes de Texto e Mobile */
        .nav-gap { gap: 10px; }
        
        /* Garante que o texto não parte nem desaparece */
        .img-label { 
            font-weight: bold; 
            font-size: 14px; 
            white-space: nowrap; 
            margin-right: 8px;
        }

        @media (max-width: 992px) {
            .navbar-container { justify-content: center !important; gap: 15px; padding-top: 10px; }
            .section-block { justify-content: center !important; margin-bottom: 5px; }
            #toggleNavBtn { width: 40px; height: 40px; top: 10px; left: 10px; font-size: 18px; }
        }
    </style>
</head>

<body>

    <?php
    session_start();
    ?>
    
    <button id="toggleNavBtn" onclick="toggleNavbar()" title="Mostrar/Esconder Menu">
        &#8593;
    </button>

    <nav id="mainNav" class="navbar navbar-light bg-light" style="margin-bottom: 0;">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap navbar-container" style="padding-left: 70px;">
            
            <div class="d-flex align-items-center flex-wrap nav-gap section-block">
                
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                       Choose Theme
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php
                        include "funcoesBD.php";
                        if(function_exists('obterDadosBaseDados')){
                            $sql_temas = "SELECT nome FROM tema WHERE utilizadorId = 2" ;
                            $resultado = obterDadosBaseDados($sql_temas);
                            if ($resultado && mysqli_num_rows($resultado) > 0) {
                                while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                                    echo '<li><button class="dropdown-item escolheTema" type="button" value="' . $linhaTabela['nome'] . '">' . $linhaTabela['nome'] . '</button></li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>

                <?php if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true) { ?>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButtonU" data-bs-toggle="dropdown" aria-expanded="false">
                        Your Themes
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonU">
                        <?php
                        $userId = $_SESSION['userId']; 
                        $sql_temas = "SELECT nome FROM tema WHERE utilizadorId = $userId"; 
                        $resultado = obterDadosBaseDados($sql_temas);
                        if ($resultado && mysqli_num_rows($resultado) > 0) {
                            while ($linhaTabela = mysqli_fetch_assoc($resultado)) {
                                echo '<li><button class="dropdown-item escolheTemaU" type="button" value="' . $linhaTabela['nome'] . '">' . $linhaTabela['nome'] . '</button></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                <?php } ?>

                <a class="btn btn-outline-success" href="CriarTema.php">Create</a>
                
                <div class="d-flex align-items-center">
                    <span class="img-label">Nº of Images:</span>
                    <div class="input-group-custom">
                        <button class="btn btn-qty" type="button" onclick="alterarQtd(-2)">-</button>
                        <input type="number" class="form-control" id="numImagens" value="8" min="2" max="16" onchange="validarInput(this)">
                        <button class="btn btn-qty" type="button" onclick="alterarQtd(2)">+</button>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-center align-items-center section-block">
                <a href="index.php" class="btn btn-outline-dark px-4 fw-bold">TOURNAMENT</a>
            </div>

            <div class="d-flex align-items-center flex-wrap nav-gap justify-content-end section-block">
                <a href="Estatisticas.php" class="btn btn-outline-secondary">Statistics</a>
                <a href="sobre.php" class="btn btn-outline-secondary">About</a>
                <?php
                if (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] == true) {
                    echo '<a href="Logout.php" class="btn btn-outline-secondary">Logout</a>';
                } else {
                    echo '<a href="Login.php" class="btn btn-outline-secondary">Login</a>';
                    echo '<a href="Registar.php" class="btn btn-outline-secondary">Register</a>';
                }
                ?>
            </div>
        </div>
    </nav>

    <script>
        // 1. Esconder Navbar
        function toggleNavbar() {
            var nav = document.getElementById('mainNav');
            var btn = document.getElementById('toggleNavBtn');
            nav.classList.toggle('navbar-hidden');
            
            if (nav.classList.contains('navbar-hidden')) {
                btn.innerHTML = "&#8595;";
                btn.style.opacity = "0.7"; 
            } else {
                btn.innerHTML = "&#8593;";
                btn.style.opacity = "1";
            }
        }

        // 2. Botões (+ e -)
        function alterarQtd(valor) {
            var input = document.getElementById('numImagens');
            var atual = parseInt(input.value) || 0; // Se estiver vazio, assume 0
            var novoValor = atual + valor;

            if (novoValor >= 2 && novoValor <= 16) {
                input.value = novoValor;
                dispararEvento(input);
            }
        }

        // 3. Validar Escrita Manual
        function validarInput(input) {
            var valor = parseInt(input.value);

            // Se não for número ou for menor que 2, fixa em 2
            if (isNaN(valor) || valor < 2) {
                input.value = 2;
            } 
            // Se for maior que 16, fixa em 16
            else if (valor > 16) {
                input.value = 16;
            }
            
            // Se for número ímpar (ex: 7), ajusta para par (8) - Opcional, depende da tua lógica
            // if (input.value % 2 !== 0) input.value = parseInt(input.value) + 1;

            dispararEvento(input);
        }

        // Helper para avisar o resto do site que mudou
        function dispararEvento(elemento) {
            elemento.dispatchEvent(new Event('change'));
            elemento.dispatchEvent(new Event('input'));
        }
    </script>
</body>
</html>