<?php

namespace Crm\PrivatbankarModule\Gateways;

use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Crm\PaymentsModule\Models\Gateways\GatewayAbstract;
use Crm\PaymentsModule\Repositories\PaymentMetaRepository;
use Nette\Application\LinkGenerator;
use Nette\Http\Response;
use Nette\Localization\Translator;
use Nette\Utils\Strings;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Omnipay;
use Omnipay\Privatbankar\Gateway;
use Omnipay\Privatbankar\Message\PurchaseRequest;

abstract class AbstractPrivatbankar extends GatewayAbstract
{
    protected Gateway $gateway;

    protected $paymentMetaRepository;

    protected $paymentMethod;

    protected $payment;

    public function __construct(
        LinkGenerator $linkGenerator,
        ApplicationConfig $applicationConfig,
        Response $httpResponse,
        Translator $translator,
        PaymentMetaRepository $paymentMetaRepository,
    ) {
        parent::__construct($linkGenerator, $applicationConfig, $httpResponse, $translator);
        $this->paymentMetaRepository = $paymentMetaRepository;
    }

    protected function initialize()
    {
        /** @var Gateway $gateway */
        $gateway = Omnipay::create('Privatbankar');
        $this->gateway = $gateway;

        $this->gateway->setSource($this->applicationConfig->get('privatbankar_source'));
        $this->gateway->setTestMode($this->applicationConfig->get('privatbankar_mode') !== 'live');
    }

    public function begin($payment)
    {
        $this->initialize();

        if (!isset($this->paymentMethod)) {
            throw new InvalidRequestException('unable to request Privatbankar without paymentMethod');
        }

        $meta = $this->paymentMetaRepository->values(
            $payment,
            'firstname',
            'lastname',
            'company',
            'country',
            'postcode',
            'city',
            'street',
            'phone',
        )->fetchPairs('key', 'value');

        /** @var PurchaseRequest $purchaseRequest */
        $purchaseRequest = $this->gateway->purchase();
        $purchaseRequest
            ->setTransactionId($payment->variable_symbol)
            ->setPaymentMethod($this->paymentMethod)
            ->setPayer([
                'email' => $payment->user->email,
                'firstname' => $meta['firstname'] ?? null,
                'lastname' => $meta['lastname'] ?? null,
                'company' => $meta['company'] ?? null,
                'country' => $meta['country'] ?? null,
                'postcode' => $meta['postcode'] ?? null,
                'city' => $meta['city'] ?? null,
                'street' => $meta['street'] ?? null,
                'phone' => $meta['phone'] ?? null,
            ])
            ->setCartItems($this->getItems($payment));

        $this->response = $purchaseRequest->send();

        if (!$this->response->getTransactionReference()) {
            if ($this->response->getData()['status'] === 'error') {
                throw new InvalidRequestException('Unable to initialize Privatbankar payment: ' . $this->response->getData()['message'][0]);
            }
            throw new InvalidRequestException("Unable to initialize Privatbankar payment, gateway didn't return transaction reference");
        }

        $this->paymentMetaRepository->add($payment, 'privatbankar_transaction_reference', $this->response->getTransactionReference());
    }

    public function complete($payment): ?bool
    {
        $this->initialize();
        $this->payment = $payment;
        $ipn = $this->paymentMetaRepository->findByPaymentAndKey($payment, 'privatbankar_ipn');
        return (bool) $ipn;
    }

    protected function getItems($payment)
    {
        $items = [];
        foreach ($payment->related('payment_items') as $paymentItem) {
            $vatCoef = 1 + $paymentItem->vat / 100;
            $vatAmount = round($paymentItem->amount / $vatCoef * ($vatCoef - 1), 2);
            $netAmount = $paymentItem->amount - $vatAmount;

            $items[] =[
                'ref' => Strings::webalize($paymentItem->name),
                'name' => $paymentItem->name,
                'price_net' => $netAmount,
                'vat' => $paymentItem->vat,
                'price_vat' => $vatAmount,
                'price' => $paymentItem->count * $paymentItem->amount,
            ];
        }
        return $items;
    }
}
