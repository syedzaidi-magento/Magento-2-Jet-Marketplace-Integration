<?php


namespace Syedzaidi\JetIntegration\Api;


use Magento\Framework\Api\SearchCriteriaInterface;

interface JetTokenRepositoryInterface
{
    /**
     * @param $id
     * @return Syedzaidi\JetIntegration\Api\Data\JetTokenInterface
     * @throws Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTokenById($id);

    /**
     * @return mixed
     */
    public function getTokenList();

    /**
     * @param $token
     * @return mixed
     */
    public function setNewToken($token);
}