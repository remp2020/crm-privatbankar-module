<?php

namespace Crm\PrivatbankarModule\Api;

use Crm\ApiModule\Models\Api\ApiHandler;
use Crm\ApiModule\Models\Params\InputParam;
use Crm\ApiModule\Models\Params\ParamsProcessor;
use Crm\ApiModule\Models\Response\EmptyResponse;
use Crm\PaymentsModule\Models\PaymentProcessor;
use Crm\PaymentsModule\Repositories\PaymentMetaRepository;
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
