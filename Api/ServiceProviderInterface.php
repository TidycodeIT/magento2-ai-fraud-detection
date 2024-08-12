<?php

namespace Tidycode\AIFraudDetection\Api;

interface ServiceProviderInterface
{
    const FRAUD_MANAGEMENT_FULLY_MANAGED = 'fully_managed';
    const FRAUD_MANAGEMENT_RISK_LEVEL = 'risk_level';
    const PREDICT_API_RESPONSE_RISK_LEVEL = 'risk_level';
    const PREDICT_API_RESPONSE_IS_FRAUD_FOR_TOOL = 'is_fraud_for_tool';
    const PAYMENT_MAGENTO_COLUMN = 'magento_payment_method';
    const PAYMENT_TOOL_COLUMN = 'tool_payment_code';
    const ORDER_ANTI_FRAUD_CHECKED = 'anti_fraud_checked';
    const FALSE_POSITIVE_AND_NEGATIVE_CONTROLLER = 'antifraudtool/error/report';
    const CONTROLLER_STATUS = 'status';
    const CONTROLLER_ORDER_ID = 'order_id';
}
