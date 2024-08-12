<?php

namespace Tidycode\AIFraudDetection\Helper;

use Tidycode\AIFraudDetection\Api\ServiceProviderInterface;

class CheckPredictApiResponse
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
     * @param object $predictApiResponse
     * @param int $storeId
     * @return bool
     */
    public function isFraud(object $predictApiResponse, int $storeId = 0): bool
    {
        if (!$this->validatePredictApiResponse($predictApiResponse)) {
            return false;
        }

        $isFraudForTool = ServiceProviderInterface::PREDICT_API_RESPONSE_IS_FRAUD_FOR_TOOL;
        $riskLevel = ServiceProviderInterface::PREDICT_API_RESPONSE_RISK_LEVEL;

        switch ($this->moduleConfig->getFraudManagementMode($storeId)) {
            case ServiceProviderInterface::FRAUD_MANAGEMENT_FULLY_MANAGED:
                return (bool)$predictApiResponse->$isFraudForTool;

            case ServiceProviderInterface::FRAUD_MANAGEMENT_RISK_LEVEL:
                return $this->moduleConfig->getRiskLevel() < (int)$predictApiResponse->$riskLevel;

            default:
                return false;
        }
    }

    /**
     * @param object $predictApiResponse
     * @return bool
     */
    protected function validatePredictApiResponse(object $predictApiResponse): bool
    {
        if (empty($predictApiResponse)) {
            return false;
        }

        $isFraudForTool = ServiceProviderInterface::PREDICT_API_RESPONSE_IS_FRAUD_FOR_TOOL;
        $riskLevel = ServiceProviderInterface::PREDICT_API_RESPONSE_RISK_LEVEL;

        if (empty($predictApiResponse->$isFraudForTool) && empty($predictApiResponse->$riskLevel)) {
            return false;
        }

        return true;
    }
}
