<?php

namespace Crm\PrivatbankarModule\Api;

use Crm\ApiModule\Api\EmptyResponse;
use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Params\InputParam;
use Crm\ApiModule\Params\ParamsProcessor;
use Crm\PaymentsModule\PaymentProcessor;
use Crm\PaymentsModule\Repository\PaymentMetaRepository;
use Nette\Http\Response;

class IpnHandler extends \Crm\ApiModule\Api\ApiHandler
{
    private $paymentMetaRepository;

    private $paymentProcessor;

    public function __construct(PaymentMetaRepository $paymentMetaRepository, PaymentProcessor $paymentProcessor)
    {
        $this->paymentMetaRepository = $paymentMetaRepository;
        $this->paymentProcessor = $paymentProcessor;
    }

    public function params(): array
    {
        return [
            new InputParam(InputParam::TYPE_GET, 'uuid', InputParam::REQUIRED),
        ];
    }

    public function handle(ApiAuthorizationInterface $authorization)
    {
        $paramsProcessor = new ParamsProcessor($this->params());
        if ($paramsProcessor->isError()) {
            $response = new JsonResponse(['status' => 'error', 'message' => $paramsProcessor->isError()]);
            $response->setHttpCode(Response::S400_BAD_REQUEST);
            return $response;
        }
        $params = $paramsProcessor->getValues();

        $meta = $this->paymentMetaRepository->findByMeta('privatbankar_transaction_reference', $params['uuid']);
        if (!$meta) {
            $response = new JsonResponse(['status' => 'error', 'message' => 'payment not found: ' . $params['uuid']]);
            $response->setHttpCode(Response::S404_NOT_FOUND);
            return $response;
        }

        $this->paymentMetaRepository->add($meta->payment, 'privatbankar_ipn', 1);

        $this->paymentProcessor->complete($meta->payment, function () {
            // no callback here in API...
        });

        $response = new EmptyResponse();
        $response->setHttpCode(Response::S200_OK);
        return $response;
    }
}
