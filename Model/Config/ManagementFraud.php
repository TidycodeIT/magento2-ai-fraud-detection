<?php

namespace Tidycode\AIFraudDetection\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Tidycode\AIFraudDetection\Api\ServiceProviderInterface;

class ManagementFraud implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => ServiceProviderInterface::FRAUD_MANAGEMENT_FULLY_MANAGED,
                'label' => __('Fully managed')
            ],
            [
                'value' => ServiceProviderInterface::FRAUD_MANAGEMENT_RISK_LEVEL,
                'label' => __('Risk level')
            ]
        ];
    }
}
