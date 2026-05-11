<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->decimal('threshold', 3, 2);
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['name' => 'sexual/minors',          'label' => 'Sexual content involving minors', 'threshold' => 0.10, 'sort_order' =>  1],
            ['name' => 'hate/threatening',       'label' => 'Hate (threatening)',              'threshold' => 0.30, 'sort_order' =>  2],
            ['name' => 'harassment/threatening', 'label' => 'Harassment (threatening)',        'threshold' => 0.30, 'sort_order' =>  3],
            ['name' => 'self-harm/intent',       'label' => 'Self-harm intent',                'threshold' => 0.30, 'sort_order' =>  4],
            ['name' => 'self-harm/instructions', 'label' => 'Self-harm instructions',          'threshold' => 0.30, 'sort_order' =>  5],
            ['name' => 'illicit/violent',        'label' => 'Illicit (violent)',               'threshold' => 0.30, 'sort_order' =>  6],
            ['name' => 'hate',                   'label' => 'Hate',                            'threshold' => 0.50, 'sort_order' =>  7],
            ['name' => 'self-harm',              'label' => 'Self-harm',                       'threshold' => 0.50, 'sort_order' =>  8],
            ['name' => 'illicit',                'label' => 'Illicit',                         'threshold' => 0.60, 'sort_order' =>  9],
            ['name' => 'harassment',             'label' => 'Harassment',                      'threshold' => 0.70, 'sort_order' => 10],
            ['name' => 'sexual',                 'label' => 'Sexual',                          'threshold' => 0.70, 'sort_order' => 11],
            ['name' => 'violence',               'label' => 'Violence',                        'threshold' => 0.70, 'sort_order' => 12],
            ['name' => 'violence/graphic',       'label' => 'Violence (graphic)',              'threshold' => 0.70, 'sort_order' => 13],
        ];

        DB::table('moderation_categories')->insert(array_map(
            fn (array $r) => $r + ['enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            $rows,
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_categories');
    }
};
