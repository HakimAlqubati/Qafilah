<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;


class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['name'] ?? null) !== $this->record->name) {
            $base = Str::slug($data['name'] ?? '');
            $slug = $base;
            $i = 2;
            while (Product::where('slug', $slug)
                ->whereKeyNot($this->record->getKey())
                ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }

}
