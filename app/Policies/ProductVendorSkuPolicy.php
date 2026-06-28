<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProductVendorSku;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductVendorSkuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductVendorSku');
    }

    public function view(AuthUser $authUser, ProductVendorSku $productVendorSku): bool
    {
        return $authUser->can('View:ProductVendorSku');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductVendorSku');
    }

    public function update(AuthUser $authUser, ProductVendorSku $productVendorSku): bool
    {
        return $authUser->can('Update:ProductVendorSku');
    }

    public function delete(AuthUser $authUser, ProductVendorSku $productVendorSku): bool
    {
        return $authUser->can('Delete:ProductVendorSku');
    }

    public function restore(AuthUser $authUser, ProductVendorSku $productVendorSku): bool
    {
        return $authUser->can('Restore:ProductVendorSku');
    }

    public function forceDelete(AuthUser $authUser, ProductVendorSku $productVendorSku): bool
    {
        return $authUser->can('ForceDelete:ProductVendorSku');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductVendorSku');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductVendorSku');
    }

    public function replicate(AuthUser $authUser, ProductVendorSku $productVendorSku): bool
    {
        return $authUser->can('Replicate:ProductVendorSku');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductVendorSku');
    }

}