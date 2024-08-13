<?php

namespace Tidycode\AIFraudDetection\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View as Subject;
use Magento\Framework\View\LayoutInterface;
use Tidycode\AIFraudDetection\Helper\ModuleConfig;
use Magento\Sales\Model\Order;
use Tidycode\AIFraudDetection\Api\ServiceProviderInterface;

class FalsePositiveAndNegativeButton
{
    /**
     * @var ModuleConfig
     */
    protected ModuleConfig $moduleConfig;

    /**
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param Subject $subject
     * @param LayoutInterface $layout
     * @return LayoutInterface[]
     */
    public function beforeSetLayout(Subject $subject, LayoutInterface $layout): array
    {
        if (!$this->moduleConfig->isEnabled($subject->getOrder()->getStoreId())) {
            return [$layout];
        }
        $orderStatus = $subject->getOrder()->getStatus();
        $falsePositiveMsg = __('This order has been reported as probable fraud. Do you want to report an error to the anti-fraud tool and unblock it?');
        $falseNegativeMsg = __('No fraud was detected in this order. Do you want to report it to the anti-fraud tool and block it?');
        $falsePositiveBtn = __('It\'s NOT a fraud');
        $falseNegativeBtn = __('It IS a fraud');

        $popupMessage = $orderStatus == Order::STATUS_FRAUD ? $falsePositiveMsg : $falseNegativeMsg;
        $label = $orderStatus == Order::STATUS_FRAUD ? $falsePositiveBtn : $falseNegativeBtn;

        $params = [
            ServiceProviderInterface::CONTROLLER_STATUS => $orderStatus,
            ServiceProviderInterface::CONTROLLER_ORDER_ID => $subject->getOrderId(),
        ];

        $url = $subject->getUrl(ServiceProviderInterface::FALSE_POSITIVE_AND_NEGATIVE_CONTROLLER, $params);

        $subject->addButton(
            'false_positive_button',
            [
                'label' => $label,
                'class' => 'antiFraudToolError primary',
                'onclick' => "confirmSetLocation('{$popupMessage}', '{$url}')"
            ]
        );

        return [$layout];
    }
}
