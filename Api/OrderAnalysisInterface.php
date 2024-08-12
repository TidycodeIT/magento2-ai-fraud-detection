<?php

namespace Tidycode\AIFraudDetection\Api;

use Magento\Sales\Model\Order;

interface OrderAnalysisInterface
{
    /**
     * @param Order $order
     * @return bool
     */
    public function isFraud(Order $order): bool;
}
