<?php

namespace Crm\PrivatbankarModule\Seeders;

use Crm\ApplicationModule\Seeders\ISeeder;
use Crm\PaymentsModule\Repository\PaymentGatewaysRepository;
use Crm\PrivatbankarModule\Gateways\Privatbankar;
use Crm\PrivatbankarModule\Gateways\PrivatbankarRecurrent;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentGatewaysSeeder implements ISeeder
{
    private $paymentGatewaysRepository;
    
    public function __construct(PaymentGatewaysRepository $paymentGatewaysRepository)
    {
        $this->paymentGatewaysRepository = $paymentGatewaysRepository;
    }

    public function seed(OutputInterface $output)
    {
        $code = Privatbankar::GATEWAY_CODE;
        if (!$this->paymentGatewaysRepository->exists($code)) {
            $this->paymentGatewaysRepository->add(
                'Privatbankar',
                $code,
                450,
                true,
                false
            );
            $output->writeln("  <comment>* payment type <info>{$code}</info> created</comment>");
        } else {
            $output->writeln("  * payment type <info>{$code}</info> exists");
        }

        $code = PrivatbankarRecurrent::GATEWAY_CODE;
        if (!$this->paymentGatewaysRepository->exists($code)) {
            $this->paymentGatewaysRepository->add(
                'Privatbankar Recurrent',
                $code,
                460,
                true,
                true
            );
            $output->writeln("  <comment>* payment type <info>{$code}</info> created</comment>");
        } else {
            $output->writeln("  * payment type <info>{$code}</info> exists");
        }
    }
}
