<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('lang.company_info'))
                    ->schema([
                        TextEntry::make('name')->label(__('lang.customer_name')),
                        TextEntry::make('code')->label(__('lang.code')),
                        TextEntry::make('vat_number')->label(__('lang.vat_number')),
                        TextEntry::make('commercial_register')->label(__('lang.commercial_register')),
                    ])->columns(2),

                Section::make(__('lang.contact_info'))
                    ->schema([
                        TextEntry::make('contact_person')->label(__('lang.contact_person')),
                        TextEntry::make('phone')->label(__('lang.phone')),
                        TextEntry::make('email')->label(__('lang.email')),
                    ])->columns(3),

                Section::make(__('lang.financial_info'))
                    ->schema([
                        TextEntry::make('credit_limit')
                            ->label(__('lang.credit_limit'))
                            ->money('SAR'),
                        TextEntry::make('payment_terms')->label(__('lang.payment_terms')),
                        IconEntry::make('is_active')
                            ->label(__('lang.status'))
                            ->boolean(),
                    ])->columns(3),

                Section::make(__('lang.addresses'))
                    ->schema([
                        RepeatableEntry::make('addresses')
                            ->label('')
                            ->schema([
                                TextEntry::make('type')->label(__('lang.address_type')),
                                TextEntry::make('city.name')->label(__('lang.city')),
                                TextEntry::make('district.name')->label(__('lang.district')),
                                TextEntry::make('address')->label(__('lang.address')),
                                IconEntry::make('is_default')
                                    ->label(__('lang.is_default'))
                                    ->boolean(),
                            ])->columns(2)
                    ])
            ]);
    }
}
