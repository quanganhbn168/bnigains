<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GainsProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class GainsProfilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GainsProfile');
    }

    public function view(AuthUser $authUser, GainsProfile $gainsProfile): bool
    {
        return $authUser->can('View:GainsProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GainsProfile');
    }

    public function update(AuthUser $authUser, GainsProfile $gainsProfile): bool
    {
        return $authUser->can('Update:GainsProfile');
    }

    public function delete(AuthUser $authUser, GainsProfile $gainsProfile): bool
    {
        return $authUser->can('Delete:GainsProfile');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GainsProfile');
    }

    public function restore(AuthUser $authUser, GainsProfile $gainsProfile): bool
    {
        return $authUser->can('Restore:GainsProfile');
    }

    public function forceDelete(AuthUser $authUser, GainsProfile $gainsProfile): bool
    {
        return $authUser->can('ForceDelete:GainsProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GainsProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GainsProfile');
    }

    public function replicate(AuthUser $authUser, GainsProfile $gainsProfile): bool
    {
        return $authUser->can('Replicate:GainsProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GainsProfile');
    }

}