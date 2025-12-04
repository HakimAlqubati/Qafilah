<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater; // إضافي
use Filament\Forms\Components\Placeholder; // إضافي
use App\Models\Attribute;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make()->columnSpanFull()->skippable()->schema([
                    // ... (Step 1 code remains the same) ...
                    Step::make('basic')
                    ->label(__('lang.basic_info'))
                    ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            Section::make(__('lang.basic_information'))
                                ->description(__('lang.attribute_details'))
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label(__('lang.attribute_name_placeholder'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(2),

                                            TextInput::make('code')
                                                ->label(__('lang.code_slug'))
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(50)
                                                ->alphaDash()
                                                ->columnSpan(1),
                                        ]),

                                    Select::make('input_type')
                                        ->label(__('lang.input_type'))
                                        ->required()
                                        ->options(Attribute::inputTypeOptions())
                                        ->native(false)
                                        ->live() // مهم جداً: لتحديث الحالة عند التغيير
                                        ->columnSpanFull(),
                                ]),

                            Fieldset::make(__('lang.functional_settings'))
                                ->columns(2)
                                ->schema([
                                    Toggle::make('is_required')
                                        ->label(__('lang.required_for_completion'))
                                        ->inline(false)
                                        ->helperText(__('lang.required_desc')),

                                    Toggle::make('active')
                                        ->label(__('lang.is_active'))
                                        ->inline(false)
                                        ->default(true)
                                        ->helperText(__('lang.active_desc')),
                                ]),
                        ]),

                    // ==========================================
                    //  Step 2: Values (تكملة الكود هنا)
                    // ==========================================
                    Step::make('values')
                        ->label(__('lang.attribute_values')) // عنوان الخطوة
                        ->description(__('lang.manage_options'))
                        ->columnSpanFull()
                        ->visible(fn(Get $get) => in_array($get('input_type'), ['select', 'radio']))

                        ->schema([

                            // 1. رسالة تظهر إذا كان النوع لا يحتاج قيم (مثل النص أو الرقم)
                            Placeholder::make('no_values_needed')
                                ->label('')
                                ->content(__('lang.values_not_required_for_input_type'))
                                ->visible(fn(Get $get) => !in_array($get('input_type'), ['select', 'radio'])),

                            // 2. إدارة القيم (Repeater) تظهر فقط للـ Select و Radio
                            Repeater::make('values') // اسم العلاقة في الموديل
                                ->relationship() // تفعيل العلاقة
                                ->label(__('lang.defined_values'))
                                ->visible(fn(Get $get) => in_array($get('input_type'), ['select', 'radio']))
                                ->table([
                                    TableColumn::make(__('lang.value')),
                                    TableColumn::make(__('lang.sort_order')),
                                    TableColumn::make(__('lang.is_active')),
                                ])
                                ->schema([

                                    TextInput::make('value')
                                        ->label(__('lang.value'))
                                        ->required()
                                        ->distinct() // يمنع تكرار القيم داخل القائمة
                                        ->columnSpan(2),

                                    TextInput::make('sort_order')
                                        ->label(__('lang.sort_order'))
                                        ->numeric()
                                        ->default(0)
                                        ->columnSpan(1),

                                    Toggle::make('is_active')
                                        ->label(__('lang.active'))
                                        ->default(true)
                                        ->inline(false)
                                        ->columnSpan(1),

                                ])
                                ->defaultItems(1)
                                ->reorderable('sort_order') // تفعيل إعادة الترتيب بالسحب والإفلات
                                ->cloneable() // السماح بنسخ القيم لتسريع الإدخال
                                ->addActionLabel(__('lang.add_new_value'))
                                ->collapsible()
                                ->itemLabel(fn(array $state): ?string => $state['value'] ?? null) // عرض القيمة كعنوان للعنصر عند الطي
                                ->columnSpanFull(),
                        ]),
                ])
            ]);
    }
}
