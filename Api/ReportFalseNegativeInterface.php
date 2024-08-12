<?php

namespace Tidycode\AIFraudDetection\Api;

use Magento\Sales\Model\Order;
use Exception;

interface ReportFalseNegativeInterface
{
    /**
     * @param Order $order
     * @return void
     * @throws Exception
     */
    public function execute(Order $order);
}
