<?php

namespace Crm\PrivatbankarModule\Gateways;

class Privatbankar extends AbstractPrivatbankar
{
    public const GATEWAY_CODE = 'privatbankar';

    public function begin($payment)
    {
        $this->initialize();
        $this->paymentMethod = 'otp';
        parent::begin($payment);
    }
}
