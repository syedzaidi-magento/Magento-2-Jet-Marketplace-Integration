<?php

namespace Syedzaidi\JetIntegration\Block\Adminhtml\Order\Edit;

use Magento\Cms\Block\Adminhtml\Page\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CancelButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getOrderId()) {
            $data = [
                'label' => __('Cancel Order'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Url to send delete requests to.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/cancel', ['order_id' => $this->getOrderId()]);
    }

    public function getOrderId()
    {
        try {
            return $this->context->getRequest()->getParam('order_id');
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }
}