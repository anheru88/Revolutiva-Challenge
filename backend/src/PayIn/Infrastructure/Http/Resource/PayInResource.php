<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\PayIn\Application\Query\PayInResponse;

/**
 * @property PayInResponse $resource
 */
final class PayInResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
