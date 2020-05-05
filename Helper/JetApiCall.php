<?php


namespace Syedzaidi\JetIntegration\Helper;


use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Rest\Request;
use Syedzaidi\JetIntegration\Api\JetTokenRepositoryInterface;
use Syedzaidi\JetIntegration\Model\JetOrderFactory;
use Syedzaidi\JetIntegration\Model\JetProductFactory;


class JetApiCall
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
     * CATEGORY id
     */
    const CATEGORY_ID = 'general/jet_integration_settings/category_id';

    /**
     * API browse node id
     */
    const JET_BROWSE_NODE_ID = 'general/jet_integration_settings/jet_browse_node_id';

    /**
     * API fulfillment node id
     */
    const FULFILLMENT_NODE_KEY = 'general/jet_integration_settings/fulfillment_node_id';

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var UrlInterface
     */
    private $urlInterface;
    /**
     * @var JetProductFactory
     */
    private $jetProductFactory;
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var JetOrderFactory
     */
    private $jetOrderFactory;

    /**
     * JetApiCall constructor.
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlInterface
     * @param JetProductFactory $jetProductFactory
     * @param JetOrderFactory $jetOrderFactory
     * @param StockRegistryInterface $stockRegistry
     * @param JetTokenRepositoryInterface $jetTokenRepositoryInterface
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        UrlInterface $urlInterface,
        JetProductFactory $jetProductFactory,
        JetOrderFactory $jetOrderFactory,
        StockRegistryInterface $stockRegistry,
        JetTokenRepositoryInterface $jetTokenRepositoryInterface
    ){
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->scopeConfig = $scopeConfig;
        $this->jetTokenRepositoryInterface = $jetTokenRepositoryInterface;
        $this->collectionFactory = $collectionFactory;
        $this->urlInterface = $urlInterface;
        $this->jetProductFactory = $jetProductFactory;
        $this->stockRegistry = $stockRegistry;
        $this->jetOrderFactory = $jetOrderFactory;
    }

    public function allProductByCategory()
    {
        $categories = $this->scopeConfig->getValue(self::CATEGORY_ID, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $categories]);

        return $collection;
    }

    public function jetProductData()
    {
        $nodeId = $this->scopeConfig->getValue(self::JET_BROWSE_NODE_ID, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        $productData = [];
        foreach ($this->allProductByCategory() as $product){
            $productDataSet = [
                "product_title" => $product->getName(),
                "product_description" => $product->getDescription(),
                "mfr_part_number" => $product->getSku(),
                "ASIN" => $product->getAsin(),
                "jet_browse_node_id" => intval($nodeId),
                "multipack_quantity" => 6,
                "brand" => $product->getBrand(),
                "main_image_url" => $this->urlInterface->getBaseUrl() . substr($product->getImage(), 1),
            ];
            array_push($productData,  $productDataSet);

        }
        return $productData;
    }

    public function jetInventoryData()
    {
        $fulfillment_id = $this->scopeConfig->getValue(self::FULFILLMENT_NODE_KEY, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        $inventoryData = [];
        foreach ($this->allProductByCategory() as $product){
            $product_qty = $this->stockRegistry->getStockItemBySku($product->getSku());
            $inventoryDataSet = [
                "inventory" =>
                [
                    "fulfillment_nodes" => [
                        [
                            'fulfillment_node_id' => $fulfillment_id,
                            'quantity' => $product_qty->getQty()
                        ],
                    ]
                ],
                "sku" => $product->getSku(),
                "price" => floatval($product->getPrice()),
            ];
            array_push($inventoryData,  $inventoryDataSet);

        }
        return $inventoryData;
    }

    public function getSaveToken()
    {
        return $this->jetTokenRepositoryInterface->getTokenById(0);

    }

    public function setNewSaveToken()
    {
        $new_token = "Bearer " . $this->getToken();
        return $this->jetTokenRepositoryInterface->setNewToken($new_token);
    }

    public function getSingleSku($sku)
    {
        $response = $this->sendRequest(static::API_REQUEST_ENDPOINT . $sku, [], Request::METHOD_GET);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        // Add your logic using $responseContent
        if ($status === 401) {
            $this->setNewSaveToken(); // create new token if it expire
            return "$status Not authorized. Please regenerate token";
        }
        return json_decode($responseContent);
    }

    public function jetOrderData()
    {
        $orderList = $this->ordersByTagged("ready", "");
        //print_r($orderList->order_urls);
        $orderData = [];
        foreach ($orderList->order_urls as $order_url) {
            $orderId = substr("$order_url", 30);
            array_push($orderData, $this->ordersDetails($orderId));
        }
        return $orderData;
    }

    public function ordersByTagged($byStatus, $tag)
    {
        $response = $this->sendRequest("api/orders/" . $byStatus . "/" . $tag, [], Request::METHOD_GET);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return json_decode($responseContent);
    }

    public function ordersDetails($jetDefinedOrderId)
    {
        $response = $this->sendRequest("api/orders/" . "withoutShipmentDetail/" . $jetDefinedOrderId, [], "Get");
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return json_decode($responseContent);
    }

    public function saveJetProducts()
    {
        foreach ($this->jetProductData() as $item) {
            $singleItem = (array)$this->getSingleSku($item["mfr_part_number"]);
            $jet_product = $this->jetProductFactory->create();
            $jet_product->load($item["mfr_part_number"], "merchant_sku");
            $jet_product->setMerchantSku($singleItem['merchant_sku']);
            $lastUpdate = isset($singleItem['sku_last_update']) ? $singleItem['sku_last_update'] : "N/A";
            $jet_product->setSkuLastUpdate($lastUpdate);
            $createdDate = isset($singleItem['sku_created_date']) ? $singleItem['sku_created_date'] : "N/A";
            $jet_product->setSkuCreatedDate($createdDate);
            $jet_product->setStatus($singleItem['status']);
            $jet_product->save();
        }
    }

    public function saveJetOrders()
    {
        foreach ($this->jetOrderData() as $order) {
            $jet_order = $this->jetOrderFactory->create();
            $jet_order->load($order->alt_order_id, "alt_order_id");
            $jet_order->setAltOrderId($order->alt_order_id);
            $jet_order->setName($order->buyer->name);
            $jet_order->setPhoneNumber($order->buyer->phone_number);
            $jet_order->setHashEmail($order->hash_email);
            $jet_order->setOrderPlacedDate($order->order_placed_date);
            $jet_order->setStatus($order->status);
            $jet_order->save();
      }
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
        $responseContent = json_decode($responseContent, true);
        $token =  $responseContent['id_token'];
        return $token;
    }

    public function fulfillmentNodeId()
    {
        return "&fulfillment_node=" . $this->scopeConfig->getValue(self::FULFILLMENT_NODE_KEY, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
    }

    public function sendRequest($uriEndpoint, $params, $requestMethod)
    {
       return $this->doRequest($uriEndpoint, $params, $requestMethod);
    }

    /**
     * Do API request with provided params
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     * @return Response
     */
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => self::API_REQUEST_URI
        ]]);
        try {
            $params = array_merge($params, ['headers' => ['Content-type' => 'application/json', 'charset' => "UTF-8", 'Authorization' => $this->getSaveToken()]]);
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