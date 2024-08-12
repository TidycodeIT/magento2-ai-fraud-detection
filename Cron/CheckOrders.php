<?php

namespace Tidycode\AIFraudDetection\Cron;

use Tidycode\AIFraudDetection\Helper\ModuleConfig;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Tidycode\AIFraudDetection\Api\ServiceProviderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Tidycode\AIFraudDetection\Api\OrderAnalysisInterface;

class CheckOrders
{
    /**
     * @var ModuleConfig
     */
    protected ModuleConfig $moduleConfig;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;
    /**
     * @var OrderAnalysisInterface
     */
    protected OrderAnalysisInterface $orderAnalysis;

    /**
     * @param ModuleConfig $moduleConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderAnalysisInterface $orderAnalysis
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        OrderAnalysisInterface $orderAnalysis
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderAnalysis = $orderAnalysis;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $thresholdOrderId = $this->moduleConfig->getThresholdId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ServiceProviderInterface::ORDER_ANTI_FRAUD_CHECKED, '1', 'neq')
            ->addFilter(OrderInterface::ENTITY_ID, $thresholdOrderId, 'gt')
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        /** @var $order Order */
        foreach ($orders as $order) {
            if (!$this->moduleConfig->isEnabled($order->getStoreId())) {
                continue;
            }

            if ($this->orderAnalysis->isFraud($order)) {
                $order->addCommentToStatusHistory('', Order::STATUS_FRAUD);
            }

            $order->setData(ServiceProviderInterface::ORDER_ANTI_FRAUD_CHECKED, 1);

            $this->orderRepository->save($order);
        }
    }
}
