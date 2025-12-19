<?php

namespace App\Filament\Resources\Products\Schemas\Helpers;

use App\Models\Attribute;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;

class AttributeValueFieldBuilder
{
    /**
     * Build value field based on attribute input type
     */
    public static function make(?int $attributeId): array
    {
        if (!$attributeId) {
            return [
                TextInput::make('value')
                    ->label(__('lang.value'))
                    ->placeholder(__('lang.select_attribute_first'))
                    ->disabled()
                    ->columnSpan(12),
            ];
        }

        $attribute = Attribute::with('values')->find($attributeId);
        if (!$attribute) {
            return [
                TextInput::make('value')
                    ->label(__('lang.value'))
                    ->placeholder(__('lang.attribute_not_found'))
                    ->disabled()
                    ->columnSpan(12),
            ];
        }

        $type = $attribute->input_type;
        $choiceOptions = null;
        if ($attribute->isChoiceType()) {
            $choiceOptions = AttributeHelpers::buildChoiceOptionsFromValues($attribute);
        }

        return match ($type) {
            Attribute::$INPUT_TYPES['NUMBER'] => self::numberField($attribute),
            Attribute::$INPUT_TYPES['SELECT'] => self::selectField($attribute, $choiceOptions),
            Attribute::$INPUT_TYPES['RADIO'] => self::radioField($attribute, $choiceOptions),
            Attribute::$INPUT_TYPES['BOOLEAN'] => self::booleanField($attribute),
            Attribute::$INPUT_TYPES['DATE'] => self::dateField($attribute),
            default => self::textField($attribute),
        };
    }

    private static function numberField(Attribute $attribute): array
    {
        return [
            TextInput::make('value')
                ->label(__('lang.value'))
                ->numeric()
                ->inputMode('decimal')
                ->required($attribute->is_required)
                ->columnSpan(12),
        ];
    }

    private static function selectField(Attribute $attribute, ?array $choiceOptions): array
    {
        return [
            Select::make('value')
                ->label(__('lang.value'))
                ->options($choiceOptions ?? [])
                ->searchable()
                ->preload()
                ->native(false)
                ->placeholder(__('lang.choose_value'))
                ->required($attribute->is_required)
                ->columnSpan(12),
        ];
    }

    private static function radioField(Attribute $attribute, ?array $choiceOptions): array
    {
        return [
            Radio::make('value')
                ->label(__('lang.value'))
                ->options($choiceOptions ?? [])
                ->required($attribute->is_required)
                ->inline()
                ->columnSpan(12),
        ];
    }

    private static function booleanField(Attribute $attribute): array
    {
        return [
            Toggle::make('value')
                ->label(__('lang.value'))
                ->inline(false)
                ->required($attribute->is_required)
                ->dehydrateStateUsing(fn($state) => $state ? '1' : '0')
                ->afterStateHydrated(function (Set $set, $state) {
                    $set('value', ($state === '1' || $state === 1 || $state === true));
                })
                ->columnSpan(12),
        ];
    }

    private static function dateField(Attribute $attribute): array
    {
        return [
            DatePicker::make('value')
                ->label(__('lang.value'))
                ->native(false)
                ->required($attribute->is_required)
                ->dehydrateStateUsing(function ($state) {
                    if (empty($state)) {
                        return null;
                    }
                    try {
                        return \Carbon\Carbon::parse($state)->format('Y-m-d');
                    } catch (\Throwable) {
                        return $state;
                    }
                })
                ->columnSpan(12),
        ];
    }

    private static function textField(Attribute $attribute): array
    {
        return [
            TextInput::make('value')
                ->label(__('lang.value'))
                ->required($attribute->is_required)
                ->maxLength(255)
                ->columnSpan(12),
        ];
    }
}
