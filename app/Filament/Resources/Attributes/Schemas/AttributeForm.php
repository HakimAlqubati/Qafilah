<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Models\Attribute; // تأكد من استيراد نموذج الخاصية
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // 1. قسم التعريف الأساسي (الاسم، الكود، ونوع الإدخال)
                Section::make(__('lang.basic_information'))
                    ->description(__('lang.attribute_details'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                // 1.1 Name (الاسم الظاهر)
                                TextInput::make('name')
                                    ->label(__('lang.attribute_name_placeholder'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                // 1.2 Code (الكود التقني)
                                TextInput::make('code')
                                    ->label(__('lang.code_slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true) // فريد في قاعدة البيانات
                                    ->maxLength(50)
                                    ->alphaDash() // يسمح بالحروف والأرقام والداش فقط
                                    ->columnSpan(1),
                            ]),

                        // 1.3 Input Type (نوع الإدخال)
                        Select::make('input_type')
                            ->label(__('lang.input_type'))
                            ->required()
                            ->options(Attribute::inputTypeOptions()) // استخدام الدالة المساعدة من النموذج
                            ->native(false) // لتحسين شكل الـ Select
                            ->columnSpanFull(),
                    ]),

                // 2. قسم الإعدادات الوظيفية (هل هو مطلوب ونشط)
                Fieldset::make(__('lang.functional_settings'))
                    ->columns(2)
                    ->schema([
                        // 2.1 Is Required (هل هو مطلوب لإكمال المنتج)
                        Toggle::make('is_required')
                            ->label(__('lang.required_for_completion'))
                            ->inline(false) // يجعل التبديل أسفل النص
                            ->helperText(__('lang.required_desc')),

                        // 2.2 Active (هل هو متاح للاستخدام)
                        Toggle::make('active')
                            ->label(__('lang.is_active'))
                            ->inline(false)
                            ->default(true)
                            ->helperText(__('lang.active_desc')),
                    ]),
            ]);
    }
}
