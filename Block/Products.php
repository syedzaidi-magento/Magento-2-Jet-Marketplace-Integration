<?php
declare(strict_types=1);

namespace Syedzaidi\JetIntegration\Block;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class Products
 * @package Syedzaidi\JetIntegration\Block
 */
class Products extends Template
{
    /**
     * API request URL
     */
    const API_REQUEST_URI = 'https://merchant-api.jet.com/';

    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = 'api/merchant-skus/';

    /**
     * API user key
     */
    const USER_KEY = 'general/jet_integration_settings/user_key';

    /**
     * API secret key
     */
    const SECRET_KEY = 'general/jet_integration_settings/secret_key';

    /**
     * @var ClientFactory
     */
    private $clientFactory;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Template\Context $context,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function getToken()
    {
        $user = $this->scopeConfig->getValue(self::USER_KEY, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        $secret = $this->scopeConfig->getValue(self::SECRET_KEY, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);

        $end_point = 'api/token';
        $params = [
              'json' => [
                  'user' => $user,
                  'pass' => $secret
              ]
        ];
        $method = Request::HTTP_METHOD_POST;
        $response = $this->doRequest($end_point, $params, $method);
        $status = $response->getStatusCode();
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        return $responseContent;
    }

    public function getSingleSku($sku)
    {
        $response = $this->doRequest(static::API_REQUEST_ENDPOINT . $sku);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        // Add your logic using $responseContent
        return json_decode($responseContent);

    }

    public function getProducts()
    {
        $sku_list = ['my-12345-product', 'my-12345-product', 'my-12345-product'];

        $products = [];
        foreach ($sku_list as $sku) {
            array_push($products, $this->getSingleSku($sku));
        }
        return $products;
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [
            'headers'  => [
                'Content-type' => 'application/json',
                'Authorization' => 'TOKEN_ID'
            ]
        ],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => self::API_REQUEST_URI
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

}