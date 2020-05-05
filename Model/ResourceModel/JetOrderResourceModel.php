<?php

namespace Syedzaidi\JetIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class JetOrderResourceModel extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('jet_integration_order', 'order_id');
    }
}