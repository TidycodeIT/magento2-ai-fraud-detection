<?php

namespace Tidycode\AIFraudDetection\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Tidycode\AIFraudDetection\Helper\ModuleConfig;
use Tidycode\AIFraudDetection\Api\OrderAnalysisInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Exception;

class AnalyzeOrder extends Command
{
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var ModuleConfig
     */
    protected ModuleConfig $moduleConfig;
    /**
     * @var OrderAnalysisInterface
     */
    protected OrderAnalysisInterface $orderAnalysis;

    /**
     * @param State $state
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleConfig $moduleConfig
     * @param OrderAnalysisInterface $orderAnalysis
     * @param string|null $name
     * @throws LocalizedException
     */
    public function __construct(
        State $state,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleConfig $moduleConfig,
        OrderAnalysisInterface $orderAnalysis,
        string $name = null
    ) {
        $state->setAreaCode(Area::AREA_ADMINHTML);
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderAnalysis = $orderAnalysis;
        $this->moduleConfig = $moduleConfig;

        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('tidycode:fraud-detection:analize-order');
        $this->setDescription("It analyzes the given order and checks through Tidycode's artificial intelligence - based anti - fraud tool whether it is fraud or not.");
        $this->addArgument("order", InputArgument::REQUIRED, "Order id");

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderId = $input->getArgument("order");

        try {
            /** @var $order Order */
            $order = $this->orderRepository->get($orderId);
        } catch (InputException|NoSuchEntityException $e) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $orderId)
                ->create();
            $orders = $this->orderRepository->getList($searchCriteria)->getItems();
            /** @var $order Order */
            $order = !empty($orders) ? $orders[0] : null;
        }

        if (empty($order)) {
            throw new NoSuchEntityException(
                __("The entity that was requested doesn't exist. Verify the entity and try again.")
            );
        }

        if(!$this->moduleConfig->isEnabled($order->getStoreId())){
            throw new Exception(
                __('The module is not enabled for this website. Check the configurations and try again.')
            );
        }

        if ($this->orderAnalysis->isFraud($order)) {
            $output->writeln('The order IS probably a fraud!');
        } else {
            $output->writeln('The order is probably NOT a fraud');
        }

        return 0;
    }
}
