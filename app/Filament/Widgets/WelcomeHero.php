<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class WelcomeHero extends Widget
{
  use HasWidgetShield;
  protected string $view = 'filament.widgets.welcome-hero';
  protected int|string|array $columnSpan = 'full';

  protected static ?int $sort = -10;
}
