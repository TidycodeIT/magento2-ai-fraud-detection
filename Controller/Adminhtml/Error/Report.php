<?php

namespace Tidycode\AIFraudDetection\Controller\Adminhtml\Error;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Tidycode\AIFraudDetection\Api\ServiceProviderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tidycode\AIFraudDetection\Api\ReportFalsePositiveInterface;
use Tidycode\AIFraudDetection\Api\ReportFalseNegativeInterface;
use Magento\Framework\Controller\Result\Redirect;
use Tidycode\AIFraudDetection\Helper\ControllerHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;

class Report extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Tidycode_AIFraudDetection::report_error';

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
     * @param OrderRepositoryInterface $orderRepository
     * @param ReportFalsePositiveInterface $reportFalsePositive
     * @param ReportFalseNegativeInterface $reportFalseNegative
     * @param ControllerHelper $controllerHelper
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        ReportFalsePositiveInterface $reportFalsePositive,
        ReportFalseNegativeInterface $reportFalseNegative,
        ControllerHelper $controllerHelper
    ) {
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
        $orderStatus = $this->_request->getParam(ServiceProviderInterface::CONTROLLER_STATUS);
        $orderId = $this->_request->getParam(ServiceProviderInterface::CONTROLLER_ORDER_ID);

        try {
            /** @var $order Order */
            $order = $this->orderRepository->get($orderId);
        } catch (InputException|NoSuchEntityException $e) {
            return $this->returnRedirect($orderId, $e->getMessage(), false);
        }

        if ($orderStatus == Order::STATUS_FRAUD) {
            try {
                $this->reportFalsePositive->execute($order);
                $order->addCommentToStatusHistory('', $this->controllerHelper->previousOrderStatus($order));
            } catch (Exception $e) {
                return $this->returnRedirect($orderId, $e->getMessage(), false);
            }
        } else {
            try {
                $this->reportFalseNegative->execute($order);
                $order->addCommentToStatusHistory('', Order::STATUS_FRAUD);
            } catch (Exception $e) {
                return $this->returnRedirect($orderId, $e->getMessage(), false);
            }
        }

        $this->orderRepository->save($order);

        return $this->returnRedirect($orderId, __('Report sent!'));
    }

    /**
     * @param $orderId
     * @param $message
     * @param bool $success
     * @return Redirect
     */
    protected function returnRedirect($orderId, $message, bool $success = true): Redirect
    {
        if ($success) {
            $this->messageManager->addSuccessMessage($message);
        } else {
            $this->messageManager->addErrorMessage($message);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);

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
