<?php


namespace Syedzaidi\JetIntegration\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class JetProductResourceModel extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('jet_integration_product', 'product_id');
    }
}