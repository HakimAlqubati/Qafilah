<?php

namespace App\Filament\Resources\Vendors\Schemas\Components\Tabs;

use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Tabs\Tab;

class LegalPoliciesTab
{
    /**
     * تبويب السياسات القانونية الخاصة بالتاجر.
     * يظهر في لوحة الأدمن ولوحة التاجر معاً (عبر الوراثة).
     */
    public static function make(): Tab
    {
        return Tab::make(__('lang.legal_policies'))
            ->icon('heroicon-o-document-text')
            ->schema([
                // الأحكام والشروط
                RichEditor::make('terms_and_conditions')
                    ->label(__('lang.terms_and_conditions'))
                    ->toolbarButtons([
                        'bold', 'italic', 'underline',
                        'bulletList', 'orderedList',
                        'h2', 'h3',
                        'undo', 'redo',
                    ])
                    ->columnSpanFull(),

                // سياسة الخصوصية
                RichEditor::make('privacy_policy')
                    ->label(__('lang.privacy_policy'))
                    ->toolbarButtons([
                        'bold', 'italic', 'underline',
                        'bulletList', 'orderedList',
                        'h2', 'h3',
                        'undo', 'redo',
                    ])
                    ->columnSpanFull(),

                // سياسة المتجر
                RichEditor::make('store_policy')
                    ->label(__('lang.store_policy'))
                    ->toolbarButtons([
                        'bold', 'italic', 'underline',
                        'bulletList', 'orderedList',
                        'h2', 'h3',
                        'undo', 'redo',
                    ])
                    ->columnSpanFull(),

                // سياسة الاسترجاع والعائدات
                RichEditor::make('return_policy')
                    ->label(__('lang.return_policy'))
                    ->toolbarButtons([
                        'bold', 'italic', 'underline',
                        'bulletList', 'orderedList',
                        'h2', 'h3',
                        'undo', 'redo',
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
