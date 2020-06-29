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
use Syedzaidi\JetIntegration\Model\JetReturnFactory;


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
     * API fulfillment node id
     */
    const MODULE_ENABLE = 'general/jet_integration_settings/enable';

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
     * @var JetReturnFactory
     */
    private $jetReturnFactory;

    /**
     * JetApiCall constructor.
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlInterface
     * @param JetProductFactory $jetProductFactory
     * @param JetOrderFactory $jetOrderFactory
     * @param JetReturnFactory $jetReturnFactory
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
        JetReturnFactory $jetReturnFactory,
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
        $this->jetReturnFactory = $jetReturnFactory;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Exception
     */
    public function allProductByCategory()
    {
        $categories = $this->scopeConfig->getValue(self::CATEGORY_ID, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        if ($categories) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoriesFilter(['in' => $categories]);
            return $collection;
        }else {
            throw new \Exception('Category id not define under Configuration > Jet Integration setting.');
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function jetProductData()
    {
        $nodeId = $this->scopeConfig->getValue(self::JET_BROWSE_NODE_ID, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        if ($this->allProductByCategory()) {
            $productData = [];
            foreach ($this->allProductByCategory() as $product) {
                $productDataSet = [
                    "product_title" => $product->getName(),
                    "product_description" => $product->getDescription(),
                    "mfr_part_number" => $product->getSku(),
                    "ASIN" => $product->getAsin(),
                    "jet_browse_node_id" => intval($nodeId),
                    "multipack_quantity" => 6,
                    "brand" => $product->getBrand(),
                    "main_image_url" => $this->urlInterface->getBaseUrl() . "pub/media/catalog/product/" . substr($product->getImage(), 1),
                ];
                array_push($productData, $productDataSet);
            }
            return $productData;
        }else {
            throw new \Exception('Products are not populated in provided Category id.');
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function jetInventoryData()
    {
        $fulfillment_id = $this->scopeConfig->getValue(self::FULFILLMENT_NODE_KEY, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
        if ($this->allProductByCategory()) {
            $inventoryData = [];
            foreach ($this->allProductByCategory() as $product) {
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
                array_push($inventoryData, $inventoryDataSet);
            }
            return $inventoryData;
        }else {
            throw new \Exception('Products inventory not set.');
        }
    }

    /**
     * @return \Syedzaidi\JetIntegration\Api\Data\JetTokenInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSaveToken()
    {
        return $this->jetTokenRepositoryInterface->getTokenById(0);

    }

    /**
     * @return mixed
     */
    public function setNewSaveToken()
    {
        $new_token = "Bearer " . $this->getToken();
        return $this->jetTokenRepositoryInterface->setNewToken($new_token);
    }

    /**
     * @param $sku
     * @return mixed|string
     */
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

    /**
     * @return array
     */
    public function jetOrderData()
    {
        $orderList = $this->ordersByTagged("ready", "");
        $orderListAcknowledged = $this->ordersByTagged("acknowledged", "");
        $orderListInprogress = $this->ordersByTagged("inprogress", "");
        $orderListComplete = $this->ordersByTagged("complete", "");
        $orderData = [];
        if ($orderList) {
            foreach ($orderList->order_urls as $order_url) {
                $orderId = substr("$order_url", 30);
                array_push($orderData, $this->ordersDetails($orderId));
            }
        }
        if ($orderListAcknowledged) {
            foreach ($orderListAcknowledged->order_urls as $order_url) {
                $orderId = substr("$order_url", 30);
                array_push($orderData, $this->ordersDetails($orderId));
            }
        }
        if ($orderListInprogress) {
            foreach ($orderListInprogress->order_urls as $order_url) {
                $orderId = substr("$order_url", 30);
                array_push($orderData, $this->ordersDetails($orderId));
            }
        }
        if ($orderListComplete) {
            foreach ($orderListComplete->order_urls as $order_url) {
                $orderId = substr("$order_url", 30);
                array_push($orderData, $this->ordersDetails($orderId));
            }
        }
        return $orderData;
    }

    /**
     * @return array
     */
    public function jetReturnData()
    {
        $returnCreateList = $this->returnsByStatus("created");
        $returnInProgressList = $this->returnsByStatus("inprogress");
        $returnCompletedList = $this->returnsByStatus("completed by merchant");

        $returnData = [];
        if ($returnCreateList) {
            foreach ($returnCreateList->return_urls as $return_url) {
                $returnId = substr("$return_url", 15);
                array_push($returnData, $this->returnsDetails($returnId));
            }
        }
        if ($returnInProgressList) {
            foreach ($returnInProgressList->return_urls as $return_url) {
                $returnId = substr("$return_url", 15);
                array_push($returnData, $this->returnsDetails($returnId));
            }
        }
        if ($returnCompletedList) {
            foreach ($returnCompletedList->return_urls as $return_url) {
                $returnId = substr("$return_url", 15);
                array_push($returnData, $this->returnsDetails($returnId));
            }
        }

        return $returnData;
    }

    /**
     * @param $byStatus
     * @param $tag
     * @return mixed
     */
    public function ordersByTagged($byStatus, $tag)
    {
        $response = $this->sendRequest("api/orders/" . $byStatus . "/" . $tag, [], Request::METHOD_GET);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return json_decode($responseContent);
    }

    /**
     * @param $jetDefinedOrderId
     * @return mixed
     */
    public function ordersDetails($jetDefinedOrderId)
    {
        $response = $this->sendRequest("api/orders/" . "withoutShipmentDetail/" . $jetDefinedOrderId, [], "Get");
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return json_decode($responseContent);
    }

    /**
     * @param $byStatus
     * @param $isCancelled
     * @return mixed
     */
    public function ordersByStatus($byStatus, $isCancelled)
    {
        $fullfillment = "?isCancelled=$isCancelled" . $this->fulfillmentNodeId();
        $response = $this->sendRequest("api/orders/" . $byStatus . $fullfillment, $parram = [], "GET");
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return json_decode($responseContent);
    }

    /**
     * @param $byStatus
     * @return mixed
     */
    public function returnsByStatus($byStatus)
    {
        $response = $this->sendRequest("api/returns/" . $byStatus, $parram = [], "GET");
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return json_decode($responseContent);
    }

    /**
     * @param $jetDefinedReturnId
     * @return mixed
     */
    public function returnsDetails($jetDefinedReturnId)
    {
        $response = $this->sendRequest("api/returns/state/" . $jetDefinedReturnId, [], "Get");
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return json_decode($responseContent);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function saveJetProducts()
    {
        if ($this->jetProductData()) {
            foreach ($this->jetProductData() as $item) {
                $singleItem = (array)$this->getSingleSku($item["mfr_part_number"]);
                if ($singleItem) {
                    $jet_product = $this->jetProductFactory->create();
                    $jet_product->load($item["mfr_part_number"], "merchant_sku");
                    $merchantSku = isset($singleItem['merchant_sku']) ? $singleItem['merchant_sku'] : null;
                    $jet_product->setMerchantSku($merchantSku);
                    $lastUpdate = isset($singleItem['sku_last_update']) ? $singleItem['sku_last_update'] : null;
                    $jet_product->setSkuLastUpdate($lastUpdate);
                    $createdDate = isset($singleItem['sku_created_date']) ? $singleItem['sku_created_date'] : null;
                    $jet_product->setSkuCreatedDate($createdDate);
                    $status = isset($singleItem['status']) ? $singleItem['status'] : null;
                    $jet_product->setStatus($status);
                    $jet_product->save();
                }
            }
        }
    }

    /**
     * @return void
     */
    public function saveJetOrders()
    {
        if ($this->jetOrderData()) {
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
    }

    /**
     * @return void
     */
    public function saveJetReturns()
    {
        if ($this->jetReturnData()) {
            foreach ($this->jetReturnData() as $return) {
                $jet_return = $this->jetReturnFactory->create();
                $jet_return->load($return->reference_return_authorization_id, "reference_return_authorization_id");
                $jet_return->setAltOrderId($return->alt_order_id);
                $jet_return->setMerchantOrderId($return->merchant_order_id);
                $jet_return->setMerchantReturnAuthorizationId($return->merchant_return_authorization_id);
                $jet_return->setReferenceReturnAuthorizationId($return->reference_return_authorization_id);
                $jet_return->setShippingCarrier($return->shipping_carrier);
                $jet_return->setTrackingNumber($return->tracking_number);
                $jet_return->setReturnStatus($return->return_status);
                $jet_return->save();
            }
        }
    }

    /**
     * @return void
     */
    public function jetOrderAcknowledge()
    {
        if ($this->jetOrderData()) {
            foreach ($this->jetOrderData() as $order) {
                $altOrderId = $order->alt_order_id;
                $jetDefinedOrderId = $order->merchant_order_id;
                $orderItems = (array)$order->order_items[0];
                $orderItemId = $orderItems['order_item_id'];
                if ($order->status === "ready" && $order->jet_request_directed_cancel === false) {
                    $acknowledgeData = [
                        "json" => [
                            "acknowledgement_status" => "accepted",
                            "alt_order_id" => $altOrderId,
                            "order_items" => [
                                [
                                    'order_item_acknowledgement_status' => "fulfillable",
                                    'order_item_id' => $orderItemId
                                ]
                            ]
                        ]
                    ];
                    $this->sendRequest("api/orders/" . $jetDefinedOrderId . "/acknowledge", $acknowledgeData, "Put");
                }
            }
        }
    }

    /**
     * @return mixed
     */
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

    /**
     * @return string
     */
    public function fulfillmentNodeId()
    {
        return "&fulfillment_node=" . $this->scopeConfig->getValue(self::FULFILLMENT_NODE_KEY, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
    }
    /**
     * @return string
     */
    public function moduleEnable()
    {
        return $this->scopeConfig->getValue(self::MODULE_ENABLE, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
    }

    /**
     * @param $uriEndpoint
     * @param $params
     * @param $requestMethod
     * @return Response
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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