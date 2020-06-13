<?php


namespace Syedzaidi\JetIntegration\Ui\DataProvider\JetReturnForm;

use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Syedzaidi\JetIntegration\Model\ResourceModel\JetReturn\JetReturnCollectionModelFactory;

class DataProvider extends AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var array
     */
    protected $_loadedData;
    /**
     * @var JetReturnCollectionModelFactory
     */
    private $collectionModelFactory;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param JetReturnCollectionModelFactory $collectionModelFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        JetReturnCollectionModelFactory $collectionModelFactory,
        array $meta = [],
        array $data = []
    ){
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionModelFactory = $collectionModelFactory;
    }

    /**
     * @return array|mixed
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $collection = $this->collectionModelFactory->create();
        $items = $collection->getItems();
        foreach ($items as $item) {
            $this->_loadedData[$item->getReturnId()] = $item->getData();
        }
        return $this->_loadedData;
    }

    /**
     * @param Filter $filter
     * @return null
     */
    public function addFilter(Filter $filter)
    {
        return null;
    }
}