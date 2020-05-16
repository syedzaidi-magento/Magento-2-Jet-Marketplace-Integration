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
        $trackingData = [
            "alt_order_id" => "371306144113",
            "shipments" =>
                [
                    "alt_shipment_id" => "123123",
                    "shipment_tracking_number" => "1Z12342452342",
                    //"response_shipment_date" => "2020-05-13T06:23:52.1325242Z",
                    //"response_shipment_method" => "FedExGround",
                    //"expected_delivery_date" => "2020-05-22T23:59:59.0000000Z",
                    //"ship_from_zip_code" => "92802",
                    //"carrier_pick_up_date" => "2020-05-20T23:59:59.0000000Z",
                    //"carrier" => "FedEx",
                    "shipment_items" => [
                        [
                            'alt_shipment_item_id' => "321212",
                            'merchant_sku' => "my-12345-product",
                            'response_shipment_sku_quantity' => 1
                        ],
                    ]
                ]
        ];
        echo "<pre>";
        //print_r($data);
        print_r($trackingData);

        $response = $this->jetApiCall->sendRequest("api/orders/371306144113/shipped", $trackingData, "PUT");
        $status = $response->getStatusCode();
        //$responseBody = $response->getBody();
        //$responseContent = $responseBody->getContents();
        //$responseContent = json_decode($responseContent, true);
        echo $status;

        //$jet_order->setTrackingNumber($data['tracking_number']);
        //$jet_order->save();
        //$this->messageManager->addSuccessMessage(__('Tracking number updated.'));
        //return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }

}