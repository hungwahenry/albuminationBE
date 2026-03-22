<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Resources\ReportReasonResource;
use App\Http\Resources\ReportResource;
use App\Models\User;
use App\Services\ReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(private ReportService $service) {}

    public function reasons(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(ReportService::getReportableTypes()))],
        ]);

        $reportableClass = ReportService::getReportableTypes()[$request->type];
        $reasons = $this->service->getReasons($reportableClass);

        return $this->success(ReportReasonResource::collection($reasons));
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $reportable = $this->service->resolveReportable(
            $request->validated('type'),
            $request->validated('id'),
        );

        // Prevent self-reporting
        $ownerId = $reportable instanceof User
            ? $reportable->id
            : ($reportable->user_id ?? null);

        if ($ownerId && $ownerId === $request->user()->id) {
            return $this->error('You cannot report your own content', 422);
        }

        // Prevent duplicate reports
        if ($reportable->isReportedBy($request->user()->id)) {
            return $this->error('You have already reported this', 422);
        }

        $report = $this->service->create(
            $request->user(),
            $reportable,
            $request->validated('reason_id'),
            $request->validated('body'),
        );

        return $this->success(new ReportResource($report->load('reason')), 'Report submitted', 201);
    }
}
