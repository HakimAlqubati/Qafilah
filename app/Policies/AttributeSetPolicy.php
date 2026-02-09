<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AttributeSet;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributeSetPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AttributeSet');
    }

    public function view(AuthUser $authUser, AttributeSet $attributeSet): bool
    {
        return $authUser->can('View:AttributeSet');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AttributeSet');
    }

    public function update(AuthUser $authUser, AttributeSet $attributeSet): bool
    {
        return $authUser->can('Update:AttributeSet');
    }

    public function delete(AuthUser $authUser, AttributeSet $attributeSet): bool
    {
        return $authUser->can('Delete:AttributeSet');
    }

    public function restore(AuthUser $authUser, AttributeSet $attributeSet): bool
    {
        return $authUser->can('Restore:AttributeSet');
    }

    public function forceDelete(AuthUser $authUser, AttributeSet $attributeSet): bool
    {
        return $authUser->can('ForceDelete:AttributeSet');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AttributeSet');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AttributeSet');
    }

    public function replicate(AuthUser $authUser, AttributeSet $attributeSet): bool
    {
        return $authUser->can('Replicate:AttributeSet');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AttributeSet');
    }

}