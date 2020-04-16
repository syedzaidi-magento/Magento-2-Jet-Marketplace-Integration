<?php


namespace Syedzaidi\JetIntegration\Api\Data;


use Magento\Framework\Api\ExtensibleDataInterface;

interface JetTokenInterface extends ExtensibleDataInterface
{
    /**
     * @return int
     */
    public function getTokenId();

    /**
     * @param $id
     * @return void
     */
    public function setTokenId($id);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param $token
     * @return void
     */
    public function setToken($token);
}