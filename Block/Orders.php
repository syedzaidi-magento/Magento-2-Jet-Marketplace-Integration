<?php
declare(strict_types=1);

namespace Syedzaidi\JetIntegration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Webapi\Rest\Request;
use Syedzaidi\JetIntegration\Helper\JetApiCall;


class Orders extends Template
{
    /**
     * @var JetApiCall
     */
    private $jetApiCall;

    /**
     * Products constructor.
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

    public function jetOrderList()
    {
        $this->jetApiCall->setNewSaveToken();
        $this->jetApiCall->saveJetOrders();
        $this->jetApiCall->jetOrderAcknowledge();

    }
}