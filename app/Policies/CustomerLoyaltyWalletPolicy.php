<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerLoyaltyWallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerLoyaltyWalletPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CustomerLoyaltyWallet');
    }

    public function view(AuthUser $authUser, CustomerLoyaltyWallet $customerLoyaltyWallet): bool
    {
        return $authUser->can('View:CustomerLoyaltyWallet');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CustomerLoyaltyWallet');
    }

    public function update(AuthUser $authUser, CustomerLoyaltyWallet $customerLoyaltyWallet): bool
    {
        return $authUser->can('Update:CustomerLoyaltyWallet');
    }

    public function delete(AuthUser $authUser, CustomerLoyaltyWallet $customerLoyaltyWallet): bool
    {
        return $authUser->can('Delete:CustomerLoyaltyWallet');
    }

    public function restore(AuthUser $authUser, CustomerLoyaltyWallet $customerLoyaltyWallet): bool
    {
        return $authUser->can('Restore:CustomerLoyaltyWallet');
    }

    public function forceDelete(AuthUser $authUser, CustomerLoyaltyWallet $customerLoyaltyWallet): bool
    {
        return $authUser->can('ForceDelete:CustomerLoyaltyWallet');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CustomerLoyaltyWallet');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CustomerLoyaltyWallet');
    }

    public function replicate(AuthUser $authUser, CustomerLoyaltyWallet $customerLoyaltyWallet): bool
    {
        return $authUser->can('Replicate:CustomerLoyaltyWallet');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CustomerLoyaltyWallet');
    }

}