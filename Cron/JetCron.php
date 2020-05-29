<?php
declare(strict_types=1);

namespace Syedzaidi\JetIntegration\Cron;

use Psr\Log\LoggerInterface;
use Syedzaidi\JetIntegration\Block\Orders;
use Syedzaidi\JetIntegration\Block\Products;

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
     * JetCron constructor.
     * @param LoggerInterface $logger
     * @param Products $products
     * @param Orders $orders
     */
    public function __construct(
        LoggerInterface $logger,
        Products $products,
        Orders $orders
    ){
        $this->logger = $logger;
        $this->products = $products;
        $this->orders = $orders;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute() {
        $this->products->sendPriceToJet();
        $this->products->sendInventoryToJet();
        $this->products->sendCatalogToJet();
        $this->logger->info('Jet Cron every day at 04:00 AM');
    }

    public function orders() {
        $this->orders->jetOrderList();
        $this->logger->info('Jet Cron order update every minute...');
    }

}