<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModerationSettingResource\Pages;
use App\Models\ModerationSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ModerationSettingResource extends Resource
{
    protected static ?string $model = ModerationSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?string $navigationLabel = 'Moderation Settings';
    protected static ?string $slug = 'moderation-settings';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Master switches')
                ->description('Turn moderation on or off, and decide what happens if the OpenAI API is unreachable.')
                ->schema([
                    Toggle::make('enabled')
                        ->label('Moderation enabled')
                        ->helperText('When off, all UGC is published without moderation checks.')
                        ->default(true),

                    Select::make('fail_mode')
                        ->label('Behaviour on API failure')
                        ->helperText('fail_open = allow submissions through (log a warning). fail_closed = reject submissions until OpenAI is reachable again.')
                        ->options([
                            ModerationSetting::FAIL_OPEN   => 'fail_open — allow on failure',
                            ModerationSetting::FAIL_CLOSED => 'fail_closed — reject on failure',
                        ])
                        ->required(),

                    TextInput::make('cache_ttl_hours')
                        ->label('Cache TTL (hours)')
                        ->helperText('Identical text is moderated once and the verdict is reused for this many hours.')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(24 * 30)
                        ->required(),
                ])
                ->columns(1),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditModerationSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => 1]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth('admin')->user()?->can('moderation.manage') ?? false;
    }
}
