<?php

require __DIR__.'/../vendor/autoload.php';

use Vottun\VottunClient;
use Vottun\ERCv1\ERC20Client;

$vottunApiKey = ''; // Reemplazar con tu clave API de Vottun
$vottunApplicationVkn = ''; // Reemplazar con tu VKN de aplicación de Vottun

$network = 80002; // Amoy testnet
$destAddress = ''; // Dirección de destino

$vottunClient = new VottunClient($vottunApiKey, $vottunApplicationVkn);
$erc20Token = new ERC20Client($vottunClient, $network, null);

# Desplegar un nuevo token ERC20
$initialSupply = \Web3\Utils::toWei("1000000", 'ether');
$response = $erc20Token->deploy('TestToken', 'TST', 'TestToken', $initialSupply);
echo "Respuesta del despliegue: {$response}\n";

if ($erc20Token->getContractAddress()) {
	# Transferir algunos tokens a la dirección de destino
	$amount = \Web3\Utils::toWei("100", 'ether');
	$response = $erc20Token->transfer($destAddress, $amount);
	echo "Respuesta de la transferencia: {$response}\n";

	# Obtener el balance de la dirección de destino
	$response = $erc20Token->balanceOf($destAddress);
	echo "Balance de $destAddress: {$response}\n";
}
?>
