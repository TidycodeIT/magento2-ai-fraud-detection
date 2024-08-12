<?php

namespace Tidycode\AIFraudDetection\Controller\Adminhtml\Massaction;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Tidycode\AIFraudDetection\Helper\ModuleConfig;
use Tidycode\AIFraudDetection\Api\ReportFalsePositiveInterface;
use Tidycode\AIFraudDetection\Api\ReportFalseNegativeInterface;
use Tidycode\AIFraudDetection\Helper\ControllerHelper;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;

class Report extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Tidycode_AIFraudDetection::report_error_actions';

    /**
     * @var ModuleConfig
     */
    protected ModuleConfig $moduleConfig;
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;
    /**
     * @var ReportFalsePositiveInterface
     */
    protected ReportFalsePositiveInterface $reportFalsePositive;
    /**
     * @var ReportFalseNegativeInterface
     */
    protected ReportFalseNegativeInterface $reportFalseNegative;
    /**
     * @var ControllerHelper
     */
    protected ControllerHelper $controllerHelper;

    /**
     * @param Context $context
     * @param ModuleConfig $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param ReportFalsePositiveInterface $reportFalsePositive
     * @param ReportFalseNegativeInterface $reportFalseNegative
     * @param ControllerHelper $controllerHelper
     */
    public function __construct(
        Context $context,
        ModuleConfig $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        ReportFalsePositiveInterface $reportFalsePositive,
        ReportFalseNegativeInterface $reportFalseNegative,
        ControllerHelper $controllerHelper
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->reportFalsePositive = $reportFalsePositive;
        $this->reportFalseNegative = $reportFalseNegative;
        $this->controllerHelper = $controllerHelper;

        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $orderIds = $this->_request->getParam('selected');
        $fraud = (bool)$this->_request->getParam('fraud');

        foreach ($orderIds as $orderId) {
            try {
                /** @var $order Order */
                $order = $this->orderRepository->get($orderId);
            } catch (InputException|NoSuchEntityException $e) {
                continue;
            }

            $status = $order->getStatus();
            $incrementId = $order->getIncrementId();
            $newStatus = Order::STATUS_FRAUD;

            // If module is not enable
            if (!$this->moduleConfig->isEnabled($orderId)) {
                $noActiveMessage = 'The module is not enabled for the website related to the order ' . $incrementId . ', check the configuration and try again';
                $this->messageManager->addErrorMessage(__($noActiveMessage));
                continue;
            }

            // Skip order flagged as fraud but already forde
            if ($fraud && $status == Order::STATUS_FRAUD) {
                $message = 'The order ' . $incrementId . ' was already flagged as fraud, report not sent';
                $this->messageManager->addWarningMessage(__($message));
                continue;
            }

            // Skip order reported as NON-fraud and already NON-fraud
            if (!$fraud && $status != Order::STATUS_FRAUD) {
                $message = 'The order ' . $incrementId . ' was already flagged as NOT fraud, report not sent';
                $this->messageManager->addWarningMessage(__($message));
                continue;
            }

            // Processing order as fraud but NOT marked as fraud
            if ($fraud && $status != Order::STATUS_FRAUD) {
                try {
                    $this->reportFalseNegative->execute($order);
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                    continue;
                }
            }

            // Processing order NOT fraud but marked as fraud
            if (!$fraud && $status == Order::STATUS_FRAUD) {
                try {
                    $this->reportFalsePositive->execute($order);
                    $newStatus = $this->controllerHelper->previousOrderStatus($order);
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                    continue;
                }
            }

            $this->setOrderMessage($order, $newStatus);
            $this->orderRepository->save($order);
        }

        return $this->returnRedirect();
    }

    /**
     * @param Order $order
     * @param $newStatus
     * @return void
     */
    protected function setOrderMessage(Order $order, $newStatus = Order::STATUS_FRAUD)
    {
        $message = 'Report for order ' . $order->getIncrementId() . ' sent!';
        $this->messageManager->addSuccessMessage(__($message));

        $order->addCommentToStatusHistory('', $newStatus);
    }

    /**
     * @return Redirect
     */
    protected function returnRedirect(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/index');
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
