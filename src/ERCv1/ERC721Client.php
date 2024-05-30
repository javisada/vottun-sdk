<?php

namespace Vottun\ERCv1;

use Vottun\VottunClient;

class ERC721Client
{
	private $client;
	private $contractAddress;
	private $network;

	/**
	* @notice Crea una instancia de ERC721Client para interactuar con la API de ERC721.
	* @dev Este constructor inicializa ERC721Client con un VottunClient. El VottunClient debe estar configurado con las credenciales y ajustes necesarios para interactuar con la API de Vottun. ERC721Client proporciona métodos para desplegar contratos ERC721, transferir NFTs y más, utilizando la API de Vottun.
	* @param VottunClient $client La instancia de VottunClient configurada con las credenciales y ajustes de la API.
	* @param int $network El ID de la red donde se desplegará el contrato ERC721.
	* @param string $contractAddress La dirección del contrato ERC721 (opcional).
	*/
	public function __construct(VottunClient $client, int $network, string $contractAddress = null)
	{
		$this->client = $client;
		$this->network = intval($network);
		$this->contractAddress = $contractAddress;
	}

	/**
	* @notice Despliega un nuevo contrato ERC721 en la cadena de bloques.
	* @dev Llama a la API de Vottun para desplegar un nuevo contrato ERC721 con los parámetros iniciales especificados. La operación de despliegue requiere que el llamante esté autenticado con credenciales API válidas.
	* @param string $name El nombre de la colección ERC721.
	* @param string $symbol El símbolo de la colección ERC721.
	* @param string $alias El alias de la colección ERC721 (opcional).
	* @param int $gasLimit El límite de gas para la transacción (opcional).
	* @return string El hash de transacción de la operación de despliegue.
	* @example deploy('MiToken', 'MTK', 'MiToken', 1000000, 80002)
	*/
	public function deploy(string $name, string $symbol, string $alias = null, int $gasLimit = null): string
	{
		$uri = 'erc/v1/erc721/deploy';

		if (!$this->network) {
			throw new \Exception("Se requiere el ID de red para desplegar el contrato ERC721.");
		}

		if (!$name || !$symbol) {
			throw new \Exception("Se requieren el nombre y el símbolo para desplegar el contrato ERC721.");
		}

		$gasLimit = intval($gasLimit);

		# Prepara los datos para enviar a la API
		$data = "{
			\"network\": {$this->network},
			\"name\": \"{$name}\",
			\"symbol\": \"{$symbol}\",
			\"alias\": \"{$alias}\",
			\"gasLimit\": {$gasLimit}
		}";

		# Envía la solicitud a la API
		$response = $this->client->post($uri, $data);

		# Establece la dirección del contrato y la red para futuras operaciones
		if (isset($response['contractAddress']) && isset($response['txHash'])) {
			$this->contractAddress = $response['contractAddress'];
		}

