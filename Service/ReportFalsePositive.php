<?php

namespace Tidycode\AIFraudDetection\Service;

use Tidycode\AIFraudDetection\Api\ReportFalsePositiveInterface;
use Magento\Sales\Model\Order;
use Tidycode\AIClient\FraudDetectionFactory;
use Tidycode\AIFraudDetection\Helper\ModuleConfig;
use Tidycode\AIFraudDetection\Model\InitClient;
use Tidycode\AIFraudDetection\Helper\GetDataFromOrder;
use Exception;

class ReportFalsePositive extends InitClient implements ReportFalsePositiveInterface
{
    protected GetDataFromOrder $dataFromOrder;

    /**
     * @param FraudDetectionFactory $fraudDetection
     * @param ModuleConfig $moduleConfig
     * @param GetDataFromOrder $dataFromOrder
     */
    public function __construct(
        FraudDetectionFactory $fraudDetection,
        ModuleConfig $moduleConfig,
        GetDataFromOrder $dataFromOrder
    ) {
        $this->dataFromOrder = $dataFromOrder;

        parent::__construct($fraudDetection, $moduleConfig);
    }

    /**
     * @param Order $order
     * @return void
     * @throws Exception
     */
    public function execute(Order $order)
    {
        if (!$this->moduleConfig->isEnabled($order->getStoreId())) {
            return;
        }

        $this->initClient();

        $data = $this->dataFromOrder->execute($order);
        $this->client->reportFalsePositive($data);
    }
}
