<?php

namespace Tidycode\AIFraudDetection\Helper;

use Magento\Sales\Model\Order;

class ControllerHelper
{
    /**
     * @param Order $order
     * @return string
     */
    public function previousOrderStatus(Order $order): string
    {
        $statues = $order->getAllStatusHistory();

        $previousStatus = Order::STATE_PROCESSING;

        $cnt = 0;
        foreach ($statues as $status) {
            if ($cnt == 1) {
                $previousStatus = $status->getData('status');
                break;
            }
            $cnt++;
        }

        return $previousStatus;
    }
}
