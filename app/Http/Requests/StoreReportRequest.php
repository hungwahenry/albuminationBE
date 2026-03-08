<?php

namespace App\Http\Requests;

use App\Services\ReportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'      => ['required', 'string', Rule::in(array_keys(ReportService::getReportableTypes()))],
            'id'        => ['required', 'integer'],
            'reason_id' => ['required', 'integer', 'exists:report_reasons,id'],
            'body'      => ['nullable', 'string', 'max:1000'],
        ];
    }
}
