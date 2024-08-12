<?php

namespace Tidycode\AIFraudDetection\Model\Config\PaymentMapping;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Payment\Helper\Data as PaymentHelper;

class MagentoPaymentsColumn extends Select
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
     * @var PaymentHelper
     */
    protected PaymentHelper $paymentHelper;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Config $emailConfig
     * @param PaymentHelper $paymentHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $emailConfig,
        PaymentHelper $paymentHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->emailConfig = $emailConfig;
        $this->paymentHelper = $paymentHelper;

        parent::__construct($context, $data);
    }

    /**
     * @param $value
     * @return MagentoPaymentsColumn
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @param $value
     * @return MagentoPaymentsColumn
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    protected function getSourceOptions(): array
    {
        $options = [];

        foreach ($this->paymentHelper->getPaymentMethodList() as $code => $title) {
            $options[] = [
                'value' => $code,
                'label' => $title
            ];
        }

        return $options;
    }
}
