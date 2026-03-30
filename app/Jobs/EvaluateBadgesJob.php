<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Badge\BadgeEvaluator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class EvaluateBadgesJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 30;
    public array $backoff = [5, 15];

    /**
     * Models that are permitted as badge evaluation subjects.
     * Prevents arbitrary class instantiation from serialized queue payloads.
     */
    private const ALLOWED_SUBJECT_TYPES = [
        \App\Models\Take::class,
        \App\Models\Rotation::class,
        \App\Models\RotationComment::class,
        \App\Models\TakeReply::class,
        \App\Models\Report::class,
        \App\Models\Album::class,
    ];

    public function __construct(
        public readonly string $trigger,
        public readonly int    $userId,
        public readonly ?string $subjectType = null,
        public readonly ?int    $subjectId   = null,
    ) {}

    public function handle(BadgeEvaluator $evaluator): void
    {
        $user = User::find($this->userId);
        if (!$user) return;

        $subject = $this->resolveSubject();

        $evaluator->evaluate($this->trigger, $user, $subject);
    }

    private function resolveSubject(): ?Model
    {
        if (!$this->subjectType || !$this->subjectId) return null;

        if (!in_array($this->subjectType, self::ALLOWED_SUBJECT_TYPES, true)) {
            Log::warning('EvaluateBadgesJob: rejected disallowed subject type', [
                'subject_type' => $this->subjectType,
                'trigger'      => $this->trigger,
                'user_id'      => $this->userId,
            ]);
            return null;
        }

        return $this->subjectType::find($this->subjectId);
    }
}
