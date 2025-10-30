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
                Section::make('Basic Definition')
                    ->description('Define the core identity and input type of this attribute.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                // 1.1 Name (الاسم الظاهر)
                                TextInput::make('name')
                                    ->label('Attribute Name (e.g., Color)')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                // 1.2 Code (الكود التقني)
                                TextInput::make('code')
                                    ->label('Code (Slug)')
                                    ->required()
                                    ->unique(ignoreRecord: true) // فريد في قاعدة البيانات
                                    ->maxLength(50)
                                    ->alphaDash() // يسمح بالحروف والأرقام والداش فقط
                                    ->columnSpan(1),
                            ]),

                        // 1.3 Input Type (نوع الإدخال)
                        Select::make('input_type')
                            ->label('Input Type')
                            ->required()
                            ->options(Attribute::inputTypeOptions()) // استخدام الدالة المساعدة من النموذج
                            ->native(false) // لتحسين شكل الـ Select
                            ->columnSpanFull(),
                    ]),

                // 2. قسم الإعدادات الوظيفية (هل هو مطلوب ونشط)
                Fieldset::make('Functional Settings')
                    ->columns(2)
                    ->schema([
                        // 2.1 Is Required (هل هو مطلوب لإكمال المنتج)
                        Toggle::make('is_required')
                            ->label('Required for Product Completion?')
                            ->inline(false) // يجعل التبديل أسفل النص
                            ->helperText('If enabled, this attribute must be specified for every product using its Attribute Set.'),

                        // 2.2 Active (هل هو متاح للاستخدام)
                        Toggle::make('active')
                            ->label('Is Attribute Active?')
                            ->inline(false)
                            ->default(true)
                            ->helperText('If disabled, the attribute cannot be assigned to any Attribute Set.'),
                    ]),
            ]);
    }
}
