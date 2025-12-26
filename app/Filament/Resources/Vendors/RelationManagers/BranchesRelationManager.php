<?php

namespace App\Filament\Resources\Vendors\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Tabs;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Vendors\Schemas\VendorForm;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('lang.branches');
    }

    public static function getModelLabel(): string
    {
        return __('lang.branch');
    }

    public static function getBadge(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->branches()->count();
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('lang.branch'))
                    ->tabs([
                        // Tab 1: Basic Info (Customized for Branch)
                        \Filament\Schemas\Components\Tabs\Tab::make(__('lang.basic_info'))
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                // parent_id is hidden - automatically set by relation manager
                                \Filament\Forms\Components\Hidden::make('parent_id')
                                    ->default(fn() => $this->getOwnerRecord()->getKey()),

                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        // Branch Name (not Vendor Name)
                                        \Filament\Forms\Components\TextInput::make('name')
                                            ->label(__('lang.branch_name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, ?string $state) {
                                                $set('slug', \Illuminate\Support\Str::slug($state));
                                            }),

                                        // Slug is hidden but auto-generated
                                        \Filament\Forms\Components\Hidden::make('slug'),
                                    ]),

                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('contact_person')
                                            ->label(__('lang.contact_person'))
                                            ->maxLength(255),

                                        \Filament\Forms\Components\TextInput::make('phone')
                                            ->label(__('lang.phone_number'))
                                            ->tel()
                                            ->maxLength(50),
                                    ]),

                                \Filament\Forms\Components\TextInput::make('vat_id')
                                    ->label(__('lang.vat_id'))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->helperText(__('lang.main_vendor_helper')),
                            ]),

                        // Tab 2: Location & Delivery
                        \App\Filament\Resources\Vendors\Schemas\Components\Tabs\LocationDeliveryTab::make(),

                        // Tab 3: Settings & Media
                        \App\Filament\Resources\Vendors\Schemas\Components\Tabs\SettingsMediaTab::make(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('lang.branch_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label(__('lang.city'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('lang.phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('lang.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => __("lang.{$state}")),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('lang.add_branch')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
