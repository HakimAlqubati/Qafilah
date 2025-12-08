<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label(__('lang.confirm_order'))
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->visible(fn() => $this->record->status === Order::STATUS_PENDING)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->confirm();
                    Notification::make()
                        ->title(__('lang.order_confirmed'))
                        ->success()
                        ->send();
                }),

            Action::make('cancel')
                ->label(__('lang.cancel_order'))
                ->icon(Heroicon::OutlinedXMark)
                ->color('danger')
                ->visible(fn() => $this->record->isCancellable())
                ->requiresConfirmation()
                ->modalHeading(__('lang.cancel_order'))
                ->modalDescription(__('lang.cancel_order_confirmation'))
                ->action(function () {
                    $this->record->cancel();
                    Notification::make()
                        ->title(__('lang.order_cancelled'))
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
