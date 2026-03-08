<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->reportable_type,
            'reason'     => $this->reason->label,
            'body'       => $this->body,
            'status'     => $this->status,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
