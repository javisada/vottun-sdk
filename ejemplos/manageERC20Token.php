<?php

require __DIR__.'/../vendor/autoload.php';

use Vottun\VottunClient;
use Vottun\ERCv1\ERC20Client;

$vottunApiKey = ''; // Reemplazar con tu clave API de Vottun
$vottunApplicationVkn = ''; // Reemplazar con tu VKN de aplicación de Vottun

$network = 80002; // Amoy testnet
$contractAddress = ''; // Dirección del contrato del token
$ownerAddress = ''; // Dirección del propietario del token
$otherAddress = ''; // Dirección de destino

$vottunClient = new VottunClient($vottunApiKey, $vottunApplicationVkn);
$erc20Token = new ERC20Client($vottunClient, $network, $contractAddress);

/*# Transferir algunos tokens a la dirección de destino
$amount = $vottunClient->etherToWei("100");
$response = $erc20Token->transfer($otherAddress, $amount);
echo "Respuesta de la transferencia:\n";
print_r($response);*/

# Aumentar la asignación (allowance) de la dirección de destino
$amount = strval(\Web3\Utils::toWei("100.001", 'ether'));
$response = $erc20Token->increaseAllowance($otherAddress, $amount);
echo "Respuesta de increaseAllowance: {$response}\n";

# Obtener la asignación (allowance) de la dirección de destino
$response = $erc20Token->allowance($ownerAddress, $otherAddress);
echo "Asignación de {$otherAddress}: {$response}\n";

# Obtener el balance de la dirección de destino
$response = $erc20Token->balanceOf($otherAddress);
echo "Balance de {$otherAddress}: {$response}\n";
?>
