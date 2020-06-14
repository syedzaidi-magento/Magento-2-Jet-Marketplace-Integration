<?php


namespace Syedzaidi\JetIntegration\Block;


use Magento\Framework\View\Element\Template;
use Syedzaidi\JetIntegration\Helper\JetApiCall;

class Returns extends Template
{
    /**
     * @var JetApiCall
     */
    private $jetApiCall;

    /**
     * Returns constructor.
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
     * @return void
     */
    public function jetReturnList()
    {
        $this->jetApiCall->setNewSaveToken();
        $this->jetApiCall->saveJetReturns();
    }
}