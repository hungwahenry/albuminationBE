<?php

namespace App\Http\Resources;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->resolveTypeKey(),
            'reason'     => $this->reason->label,
            'body'       => $this->body,
            'status'     => $this->status,
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    private function resolveTypeKey(): string
    {
        $flipped = array_flip(ReportService::getReportableTypes());

        return $flipped[$this->reportable_type] ?? class_basename($this->reportable_type);
    }
}
