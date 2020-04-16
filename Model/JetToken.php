<?php


namespace Syedzaidi\JetIntegration\Model;


use Magento\Framework\Model\AbstractExtensibleModel;
use Syedzaidi\JetIntegration\Api\Data\JetTokenInterface;

class JetToken extends AbstractExtensibleModel implements JetTokenInterface
{
    const JET_TOKEN_ID = "token_id";
    const JET_TOKEN = "token";

    public function _construct()
    {
        $this->_init('Syedzaidi\JetIntegration\Model\ResourceModel\JetTokenResourceModel');
    }

    public function getTokenId()
    {
        return $this->getData(self::JET_TOKEN_ID);
    }

    public function setTokenId($id)
    {
        $this->setData(self::JET_TOKEN_ID, $id);
        return $this;
    }


    public function getToken()
    {
        return $this->getData(self::JET_TOKEN);
    }

    public function setToken($token)
    {
        $this->setData(self::JET_TOKEN, $token);
        return $this;
    }


}