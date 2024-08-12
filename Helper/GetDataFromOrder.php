<?php

namespace Tidycode\AIFraudDetection\Helper;

use Magento\Sales\Model\Order;

class GetDataFromOrder
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
     * @param Order $order
     * @return array
     */
    public function execute(Order $order): array
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        return [
            'email' => $order->getCustomerEmail(),
            'firstname' => $order->getCustomerFirstname(),
            'lastname' => $order->getCustomerLastname(),
            'ips' => $order->getRemoteIp(),
            'billing_address' => [
                'address_type' => 'billing',
                'city' => $billingAddress->getCity(),
                'country_id' => $billingAddress->getCountryId(),
                'email' => $billingAddress->getEmail(),
                'firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'middlename' => !empty($billingAddress->getMiddlename()) ? $billingAddress->getMiddlename() : '',
                'postcode' => $billingAddress->getPostcode(),
                'region_id' => $billingAddress->getRegionCode(),
                'street' => is_array($billingAddress->getStreet()) ? implode(' ', $billingAddress->getStreet()) : $billingAddress->getStreet(),
                'telephone' => $billingAddress->getTelephone(),
            ],
            'shipping_address' => [
                'address_type' => 'shipping',
                'city' => $shippingAddress->getCity(),
                'country_id' => $shippingAddress->getCountryId(),
                'email' => $shippingAddress->getEmail(),
                'firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'middlename' => !empty($shippingAddress->getMiddlename()) ? $shippingAddress->getMiddlename() : '',
                'postcode' => $shippingAddress->getPostcode(),
                'region_id' => $shippingAddress->getRegionCode(),
                'street' => is_array($shippingAddress->getStreet()) ? implode(' ', $shippingAddress->getStreet()) : $shippingAddress->getStreet(),
                'telephone' => $shippingAddress->getTelephone(),
            ],
            'currency' => $order->getOrderCurrencyCode(),
            'payment_method' => $this->moduleConfig->paymentMapping($order->getPayment()->getMethod()),
            'total_paid' => $order->getRemoteIp(),
            'shipping_cost' => $order->getShippingAmount(),
            'item_count' => count($order->getAllItems()),
            'date' => $order->getCreatedAt()
        ];
    }
}
