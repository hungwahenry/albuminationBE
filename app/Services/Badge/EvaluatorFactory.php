<?php

namespace App\Services\Badge;

use App\Contracts\BadgeEvaluatorContract;
use App\Services\Badge\Evaluators\AlwaysEvaluator;
use App\Services\Badge\Evaluators\AnyCompositeEvaluator;
use App\Services\Badge\Evaluators\AttributeEvaluator;
use App\Services\Badge\Evaluators\CompositeEvaluator;
use App\Services\Badge\Evaluators\CountThresholdEvaluator;
use App\Services\Badge\Evaluators\FirstActionEvaluator;
use App\Services\Badge\Evaluators\ProfileCompletenessEvaluator;
use App\Services\Badge\Evaluators\RelationCountEvaluator;
use App\Services\Badge\Evaluators\TimeWindowEvaluator;
use InvalidArgumentException;

class EvaluatorFactory
{
    public static function make(array $criteria): BadgeEvaluatorContract
    {
        return match ($criteria['type']) {
            'first'              => new FirstActionEvaluator($criteria),
            'count_threshold'    => new CountThresholdEvaluator($criteria),
            'attribute'          => new AttributeEvaluator($criteria),
            'relation_count'     => new RelationCountEvaluator($criteria),
            'time_window'        => new TimeWindowEvaluator($criteria),
            'profile_complete'   => new ProfileCompletenessEvaluator($criteria),
            'all'                => new CompositeEvaluator($criteria),
            'any'                => new AnyCompositeEvaluator($criteria),
            'always'             => new AlwaysEvaluator(),
            default              => throw new InvalidArgumentException("Unknown badge evaluator type: [{$criteria['type']}]"),
        };
    }
}
