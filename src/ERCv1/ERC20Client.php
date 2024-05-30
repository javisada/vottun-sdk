<?php

namespace Vottun\ERCv1;

use Vottun\VottunClient;

class ERC20Client
{
	private $client;
	private $contractAddress;
	private $network;

	/**
	* @notice Crea una instancia de ERC20Client para interactuar con la API de ERC20.
	* @dev Este constructor inicializa ERC20Client con un VottunClient. El VottunClient debe estar configurado con las credenciales y ajustes necesarios para interactuar con la API de Vottun. ERC20Client proporciona métodos para desplegar tokens ERC20, transferir tokens y más, utilizando la API de Vottun.
	* @param VottunClient $client La instancia de VottunClient configurada con las credenciales y ajustes de la API.
	* @param int $network El ID de la red donde se despliega el token ERC20.
	* @param string $contractAddress La dirección del contrato del token ERC20 (opcional).
	*/
	public function __construct(VottunClient $client, int $network, string $contractAddress = null)
	{
		$this->client = $client;
		$this->network = intval($network);
		$this->contractAddress = $contractAddress;
	}

	/**
	* @notice Despliega un nuevo contrato de token ERC20 en la cadena de bloques.
	* @dev Llama a la API de Vottun para desplegar un nuevo contrato de token ERC20 con los parámetros iniciales especificados. La operación de despliegue requiere que el llamante esté autenticado con credenciales de API válidas.
	* @param string $name El nombre del token ERC20.
	* @param string $symbol El símbolo del token ERC20.
	* @param string $alias El alias del token ERC20.
	* @param string $initialSupply El suministro inicial del token ERC20, en wei.
	* @param int $gasLimit El límite de gas para la transacción (opcional).
	* @return string El hash de transacción de la operación de despliegue.
	* @example deploy('MiToken', 'MTK', 'MiToken', 1000000, 80002)
	*/
	public function deploy(string $name, string $symbol, string $alias, string $initialSupply, int $gasLimit = null): string
	{
		$uri = 'erc/v1/erc20/deploy';

		if (!$this->network) {
			throw new \Exception("Se requiere el ID de red para desplegar el token ERC20.");
		}

		if (!$name || !$symbol || !$initialSupply) {
			throw new \Exception("Se requieren el nombre, el símbolo y el suministro inicial para desplegar el token ERC20.");
		}

		$gasLimit = intval($gasLimit);

		# Prepara los datos para enviar a la API
		$data = "{
			\"network\": {$this->network},
			\"name\": \"{$name}\",
			\"symbol\": \"{$symbol}\",
			\"alias\": \"{$alias}\",
			\"initialSupply\": {$initialSupply},
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
 * @notice Valida la dirección del contrato y el ID de la red.
 * @dev Esta función verifica si la dirección del contrato y el ID de la red están configurados en la instancia de ERC20Client.
 * @return bool Verdadero si la dirección del contrato y el ID de la red están configurados, falso en caso contrario.
 */
private function validateContract(): bool
{
    return !empty($this->contractAddress) && intval($this->network);
}

/**
 * @notice Transfiere tokens ERC20 de la cuenta del llamante a otra dirección.
 * @dev Esta función llama a la API de Vottun para ejecutar una operación de transferencia de tokens en nombre del llamante. La cuenta del llamante debe tener un saldo suficiente de tokens para cubrir la cantidad de transferencia. Esta operación requiere que el llamante esté autenticado con credenciales API válidas.
 * @param string $recipient La dirección del destinatario que recibirá los tokens.
 * @param string $amount La cantidad de tokens a transferir, en wei.
 * @param int|null $gasLimit El límite de gas para la transacción (opcional).
 * @return string El hash de transacción de la operación de transferencia.
 */
public function transfer(string $recipient, string $amount, ?int $gasLimit = null): string
{
    $uri = 'erc/v1/erc20/transfer';

    if (!$this->validateContract()) {
        throw new \Exception("Se requiere la dirección del contrato y la red.");
    }

    if (!$recipient || !$amount) {
        throw new \Exception("Se requiere la dirección del destinatario y la cantidad.");
    }

    $gasLimit = intval($gasLimit);

    # Preparar los datos para enviar a la API
    $data = "{
        \"contractAddress\": \"{$this->contractAddress}\",
        \"network\": {$this->network},
        \"recipient\": \"{$recipient}\",
        \"amount\": {$amount},
        \"gasLimit\": {$gasLimit}
    }";

    if (!json_decode($data)) {
        throw new \Exception("Error en la validación de datos.");
    }

    # Enviar la solicitud a la API
    $response = $this->client->post($uri, $data);

    return $response['txHash'];
}


