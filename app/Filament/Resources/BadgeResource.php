<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Filament\Resources\BadgeResource\RelationManagers;
use App\Models\Badge;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Features';
    protected static ?int $navigationSort = 1;

    // ─── Trigger options for table filters — derived from live badge data ─────

    private static function triggerOptions(): array
    {
        return Cache::remember('badge:trigger_options', 300, fn () =>
            Badge::distinct()
                ->orderBy('trigger')
                ->pluck('trigger')
                ->mapWithKeys(fn ($t) => [$t => Str::of($t)->replace('_', ' ')->title()->toString()])
                ->all()
        );
    }

    // ─── Form ──────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Badge Details')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('slug', \Illuminate\Support\Str::slug($state))
                        ),

                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Auto-generated from name. Must be unique.'),
                ]),

                Textarea::make('description')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Grid::make(3)->schema([
                    Select::make('rarity')
                        ->options(fn () => \App\Models\BadgeRarityConfig::orderBy('sort_order')->pluck('label', 'key')->all())
                        ->required()
                        ->native(false),

                    TextInput::make('trigger')
                        ->required()
                        ->placeholder('rotation_published')
                        ->helperText('The event name that triggers evaluation (e.g. take_created, rotation_published).'),

                    Toggle::make('active')
                        ->default(true)
                        ->inline(false)
                        ->helperText('Inactive badges will not be evaluated or awarded.'),
                ]),
            ]),

            // ── Icon ──────────────────────────────────────────────────────────

            Section::make('Icon')->schema([
                FileUpload::make('icon')
                    ->label('Badge Image')
                    ->disk('public')
                    ->directory('badges/icons')
                    ->image()
                    ->imagePreviewHeight('80')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                    ->maxSize(1024)
                    ->required()
                    ->helperText('PNG, WebP, or SVG. Max 1 MB.'),
            ]),

            // ── Criteria Builder ──────────────────────────────────────────────

            Section::make('Evaluation Criteria')->schema([
                Select::make('criteria.type')
                    ->label('Evaluator Type')
                    ->options([
                        'first'            => 'First Action',
                        'count_threshold'  => 'Count Threshold',
                        'attribute'        => 'Attribute Check',
                        'relation_count'   => 'Relation Count',
                        'time_window'      => 'Time Window',
                        'profile_complete' => 'Profile Completeness',
                        'all'              => 'Composite (All Must Pass)',
                    ])
                    ->required()
                    ->native(false)
                    ->live()
                    ->columnSpanFull(),

                // first
                Group::make([
                    TextInput::make('criteria.user_relation')
                        ->label('User Relation')
                        ->placeholder('takes')
                        ->helperText('The Eloquent relation name on the User model (e.g. takes, rotations, stannedArtists).')
                        ->required(),
                ])->hidden(fn (Get $get) => $get('criteria.type') !== 'first'),

                // count_threshold
                Group::make([
                    Grid::make(2)->schema([
                        TextInput::make('criteria.user_relation')
                            ->label('User Relation')
                            ->placeholder('takes')
                            ->helperText('Eloquent relation on User model. Leave blank if using Action below.'),

                        TextInput::make('criteria.action')
                            ->label('Custom Action Key')
                            ->placeholder('loves_given')
                            ->helperText('Used for actions not directly mapped to a relation (e.g. loves_given, rotation_comments).'),
                    ]),
                    TextInput::make('criteria.threshold')
                        ->label('Threshold')
                        ->numeric()
                        ->required()
                        ->placeholder('10'),
                ])->hidden(fn (Get $get) => $get('criteria.type') !== 'count_threshold'),

                // attribute
                Group::make([
                    Grid::make(3)->schema([
                        TextInput::make('criteria.field')
                            ->label('Field')
                            ->placeholder('rating')
                            ->helperText('Column on the subject model or User model.')
                            ->required(),

                        Select::make('criteria.operator')
                            ->label('Operator')
                            ->options([
                                '='  => '= equals',
                                '!=' => '!= not equals',
                                '>'  => '> greater than',
                                '>=' => '>= greater or equal',
                                '<'  => '< less than',
                                '<=' => '<= less or equal',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('criteria.value')
                            ->label('Value')
                            ->placeholder('10')
                            ->helperText('Use null for null checks, true/false for booleans.')
                            ->afterStateHydrated(fn ($component, $state) =>
                                $component->state($state === null ? 'null' : $state)
                            )
                            ->dehydrateStateUsing(fn ($state) =>
                                $state === 'null' || $state === '' || $state === null ? null : $state
                            ),
                    ]),
                ])->hidden(fn (Get $get) => $get('criteria.type') !== 'attribute'),

                // relation_count
                Group::make([
                    Grid::make(2)->schema([
                        TextInput::make('criteria.relation')
                            ->label('Relation')
                            ->placeholder('loves')
                            ->helperText('Relation name on the subject model (e.g. loves, agrees, disagrees).')
                            ->required(),

                        TextInput::make('criteria.threshold')
                            ->label('Threshold')
                            ->numeric()
                            ->required()
                            ->placeholder('10'),
                    ]),
                ])->hidden(fn (Get $get) => $get('criteria.type') !== 'relation_count'),

                // time_window
                Group::make([
                    Grid::make(2)->schema([
                        TagsInput::make('criteria.days')
                            ->label('Days of Week')
                            ->placeholder('Friday')
                            ->helperText('Which days trigger the badge (e.g. Friday, Saturday). Leave blank for time-of-day check.'),

                        Grid::make(2)->schema([
                            TextInput::make('criteria.start')
                                ->label('Start Time')
                                ->placeholder('02:00')
                                ->helperText('24h format. Used for time-of-day window.'),

                            TextInput::make('criteria.end')
                                ->label('End Time')
                                ->placeholder('04:00')
                                ->helperText('24h format.'),
                        ]),
                    ]),
                ])->hidden(fn (Get $get) => $get('criteria.type') !== 'time_window'),

                // profile_complete — no extra fields needed
                Group::make([
                    \Filament\Forms\Components\Placeholder::make('profile_complete_note')
                        ->label('')
                        ->content('No additional criteria needed. The evaluator checks all required profile fields are filled.'),
                ])->hidden(fn (Get $get) => $get('criteria.type') !== 'profile_complete'),

                // all (composite)
                Group::make([
                    KeyValue::make('criteria.evaluators')
                        ->label('Sub-Evaluators (JSON)')
                        ->helperText('Define nested evaluator criteria objects. Each key is an evaluator label, value is its criteria JSON.')
                        ->nullable(),
                ])->hidden(fn (Get $get) => $get('criteria.type') !== 'all'),
            ]),
        ]);
    }

    // ─── Infolist ──────────────────────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Badge')->schema([
                InfoGrid::make(4)->schema([
                    TextEntry::make('name'),
                    TextEntry::make('slug')->copyable(),
                    TextEntry::make('rarity')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'common'    => 'gray',
                            'rare'      => 'info',
                            'epic'      => 'warning',
                            'legendary' => 'danger',
                            default     => 'gray',
                        }),
                    IconEntry::make('active')->boolean(),
                ]),
                TextEntry::make('description')->columnSpanFull(),
            ]),

            InfoSection::make('Icon')->schema([
                ImageEntry::make('icon')
                    ->label('Badge Image')
                    ->height(80)
                    ->placeholder('No image uploaded'),
            ]),

            InfoSection::make('Trigger & Criteria')->schema([
                TextEntry::make('trigger')
                    ->badge()
                    ->color('gray'),
                TextEntry::make('criteria')
                    ->label('Criteria (JSON)')
                    ->state(fn (Badge $record) => json_encode($record->criteria, JSON_PRETTY_PRINT))
                    ->fontFamily('mono')
                    ->columnSpanFull(),
            ]),

            InfoSection::make('Stats')->schema([
                InfoGrid::make(2)->schema([
                    TextEntry::make('users_count')
                        ->label('Times Earned')
                        ->state(fn (Badge $record) => $record->users()->count()),
                    TextEntry::make('created_at')->dateTime(),
                ]),
            ]),
        ]);
    }

    // ─── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('icon')
                    ->label('Icon')
                    ->disk('public')
                    ->size(40)
                    ->rounded(),

                TextColumn::make('name')->searchable()->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('rarity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'common'    => 'gray',
                        'rare'      => 'info',
                        'epic'      => 'warning',
                        'legendary' => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('trigger')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Earned By')
                    ->state(fn (Badge $record) => $record->users()->count())
                    ->sortable()
                    ->alignEnd(),

                ToggleColumn::make('active')
                    ->visible(fn () => auth('admin')->user()?->can('badges.manage')),
            ])
            ->defaultSort('rarity')
            ->filters([
                SelectFilter::make('rarity')
                    ->options(fn () => \App\Models\BadgeRarityConfig::orderBy('sort_order')->pluck('label', 'key')->all()),

                SelectFilter::make('trigger')
                    ->options(self::triggerOptions()),

                TernaryFilter::make('active'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth('admin')->user()?->can('badges.manage')),
                DeleteAction::make()
                    ->visible(fn () => auth('admin')->user()?->can('badges.manage'))
                    ->before(function (Badge $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Deleted badge: {$record->name}");
                    }),
            ])
            ->bulkActions([]);
    }

    // ─── Pages & Relations ─────────────────────────────────────────────────────

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\EarnedByRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'view'   => Pages\ViewBadge::route('/{record}'),
            'edit'   => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
