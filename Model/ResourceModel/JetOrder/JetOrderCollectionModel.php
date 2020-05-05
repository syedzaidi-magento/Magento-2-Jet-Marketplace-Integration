<?php

namespace Syedzaidi\JetIntegration\Model\ResourceModel\JetOrder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class JetOrderCollectionModel extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Syedzaidi\JetIntegration\Model\JetOrder', 'Syedzaidi\JetIntegration\Model\ResourceModel\JetOrderResourceModel');
    }
}