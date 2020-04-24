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
     * Products constructor.
     * @param Template\Context $context
     * @param JetApiCall $jetApiCall
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param Filter $filter
     * @param FilterGroup $filterGroup
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        JetApiCall $jetApiCall,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaInterface $searchCriteria,
        Filter $filter,
        FilterGroup $filterGroup,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->jetApiCall = $jetApiCall;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
    }

    public function sendCatalogToJet()
    {
        foreach ($this->jetApiCall->allProductByCategory() as $item){
           echo "<pre>";
           print_r($item);
       }
    }

    public function getSingleSku($sku)
    {
        $response = $this->jetApiCall->sendRequest(static::API_REQUEST_ENDPOINT . $sku, [], Request::METHOD_GET);
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
        $sku_list = ['my-12345-product', 'abc-1234-asdf', 'my-12345-product', 'my-12345-product', 'abc-1234-asdf'];

        $products = [];
        foreach ($sku_list as $sku) {
            array_push($products,  $this->getSingleSku($sku) ?: ['sku_not_found' => $sku]);
        }

        return $products;
    }
}