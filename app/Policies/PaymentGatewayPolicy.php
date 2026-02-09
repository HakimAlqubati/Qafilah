<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PaymentGateway;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentGatewayPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaymentGateway');
    }

    public function view(AuthUser $authUser, PaymentGateway $paymentGateway): bool
    {
        return $authUser->can('View:PaymentGateway');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaymentGateway');
    }

    public function update(AuthUser $authUser, PaymentGateway $paymentGateway): bool
    {
        return $authUser->can('Update:PaymentGateway');
    }

    public function delete(AuthUser $authUser, PaymentGateway $paymentGateway): bool
    {
        return $authUser->can('Delete:PaymentGateway');
    }

    public function restore(AuthUser $authUser, PaymentGateway $paymentGateway): bool
    {
        return $authUser->can('Restore:PaymentGateway');
    }

    public function forceDelete(AuthUser $authUser, PaymentGateway $paymentGateway): bool
    {
        return $authUser->can('ForceDelete:PaymentGateway');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaymentGateway');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaymentGateway');
    }

    public function replicate(AuthUser $authUser, PaymentGateway $paymentGateway): bool
    {
        return $authUser->can('Replicate:PaymentGateway');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaymentGateway');
    }

}