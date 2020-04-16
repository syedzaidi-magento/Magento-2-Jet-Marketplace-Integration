<?php


namespace Syedzaidi\JetIntegration\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Syedzaidi\JetIntegration\Api\JetTokenRepositoryInterface;
use Syedzaidi\JetIntegration\Api\Magento;
use Syedzaidi\JetIntegration\Api\Syedzaidi;
use Syedzaidi\JetIntegration\Model\ResourceModel\JetToken\JetTokenCollectionModelFactory;

class JetTokenRepository implements JetTokenRepositoryInterface
{

    /**
     * @var JetTokenCollectionModelFactory
     */
    private $collectionModelFactory;

    /**
     * @var JetTokenFactory
     */
    private $jetTokenFactory;

    /**
     * JetTokenRepository constructor.
     * @param JetTokenCollectionModelFactory $collectionModelFactory
     * @param JetTokenFactory $jetTokenFactory
     */
    public function __construct(
        JetTokenCollectionModelFactory $collectionModelFactory,
        JetTokenFactory $jetTokenFactory
    ){

        $this->collectionModelFactory = $collectionModelFactory;
        $this->jetTokenFactory = $jetTokenFactory;
    }

    public function getTokenById($id)
    {
        $jet_token = $this->jetTokenFactory->create();
        $jet_token->getResource()->load($jet_token, $id);
        if ($jet_token->getTokenId()) {
            return $jet_token->getToken();
        }
        throw new NoSuchEntityException(__('Unable to find token with ID "%1"', $id));

    }

    public function setNewToken($token)
    {
        $new_token = $this->jetTokenFactory->create();
        $new_token->getResource()->load($new_token, 1);
        $new_token->setToken($token);
        $new_token->save();
    }

    public function getTokenList()
    {
        $items = $this->collectionModelFactory->create();
        return $items->getData();
    }

}