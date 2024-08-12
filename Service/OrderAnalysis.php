<?php

namespace Tidycode\AIFraudDetection\Service;

use Tidycode\AIFraudDetection\Model\InitClient;
use Tidycode\AIFraudDetection\Api\OrderAnalysisInterface;
use Tidycode\AIClient\FraudDetectionFactory;
use Tidycode\AIFraudDetection\Helper\ModuleConfig;
use Magento\Sales\Model\Order;
use Tidycode\AIFraudDetection\Helper\GetDataFromOrder;
use Tidycode\AIFraudDetection\Helper\CheckPredictApiResponse;
use Exception;

class OrderAnalysis extends InitClient implements OrderAnalysisInterface
{
    /**
     * @var GetDataFromOrder
     */
    protected GetDataFromOrder $dataFromOrder;
    /**
     * @var CheckPredictApiResponse
     */
    protected CheckPredictApiResponse $checkPredictApiResponse;

    /**
     * @param FraudDetectionFactory $fraudDetection
     * @param ModuleConfig $moduleConfig
     * @param GetDataFromOrder $dataFromOrder
     * @param CheckPredictApiResponse $checkPredictApiResponse
     */
    public function __construct(
        FraudDetectionFactory $fraudDetection,
        ModuleConfig $moduleConfig,
        GetDataFromOrder $dataFromOrder,
        CheckPredictApiResponse $checkPredictApiResponse
    ) {
        $this->dataFromOrder = $dataFromOrder;
        $this->checkPredictApiResponse = $checkPredictApiResponse;

        parent::__construct($fraudDetection, $moduleConfig);
    }

    /**
     * @param Order $order
     * @return bool
     * @throws Exception
     */
    public function isFraud(Order $order): bool
    {
        if (!$this->moduleConfig->isEnabled($order->getStoreId())) {
            return false;
        }

        $this->initClient();

        $data = $this->dataFromOrder->execute($order);
        $response = $this->client->detectFraud($data);

        return $this->checkPredictApiResponse->isFraud($response);
    }
}
