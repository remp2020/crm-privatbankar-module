<?php

namespace Crm\PrivatbankarModule\Presenters;

use Crm\ApplicationModule\Presenters\FrontendPresenter;
use Crm\PaymentsModule\Repositories\PaymentMetaRepository;
use Crm\PaymentsModule\Repositories\PaymentsRepository;
use Nette\DI\Attributes\Inject;

class ReturnPresenter extends FrontendPresenter
{
    #[Inject]
    public PaymentMetaRepository $paymentMetaRepository;

    #[Inject]
    public PaymentsRepository $paymentsRepository;

    public function renderThankyou($uuid)
    {
        $meta = $this->paymentMetaRepository->findByMeta('privatbankar_transaction_reference', $uuid);
        if (!$meta) {
            // will be processed as error
            $this->redirect(':Payments:Return:gateway');
        }
        $this->template->payment = $meta->payment;
    }

    public function renderError($uuid)
    {
        $meta = $this->paymentMetaRepository->findByMeta('privatbankar_transaction_reference', $uuid);
        if (!$meta) {
            // will be processed as error
            $this->redirect(':Payments:Return:gateway');
        }

        // will be processed as error and fail the payment
        $this->redirect(':Payments:Return:gateway', [
            'gatewayCode' => $meta->payment->payment_gateway->code,
            'VS' => $meta->payment->variable_symbol,
        ]);
    }

    public function actionCancel($uuid)
    {
        $this->redirect('error', $uuid);
    }

    public function actionTimeout($uuid)
    {
        $this->redirect('error', $uuid);
    }

    public function handleCheckIpn($paymentId)
    {
        $payment = $this->paymentsRepository->find($paymentId);
        $ipn = $this->paymentMetaRepository->findByPaymentAndKey($payment, 'privatbankar_ipn');

        if ($ipn) {
            $this->redirect(':Payments:Return:gateway', [
                'gatewayCode' => $payment->payment_gateway->code,
                'VS' => $payment->variable_symbol,
            ]);
        }
    }
}
