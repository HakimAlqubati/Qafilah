<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;
    public function mount(): void
    {
        parent::mount();

        // Pre-fill the form with existing settings
        $this->form->fill(Setting::get()->pluck("value", "key")->toArray());
    }
    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        $finalSettings = [];
        foreach ($data as $key => $value) {

            $finalSettings[] = ["key" => $key, "value" => $value];
        }
         Setting::upsert($finalSettings, ["key"]);
    }
}
