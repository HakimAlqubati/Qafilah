<?php

namespace App\Repositories\Address;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Collection;

class CustomerAddressRepository
{
    public function listForCustomer(int $customerId): Collection
    {
        return CustomerAddress::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
    }

    public function createForCustomer(int $customerId, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($customerId, $data) {
            $payload = [
                'customer_id'  => $customerId,
                'type'         => $data['type'] ?? 'general',
                'city_id'      => $data['city_id'] ?? null,
                'district_id'  => $data['district_id'] ?? null,
                'address'      => $data['address'] ?? null,
                'latitude'     => $data['latitude'] ?? null,
                'longitude'    => $data['longitude'] ?? null,
                'is_default'   => 0,
            ];

            $hasDefault = CustomerAddress::query()
                ->where('customer_id', $customerId)
                ->where('is_default', 1)
                ->exists();
            $makeDefault = (bool) ($data['is_default'] ?? false);
            if (!$hasDefault || $makeDefault) {
                CustomerAddress::query()
                    ->where('customer_id', $customerId)
                    ->update(['is_default' => 0]);

                $payload['is_default'] = 1;
            }

            return CustomerAddress::create($payload)->refresh();
        });
    }


    public function updateForCustomer(int $customerId, int $addressId, array $data): CustomerAddress
    {
        $address = CustomerAddress::query()
            ->where('id', $addressId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        $address->fill($data)->save();

        return $address->refresh();
    }

    public function deleteForCustomer(int $customerId, int $addressId): void
    {
        CustomerAddress::query()
            ->where('id', $addressId)
            ->where('customer_id', $customerId)
            ->firstOrFail()
            ->delete();
    }

    public function setDefaultForCustomer(int $customerId, int $addressId): CustomerAddress
    {
        return DB::transaction(function () use ($customerId, $addressId) {
            $address = CustomerAddress::query()
                ->where('id', $addressId)
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->firstOrFail();

            CustomerAddress::query()
                ->where('customer_id', $customerId)
                ->update(['is_default' => 0]);

            $address->update(['is_default' => 1]);

            return $address->refresh();
        });
    }
}
