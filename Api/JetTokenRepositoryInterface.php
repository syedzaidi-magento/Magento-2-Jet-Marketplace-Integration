<?php

namespace Syedzaidi\JetIntegration\Api;

use Magento\Framework\Exception\NoSuchEntityException;

interface JetTokenRepositoryInterface
{
    /**
     * @param $id
     * @return Data\JetTokenInterface
     * @throws NoSuchEntityException
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