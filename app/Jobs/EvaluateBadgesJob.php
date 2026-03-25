<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Badge\BadgeEvaluator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;

class EvaluateBadgesJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 30;
    public array $backoff = [5, 15];

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

        return $this->subjectType::find($this->subjectId);
    }
}
