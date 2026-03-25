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
        $users = $this->option('user')
            ? User::where('id', $this->option('user'))->get()
            : User::all();

        $triggers = Badge::where('active', true)->pluck('trigger')->unique();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            foreach ($triggers as $trigger) {
                $evaluator->evaluate($trigger, $user);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Badge recalculation complete.');
    }
}
