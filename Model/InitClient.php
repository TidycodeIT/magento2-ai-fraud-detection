<?php

namespace Tidycode\AIFraudDetection\Model;

use Tidycode\AIFraudDetection\Api\InitClientInterface;
use Tidycode\AIClient\FraudDetection;
use Tidycode\AIClient\FraudDetectionFactory;
use Tidycode\AIFraudDetection\Helper\ModuleConfig;

class InitClient implements InitClientInterface
{
    /**
     * @var FraudDetection|null
     */
    protected ?FraudDetection $client = null;
    /**
     * @var FraudDetectionFactory
     */
    protected FraudDetectionFactory $fraudDetection;
    /**
     * @var ModuleConfig
     */
    protected ModuleConfig $moduleConfig;

    /**
     * @param FraudDetectionFactory $fraudDetection
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        FraudDetectionFactory $fraudDetection,
        ModuleConfig $moduleConfig
    ) {
        $this->fraudDetection = $fraudDetection;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @return void
     */
    public function initClient()
    {
        if (empty($this->client)) {
            $this->client = $this->fraudDetection->create(['apiKey' => $this->moduleConfig->getApiKey()]);
        }
    }
}
