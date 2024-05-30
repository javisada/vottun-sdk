<?php

namespace Vottun;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use phpseclib\Math\BigInteger;

class VottunClient {
	protected $client;
	protected $apiKey;
	protected $applicationVkn;

	/**
	 * Constructor de VottunClient.
	 * @param string $apiKey Clave API de Vottun
	 * @param string $applicationVkn VKN de la aplicación de Vottun
	 */
	public function __construct(string $apiKey, string $applicationVkn) {
		$this->apiKey = $apiKey;
		$this->applicationVkn = $applicationVkn;
		$this->client = new GuzzleClient([
			'base_uri' => 'https://api.vottun.tech/',
			'timeout'  => 2.0,
			'headers' => [
				'Authorization' => "Bearer {$this->apiKey}",
				'x-application-vkn' => $this->applicationVkn,
				'Accept' => 'application/json',
			],
		]);
	}

	/**
	 * Realiza una solicitud GET
	 * @param string $uri URI
	 * @param array $query Parámetros de consulta
	 * @return array Cuerpo de la respuesta
	 * @throws \Exception
	 */
	public function get($uri, $query) {
		try {
			$response = $this->client->request('GET', $uri, ['query' => $query]);

			$body = json_decode($response->getBody()->getContents(), true, 512, JSON_BIGINT_AS_STRING);

			# Verificar códigos de error HTTP generales
			if (isset($body['code'])) {
				throw new \Exception("Error de la API de Vottun: [{$body['code']}] {$body['message']}");
			}

			return $body;
		} catch (RequestException $e) {
			# Capturar errores HTTP, como 404 No Encontrado
			throw new \Exception("Error de Solicitud HTTP: " . $e->getMessage(), 0, $e);
		} catch (GuzzleException $e) {
			# Capturar errores de red, como fallos en la resolución DNS
			throw new \Exception("Error del Cliente HTTP: " . $e->getMessage(), 0, $e);
		} catch (\Exception $e) {
			# Capturar errores inesperados
			throw new \Exception("Error Inesperado: " . $e->getMessage(), 0, $e);
		}
	}

	/**
	 * Realiza una solicitud POST
	 * @param string $uri URI
	 * @param array|string $data Cuerpo de la solicitud
	 * @return array Cuerpo de la respuesta
	 * @throws \Exception
	 */
	public function post($uri, $data) {
		try {
			if (is_array($data)) {
				$response = $this->client->request('POST', $uri, [
					'json' => $data
				]);
			}
			elseif (json_decode($data)) {
				$response = $this->client->request('POST', $uri, [
					'body' => $data,
					'headers' => [
						'Content-Type' => 'application/json',
					]
				]);
			}

			$body = json_decode($response->getBody()->getContents(), true, 512, JSON_BIGINT_AS_STRING);

			# Verificar códigos de error HTTP generales
			if (isset($body['code'])) {
				throw new \Exception("Error de la API de Vottun: [{$body['code']}] {$body['message']}");
			}

			return $body;
		} catch (RequestException $e) {
			# Capturar errores HTTP, como 404 No Encontrado
			throw new \Exception("Error de Solicitud HTTP: " . $e->getMessage(), 0, $e);
		} catch (GuzzleException $e) {
			# Capturar errores de red, como fallos en la resolución DNS
			throw new \Exception("Error del Cliente HTTP: " . $e->getMessage(), 0, $e);
		} catch (\Exception $e) {
			# Capturar errores inesperados
			throw new \Exception("Error Inesperado: " . $e->getMessage(), 0, $e);
		}
	}
}
