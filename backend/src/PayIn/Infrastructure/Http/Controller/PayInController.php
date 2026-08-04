<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Http\Controller;

use Illuminate\Http\JsonResponse;
use Src\PayIn\Application\UseCase\CreatePayInHandler;
use Src\PayIn\Application\UseCase\GetPayInHandler;
use Src\PayIn\Infrastructure\Http\Request\CreatePayInRequest;
use Src\PayIn\Infrastructure\Http\Resource\PayInResource;

final class PayInController
{
    public function store(CreatePayInRequest $request, CreatePayInHandler $handler): JsonResponse
    {
        $response = $handler->handle($request->toCommand());

        return PayInResource::make($response)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(string $uuid, GetPayInHandler $handler): JsonResponse
    {
        $response = $handler->handle($uuid);

        return PayInResource::make($response)->response();
    }
}
