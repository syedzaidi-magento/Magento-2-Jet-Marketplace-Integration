<?php


namespace Syedzaidi\JetIntegration\Block;


use Magento\Framework\View\Element\Template;

class Products extends Template
{
    public function getProducts()
    {
        return 'product list from Block.';
    }

}