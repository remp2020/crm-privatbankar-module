<?php

namespace Crm\PrivatbankarModule\Gateways;

use Crm\PaymentsModule\Models\Gateways\RecurrentPaymentInterface;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Privatbankar\Message\PurchaseRequest;
use Omnipay\Privatbankar\Message\PurchaseResponse;

class PrivatbankarRecurrent extends AbstractPrivatbankar implements RecurrentPaymentInterface
{
    public const GATEWAY_CODE = 'privatbankar_recurrent';

    public function begin($payment)
    {
        $this->initialize();
        $this->paymentMethod = 'recurring_manual';
        parent::begin($payment);
    }

    public function charge($payment, $token): string
    {
        $this->initialize();

        /** @var PurchaseRequest $chargeRequest */
        $chargeRequest = $this->gateway->charge();

        /** @var PurchaseResponse $response */
        $response = $chargeRequest
            ->setTransactionReference($token)
            ->send();
        $this->response = $response;

        $this->checkChargeStatus($payment, $this->getResultCode());

        $this->paymentMetaRepository->add($payment, 'privatbankar_token', $response->getTransactionId());

        return self::CHARGE_OK;
    }

    public function checkValid($token)
    {
        // TODO: 24 charges or 2 years since initial payment
        throw new InvalidRequestException(self::GATEWAY_CODE . " gateway doesn't support checking if token is still valid");
    }

    public function checkExpire($recurrentPayments)
    {
        throw new InvalidRequestException(self::GATEWAY_CODE . " gateway doesn't support token expiration check");
    }

    public function hasRecurrentToken(): bool
    {
        return (bool) $this->paymentMetaRepository->findByPaymentAndKey($this->payment, 'privatbankar_transaction_reference');
    }

    public function getRecurrentToken()
    {
        return $this->paymentMetaRepository->findByPaymentAndKey($this->payment, 'privatbankar_transaction_reference')->value;
    }

    public function getResultCode(): ?string
    {
        return $this->response->getTransactionStatus();
    }

    public function getResultMessage(): ?string
    {
        return $this->response->getMessage();
    }
}
