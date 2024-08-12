<?php

namespace Tidycode\AIFraudDetection\Api;

use Magento\Sales\Model\Order;
use Exception;

interface ReportFalsePositiveInterface
{
    /**
     * @param Order $order
     * @return void
     * @throws Exception
     */
    public function execute(Order $order);
}
