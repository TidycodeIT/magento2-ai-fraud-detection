<?php

namespace Tidycode\AIFraudDetection\Model\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Tidycode\AIFraudDetection\Model\Config\PaymentMapping\MagentoPaymentsColumn;
use Tidycode\AIFraudDetection\Api\ServiceProviderInterface;

class PaymentMapping extends AbstractFieldArray
{
    /**
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(ServiceProviderInterface::PAYMENT_MAGENTO_COLUMN, [
            'label' => __('Magento payment method'),
            'renderer' => $this->magentoPaymentsRenderer(),
            'class' => 'required'
        ]);

        $this->addColumn(ServiceProviderInterface::PAYMENT_TOOL_COLUMN, [
            'label' => __('Payment code in eurocom'),
            'renderer' => $this->toolPaymentsRenderer(),
            'class' => 'required'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @return BlockInterface|MagentoPaymentsColumn|(MagentoPaymentsColumn&BlockInterface)
     * @throws LocalizedException
     */
    protected function magentoPaymentsRenderer()
    {
        return $this->getLayout()->createBlock(PaymentMapping\MagentoPaymentsColumn::class, '',
            ['data' => ['is_render_to_js_template' => true]]);
    }

    /**
     * @return BlockInterface|PaymentMapping\ToolPaymentsColumn|(PaymentMapping\ToolPaymentsColumn&BlockInterface)
     * @throws LocalizedException
     */
    protected function toolPaymentsRenderer()
    {
        return $this->getLayout()->createBlock(PaymentMapping\ToolPaymentsColumn::class, '',
            ['data' => ['is_render_to_js_template' => true]]);
    }

    /**
     * @param DataObject $row
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $magentoTemplateId = $row->getMagentoPaymentMethod();
        if ($magentoTemplateId !== null) {
            $options['option_' . $this->magentoPaymentsRenderer()->calcOptionHash($magentoTemplateId)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
