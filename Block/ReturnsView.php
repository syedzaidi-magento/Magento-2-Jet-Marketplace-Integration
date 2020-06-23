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

    /**
     * @return void
     */
    public function realTimeReturn()
    {
        $returnId = $this->getRequest()->getParam('return_id');
        $jetReturn = $this->jetReturnFactory->create();
        $jetReturn->load($returnId, 'return_id');
        $returnAuthorizationId = $jetReturn->getMerchantReturnAuthorizationId();
        $orderReturn = (array) $this->jetApiCall->returnsDetails($returnAuthorizationId);

        echo "<pre>".json_encode($orderReturn, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."</pre>";
    }
}