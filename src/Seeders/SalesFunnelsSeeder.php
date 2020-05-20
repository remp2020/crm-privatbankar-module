<?php

namespace Crm\PrivatbankarModule\Seeders;

use Crm\ApplicationModule\Seeders\ISeeder;
use Crm\PaymentsModule\Repository\PaymentGatewaysRepository;
use Crm\PrivatbankarModule\Gateways\Privatbankar;
use Crm\PrivatbankarModule\Gateways\PrivatbankarRecurrent;
use Crm\SalesFunnelModule\Repository\SalesFunnelsPaymentGatewaysRepository;
use Crm\SalesFunnelModule\Repository\SalesFunnelsRepository;
use Crm\SalesFunnelModule\Repository\SalesFunnelsSubscriptionTypesRepository;
use Crm\SubscriptionsModule\Repository\SubscriptionTypesRepository;
use Symfony\Component\Console\Output\OutputInterface;

class SalesFunnelsSeeder implements ISeeder
{
    private $salesFunnelsRepository;

    private $paymentGatewaysRepository;

    private $salesFunnelsPaymentGatewaysRepository;

    private $salesFunnelsSubscriptionTypesRepository;

    private $subscriptionTypesRepository;

    public function __construct(
        SalesFunnelsRepository $salesFunnelsRepository,
        PaymentGatewaysRepository $paymentGatewaysRepository,
        SalesFunnelsPaymentGatewaysRepository $salesFunnelsPaymentGatewaysRepository,
        SalesFunnelsSubscriptionTypesRepository $salesFunnelsSubscriptionTypesRepository,
        SubscriptionTypesRepository $subscriptionTypesRepository
    ) {
        $this->salesFunnelsRepository = $salesFunnelsRepository;
        $this->paymentGatewaysRepository = $paymentGatewaysRepository;
        $this->salesFunnelsPaymentGatewaysRepository = $salesFunnelsPaymentGatewaysRepository;
        $this->salesFunnelsSubscriptionTypesRepository = $salesFunnelsSubscriptionTypesRepository;
        $this->subscriptionTypesRepository = $subscriptionTypesRepository;
    }

    public function seed(OutputInterface $output)
    {
        foreach (glob(__DIR__ . '/sales_funnels/*.twig') as $filename) {
            $info = pathinfo($filename);
            $key = $info['filename'];

            $funnel = $this->salesFunnelsRepository->findByUrlKey($key);
            if (!$funnel) {
                $funnel = $this->salesFunnelsRepository->add($key, $key, file_get_contents($filename));
                $output->writeln('  <comment>* funnel <info>' . $key . '</info> created</comment>');
            } else {
                $output->writeln('  * funnel <info>' . $key . '</info> exists');
            }

            $this->salesFunnelsSubscriptionTypesRepository->add($funnel, $this->subscriptionTypesRepository->findByCode('sample')); // seeded by salesfunnels module
            $this->salesFunnelsPaymentGatewaysRepository->add($funnel, $this->paymentGatewaysRepository->findByCode(Privatbankar::GATEWAY_CODE));
            $this->salesFunnelsPaymentGatewaysRepository->add($funnel, $this->paymentGatewaysRepository->findByCode(PrivatbankarRecurrent::GATEWAY_CODE));
        }
    }
}
