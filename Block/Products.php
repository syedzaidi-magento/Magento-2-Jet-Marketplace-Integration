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
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->jetApiCall = $jetApiCall;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
    }

    /**
     * @return null
     */
    public function sendCatalogToJet()
    {
        $this->jetApiCall->setNewSaveToken();
        foreach ($this->jetApiCall->jetProductData() as $item) {
            $params = ["json" => $item];
            $response = $this->jetApiCall->sendRequest("api/merchant-skus/" . $item['mfr_part_number'], $params, Request::HTTP_METHOD_PUT);
            $status = $response->getStatusCode(); // 200 status code
        }
        $this->jetApiCall->saveJetProducts();
        return null;
    }

    /**
     * @return null
     */
    public function sendInventoryToJet()
    {
        foreach ($this->jetApiCall->jetInventoryData() as $item) {
            $params = ["json" => $item['inventory']];
            $response = $this->jetApiCall->sendRequest("api/merchant-skus/" . $item['sku'] . "/inventory", $params, "PATCH");
            $status = $response->getStatusCode(); // 200 status code
        }
        return null;
    }

    /**
     * @return null
     */
    public function sendPriceToJet()
    {
        foreach ($this->jetApiCall->jetInventoryData() as $item) {
            $params = ["json" => ['price' => $item['price']]];
            $response = $this->jetApiCall->sendRequest("api/merchant-skus/" . $item['sku'] . "/price", $params, "PUT");
            $status = $response->getStatusCode(); // 200 status code
        }
        return null;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $products = [];
        foreach ($this->jetApiCall->allProductByCategory() as $item) {
            array_push($products, $this->jetApiCall->getSingleSku($item["mfr_part_number"]) ?: ['sku_not_found' => $item["mfr_part_number"]]);
        }
        return $products;
    }

}