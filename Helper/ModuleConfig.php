<?php

namespace Tidycode\AIFraudDetection\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Tidycode\AIFraudDetection\Api\ServiceProviderInterface;

class ModuleConfig extends AbstractHelper
{
    // SECTION
    const SECTION = 'fraud_detection/';
    // GROUPS
    const GENERAL_GROUP = self::SECTION . 'general/';
    const ORDER_ANALYSIS_GROUP = self::SECTION . 'order_analysis/';
    // FIELDS
    const ENABLE = self::GENERAL_GROUP . 'enable';
    const API_KEY = self::GENERAL_GROUP . 'api_key';
    const PAYMENT_MAPPING = self::GENERAL_GROUP . 'payment_mapping';
    const THRESHOLD_ID = self::GENERAL_GROUP . 'threshold_id';
    const FRAUD_MANAGEMENT_MODE = self::ORDER_ANALYSIS_GROUP . 'fraud_management_mode';
    const RISK_LEVEL = self::ORDER_ANALYSIS_GROUP . 'risk_level';

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled(int $storeId = 0): bool
    {
        return $this->scopeConfig->isSetFlag(self::ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::API_KEY);
    }

    /**
     * @param $magentoPaymentCode
     * @return string
     */
    public function paymentMapping($magentoPaymentCode): string
    {
        $paymentMapping = $this->scopeConfig->getValue(self::PAYMENT_MAPPING);

        if (!is_array($paymentMapping)) {
            $paymentMapping = $this->serializer->unserialize($paymentMapping);
        }

        foreach ($paymentMapping as $payment) {
            if ($magentoPaymentCode == $payment[ServiceProviderInterface::PAYMENT_MAGENTO_COLUMN]) {
                return (string)$payment[ServiceProviderInterface::PAYMENT_TOOL_COLUMN];
            }
        }

        return '';
    }

    /**
     * @return int
     */
    public function getThresholdId(): int
    {
        return (int)$this->scopeConfig->getValue(self::THRESHOLD_ID);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getFraudManagementMode(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(self::FRAUD_MANAGEMENT_MODE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return int
     */
    public function getRiskLevel(int $storeId = 0): int
    {
        return (int)$this->scopeConfig->getValue(self::RISK_LEVEL, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
