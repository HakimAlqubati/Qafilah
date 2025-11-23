<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $locale;

    public function mount()
    {
        $this->locale = app()->getLocale();
    }

    public function updatedLocale($value)
    {
        session()->put('locale', $value);
        app()->setLocale($value);

        // Redirect to refresh the page with new locale
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('filament.partials.language-switcher');
    }
}
