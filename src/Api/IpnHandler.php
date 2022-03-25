<?php

namespace Crm\PrivatbankarModule\Api;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApiModule\Api\EmptyResponse;
use Crm\ApiModule\Params\InputParam;
use Crm\ApiModule\Params\ParamsProcessor;
use Crm\PaymentsModule\PaymentProcessor;
use Crm\PaymentsModule\Repository\PaymentMetaRepository;
use Nette\Http\Response;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class IpnHandler extends ApiHandler
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

    public function handle(array $params): ResponseInterface
    {
        $paramsProcessor = new ParamsProcessor($this->params());
        if ($paramsProcessor->hasError()) {
            $response = new JsonApiResponse(Response::S400_BAD_REQUEST, ['status' => 'error', 'message' => $paramsProcessor->hasError()]);
            return $response;
        }
        $params = $paramsProcessor->getValues();

        $meta = $this->paymentMetaRepository->findByMeta('privatbankar_transaction_reference', $params['uuid']);
        if (!$meta) {
            $response = new JsonApiResponse(Response::S404_NOT_FOUND, ['status' => 'error', 'message' => 'payment not found: ' . $params['uuid']]);
            return $response;
        }

        $this->paymentMetaRepository->add($meta->payment, 'privatbankar_ipn', 1);

        $this->paymentProcessor->complete($meta->payment, function () {
            // no callback here in API...
        });

        $response = new EmptyResponse();
        $response->setCode(Response::S200_OK);
        return $response;
    }
}
