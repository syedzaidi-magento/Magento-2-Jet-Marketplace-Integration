<?php


namespace Syedzaidi\JetIntegration\Model\ResourceModel\JetToken;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class JetTokenCollectionModel extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Syedzaidi\JetIntegration\Model\JetToken', 'Syedzaidi\JetIntegration\Model\ResourceModel\JetTokenResourceModel');
    }
}