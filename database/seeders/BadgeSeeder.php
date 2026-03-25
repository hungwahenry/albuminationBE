<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // ── Getting Started ──────────────────────────────────────────────
            [
                'slug'        => 'first_take',
                'name'        => 'First Take',
                'description' => 'Posted your first take on an album.',
                'icon'        => 'microphone',
                'rarity'      => 'common',
                'trigger'     => 'take_created',
                'criteria'    => ['type' => 'first', 'user_relation' => 'takes'],
            ],
            [
                'slug'        => 'first_rotation',
                'name'        => 'On Rotation',
                'description' => 'Published your first rotation.',
                'icon'        => 'playlist',
                'rarity'      => 'common',
                'trigger'     => 'rotation_published',
                'criteria'    => ['type' => 'first', 'user_relation' => 'rotations'],
            ],
            [
                'slug'        => 'first_stan',
                'name'        => 'Day One',
                'description' => 'Stanned your first artist.',
                'icon'        => 'star',
                'rarity'      => 'common',
                'trigger'     => 'stan_created',
                'criteria'    => ['type' => 'first', 'user_relation' => 'stannedArtists'],
            ],
            [
                'slug'        => 'first_comment',
                'name'        => 'In the Comments',
                'description' => 'Left your first comment on a rotation.',
                'icon'        => 'chat',
                'rarity'      => 'common',
                'trigger'     => 'rotation_comment_created',
                'criteria'    => ['type' => 'count_threshold', 'action' => 'rotation_comments', 'threshold' => 1],
            ],
            [
                'slug'        => 'first_reply',
                'name'        => 'In Reply To',
                'description' => 'Replied to your first take.',
                'icon'        => 'reply',
                'rarity'      => 'common',
                'trigger'     => 'take_reply_created',
                'criteria'    => ['type' => 'count_threshold', 'action' => 'take_replies', 'threshold' => 1],
            ],
            [
                'slug'        => 'profile_complete',
                'name'        => 'Fully Loaded',
                'description' => 'Filled out every field on your profile.',
                'icon'        => 'user-check',
                'rarity'      => 'common',
                'trigger'     => 'profile_updated',
                'criteria'    => ['type' => 'profile_complete'],
            ],

            // ── Takes & Ratings ───────────────────────────────────────────────
            [
                'slug'        => 'takes_10',
                'name'        => 'Hot Take',
                'description' => 'Posted 10 takes.',
                'icon'        => 'fire',
                'rarity'      => 'common',
                'trigger'     => 'take_created',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'takes', 'threshold' => 10],
            ],
            [
                'slug'        => 'takes_50',
                'name'        => 'Critic',
                'description' => 'Posted 50 takes.',
                'icon'        => 'pen',
                'rarity'      => 'rare',
                'trigger'     => 'take_created',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'takes', 'threshold' => 50],
            ],
            [
                'slug'        => 'takes_100',
                'name'        => 'Professional Critic',
                'description' => 'Posted 100 takes.',
                'icon'        => 'badge',
                'rarity'      => 'epic',
                'trigger'     => 'take_created',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'takes', 'threshold' => 100],
            ],
            [
                'slug'        => 'perfect_score',
                'name'        => 'Flawless',
                'description' => 'Gave a perfect rating.',
                'icon'        => 'diamond',
                'rarity'      => 'rare',
                'trigger'     => 'take_created',
                'criteria'    => ['type' => 'attribute', 'field' => 'rating', 'operator' => '=', 'value' => 10],
            ],
            [
                'slug'        => 'zero_score',
                'name'        => 'Ruthless',
                'description' => 'Gave the lowest possible rating.',
                'icon'        => 'skull',
                'rarity'      => 'rare',
                'trigger'     => 'take_created',
                'criteria'    => ['type' => 'attribute', 'field' => 'rating', 'operator' => '=', 'value' => 1],
            ],
            [
                'slug'        => 'most_agreed',
                'name'        => 'Right Every Time',
                'description' => 'Had a take with 50 or more agrees.',
                'icon'        => 'thumbs-up',
                'rarity'      => 'epic',
                'trigger'     => 'take_reacted',
                'criteria'    => ['type' => 'relation_count', 'relation' => 'agrees', 'threshold' => 50],
            ],
            [
                'slug'        => 'most_disagreed',
                'name'        => 'Controversial',
                'description' => 'Had a take with 20 or more disagrees.',
                'icon'        => 'lightning',
                'rarity'      => 'rare',
                'trigger'     => 'take_reacted',
                'criteria'    => ['type' => 'relation_count', 'relation' => 'disagrees', 'threshold' => 20],
            ],

            // ── Rotations ─────────────────────────────────────────────────────
            [
                'slug'        => 'rotations_5',
                'name'        => 'Curator',
                'description' => 'Published 5 rotations.',
                'icon'        => 'layers',
                'rarity'      => 'common',
                'trigger'     => 'rotation_published',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'rotations', 'threshold' => 5],
            ],
            [
                'slug'        => 'rotations_25',
                'name'        => 'Head Curator',
                'description' => 'Published 25 rotations.',
                'icon'        => 'crown',
                'rarity'      => 'epic',
                'trigger'     => 'rotation_published',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'rotations', 'threshold' => 25],
            ],
            [
                'slug'        => 'ranked_rotation',
                'name'        => 'Ranker',
                'description' => 'Published a ranked rotation.',
                'icon'        => 'list-numbers',
                'rarity'      => 'common',
                'trigger'     => 'rotation_published',
                'criteria'    => ['type' => 'attribute', 'field' => 'is_ranked', 'operator' => '=', 'value' => true],
            ],
            [
                'slug'        => 'rotation_loved_10',
                'name'        => 'That One List',
                'description' => 'Had a rotation reach 10 loves.',
                'icon'        => 'heart',
                'rarity'      => 'rare',
                'trigger'     => 'love_received',
                'criteria'    => ['type' => 'relation_count', 'relation' => 'loves', 'threshold' => 10],
            ],
            [
                'slug'        => 'rotation_loved_50',
                'name'        => 'Viral Rotation',
                'description' => 'Had a rotation reach 50 loves.',
                'icon'        => 'fire-heart',
                'rarity'      => 'legendary',
                'trigger'     => 'love_received',
                'criteria'    => ['type' => 'relation_count', 'relation' => 'loves', 'threshold' => 50],
            ],
            [
                'slug'        => 'new_music_friday',
                'name'        => 'New Music Friday',
                'description' => 'Published a rotation on a Friday.',
                'icon'        => 'calendar',
                'rarity'      => 'rare',
                'trigger'     => 'rotation_published',
                'criteria'    => ['type' => 'time_window', 'days' => ['Friday']],
            ],

            // ── Social ────────────────────────────────────────────────────────
            [
                'slug'        => 'followers_10',
                'name'        => 'Getting Known',
                'description' => 'Reached 10 followers.',
                'icon'        => 'users',
                'rarity'      => 'common',
                'trigger'     => 'follow_received',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'followers', 'threshold' => 10],
            ],
            [
                'slug'        => 'followers_50',
                'name'        => 'Rising',
                'description' => 'Reached 50 followers.',
                'icon'        => 'trending-up',
                'rarity'      => 'rare',
                'trigger'     => 'follow_received',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'followers', 'threshold' => 50],
            ],
            [
                'slug'        => 'followers_100',
                'name'        => 'Certified',
                'description' => 'Reached 100 followers.',
                'icon'        => 'check-circle',
                'rarity'      => 'epic',
                'trigger'     => 'follow_received',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'followers', 'threshold' => 100],
            ],
            [
                'slug'        => 'followers_500',
                'name'        => 'Scene Icon',
                'description' => 'Reached 500 followers.',
                'icon'        => 'award',
                'rarity'      => 'legendary',
                'trigger'     => 'follow_received',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'followers', 'threshold' => 500],
            ],
            [
                'slug'        => 'following_50',
                'name'        => 'Networker',
                'description' => 'Followed 50 people.',
                'icon'        => 'network',
                'rarity'      => 'common',
                'trigger'     => 'follow_given',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'following', 'threshold' => 50],
            ],

            // ── Loves & Engagement ────────────────────────────────────────────
            [
                'slug'        => 'loves_given_50',
                'name'        => 'Generous',
                'description' => 'Gave 50 loves across any content.',
                'icon'        => 'hand-heart',
                'rarity'      => 'common',
                'trigger'     => 'love_given',
                'criteria'    => ['type' => 'count_threshold', 'action' => 'loves_given', 'threshold' => 50],
            ],
            [
                'slug'        => 'loves_given_200',
                'name'        => 'Spread the Love',
                'description' => 'Gave 200 loves across any content.',
                'icon'        => 'hearts',
                'rarity'      => 'rare',
                'trigger'     => 'love_given',
                'criteria'    => ['type' => 'count_threshold', 'action' => 'loves_given', 'threshold' => 200],
            ],
            [
                'slug'        => 'loves_received_50',
                'name'        => 'Appreciated',
                'description' => 'Received 50 loves across all your content.',
                'icon'        => 'heart-filled',
                'rarity'      => 'rare',
                'trigger'     => 'love_received',
                'criteria'    => ['type' => 'count_threshold', 'action' => 'loves_received', 'threshold' => 50],
            ],

            // ── Comments ──────────────────────────────────────────────────────
            [
                'slug'        => 'comments_25',
                'name'        => 'Regular',
                'description' => 'Left 25 comments on rotations.',
                'icon'        => 'message-circle',
                'rarity'      => 'common',
                'trigger'     => 'rotation_comment_created',
                'criteria'    => ['type' => 'count_threshold', 'action' => 'rotation_comments', 'threshold' => 25],
            ],
            [
                'slug'        => 'comment_loved_10',
                'name'        => 'Good Point',
                'description' => 'Had a comment receive 10 loves.',
                'icon'        => 'message-heart',
                'rarity'      => 'rare',
                'trigger'     => 'love_received',
                'criteria'    => ['type' => 'relation_count', 'relation' => 'loves', 'threshold' => 10],
            ],

            // ── Artists ───────────────────────────────────────────────────────
            [
                'slug'        => 'stans_10',
                'name'        => 'Fan Account',
                'description' => 'Stanned 10 artists.',
                'icon'        => 'music-note',
                'rarity'      => 'common',
                'trigger'     => 'stan_created',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'stannedArtists', 'threshold' => 10],
            ],
            [
                'slug'        => 'stans_50',
                'name'        => 'Superfan',
                'description' => 'Stanned 50 artists.',
                'icon'        => 'music-star',
                'rarity'      => 'rare',
                'trigger'     => 'stan_created',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'stannedArtists', 'threshold' => 50],
            ],

            // ── Albums & Tracks ───────────────────────────────────────────────
            [
                'slug'        => 'track_favourite_10',
                'name'        => 'Deep Cut',
                'description' => 'Favourited 10 tracks.',
                'icon'        => 'music',
                'rarity'      => 'common',
                'trigger'     => 'track_favourited',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'favouriteTracks', 'threshold' => 10],
            ],
            [
                'slug'        => 'track_favourite_50',
                'name'        => 'B-Side King',
                'description' => 'Favourited 50 tracks.',
                'icon'        => 'vinyl',
                'rarity'      => 'rare',
                'trigger'     => 'track_favourited',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'favouriteTracks', 'threshold' => 50],
            ],

            // ── Profile ───────────────────────────────────────────────────────
            [
                'slug'        => 'profile_header_set',
                'name'        => 'Cover Art',
                'description' => 'Set a header album on your profile.',
                'icon'        => 'image',
                'rarity'      => 'common',
                'trigger'     => 'profile_updated',
                'criteria'    => ['type' => 'attribute', 'field' => 'header_album_id', 'operator' => '!=', 'value' => null],
            ],
            [
                'slug'        => 'pinned_rotation',
                'name'        => 'Pinned',
                'description' => 'Pinned a rotation to your profile.',
                'icon'        => 'pin',
                'rarity'      => 'common',
                'trigger'     => 'profile_updated',
                'criteria'    => ['type' => 'attribute', 'field' => 'pinned_rotation_id', 'operator' => '!=', 'value' => null],
            ],
            [
                'slug'        => 'current_vibe_set',
                'name'        => 'Mood',
                'description' => 'Set your current vibe.',
                'icon'        => 'wave',
                'rarity'      => 'common',
                'trigger'     => 'profile_updated',
                'criteria'    => ['type' => 'attribute', 'field' => 'current_vibe', 'operator' => '!=', 'value' => null],
            ],

            // ── Community ─────────────────────────────────────────────────────
            [
                'slug'        => 'album_seeded',
                'name'        => 'Digger',
                'description' => 'First to bring an album into the Albumination database.',
                'icon'        => 'shovel',
                'rarity'      => 'epic',
                'trigger'     => 'album_seeded',
                'criteria'    => ['type' => 'first', 'user_relation' => 'seededAlbums'],
            ],
            [
                'slug'        => 'albums_seeded_10',
                'name'        => 'Archivist',
                'description' => 'Seeded 10 albums into the database.',
                'icon'        => 'archive',
                'rarity'      => 'legendary',
                'trigger'     => 'album_seeded',
                'criteria'    => ['type' => 'count_threshold', 'user_relation' => 'seededAlbums', 'threshold' => 10],
            ],
            [
                'slug'        => 'report_resolved',
                'name'        => 'Good Standing',
                'description' => 'Submitted a report that was resolved in your favour.',
                'icon'        => 'shield-check',
                'rarity'      => 'rare',
                'trigger'     => 'report_resolved',
                'criteria'    => ['type' => 'attribute', 'field' => 'status', 'operator' => '=', 'value' => 'actioned'],
            ],
            [
                'slug'        => 'data_exported',
                'name'        => 'My Data',
                'description' => 'Exported your account data.',
                'icon'        => 'download',
                'rarity'      => 'common',
                'trigger'     => 'data_exported',
                'criteria'    => ['type' => 'always'],
            ],

            // ── Easter Eggs ───────────────────────────────────────────────────
            [
                'slug'        => 'night_owl',
                'name'        => 'Night Owl',
                'description' => 'Posted a take between 2am and 4am.',
                'icon'        => 'moon',
                'rarity'      => 'rare',
                'trigger'     => 'take_created',
                'criteria'    => ['type' => 'time_window', 'start' => '02:00', 'end' => '04:00'],
            ],

            // ── Vibetags ──────────────────────────────────────────────────────
            [
                'slug'        => 'vibetag_pioneer',
                'name'        => 'Vibing',
                'description' => 'Used 5 different vibetags across your rotations.',
                'icon'        => 'hashtag',
                'rarity'      => 'common',
                'trigger'     => 'rotation_published',
                'criteria'    => ['type' => 'count_threshold', 'action' => 'unique_vibetags', 'threshold' => 5],
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}
