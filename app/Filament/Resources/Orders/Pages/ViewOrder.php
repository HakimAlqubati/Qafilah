<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewOrder extends ViewRecord
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
                    $this->refreshFormData(['status']);
                }),

            Action::make('mark_processing')
                ->label(__('lang.mark_processing'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->visible(fn() => $this->record->status === Order::STATUS_CONFIRMED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->changeStatus(Order::STATUS_PROCESSING);
                    Notification::make()
                        ->title(__('lang.status_updated'))
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('mark_shipped')
                ->label(__('lang.mark_shipped'))
                ->icon(Heroicon::OutlinedTruck)
                ->color('info')
                ->visible(fn() => $this->record->status === Order::STATUS_PROCESSING)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->changeStatus(Order::STATUS_SHIPPED);
                    $this->record->update([
                        'shipped_at' => now(),
                        'shipping_status' => Order::SHIPPING_SHIPPED,
                    ]);
                    Notification::make()
                        ->title(__('lang.status_updated'))
                        ->success()
                        ->send();
                    $this->refreshFormData(['status', 'shipping_status']);
                }),

            Action::make('mark_delivered')
                ->label(__('lang.mark_delivered'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn() => in_array($this->record->status, [Order::STATUS_SHIPPED]))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->changeStatus(Order::STATUS_DELIVERED);
                    $this->record->update([
                        'delivered_at' => now(),
                        'shipping_status' => Order::SHIPPING_DELIVERED,
                    ]);
                    Notification::make()
                        ->title(__('lang.status_updated'))
                        ->success()
                        ->send();
                    $this->refreshFormData(['status', 'shipping_status']);
                }),

            Action::make('mark_completed')
                ->label(__('lang.mark_completed'))
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->visible(fn() => $this->record->status === Order::STATUS_DELIVERED)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->changeStatus(Order::STATUS_COMPLETED);
                    Notification::make()
                        ->title(__('lang.order_completed'))
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
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
                    $this->refreshFormData(['status']);
                }),

            EditAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
