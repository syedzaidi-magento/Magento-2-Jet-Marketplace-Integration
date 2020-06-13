<?php
declare(strict_types=1);

namespace Syedzaidi\JetIntegration\Cron;

use Psr\Log\LoggerInterface;
use Syedzaidi\JetIntegration\Block\Orders;
use Syedzaidi\JetIntegration\Block\Products;
use Syedzaidi\JetIntegration\Helper\JetApiCall;

class JetCron
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Products
     */
    private $products;
    /**
     * @var Orders
     */
    private $orders;
    /**
     * @var JetApiCall
     */
    private $jetApiCall;

    /**
     * JetCron constructor.
     * @param LoggerInterface $logger
     * @param JetApiCall $jetApiCall
     * @param Products $products
     * @param Orders $orders
     */
    public function __construct(
        LoggerInterface $logger,
        JetApiCall $jetApiCall,
        Products $products,
        Orders $orders
    ){
        $this->logger = $logger;
        $this->products = $products;
        $this->orders = $orders;
        $this->jetApiCall = $jetApiCall;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        if ($this->jetApiCall->moduleEnable()) {
            $this->products->sendPriceToJet();
            $this->products->sendInventoryToJet();
            $this->products->sendCatalogToJet();
            $this->logger->info('Jet Cron job every 12 hours');
        }
    }

    /**
     * @return void
     */
    public function orders() {
        if ($this->jetApiCall->moduleEnable()) {
            $this->orders->jetOrderList();
            $this->logger->info('Jet Cron job order updated...');
        }
    }

}