<?php

namespace App\Http\Controllers;

use App\Jobs\EvaluateBadgesJob;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function __construct(private ExportService $exportService) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $export = $this->exportService->build($user, $request);

        EvaluateBadgesJob::dispatch('data_exported', $user->id);

        $filename = 'albumination-export-' . now()->format('Y-m-d') . '.json';

        return response(json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 200)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
