<?php


namespace Syedzaidi\JetIntegration\Controller\Adminhtml\Orders;


use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Syedzaidi\JetIntegration\Helper\JetApiCall;
use Syedzaidi\JetIntegration\Model\JetOrderFactory;


class Save extends Action implements HttpPostActionInterface
{
    /**
     * @var JetOrderFactory
     */
    private $jetOrderFactory;
    /**
     * @var JetApiCall
     */
    private $jetApiCall;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param JetOrderFactory $jetOrderFactory
     * @param JetApiCall $jetApiCall
     */
    public function __construct(
        Action\Context $context,
        JetOrderFactory $jetOrderFactory,
        JetApiCall $jetApiCall
    ){
        parent::__construct($context);
        $this->jetOrderFactory = $jetOrderFactory;
        $this->jetApiCall = $jetApiCall;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $singleOrder = (array) $this->jetApiCall->ordersDetails($data['alt_order_id']);

        if ($singleOrder['jet_request_directed_cancel'] === true) {
            $this->messageManager->addErrorMessage(__('Tracking number not save. Order was canceled.'));
            return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
        }
        $jet_order = $this->jetOrderFactory->create();
        $jet_order->load($data['alt_order_id'], "alt_order_id");

        if ($jet_order->getTrackingNumber()) {
            $this->messageManager->addErrorMessage(__('Tracking number already saved.'));
            return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
        }

        // Call api here to add tracking number on Jet.....
        $orderItems = (array) $singleOrder['order_items'][0];
        $merchantSku = $orderItems['merchant_sku'];
        $jetDefineOrderId = $singleOrder['merchant_order_id'];
        $altOrderId = $data['alt_order_id'];
        $trackingNumber = $data['tracking_number'];
        $shippingCarrier = $data['shipping_carrier'];
        $orderDate = date('Y-m-d\TH:i:s'. substr ( ( string ) microtime (), 1, 7 ) . 'Z-h:i');
        $trackingData = [
            'json' => [
                'alt_order_id' => $altOrderId,
                'shipments' => [
                    [
                    'shipment_tracking_number' => $trackingNumber,
                    'response_shipment_date' => $orderDate,
                    'carrier' => $shippingCarrier,
                    'shipment_items' => [
                            [
                                'alt_shipment_item_id' => $altOrderId,
                                'merchant_sku' => $merchantSku,
                                'response_shipment_sku_quantity' => 1,
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $response = $this->jetApiCall->sendRequest("api/orders/". $jetDefineOrderId . "/shipped", $trackingData, "PUT");
        $status = $response->getStatusCode(); // 204 updated tracking information....

        if ($status === 204) {
            $jet_order->setTrackingNumber($data['tracking_number']);
            $jet_order->setShippingCarrier($data['shipping_carrier']);
            $jet_order->save();
            $this->messageManager->addSuccessMessage(__("Tracking number updated to Jet Marketplace and Magento Store. $status"));
            return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
        }

        $this->messageManager->addErrorMessage(__("Tracking info is not update to Jet Marketplace. $status"));
        return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);

    }

}