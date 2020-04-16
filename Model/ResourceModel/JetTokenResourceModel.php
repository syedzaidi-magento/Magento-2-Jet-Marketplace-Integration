<?php


namespace Syedzaidi\JetIntegration\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class JetTokenResourceModel
 * @package Syedzaidi\JetIntegration\Model\ResourceModel
 */
class JetTokenResourceModel extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('jet_marketplace_integration_token', 'token_id');
    }
}