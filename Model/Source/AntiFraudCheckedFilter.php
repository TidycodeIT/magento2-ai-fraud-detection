<?php

namespace Tidycode\AIFraudDetection\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AntiFraudCheckedFilter implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => "1", 'label' => __('Yes')],
            ['value' => "0", 'label' => __('No')]
        ];
    }
}
