<?php

namespace Modules\UserManagement\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('administrator') || $user->id === $model->id;
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('administrator') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('administrator');
    }
}