	/**
	* @notice Transfers ERC20 tokens from one address to another on behalf of the caller.
	* @dev This function calls the Vottun API to execute a `transferFrom` operation on behalf of the caller. This operation allows the caller to transfer tokens from the specified sender to the specified recipient. The operation requires the caller to be authenticated with valid API credentials.
	* @param string $sender The address of the sender that will transfer the tokens.
	* @param string $recipient The address of the recipient that will receive the tokens.
	* @param string $amount The amount of tokens to transfer, in wei.
	* @param int $gasLimit The gas limit for the transaction (optional).
	* @return string The transaction hash of the deployment operation.
	*/
	public function transferFrom(string $sender, string $recipient, string $amount, int $gasLimit = null): string
	{
		$uri = 'erc/v1/erc20/transferFrom';

		if (!$this->validateContract()) {
			throw new \Exception("Contract address and network are required.");
		}

		if (!$sender || !$recipient || !$amount) {
			throw new \Exception("Sender, recipient, and amount are required to transfer ERC20 token.");
		}

		$gasLimit = intval($gasLimit);

		# Prepare the data to be sent to the API
		$data = "{
			\"contractAddress\": \"{$this->contractAddress}\",
			\"network\": {$this->network},
			\"sender\": \"{$sender}\",
			\"recipient\": \"{$recipient}\",
			\"amount\": {$amount},
			\"gasLimit\": {$gasLimit}
		}";

		# Send the request to the API
		$response = $this->client->post($uri, $data);

		return $response['txHash'];
	}

	/**
	* @notice Approve a spender to spend a specific amount of tokens on behalf of the caller.
	* @dev This function calls the Vottun API to execute an `approve` operation on behalf of the caller. This operation allows the specified spender to spend the specified amount of tokens on behalf of the caller. The operation requires the caller to be authenticated with valid API credentials.
	* @param string $spender The address of the spender to approve.
	* @param string $amount The amount of tokens to approve for spending, in wei.
	* @param int $gasLimit The gas limit for the transaction (optional).
	* @return string The transaction hash of the deployment operation.
	*/
	public function increaseAllowance(string $spender, string $addedValue, int $gasLimit = null): string
	{
		$uri = 'erc/v1/erc20/increaseAllowance';

		if (!$this->validateContract()) {
			throw new \Exception("Contract address and network are required.");
		}

		if (!$spender || !$addedValue) {
			throw new \Exception("Spender and addedValue are required to increase allowance.");
		}

		$gasLimit = intval($gasLimit);

		# Prepare the data to be sent to the API
		$data = "{
			\"contractAddress\": \"{$this->contractAddress}\",
			\"network\": {$this->network},
			\"spender\": \"{$spender}\",
			\"addedValue\": {$addedValue},
			\"gasLimit\": {$gasLimit}
		}";

		$response = $this->client->post($uri, $data);

		return $response['txHash'];
	}

	/**
 * @notice Disminuye la asignación de un gastador para gastar una cantidad específica de tokens en nombre del llamante.
 * @dev Esta función llama a la API de Vottun para ejecutar una operación `decreaseAllowance` en nombre del llamante. Esta operación disminuye la asignación del gastador especificado para gastar la cantidad de tokens especificada en nombre del llamante. La operación requiere que el llamante esté autenticado con credenciales de API válidas.
 * @param string $spender La dirección del gastador para disminuir la asignación.
 * @param string $substractedValue La cantidad de tokens a disminuir en la asignación, en wei.
 * @param int|null $gasLimit El límite de gas para la transacción (opcional).
 * @return string El hash de transacción de la operación de despliegue.
 */
public function decreaseAllowance(string $spender, string $substractedValue, ?int $gasLimit = null): string
{
    $uri = 'erc/v1/erc20/decreaseAllowance';

    if (!$this->validateContract()) {
        throw new \Exception("Se requiere la dirección del contrato y la red.");
    }

    if (!$spender || !$substractedValue) {
        throw new \Exception("Se requieren el gastador y el valor a disminuir en la asignación.");
    }

    $gasLimit = intval($gasLimit);

    # Preparar los datos para enviar a la API
    $data = "{
        \"contractAddress\": \"{$this->contractAddress}\",
        \"network\": {$this->network},
        \"spender\": \"{$spender}\",
        \"substractedValue\": {$substractedValue},
        \"gasLimit\": {$gasLimit}
    }";

    $response = $this->client->post($uri, $data);

    return $response['txHash'];
}

/**
 * @notice Obtiene la asignación de un token ERC-20 específico para un propietario y un gastador dados.
 * @dev Llama a la API de Vottun para obtener la asignación del token asociado con la dirección del contrato proporcionada para el propietario y el gastador especificados. Este método realiza una operación de lectura y no requiere gas.
 * @param string $owner La dirección que posee los tokens.
 * @param string $spender La dirección que tiene permiso para gastar los tokens.
 * @return int La asignación del token ERC-20 para el propietario y el gastador especificados.
 */
