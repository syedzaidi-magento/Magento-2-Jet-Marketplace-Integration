<?php
declare(strict_types=1);

namespace Syedzaidi\JetIntegration\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Webapi\Rest\Request;
use Syedzaidi\JetIntegration\Helper\JetApiCall;
use Syedzaidi\JetIntegration\Model\JetProductFactory;

class Products extends Template
{
    /**
     * API request endpoint
     */
    const API_REQUEST_ENDPOINT = 'api/merchant-skus/';

    /**
     * @var JetApiCall
     */
    private $jetApiCall;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var FilterGroup
     */
    private $filterGroup;
    /**
     * @var JetProductFactory
     */
    private $jetProductFactory;


    /**
     * Products constructor.
     * @param Template\Context $context
     * @param JetApiCall $jetApiCall
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param Filter $filter
     * @param FilterGroup $filterGroup
     * @param JetProductFactory $jetProductFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        JetApiCall $jetApiCall,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaInterface $searchCriteria,
        Filter $filter,
        FilterGroup $filterGroup,
        JetProductFactory $jetProductFactory,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->jetApiCall = $jetApiCall;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
        $this->jetProductFactory = $jetProductFactory;
    }

    public function sendCatalogToJet()
    {
        $this->jetApiCall->setNewSaveToken();
        foreach ($this->jetApiCall->allProductByCategory() as $item) {
            $params = ["json" => $item];
            $response = $this->jetApiCall->sendRequest("api/merchant-skus/" . $item['mfr_part_number'], $params, Request::HTTP_METHOD_PUT);
            $status = $response->getStatusCode(); // 200 status code
            echo $item['product_title'] . " - Status: " . $status . "<br >";
        }
    }

    public function getSingleSku($sku)
    {
        $response = $this->jetApiCall->sendRequest("https://merchant-api.jet.com/api/merchant-skus/" . $sku, [], "GET");
        //$response = $this->jetApiCall->sendRequest(static::API_REQUEST_ENDPOINT . $sku, [], Request::METHOD_GET);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        // Add your logic using $responseContent
        if ($status === 401) {
            $this->jetApiCall->setNewSaveToken();
            return "$status Not authorized. Please regenerate token";
        }
            return json_decode($responseContent);
    }

    public function getProducts()
    {
        $products = [];
        foreach ($this->jetApiCall->allProductByCategory() as $item) {
            array_push($products, $this->getSingleSku($item["mfr_part_number"]) ?: ['sku_not_found' => $item["mfr_part_number"]]);
        }
        return $products;
    }

    public function saveJetProducts()
    {
        foreach ($this->jetApiCall->allProductByCategory() as $item) {
            $singleItem = (array)$this->getSingleSku($item["mfr_part_number"]);
            $jet_product = $this->jetProductFactory->create();
            $jet_product->load($item["mfr_part_number"], "merchant_sku");
            $jet_product->setMerchantSku($singleItem['merchant_sku']);
            $jet_product->setStatus($singleItem['status']);
            $jet_product->save();
        }
    }

}