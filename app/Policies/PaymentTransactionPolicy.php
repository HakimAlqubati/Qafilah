<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PaymentTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaymentTransaction');
    }

    public function view(AuthUser $authUser, PaymentTransaction $paymentTransaction): bool
    {
        return $authUser->can('View:PaymentTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaymentTransaction');
    }

    public function update(AuthUser $authUser, PaymentTransaction $paymentTransaction): bool
    {
        return $authUser->can('Update:PaymentTransaction');
    }

    public function delete(AuthUser $authUser, PaymentTransaction $paymentTransaction): bool
    {
        return $authUser->can('Delete:PaymentTransaction');
    }

    public function restore(AuthUser $authUser, PaymentTransaction $paymentTransaction): bool
    {
        return $authUser->can('Restore:PaymentTransaction');
    }

    public function forceDelete(AuthUser $authUser, PaymentTransaction $paymentTransaction): bool
    {
        return $authUser->can('ForceDelete:PaymentTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaymentTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaymentTransaction');
    }

    public function replicate(AuthUser $authUser, PaymentTransaction $paymentTransaction): bool
    {
        return $authUser->can('Replicate:PaymentTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaymentTransaction');
    }

}