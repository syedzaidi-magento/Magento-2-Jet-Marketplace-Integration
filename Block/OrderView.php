<?php


namespace Syedzaidi\JetIntegration\Block;


use Magento\Framework\View\Element\Template;
use Syedzaidi\JetIntegration\Helper\JetApiCall;
use Syedzaidi\JetIntegration\Model\JetOrderFactory;

class OrderView extends Template
{
    /**
     * @var JetApiCall
     */
    private $jetApiCall;
    /**
     * @var JetOrderFactory
     */
    private $jetOrderFactory;

    /**
     * OrderView constructor.
     * @param Template\Context $context
     * @param JetApiCall $jetApiCall
     * @param JetOrderFactory $jetOrderFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        JetApiCall $jetApiCall,
        JetOrderFactory $jetOrderFactory,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->jetApiCall = $jetApiCall;
        $this->jetOrderFactory = $jetOrderFactory;
    }

    public function jetOrderView()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $jet_order = $this->jetOrderFactory->create();
        $jet_order->load($orderId, 'order_id');
        $atlId = $jet_order->getAltOrderId();
        $order = (array) $this->jetApiCall->ordersDetails($atlId);
        return $order;
    }

}