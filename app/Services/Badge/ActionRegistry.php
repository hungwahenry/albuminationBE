<?php

namespace App\Services\Badge;

use App\Models\User;
use Closure;

/**
 * Registry of named count-resolvers for badge evaluation.
 *
 * Actions are registered at boot time (AppServiceProvider) and are keyed by
 * the same string used in badge criteria: { "action": "loves_given" }.
 * This replaces the hardcoded match in CountThresholdEvaluator so that new
 * action types can be added without touching evaluator code.
 *
 * Usage:
 *   ActionRegistry::register('my_action', fn (User $user) => MyModel::where('user_id', $user->id)->count());
 *   ActionRegistry::resolve('my_action', $user);  // => int
 */
class ActionRegistry
{
    /** @var array<string, Closure(User): int> */
    private static array $resolvers = [];

    public static function register(string $action, Closure $resolver): void
    {
        static::$resolvers[$action] = $resolver;
    }

    public static function resolve(string $action, User $user): int
    {
        if (!isset(static::$resolvers[$action])) {
            return 0;
        }

        return (int) (static::$resolvers[$action])($user);
    }

    public static function has(string $action): bool
    {
        return isset(static::$resolvers[$action]);
    }

    /** Return all registered action keys — used to populate admin UI options. */
    public static function keys(): array
    {
        return array_keys(static::$resolvers);
    }
}
