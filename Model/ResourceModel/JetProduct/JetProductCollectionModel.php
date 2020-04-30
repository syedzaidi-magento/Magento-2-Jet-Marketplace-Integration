<?php


namespace Syedzaidi\JetIntegration\Model\ResourceModel\JetProduct;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class JetProductCollectionModel extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Syedzaidi\JetIntegration\Model\JetProduct', 'Syedzaidi\JetIntegration\Model\ResourceModel\JetProductResourceModel');
    }
}