		return $response['txHash'];
	}

	/**
	* @notice Valida la dirección del contrato y el ID de red.
	* @dev Esta función verifica si la dirección del contrato y el ID de red están configurados en la instancia de ERC721Client.
	* @return bool Verdadero si la dirección del contrato y el ID de red están configurados, falso en caso contrario.
	*/
	private function validateContract(): bool
	{
		return !empty($this->contractAddress) && intval($this->network);
	}
	/**
	* @notice Crea nuevos NFTs ERC721 y asígnalos a una dirección de destinatario.
	* @dev Esta función llama a la API de Vottun para crear nuevos NFTs ERC721 y asignarlos a la dirección del destinatario especificado. El llamador debe estar autenticado con credenciales de API válidas y tener los permisos necesarios para crear nuevos tokens. La cuenta del llamador debe tener un saldo suficiente de tokens para cubrir la operación de creación.
	* @param string $recipientAddress La dirección del destinatario que recibirá los tokens.
	* @param int $tokenId El ID del token a crear.
	* @param string $ipfsUri El URI de los metadatos de IPFS para el token.
	* @param string $ipfsHash El hash de IPFS de los metadatos para el token.
	* @param int $royaltyPercentage El porcentaje de regalías a pagar al creador del token (opcional).
	* @param int $gasLimit El límite de gas para la transacción (opcional).
	* @return string El hash de la transacción de la operación de despliegue.
	*/
	public function mint(string $recipientAddress, int $tokenId, string $ipfsUri, string $ipfsHash, int $royaltyPercentage = null, int $gasLimit = null): string
	{
		$uri = 'erc/v1/erc721/mint';

		if (!$this->validateContract()) {
			throw new \Exception("Se requieren la dirección del contrato y la red.");
		}

		if (!$recipientAddress || !$tokenId || !$ipfsUri || !$ipfsHash) {
			throw new \Exception("Se requieren la dirección del destinatario, el ID del token, el URI de IPFS y el hash de IPFS.");
		}

		$royaltyPercentage = intval($royaltyPercentage);
		$gasLimit = intval($gasLimit);

		# Preparar los datos para enviar a la API
		$data = "{
			\"contractAddress\": \"{$this->contractAddress}\",
			\"network\": {$this->network},
			\"recipientAddress\": \"{$recipientAddress}\",
			\"tokenId\": {$tokenId},
			\"ipfsUri\": \"{$ipfsUri}\",
			\"ipfsHash\": \"{$ipfsHash}\",
			\"royaltyPercentage\": {$royaltyPercentage},
			\"gasLimit\": {$gasLimit}
		}";

		if (!json_decode($data)) {
			throw new \Exception("Error en la validación de los datos.");
		}

		# Enviar la solicitud a la API
		$response = $this->client->post($uri, $data);

		return $response['txHash'];
	}

	/**
	* @notice Transfiere un NFT ERC721 de una dirección a otra.
	* @dev Esta función llama a la API de Vottun para ejecutar una operación de `transferencia` en nombre del llamador. Esta operación permite al llamador transferir un NFT del remitente especificado al destinatario especificado. La operación requiere que el llamador esté autenticado con credenciales de API válidas.
	* @param int $id El ID del token a transferir.
	* @param string $from La dirección del remitente que transferirá el token.
	* @param string $to La dirección del destinatario que recibirá el token.
	* @return string El hash de la transacción de la operación de despliegue.
	*/
	public function transfer(int $id, string $from, string $to): string
	{
		$uri = 'erc/v1/erc721/transfer';

		if (!$this->validateContract()) {
			throw new \Exception("Se requieren la dirección del contrato y la red.");
		}

		if (!$id || !$from || !$to) {
			throw new \Exception("Se requieren el ID del token, el remitente y el destinatario.");
		}

		# Preparar los datos para enviar a la API
		$data = "{
			\"contractAddress\": \"{$this->contractAddress}\",
			\"network\": {$this->network},
			\"id\": {$id},
			\"from\": \"{$from}\",
			\"to\": \"{$to}\"
		}";

		if (!json_decode($data)) {
			throw new \Exception("Error en la validación de los datos.");
		}

		# Enviar la solicitud a la API
		$response = $this->client->post($uri, $data);

		return $response['txHash'];
	}


	/**
	* @notice Recuperar el balance de NFTs de una dirección dada.
	* @dev Esta función llama a la API de Vottun para recuperar el balance de NFTs de una dirección específica. El llamador debe estar autenticado con credenciales de API válidas y tener los permisos necesarios para acceder a los balances de tokens.
	* @param string $address La dirección para la cual recuperar el balance.
	* @return int El balance de NFTs de la dirección.
	*/
	public function balanceOf(string $address): int
	{
		$uri = 'erc/v1/erc721/balanceOf';

		if (!$this->validateContract()) {
			throw new \Exception("Se requieren la dirección del contrato y la red.");
		}

		if (!$address) {
			throw new \Exception("Se requiere la dirección.");
		}

		# Preparar los datos para enviar a la API
		$data = "{
			\"contractAddress\": \"{$this->contractAddress}\",
			\"network\": {$this->network},
			\"address\": \"{$address}\"
		}";

		if (!json_decode($data)) {
			throw new \Exception("Error en la validación de los datos.");
		}

		# Enviar la solicitud a la API
		$response = $this->client->post($uri, $data);

		return $response['balance'];
	}

	/**
	* @notice Recuperar el URI del Token de la colección ERC721.
	* @dev Esta función llama a la API de Vottun para recuperar el URI del Token de la colección ERC721. El llamador debe estar autenticado con credenciales de API válidas y tener los permisos necesarios para acceder al URI del token.
	* @return string El URI del Token de la colección ERC721.
	*/
	public function tokenUri(): string
	{
		$uri = 'erc/v1/erc721/tokenUri';

		if (!$this->validateContract()) {
			throw new \Exception("Se requieren la dirección del contrato y la red.");
		}

		# Preparar los datos para enviar a la API
		$data = "{
			\"contractAddress\": \"{$this->contractAddress}\",
			\"network\": {$this->network}
		}";

		if (!json_decode($data)) {
			throw new \Exception("Error en la validación de los datos.");
		}

		# Enviar la solicitud a la API
		$response = $this->client->post($uri, $data);

		return $response['uri'];
	}

	/**
	* @notice Recuperar el propietario de un NFT ERC721 específico.
	* @dev Esta función llama a la API de Vottun para recuperar el propietario de un NFT ERC721 específico. El llamador debe estar autenticado con credenciales de API válidas y tener los permisos necesarios para acceder al propietario del token.
	* @param int $id El ID del token para el cual recuperar el propietario.
	* @return string La dirección del propietario del token.
	*/
	public function ownerOf(int $id): string
	{
		$uri = 'erc/v1/erc721/ownerOf';

		if (!$this->validateContract()) {
			throw new \Exception("Se requieren la dirección del contrato y la red.");
		}

		if (!$id) {
			throw new \Exception("Se requiere el ID del token.");
		}

		# Preparar los datos para enviar a la API
		$data = "{
			\"contractAddress\": \"{$this->contractAddress}\",
			\"network\": {$this->network},
			\"id\": {$id}
		}";

		if (!json_decode($data)) {
			throw new \Exception("Error en la validación de los datos.");
		}

		# Enviar la solicitud a la API
		$response = $this->client->post($uri, $data);

		return $response['owner'];
	}

	/**
	* @notice Recuperar la dirección del contrato del ERC721.
	* @dev Devuelve la dirección del contrato del ERC721 que está siendo gestionado por la instancia de ERC721Client.
	* @return string La dirección del contrato del ERC721.
	*/
	public function getContractAddress(): string
	{
		return $this->contractAddress;
	}

	/**
	* @notice Recuperar el ID de la red del contrato ERC721.
	* @dev Devuelve el ID de la red blockchain donde el contrato ERC721 está desplegado.
	* @return int El ID de la red blockchain.
	*/
	public function getNetwork(): int
	{
		return $this->network;
	}

	/**
	* @notice Establecer la dirección del contrato del ERC721.
	* @dev Establece la dirección del contrato del ERC721 para ser gestionado por la instancia de ERC721Client.
	* @param string $contractAddress La dirección del contrato del ERC721.
	*/
	public function setContractAddress($contractAddress): void
	{
		$this->contractAddress = $contractAddress;
	}

	/**
	* @notice Establecer el ID de la red del contrato ERC721.
	* @dev Establece el ID de la red blockchain donde el contrato ERC721 está desplegado.
	* @param int $network El ID de la red blockchain.
	*/
	public function setNetwork($network): void
	{
		$this->network = intval($network);
	}
}

