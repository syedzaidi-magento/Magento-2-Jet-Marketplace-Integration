<?php


namespace Syedzaidi\JetIntegration\Block;


use Magento\Framework\View\Element\Template;
use phpDocumentor\Reflection\Types\String_;
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

    public function getSingleJetProduct()
    {
        echo "<pre>";
        $productId = $this->getRequest()->getParam('merchant_sku');
        $jetOrder = $this->jetApiCall->getSingleSku($productId);
        foreach ($jetOrder as $key => $value) {
            if (is_string($key) && !is_array($value)) {
                echo "<strong>$key</strong>" . ": " . $value . "<br>";
            }
            if (is_array($value)) {
                foreach ($value as $key2 => $result) {
                    echo "<strong>fulfillment_node_id</strong>: " . $result->fulfillment_node_id . "<br>";
                    echo "<strong>Quantity</strong>: " . $result->quantity . "<br>";
                    echo "<strong>inventory_last_update</strong>: " . $result->inventory_last_update . "<br>";
                    }
            }
        }
        print_r($jetOrder->sub_status);
    }
}