public function allowance(string $owner, string $spender): string
{
    $uri = 'erc/v1/erc20/allowance';

    if (!$this->validateContract()) {
        throw new \Exception("Se requiere la dirección del contrato y la red.");
    }

    # Preparar los datos para enviar a la API
    $params = [
        'contractAddress' => $this->contractAddress,
        'network' => $this->network,
        'owner' => $owner,
        'spender' => $spender
    ];

    $response = $this->client->get($uri, $params);

    return $response['allowance'];
}


	/**
	* @notice Retrieve the name of a specific ERC-20 token.
	* @dev Calls the Vottun API to obtain the name of the token associated with the provided contract address. This method performs a read operation and does not require gas.
	* @param string $contractAddress The contract address of the ERC-20 token.
	* @param string $network The network ID where the token is deployed.
	* @return string The name of the ERC-20 token.
	*/
	public function name(): string
	{
		$uri = 'erc/v1/erc20/name';

		if (!$this->validateContract()) {
			throw new \Exception("Contract address and network are required.");
		}

		# Prepare the data to be sent to the API
		$params = [
			'contractAddress' => $this->contractAddress,
			'network' => $this->network
		];

		$response = $this->client->get($uri, $params);

		return $response['name'];
	}

	/**
	* @notice Retrieve the symbol of a specific ERC-20 token.
	* @dev Calls the Vottun API to obtain the symbol of the token associated with the provided contract address. This method performs a read operation and does not require gas.
	* @param string $contractAddress The contract address of the ERC-20 token.
	* @param string $network The network ID where the token is deployed.
	* @return string The symbol of the ERC-20 token.
	*/
	public function symbol(): string
	{
		$uri = 'erc/v1/erc20/symbol';

		if (!$this->validateContract()) {
			throw new \Exception("Contract address and network are required.");
		}

		# Prepare the data to be sent to the API
		$params = [
			'contractAddress' => $this->contractAddress,
			'network' => $this->network
		];

		$response = $this->client->get($uri, $params);

		return $response['symbol'];
	}

	/**
	* @notice Retrieve the total supply of a specific ERC-20 token.
	* @dev Calls the Vottun API to obtain the total supply of the token associated with the provided contract address. This method performs a read operation and does not require gas.
	* @param string $contractAddress The contract address of the ERC-20 token.
	* @param string $network The network ID where the token is deployed.
	* @return int The total supply of the ERC-20 token.
	*/
	public function totalSupply(): string
	{
		$uri = 'erc/v1/erc20/totalSupply';

		if (!$this->validateContract()) {
			throw new \Exception("Contract address and network are required.");
		}

		# Prepare the data to be sent to the API
		$params = [
			'contractAddress' => $this->contractAddress,
			'network' => $this->network
		];

		$response = $this->client->get($uri, $params);

		return $response['totalSupply'];
	}

	/**
 * @notice Obtener el número de decimales de un token ERC-20 específico.
 * @dev Llama a la API de Vottun para obtener el número de decimales del token asociado con la dirección del contrato proporcionada. Este método realiza una operación de lectura y no requiere gas.
 * @param string $contractAddress La dirección del contrato del token ERC-20.
 * @param string $network El ID de la red donde se implementa el token.
 * @return int El número de decimales del token ERC-20.
 */
public function decimals(): string
{
    $uri = 'erc/v1/erc20/decimals';

    if (!$this->validateContract()) {
        throw new \Exception("Se requiere la dirección del contrato y la red.");
    }

    # Preparar los datos para enviar a la API
    $params = [
        'contractAddress' => $this->contractAddress,
        'network' => $this->network
    ];

    $response = $this->client->get($uri, $params);

    return $response['decimals'];
}

/**
 * @notice Obtener el saldo de un token ERC-20 específico para una dirección dada.
 * @dev Llama a la API de Vottun para obtener el saldo del token asociado con la dirección del contrato proporcionada para la dirección especificada. Este método realiza una operación de lectura y no requiere gas.
 * @param string $address La dirección para la cual se va a obtener el saldo del token.
 * @return int El saldo del token ERC-20 para la dirección especificada.
 */
public function balanceOf(string $address): string
{
    $uri = 'erc/v1/erc20/balanceOf';

    if (!$this->validateContract()) {
        throw new \Exception("Se requiere la dirección del contrato y la red.");
    }

    # Preparar los datos para enviar a la API
    $params = [
        'contractAddress' => $this->contractAddress,
        'network' => $this->network,
        'address' => $address
    ];

    $response = $this->client->get($uri, $params);

    return $response['balance'];
}

/**
 * @notice Obtener la dirección del contrato del token ERC-20.
 * @dev Devuelve la dirección del contrato del token ERC-20 que está siendo gestionado actualmente por la instancia de ERC20Client.
 * @return string La dirección del contrato del token ERC-20.
 */
public function getContractAddress(): string
{
    return $this->contractAddress;
}


	/**
 * @notice Recupera el ID de red del token ERC-20.
 * @dev Devuelve el ID de red de la blockchain donde se ha implementado el token ERC-20.
 * @return int El ID de red de la blockchain.
 */
public function getNetwork(): int
{
    return $this->network;
}

/**
 * @notice Establece la dirección del contrato del token ERC-20.
 * @dev Establece la dirección del contrato del token ERC-20 que será gestionado por la instancia de ERC20Client.
 * @param string $contractAddress La dirección del contrato del token ERC-20.
 */
public function setContractAddress($contractAddress): void
{
    $this->contractAddress = $contractAddress;
}

/**
 * @notice Establece el ID de red del token ERC-20.
 * @dev Establece el ID de red de la blockchain donde se ha implementado el token ERC-20.
 * @param int $network El ID de red de la blockchain.
 */
public function setNetwork($network): void
{
    $this->network = intval($network);
}
}
