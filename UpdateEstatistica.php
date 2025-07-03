<?php
include "funcoesBD.php";

$vencedorRonda = $_POST['vencedor']; 
$perdedorRonda = $_POST['perdedor'];
$vencedorTorneio = $_POST['vencedorTorneio'];

//quando ha espaço ele mete o "%20" por isso vou trocar-los por espaço para nao haver problema no sql
$vencedorRonda = str_replace('%20', ' ', $vencedorRonda);   
$perdedorRonda = str_replace('%20', ' ', $perdedorRonda);
$vencedorTorneio = str_replace('%20', ' ', $vencedorTorneio);

$sql_update_vencedor = "UPDATE competidor SET nBatalhasVencidas = nBatalhasVencidas + 1 WHERE imagem = '$vencedorRonda'";
$resultadoVencedor = obterDadosBaseDados($sql_update_vencedor);

$sql_update_perdedor = "UPDATE competidor SET nBatalhasPerdidas = nBatalhasPerdidas + 1 WHERE imagem = '$perdedorRonda'";
$resultadoPerdedor = obterDadosBaseDados($sql_update_perdedor);

$sql_update_vencedor_torneio = "UPDATE competidor SET nTorneiosVencidos = nTorneiosVencidos + 1 WHERE imagem = '$vencedorTorneio'";
$resultadovencedor_torneio = obterDadosBaseDados($sql_update_vencedor_torneio);

?>
