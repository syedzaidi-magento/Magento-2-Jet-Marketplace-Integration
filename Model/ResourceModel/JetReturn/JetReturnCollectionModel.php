<?php


namespace Syedzaidi\JetIntegration\Model\ResourceModel\JetReturn;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class JetReturnCollectionModel extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Syedzaidi\JetIntegration\Model\JetReturn', 'Syedzaidi\JetIntegration\Model\ResourceModel\JetReturnResourceModel');
    }
}