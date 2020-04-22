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
use Syedzaidi\JetIntegration\Api\JetTokenRepositoryInterface;
use Syedzaidi\JetIntegration\Helper\JetApiCall;

/**
 * Class Products
 * @package Syedzaidi\JetIntegration\Block
 */

class Orders extends Template
{
    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = 'api/orders/';




    /**
     * @var ClientFactory
     */
    private $clientFactory;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;
    /**
     * @var JetTokenRepositoryInterface
     */
    private $jetTokenRepositoryInterface;
    /**
     * @var JetApiCall
     */
    private $jetApiCall;

    /**
     * Products constructor.
     * @param Template\Context $context
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param JetTokenRepositoryInterface $jetTokenRepositoryInterface
     * @param JetApiCall $jetApiCall
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        JetTokenRepositoryInterface $jetTokenRepositoryInterface,
        JetApiCall $jetApiCall,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->jetTokenRepositoryInterface = $jetTokenRepositoryInterface;
        $this->jetApiCall = $jetApiCall;
    }

    public function ordersByStatus($byStatus)
    {
        $fullfillment = $this->jetApiCall->fulfillmentNodeId();
        $response = $this->jetApiCall->sendRequest(static::API_REQUEST_ENDPOINT . $byStatus . $fullfillment, $parram = [], "GET");
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        echo "<pre>";
        print_r($responseContent);
        // Add your logic using $responseContent
//        if ($status === 401) {
//            $this->setNewSaveToken();
//            return "$status Not authorized. Please regenerate token";
//        }
//        return json_decode($responseContent);
    }

    public function ordersByTagged($byStatus, $tag)
    {
        $response = $this->jetApiCall->sendRequest(static::API_REQUEST_ENDPOINT . $byStatus . "/" . $tag, [], Request::METHOD_GET);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        echo "<pre>";
        print_r($responseContent);
        // Add your logic using $responseContent
//        if ($status === 401) {
//            $this->setNewSaveToken();
//            return "$status Not authorized. Please regenerate token";
//        }
//        return json_decode($responseContent);
    }

    public function ordersDetails($jetDefinedOrderId)
    {
        $response = $this->jetApiCall->sendRequest(static::API_REQUEST_ENDPOINT . "withoutShipmentDetail/" . $jetDefinedOrderId, [], "Get");
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        echo "<pre>";
        print_r($responseContent);
        // Add your logic using $responseContent
//        if ($status === 401) {
//            $this->setNewSaveToken();
//            return "$status Not authorized. Please regenerate token";
//        }
//        return json_decode($responseContent);
    }
}