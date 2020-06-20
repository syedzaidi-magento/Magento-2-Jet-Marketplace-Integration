<?php


namespace Syedzaidi\JetIntegration\Block;


use Magento\Framework\View\Element\Template;
use Syedzaidi\JetIntegration\Helper\JetApiCall;
use Syedzaidi\JetIntegration\Model\JetReturnFactory;

class ReturnsView extends Template
{
    /**
     * @var JetReturnFactory
     */
    private $jetReturnFactory;
    /**
     * @var JetApiCall
     */
    private $jetApiCall;

    /**
     * ReturnsView constructor.
     * @param Template\Context $context
     * @param JetReturnFactory $jetReturnFactory
     * @param JetApiCall $jetApiCall
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        JetReturnFactory $jetReturnFactory,
        JetApiCall $jetApiCall,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->jetReturnFactory = $jetReturnFactory;
        $this->jetApiCall = $jetApiCall;
    }

    public function realTimeReturn()
    {
        $returnId = $this->getRequest()->getParam('return_id');
        $jetReturn = $this->jetReturnFactory->create();
        $jetReturn->load($returnId, 'return_id');
        $returnAuthorizationId = $jetReturn->getMerchantReturnAuthorizationId();
        $return = (array) $this->jetApiCall->returnsDetails($returnAuthorizationId);
        foreach ($return as $key => $value) {
            if (is_string($value)) {
                echo $key . " - " . $value . "<br >";
            }
            if (is_array($value)) {
                foreach ($value as $key1 => $value1) {
                    echo $key1 . " - -  " . "<br >";
                }
            }
        }

        echo "<hr>";
        echo "<pre>";
        print_r($return);

    }
}