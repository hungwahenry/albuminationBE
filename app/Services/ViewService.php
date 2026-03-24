<?php

namespace App\Services;

use App\Models\ContentView;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ViewService
{
    public function track(User $user, Model $viewable): void
    {
        DB::transaction(function () use ($user, $viewable) {
            $view = ContentView::firstOrCreate([
                'user_id' => $user->id,
                'viewable_type' => $viewable->getMorphClass(),
                'viewable_id' => $viewable->getKey(),
            ]);

            if ($view->wasRecentlyCreated) {
                $viewable->increment('views_count');
            }
        });
    }
}
