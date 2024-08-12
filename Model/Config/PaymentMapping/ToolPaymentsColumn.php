<?php

namespace Tidycode\AIFraudDetection\Model\Config\PaymentMapping;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Tidycode\AIClient\FraudDetection;
use Tidycode\AIClient\FraudDetectionFactory;
use Tidycode\AIFraudDetection\Helper\ModuleConfig;
use Magento\Framework\Message\ManagerInterface;
use Exception;

class ToolPaymentsColumn extends Select
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;
    /**
     * @var Config
     */
    protected Config $emailConfig;
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
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Config $emailConfig
     * @param FraudDetectionFactory $fraudDetection
     * @param ModuleConfig $moduleConfig
     * @param ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $emailConfig,
        FraudDetectionFactory $fraudDetection,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->emailConfig = $emailConfig;
        $this->fraudDetection = $fraudDetection;
        $this->moduleConfig = $moduleConfig;
        $this->messageManager = $messageManager;

        parent::__construct($context, $data);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @param $value
     * @return ToolPaymentsColumn
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    protected function getSourceOptions(): array
    {
        if (empty($this->moduleConfig->getApiKey())) {
            return [];
        }

        $this->initClient();

        try {
            $toolPaymentList = $this->client->paymentList();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage('The connection to the anti-fraud tool returned an error. Check the API key and try again.');
            $this->messageManager->addErrorMessage($e->getMessage());

            return [];
        }

        $options = [];

        foreach ($toolPaymentList as $identifier => $code) {
            $options[] = [
                'value' => $code,
                'label' => 'Code "' . $code . '" (' . $this->unslugifyIdentifier($identifier) . ')'
            ];
        }

        return $options;
    }

    /**
     * @return void
     */
    protected function initClient()
    {
        if (empty($this->client)) {
            $this->client = $this->fraudDetection->create(['apiKey' => $this->moduleConfig->getApiKey()]);
        }
    }

    /**
     * @param $identifier
     * @return string
     */
    protected function unslugifyIdentifier($identifier): string
    {
        return ucwords(str_replace('_', ' ', $identifier));
    }
}
