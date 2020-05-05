<?php

namespace Syedzaidi\JetIntegration\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

class JetOrder extends AbstractExtensibleModel
{
    public function _construct()
    {
        $this->_init('Syedzaidi\JetIntegration\Model\ResourceModel\JetOrderResourceModel');
    }
}