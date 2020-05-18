<?php
declare(strict_types=1);

namespace Syedzaidi\JetIntegration\Ui\Component\Listing\Column;


use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /** Url path */
    const ORDER_URL_PATH_VIEW = 'jetintegration/orders/view';
    const ORDER_URL_PATH_DELETE = 'jetintegration/orders/delete';
    /**
     * @var array
     */
    private $data;
    /**
     * @var UrlBuilder
     */
    private $actionUrlBuilder;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var string
     */
    private $editUrl;
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Actions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::ORDER_URL_PATH_VIEW
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->data = $data;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['order_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['order_id' => $item['order_id']]),
                        'label' => __('Update'),
                        '__disableTmpl' => true,
                    ];
                }
                if (isset($item['identifier'])) {
                    $item[$name]['preview'] = [
                        'href' => $this->scopeUrlBuilder->getUrl(
                            $item['identifier'],
                            isset($item['_first_store_id']) ? $item['_first_store_id'] : null,
                            isset($item['store_code']) ? $item['store_code'] : null
                        ),
                        'label' => __('View'),
                        '__disableTmpl' => true,
                    ];
                }
            }
        }

        return $dataSource;
    }

    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}