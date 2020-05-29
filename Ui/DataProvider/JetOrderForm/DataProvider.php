<?php
declare(strict_types=1);

namespace Syedzaidi\JetIntegration\Ui\DataProvider\JetOrderForm;


use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Syedzaidi\JetIntegration\Model\ResourceModel\JetOrder\JetOrderCollectionModelFactory;

class DataProvider extends AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var array
     */
    protected $_loadedData;
    /**
     * @var JetOrderCollectionModelFactory
     */
    private $collectionModelFactory;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param JetOrderCollectionModelFactory $collectionModelFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        JetOrderCollectionModelFactory $collectionModelFactory,
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
        foreach ($items as $employee) {
            $this->_loadedData[$employee->getOrderId()] = $employee->getData();
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