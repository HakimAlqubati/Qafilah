<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeHero extends Widget
{
    protected string $view = 'filament.widgets.welcome-hero';
      protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;
}
