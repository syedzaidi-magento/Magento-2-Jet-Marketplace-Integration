<?php


namespace Syedzaidi\JetIntegration\Controller\Adminhtml\Returns;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Syedzaidi\JetIntegration\Helper\JetApiCall;
use Syedzaidi\JetIntegration\Model\JetReturnFactory;

class Complete extends Action implements HttpPostActionInterface
{
    /**
     * @var JetApiCall
     */
    private $jetApiCall;
    /**
     * @var JetReturnFactory
     */
    private $jetReturnFactory;

    /**
     * Complete constructor.
     * @param Action\Context $context
     * @param JetApiCall $jetApiCall
     * @param JetReturnFactory $jetReturnFactory
     */
    public function __construct(
        Action\Context $context,
        JetApiCall $jetApiCall,
        JetReturnFactory $jetReturnFactory
    ){
        parent::__construct($context);
        $this->jetApiCall = $jetApiCall;
        $this->jetReturnFactory = $jetReturnFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParam('return_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $jet_return = $this->jetReturnFactory->create();
        $return = $jet_return->load($data, "return_id");
        $singleReturn = (array) $this->jetApiCall->returnsDetails($return->getMerchantReturnAuthorizationId());
print_r($singleReturn);
//        if (is_array($singleOrder) && count($singleOrder)) {
//
//            if ($singleOrder['jet_request_directed_cancel'] === true) {
//                $this->messageManager->addErrorMessage(__('Order was already canceled.'));
//                return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
//            }
//
//            if ($jet_order->getTrackingNumber()) {
//                $this->messageManager->addErrorMessage(__('Order status already completed. Order can not be cancel.'));
//                return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
//            }
//
//            if ($singleOrder['status'] === "complete") {
//                $this->messageManager->addErrorMessage(__('Order status already completed. Order can not be cancel.'));
//                return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
//            }
//
//            // Call api here to add tracking number on Jet.....
//            $orderItems = (array)$singleOrder['order_items'][0];
//            $merchantSku = $orderItems['merchant_sku'];
//            $jetDefineOrderId = $singleOrder['merchant_order_id'];
//            $altOrderId = $order->getAltOrderId();
//            $cancelData = [
//                'json' => [
//                    'alt_order_id' => $altOrderId,
//                    'shipments' => [
//                        [
//                            "alt_shipment_id" => $altOrderId,
//                            'shipment_items' => [
//                                [
//                                    'alt_shipment_item_id' => $altOrderId,
//                                    'merchant_sku' => $merchantSku,
//                                    'response_shipment_cancel_qty' => 1,
//                                ],
//                            ],
//                        ],
//                    ],
//                ]
//            ];
//
//            $response = $this->jetApiCall->sendRequest("api/orders/" . $jetDefineOrderId . "/shipped", $cancelData, "PUT");
//            $status = $response->getStatusCode(); // 204 Canceled order....
//
//            if ($status === 204) {
//                $jet_order->setStatus($singleOrder['status']);
//                $jet_order->save();
//                $this->messageManager->addSuccessMessage(__("Order has been canceled to Jet Marketplace and Magento Store. $status"));
//                return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
//            }
//
//            $this->messageManager->addErrorMessage(__("Order not cancel to Jet Marketplace. $status"));
//            return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
//        }
//        $this->messageManager->addErrorMessage(__("Order not successfully canceled to Jet Marketplace."));
//        return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }
}