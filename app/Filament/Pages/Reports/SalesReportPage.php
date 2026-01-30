<?php

namespace App\Filament\Pages\Reports;

use App\DTOs\Reports\Sales\SalesFilterDTO;
use App\Models\Order;
use App\Models\Vendor;
use App\Repositories\Reports\Sales\SalesRepositoryInterface;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;

class SalesReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ChartBar;

    protected string $view = 'filament.pages.reports.sales-report-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->subMonths(3)->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
            'status' => Order::STATUS_COMPLETED,
        ]);
    }

    public static function getNavigationLabel(): string
    {
        return __('lang.sales_report');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lang.reports');
    }

    public function getTitle(): string
    {
        return '';
        return __('lang.sales_report');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('lang.filters'))
                    ->schema([
                        DatePicker::make('start_date')
                            ->label(__('lang.start_date')),
                        DatePicker::make('end_date')
                            ->label(__('lang.end_date')),
                        Select::make('vendor_id')
                            ->label(__('lang.vendor'))
                            ->options(Vendor::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Select::make('status')
                            ->label(__('lang.status'))
                            ->options([
                                Order::STATUS_PENDING => __('lang.pending'),
                                Order::STATUS_PROCESSING => __('lang.processing'),
                                Order::STATUS_COMPLETED => __('lang.completed'),
                                Order::STATUS_CANCELLED => __('lang.cancelled'),
                                Order::STATUS_RETURNED => __('lang.returned'),
                            ])
                            ->default(Order::STATUS_COMPLETED),
                    ])
                    ->columns(4)
            ])
            ->statePath('data')
            ->live(); // Make form reactive
    }

    #[Computed]
    public function reportData()
    {
        $filter = SalesFilterDTO::fromArray($this->form->getState());

        $repository = app(SalesRepositoryInterface::class);

        return [
            'summary' => $repository->getSalesSummary($filter),
            // 'trends' => $repository->getSalesTrends($filter, 'daily'),
            'trends' => [],
            'top_products' => $repository->getTopProducts($filter, 5),
            'top_vendors' => $repository->getSalesByVendor($filter, 5),
        ];
    }
}
