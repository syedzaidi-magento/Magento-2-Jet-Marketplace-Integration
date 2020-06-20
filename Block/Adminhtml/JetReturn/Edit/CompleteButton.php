<?php


namespace Syedzaidi\JetIntegration\Block\Adminhtml\JetReturn\Edit;

use Magento\Cms\Block\Adminhtml\Page\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;


class CompleteButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData()
    {
        $data = [];
        if ($this->getReturnId()) {
            $data = [
                'label' => __('Complete Return'),
                'class' => 'save primary',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getReturnUrl() . '\', {"data": {}})',
                'sort_order' => 80,
            ];
        }
        return $data;
    }

    /**
     * Url to send delete requests to.
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getUrl('*/*/complete', ['return_id' => $this->getReturnId()]);
    }

    public function getReturnId()
    {
        try {
            return $this->context->getRequest()->getParam('return_id');
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }
}