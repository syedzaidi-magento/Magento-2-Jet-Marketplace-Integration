<?php


namespace Syedzaidi\JetIntegration\Controller\Adminhtml\Returns;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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
        if (is_array($singleReturn) && count($singleReturn)) {

            if ($singleReturn['return_status'] === "completed") {
                $this->messageManager->addErrorMessage(__('This return was already completed.'));
                return $resultRedirect->setPath('*/*/view', ['return_id' => $this->getRequest()->getParam('return_id')]);
            }

            // Call api here to add tracking number on Jet.....
            $merchantOrderId = $return->getMerchantOrderId();
            $altOrderId = $return->getAltOrderId();
            $order_item_id = $singleReturn['return_merchant_SKUs'][0]->order_item_id;
            $return_quantity = $singleReturn['return_merchant_SKUs'][0]->return_quantity;
            $principal = $singleReturn['return_merchant_SKUs'][0]->requested_refund_amount->principal;
            $tax = (float)$singleReturn['return_merchant_SKUs'][0]->requested_refund_amount->tax;
            $shipping_cost = $singleReturn['return_merchant_SKUs'][0]->requested_refund_amount->shipping_cost;
            $shipping_tax = (float)$singleReturn['return_merchant_SKUs'][0]->requested_refund_amount->shipping_tax;
            //print_r($shipping_tax);
            $jetDefineReturnId = $return->getMerchantReturnAuthorizationId();


            $completeData = [
                'json' => [
                    'merchant_order_id' => $merchantOrderId,
                    'alt_order_id' => $altOrderId,
                    'items' => [
                        [
                            "order_item_id" => $order_item_id,
                            "total_quantity_returned" => $return_quantity,
                            "order_return_refund_qty" => $return_quantity,
                            'refund_amount' => [
                                'principal' => $principal,
                                'tax' => $tax,
                                'shipping_cost' => $shipping_cost,
                                'shipping_tax' => $shipping_tax,
                            ],
                        ],
                    ],
                    "agree_to_return_charge" => true,
                ]
            ];

            try {
                $response = $this->jetApiCall->sendRequest("api/returns/" . $jetDefineReturnId . "/complete", $completeData, "PUT");
            } catch (NoSuchEntityException $e) {
                echo $e->getMessage();
            }
            $status = $response->getStatusCode(); // 204 complete order....

            if ($status === 204) {
                $return->setReturnStatus("completed by merchant");
                $this->messageManager->addSuccessMessage(__("Return has been completed to Jet Marketplace. $status"));
                return $resultRedirect->setPath('*/*/view', ['return_id' => $this->getRequest()->getParam('return_id')]);
            }
            if ($status === 400) {
                $error = substr($response->getReasonPhrase(), 165, 40);
                $this->messageManager->addErrorMessage(__("$error Status code: $status"));
                return $resultRedirect->setPath('*/*/view', ['return_id' => $this->getRequest()->getParam('return_id')]);
            }

            $this->messageManager->addErrorMessage(__("Return not successfully completed to Jet Marketplace."));
            return $resultRedirect->setPath('*/*/view', ['return_id' => $this->getRequest()->getParam('return_id')]);
        }
    }
}