<?php


namespace Syedzaidi\JetIntegration\Block;

use Magento\Framework\View\Element\Template;
use Syedzaidi\JetIntegration\Helper\JetApiCall;

class ProductsView extends Template
{
    /**
     * @var JetApiCall
     */
    private $jetApiCall;

    /**
     * ProductsView constructor.
     * @param Template\Context $context
     * @param JetApiCall $jetApiCall
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        JetApiCall $jetApiCall,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->jetApiCall = $jetApiCall;
    }

    /**
     * @return array
     */
    public function getSingleJetProduct()
    {
        $productId = $this->getRequest()->getParam('merchant_sku');
        return (array) $this->jetApiCall->getSingleSku($productId);
    }
}