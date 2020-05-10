<?php


namespace Syedzaidi\JetIntegration\Controller\Adminhtml\Orders;


use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Syedzaidi\JetIntegration\Model\JetOrderFactory;


class Save extends Action implements HttpPostActionInterface
{
    /**
     * @var JetOrderFactory
     */
    private $jetOrderFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param JetOrderFactory $jetOrderFactory
     */
    public function __construct(Action\Context $context, JetOrderFactory $jetOrderFactory)
    {
        parent::__construct($context);
        $this->jetOrderFactory = $jetOrderFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $jet_order = $this->jetOrderFactory->create();
        $jet_order->load($data['alt_order_id'], "alt_order_id");
        $jet_order->setTrackingNumber($data['tracking_number']);
        $jet_order->save();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->messageManager->addSuccessMessage(__('Tracking number updated.'));
        return $resultRedirect->setPath('*/*/');


    }

}