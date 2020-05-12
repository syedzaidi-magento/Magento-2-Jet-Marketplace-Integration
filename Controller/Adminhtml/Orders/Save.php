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

        $jet_order->setTrackingNumber($data['tracking_number']);
        $jet_order->save();
        $this->messageManager->addSuccessMessage(__('Tracking number updated.'));
        return $resultRedirect->setPath('*/*/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }

}