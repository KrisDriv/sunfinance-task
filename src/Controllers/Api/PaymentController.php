<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Config\EntityImportConfig;
use App\Controllers\AbstractController;
use App\Entities\PaymentEntity;
use App\Services\EntityHydrateService;
use App\Services\PaymentProcessorService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends AbstractController
{

    public function __construct(private readonly EntityHydrateService    $entityHydrateService,
                                private readonly PaymentProcessorService $paymentProcessorService,
                                private readonly EntityImportConfig      $importConfig,
                                private readonly LoggerInterface         $logger)
    {
    }

    public function create(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $postData = $this->entityHydrateService->translateKeys(
                $request->request->all(),
                $this->importConfig->getKeyTranslationArrayFor(PaymentEntity::class)
            );

            try {
                /** @var PaymentEntity $hydratedEntity */
                $hydratedEntity = $this->entityHydrateService->fromArray($postData, PaymentEntity::class);

            } catch (Exception $e) {
                $this->logger->error('Failed to hydrate payment entity from Http Request: ' . $e->getMessage(), [
                    'exception' => $e
                ]);

                return $this->response($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            try {
                $paymentEntity = $this->paymentProcessorService->processNewLoanPayment($hydratedEntity);

            } catch (Exception $e) {
                $this->logger->error('Failed to process payment from Http Request: ' . $e->getMessage(), [
                    'exception' => $e
                ]);

                return $this->response($e->getMessage());
            }

            return $this->response($paymentEntity);
        }

        return $this->response('GET method not implemented');
    }

}