<?php

namespace App\Console\Commands;

use App\Models\Badge;
use App\Models\User;
use App\Services\Badge\BadgeEvaluator;
use Illuminate\Console\Command;

class RecalculateBadges extends Command
{
    protected $signature   = 'badges:recalculate {--user= : Recalculate for a specific user ID}';
    protected $description = 'Retroactively evaluate and award badges for existing users';

    public function handle(BadgeEvaluator $evaluator): void
    {
        $triggers = Badge::where('active', true)->pluck('trigger')->unique()->values();

        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('User not found.');
                return;
            }

            foreach ($triggers as $trigger) {
                $evaluator->evaluate($trigger, $user);
            }

            $this->info('Badge recalculation complete.');
            return;
        }

        $total = User::count();
        $bar   = $this->output->createProgressBar($total);
        $bar->start();

        User::query()->chunk(200, function ($users) use ($evaluator, $triggers, $bar) {
            foreach ($users as $user) {
                foreach ($triggers as $trigger) {
                    $evaluator->evaluate($trigger, $user);
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Badge recalculation complete.');
    }
}
