<?php
namespace App;

use Exception;

class OnBase {

	private $clientId = '';
	private $clientSecret = '';
	private $scope = '';
	private $token = '';

	public function __construct()
	{
		// Read data from .env
		$this->clientId = $_ENV['ONBASE_CLIENT_ID'] ?? '';
		$this->clientSecret = $_ENV['ONBASE_CLIENT_SECRET'] ?? '';
		$this->scope = $_ENV['ONBASE_SCOPE'] ?? '';

		$this->token = $this->getAccessToken();
	}

	/**
	 * Get access token
	 * 
	 * @return string
	 */
	private function getAccessToken (): string
	{
		$tokenUrl = "https://login.microsoftonline.com/152d35f3-e46a-4e14-b95c-f462dab4ce70/oauth2/v2.0/token";
		$data = http_build_query([
			"grant_type"    => "client_credentials",
			"client_id"     => $this->clientId,
			"client_secret" => $this->clientSecret,
			"scope"         => $this->scope,
		]);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $tokenUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		$json = json_decode($response, true);

		if (isset($json["access_token"])) {
			return $json["access_token"];
		}

		throw new Exception("Error token PROD: " . $response);
	}

	public function getDocuments ()
	{
		$url = "https://mule-worker-internal-ng-e-documents.de-c1.cloudhub.io:8082/api/v1/cms/public-doc-list";

		$payload = [
			"DocumentType" => "PDM - Policies",
			"Filters" => [
				[
					"Operator" => "=",
					"Relation" => "AND",
					"Key" => "NG - Country Code",
					"Value" => "ESP"
				]
			],
			"Sorting" => [
				"KeywordName" => "NG - Original File Name",
				"OrderType"   => "NG"
			]
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: Bearer {$this->token}",
			"Content-Type: application/json"
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		return json_decode($response, true);
	}
}
