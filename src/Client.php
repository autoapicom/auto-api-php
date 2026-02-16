<?php

declare(strict_types=1);

namespace AutoApi;

use AutoApi\Exception\ApiException;
use AutoApi\Exception\AuthException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private HttpClient $http;
    private string $apiKey;
    private string $apiVersion;

    public function __construct(string $apiKey, string $baseUrl = 'https://api1.auto-api.com', string $apiVersion = 'v2')
    {
        $this->apiKey = $apiKey;
        $this->apiVersion = $apiVersion;
        $this->http = new HttpClient([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'timeout' => 30,
        ]);
    }

    /**
     * Available filters for a source (brands, models, body types, etc.)
     */
    public function getFilters(string $source): array
    {
        return $this->get("api/{$this->apiVersion}/{$source}/filters");
    }

    /**
     * List of offers with pagination and filters.
     *
     * Params: page (required), brand, model, configuration, complectation,
     * transmission, color, body_type, engine_type, year_from, year_to,
     * mileage_from, mileage_to, price_from, price_to
     */
    public function getOffers(string $source, array $params = []): array
    {
        return $this->get("api/{$this->apiVersion}/{$source}/offers", $params);
    }

    /**
     * Single offer by inner_id.
     */
    public function getOffer(string $source, string $innerId): array
    {
        return $this->get("api/{$this->apiVersion}/{$source}/offer", ['inner_id' => $innerId]);
    }

    /**
     * Get change_id by date (format: yyyy-mm-dd).
     */
    public function getChangeId(string $source, string $date): int
    {
        $response = $this->get("api/{$this->apiVersion}/{$source}/change_id", ['date' => $date]);

        return (int) $response['change_id'];
    }

    /**
     * Changes feed (added/changed/removed) starting from change_id.
     */
    public function getChanges(string $source, int $changeId): array
    {
        return $this->get("api/{$this->apiVersion}/{$source}/changes", ['change_id' => $changeId]);
    }

    /**
     * Get offer data by its URL on the marketplace.
     */
    public function getOfferByUrl(string $url): array
    {
        return $this->post('api/v1/offer/info', ['url' => $url]);
    }

    private function get(string $endpoint, array $params = []): array
    {
        $params['api_key'] = $this->apiKey;

        try {
            $response = $this->http->get($endpoint, [
                'query' => $params,
            ]);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }

        return $this->decode($response->getBody()->getContents(), $response->getStatusCode());
    }

    private function post(string $endpoint, array $data): array
    {
        try {
            $response = $this->http->post($endpoint, [
                'json' => $data,
                'headers' => [
                    'x-api-key' => $this->apiKey,
                ],
            ]);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }

        return $this->decode($response->getBody()->getContents(), $response->getStatusCode());
    }

    private function decode(string $body, int $statusCode): array
    {
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Invalid JSON response: ' . json_last_error_msg(), $statusCode);
        }

        return $data;
    }

    /**
     * @throws ApiException|AuthException
     * @return never
     */
    private function handleException(GuzzleException $e): never
    {
        $statusCode = 0;
        $body = null;
        $message = $e->getMessage();

        if (method_exists($e, 'getResponse') && $e->getResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if (is_array($body) && isset($body['message'])) {
                $message = $body['message'];
            }
        }

        if (in_array($statusCode, [401, 403])) {
            throw new AuthException($message, $statusCode);
        }

        throw new ApiException($message, $statusCode, $body);
    }
}
