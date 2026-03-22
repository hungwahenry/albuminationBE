<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportReason;
use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\Take;
use App\Models\TakeReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ReportService
{
    private const REPORTABLE_TYPES = [
        'rotation'         => Rotation::class,
        'take'             => Take::class,
        'take_reply'       => TakeReply::class,
        'rotation_comment' => RotationComment::class,
        'user'             => User::class,
    ];

    public function resolveReportable(string $type, int $id): Model
    {
        $class = self::REPORTABLE_TYPES[$type] ?? null;

        if (!$class) {
            throw ValidationException::withMessages(['type' => 'Invalid reportable type.']);
        }

        $model = $class::find($id);

        if (!$model) {
            throw ValidationException::withMessages(['id' => 'The reported content was not found.']);
        }

        return $model;
    }

    public function create(User $user, Model $reportable, int $reasonId, ?string $body): Report
    {
        return Report::create([
            'user_id'          => $user->id,
            'reportable_type'  => $reportable->getMorphClass(),
            'reportable_id'    => $reportable->getKey(),
            'report_reason_id' => $reasonId,
            'body'             => $body,
        ]);
    }

    public function getReasons(string $reportableType): Collection
    {
        return ReportReason::active()
            ->forType($reportableType)
            ->orderBy('sort_order')
            ->get();
    }

    public static function getReportableTypes(): array
    {
        return self::REPORTABLE_TYPES;
    }
}
