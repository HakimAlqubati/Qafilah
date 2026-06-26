<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MerchantLoyaltySetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class MerchantLoyaltySettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MerchantLoyaltySetting');
    }

    public function view(AuthUser $authUser, MerchantLoyaltySetting $merchantLoyaltySetting): bool
    {
        return $authUser->can('View:MerchantLoyaltySetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MerchantLoyaltySetting');
    }

    public function update(AuthUser $authUser, MerchantLoyaltySetting $merchantLoyaltySetting): bool
    {
        return $authUser->can('Update:MerchantLoyaltySetting');
    }

    public function delete(AuthUser $authUser, MerchantLoyaltySetting $merchantLoyaltySetting): bool
    {
        return $authUser->can('Delete:MerchantLoyaltySetting');
    }

    public function restore(AuthUser $authUser, MerchantLoyaltySetting $merchantLoyaltySetting): bool
    {
        return $authUser->can('Restore:MerchantLoyaltySetting');
    }

    public function forceDelete(AuthUser $authUser, MerchantLoyaltySetting $merchantLoyaltySetting): bool
    {
        return $authUser->can('ForceDelete:MerchantLoyaltySetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MerchantLoyaltySetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MerchantLoyaltySetting');
    }

    public function replicate(AuthUser $authUser, MerchantLoyaltySetting $merchantLoyaltySetting): bool
    {
        return $authUser->can('Replicate:MerchantLoyaltySetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MerchantLoyaltySetting');
    }

}