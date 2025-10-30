<?php

namespace App\Filament\Resources\Products\Schemas;

 
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid; 
use Filament\Infolists\Components\ViewEntry; // لاستعراض العلاقات كجداول داخلية
 use App\Models\Product; // تأكد من استيراد نموذج المنتج
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
        ->schema([])
    
            // ->schema([
            //     // 1. القسم العلوي: الهوية والحالة
            //     Split::make([
            //         Section::make('Product Identity & Status')
            //             ->schema([
            //                 Grid::make(2)
            //                     ->schema([
            //                         TextEntry::make('name')
            //                             ->label('Product Name')
            //                             ->size(TextEntry\Size::Large),

            //                         TextEntry::make('slug')
            //                             ->label('URL Slug')
            //                             ->copyable()
            //                             ->size(TextEntry\Size::Small)
            //                             ->badge(),
            //                     ]),

            //                 Grid::make(3)
            //                     ->schema([
            //                         TextEntry::make('status')
            //                             ->label('Status')
            //                             ->badge()
            //                             ->color(fn (string $state): string => match ($state) {
            //                                 Product::$STATUSES['DRAFT'] => 'warning',
            //                                 Product::$STATUSES['ACTIVE'] => 'success',
            //                                 Product::$STATUSES['INACTIVE'] => 'danger',
            //                                 default => 'secondary',
            //                             })
            //                             ->formatStateUsing(fn (string $state) => ucfirst($state)), // عرض الحالة كنص مترجم

            //                         IconEntry::make('is_featured')
            //                             ->label('Featured Product?')
            //                             ->boolean()
            //                             ->icon(fn (bool $state) => $state ? 'heroicon-o-star' : 'heroicon-o-x-circle'),

            //                         TextEntry::make('variants_count')
            //                             ->label('Total Variants')
            //                             ->count('variants')
            //                             ->size(TextEntry\Size::Medium)
            //                             ->icon('heroicon-o-tag'),
            //                     ]),
            //             ])->columnSpan(2), // هذا القسم يأخذ عمودين من الشبكة الخارجية

            //         // 2. القسم الجانبي: التدقيق
            //         Section::make('Audit & Timeline')
            //             ->schema([
            //                 TextEntry::make('created_at')
            //                     ->label('Created On')
            //                     ->dateTime(),

            //                 TextEntry::make('creator.name') // استخدام علاقة creator
            //                     ->label('Created By')
            //                     ->default('System'),

            //                 TextEntry::make('updated_at')
            //                     ->label('Last Updated')
            //                     ->dateTime(),

            //                 TextEntry::make('editor.name') // استخدام علاقة editor
            //                     ->label('Last Edited By')
            //                     ->default('N/A'),
            //             ])->columnSpan(1), // هذا القسم يأخذ عمود واحد
            //     ])->columns(3),

            //     // 3. قسم الوصف والمحتوى
            //     Section::make('Content & Descriptions')
            //         ->schema([
            //             TextEntry::make('short_description')
            //                 ->label('Short Description')
            //                 ->html() // عرض HTML إن وجد
            //                 ->markdown()
            //                 ->columnSpanFull(),

            //             TextEntry::make('description')
            //                 ->label('Full Description')
            //                 ->placeholder('No detailed description provided.')
            //                 ->html()
            //                 ->markdown()
            //                 ->columnSpanFull(),
            //         ]),

            //     // 4. قسم الكتالوج والخصائص
            //     Section::make('Catalog Structure')
            //         ->description('Categorization and Attribute Set definitions.')
            //         ->schema([
            //             Fieldset::make('Core Links')
            //                 ->columns(3)
            //                 ->schema([
            //                     TextEntry::make('category.name')
            //                         ->label('Category')
            //                         ->icon('heroicon-o-folder')
            //                         ->url(fn ($record) => $record->category ? \App\Filament\Resources\CategoryResource::getUrl('view', ['record' => $record->category_id]) : null),

            //                     TextEntry::make('brand.name')
            //                         ->label('Brand')
            //                         ->icon('heroicon-o-building-storefront'),

            //                     TextEntry::make('attributeSet.name')
            //                         ->label('Attribute Set (EAV)')
            //                         ->icon('heroicon-o-cog'),
            //                 ]),

            //             // 5. عرض جدول فرعي للخصائص الوصفية (إذا كان هناك قيم عامة للمنتج)
            //             // Note: Requires a separate view component for complex displays
            //             ViewEntry::make('attributes')
            //                 ->label('General Product Attributes')
            //                 ->view('filament.infolists.components.product-attributes') // يجب إنشاء هذا الملف
            //                 ->visible(fn (Product $record) => $record->attributes()->exists()),
            //         ]),
            // ])
        ;
    }
}
