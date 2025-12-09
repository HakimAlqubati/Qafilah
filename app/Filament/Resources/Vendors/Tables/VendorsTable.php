<?php

namespace App\Filament\Resources\Vendors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Notifications\Notification;
use App\Models\Vendor;
use App\Models\User;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Facades\Hash;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table->striped()
            ->columns([
                // 1. ID Column (Primary Key)
                TextColumn::make('id')
                    ->label(__('lang.id'))
                    ->sortable(),

                // 2. Name Column (Most important for search/identification)
                TextColumn::make('name')
                    ->label(__('lang.vendor_name'))
                    ->searchable() // Enables text search
                    ->sortable(),
                TextColumn::make('offers_count')
                    ->label(__('lang.products_count'))
                    ->counts('offers')
                    ->default(0)
                    ->alignCenter()
                    ->sortable(),

                // 3. Status Column (Visual filtering/status check)
                IconColumn::make('status')
                    ->label(__('lang.status'))
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })->alignCenter()
                    ->sortable()
                    ->icon(fn(string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        'pending' => 'heroicon-o-clock',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                // Has User Column
                IconColumn::make('has_user')
                    ->label(__('lang.has_user'))
                    ->boolean()
                    ->getStateUsing(fn(Vendor $record): bool => $record->has_user)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                // 4. Email Column
                TextColumn::make('email')
                    ->label(__('lang.email'))
                    ->copyable() // Allows quick copy on click
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Hidden by default to keep table clean

                // 5. Default Currency
                TextColumn::make('defaultCurrency.name')
                    ->label(__('lang.currency'))
                    ->sortable()
                    ->toggleable(),

                // 6. Delivery Rate
                TextColumn::make('delivery_rate_per_km')
                    ->label(__('lang.delivery_rate'))
                    ->money(fn($record) => $record->defaultCurrency?->code ?? 'SAR')
                    ->sortable()
                    ->toggleable(),

                // 5. Products Count (Performance improved with withCount in query)
                // TextColumn::make('products_count')
                //     ->label('Products')
                //     ->counts('products') // Uses the withCount applied in getEloquentQuery
                //     ->sortable(),

                // 6. Creator (Auditing - Requires Eager Loading 'creator')
                TextColumn::make('creator.name')
                    ->label(__('lang.created_by'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 7. Created At (Audit)
                TextColumn::make('created_at')
                    ->label(__('lang.creation_date'))
                    ->dateTime()
                    ->sortable(),
            ])->filtersFormColumns(4)
            ->filters([
                // Filter 1: Status Filter
                SelectFilter::make('status')
                    ->options([
                        'active' => __('lang.active'),
                        'inactive' => __('lang.inactive'),
                        'pending' => __('lang.pending_review'),
                    ])
                    ->label(__('lang.filter_by_status')),

                // Filter 2: Soft Deletes Filter
                TrashedFilter::make(),
            ], FiltersLayout::Modal)
            ->actions([
                EditAction::make(),

                // Action to create user for vendor
                Action::make('createUser')
                    ->label(__('lang.create_user'))
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn(Vendor $record): bool => !$record->has_user)
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('lang.name'))
                                    ->default(fn(Vendor $record): string => $record->name)
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label(__('lang.email'))
                                    ->email()
                                    ->default(fn(Vendor $record): ?string => $record->email)
                                    ->required()
                                    ->unique(User::class, 'email')
                                    ->maxLength(255),
                                TextInput::make('password')
                                    ->label(__('lang.password'))
                                    ->password()
                                    ->default('123456')
                                    ->required()  
                                    ->minLength(6)
                                    ->same('password_confirmation'),
                                TextInput::make('password_confirmation')
                                    ->label(__('lang.password_confirmation'))
                                    ->password()
                                    ->default('123456')
                                    ->required(),
                            ]),
                    ])
                    ->action(function (Vendor $record, array $data): void {
                        User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => Hash::make($data['password']),
                            'vendor_id' => $record->id,
                        ]);

                        Notification::make()
                            ->title(__('lang.user_created_successfully'))
                            ->success()
                            ->send();
                    })
                    ->modalHeading(__('lang.create_user_for_vendor'))
                    ->modalSubmitActionLabel(__('lang.create')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Actions for Soft Deletes (Restoration and Force Delete)
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            // Default table ordering
            ->defaultSort('id', 'desc');
    }
}
