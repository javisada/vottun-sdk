# vottun-sdk

El Vottun PHP SDK proporciona una interfaz PHP fácil de usar para interactuar con la API de Vottun. Inicialmente implementado para operaciones de tokens ERC20 y ERC721 en blockchains compatibles con Ethereum, este SDK simplifica la integración de las funcionalidades de la API de Vottun en tus aplicaciones PHP, incluyendo despliegues de contratos, transferencias de tokens, consultas de balances y gestión de permisos.

Este software no está oficialmente afiliado con Vottun.

License: LGPL v3

Características
Despliegue y mint de tokens ERC20
Despliegue y mint de NFTs ERC721
Transferencia de tokens
Gestión de permisos
Consulta de balances de tokens
Soporte para operaciones con números grandes
Fácil integración con proyectos PHP
Requisitos
PHP >=7.0
Composer
Una App ID y clave API de Vottun (https://app.vottun.io/)
Estructura de carpetas
folder
Copiar código
├── examples                  # Scripts de ejemplo
├── lib                       # Librerías
│   └── Web3                  # Librería web3p/web3.php
│       └── Utils.php         # Clase Utils de web3.php, usada para gestionar números grandes.
└── src                       # Archivos fuente
    ├── VottunClient.php      # Clase principal VottunClient, usada para interactuar con la API de Vottun.
    └── ERCv1                 # Clientes API ERC v1 de Vottun
        ├── ERC20Client.php   # Clase ERC20Client, usada para interactuar con tokens ERC20.
        └── ERC721Client.php  # Clase ERC721Client, usada para interactuar con tokens ERC721.
Instalación
Ejecuta el siguiente comando en el directorio de tu proyecto para agregar el Vottun PHP SDK como una dependencia:

bash
Copiar código
composer require ceseshi/vottun-php-sdk
Uso
Guía rápida sobre cómo usar el Vottun PHP SDK en tu proyecto:

Inicializar el Cliente
php
Copiar código
require_once 'vendor/autoload.php';

use Vottun\VottunClient;
use Vottun\ERCv1\ERC20Client;

$vottunApiKey = 'your_api_key_here';
$vottunApplicationVkn = 'your_application_vkn_here';
$vottunClient = new VottunClient($vottunApiKey, $vottunApplicationVkn);
$network = 80002; // Amoy testnet
Desplegar ERC20
php
Copiar código
$erc20token = new ERC20Client($vottunClient, $network, null);

$name = 'MyToken';
$symbol = 'MTK';
$decimals = 18;
$initialSupply = strval(\Web3\Utils::toWei("1000000", 'ether')); // Suministro inicial en Wei

$transactionHash = $erc20token->deploy($name, $symbol, $decimals, $initialSupply);
$contractAddress = $erc20token->getContractAddress();

echo "Deploy hash: {$transactionHash}";
echo "Deploy address: {$contractAddress}";
Transferir ERC20
php
Copiar código
$contractAddress = 'your_contract_address_here';
$erc20token = new ERC20Client($vottunClient, $network, $contractAddress);

$recipientAddress = 'recipient_address_here';
$amount = strval(\Web3\Utils::toWei("100.001", 'ether')); // Cantidad en Wei

$transactionHash = $erc20token->transfer($recipientAddress, $amount);
$balance = $erc20token->balanceOf($recipientAddress);

echo "Transfer hash: {$transactionHash}";
echo "Recipient balance: {$balance}";
Mint de ERC721
php
Copiar código
$contractAddress = 'your_contract_address_here';
$erc721token = new ERC721Client($vottunClient, $network, $contractAddress);

$recipientAddress = 'recipient_address_here';
$ipfsUri = 'ipfs_uri_here';
$ipfsHash = 'ipfs_hash_here';
$royaltyPercentage = 10;
$tokenId = 1;

$transactionHash = $erc721token->mint($recipientAddress, $tokenId, $ipfsUri, $ipfsHash, $royaltyPercentage);
echo "Mint hash: {$transactionHash}";
Funcionalidades pendientes
Cliente ERC1155
Cliente POAP
Cliente Web3 Core
Cliente IPFS
Cliente de Wallets Custodiadas
Cliente de Balances
Cliente de Estimación de Gas
Contribuciones
Las contribuciones al Vottun PHP SDK son bienvenidas. Por favor, asegúrate de que tus contribuciones sigan las siguientes directrices:

Haz un fork del repositorio y crea tu rama desde main.
Si has añadido código que debe ser probado, añade pruebas.
Asegúrate de que el suite de pruebas pase.
¡Envía ese pull request!
Soporte
Si encuentras algún problema o necesitas asistencia, por favor abre un issue en el repositorio de GitHub.

Licencia
Este proyecto está licenciado bajo la LGPL. Consulta el archivo LICENSE para más detalles.

Agradecimientos
Gracias al equipo de Vottun por proporcionar la API.
