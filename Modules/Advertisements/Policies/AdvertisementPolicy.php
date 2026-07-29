<?php

namespace Modules\Advertisements\Policies;

use App\Models\User;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementPolicy
{
    public function view(User $user, Advertisement $advertisement): bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        return $advertisement->user_id === $user->id;
    }

    public function submit(User $user, Advertisement $advertisement): bool
    {
        $status = $advertisement->state ?? $advertisement->status;
        if ($status instanceof \BackedEnum) {
            $status = $status->value;
        }

        return $advertisement->user_id === $user->id && in_array($status, ['Draft', 'NeedCorrection'], true);
    }

    public function approve(User $user, Advertisement $advertisement): bool
    {
        return $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }

    public function reject(User $user, Advertisement $advertisement): bool
    {
        return $this->approve($user, $advertisement);
    }

    public function correction(User $user, Advertisement $advertisement): bool
    {
        return $this->approve($user, $advertisement);
    }

    public function publish(User $user, Advertisement $advertisement): bool
    {
        return $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }

    public function pause(User $user, Advertisement $advertisement): bool
    {
        if ($advertisement->status instanceof \BackedEnum) {
            $status = $advertisement->status->value;
        } else {
            $status = $advertisement->status;
        }

        if ($status !== 'Published') {
            return false;
        }

        return $advertisement->user_id === $user->id || $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }

    public function resume(User $user, Advertisement $advertisement): bool
    {
        if ($advertisement->status instanceof \BackedEnum) {
            $status = $advertisement->status->value;
        } else {
            $status = $advertisement->status;
        }

        if ($status !== 'Paused') {
            return false;
        }

        return $advertisement->user_id === $user->id || $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }

    public function archive(User $user, Advertisement $advertisement): bool
    {
        $archivableStates = ['Published', 'Rejected', 'Expired', 'Sold'];

        $status = $advertisement->status instanceof \BackedEnum ? $advertisement->status->value : $advertisement->status;
        if (! in_array($status, $archivableStates, true)) {
            return false;
        }

        return $advertisement->user_id === $user->id || $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }

    public function restore(User $user, Advertisement $advertisement): bool
    {
        return $user->hasAnyRole(['senior-operator', 'admin', 'super-admin']);
    }

    public function sold(User $user, Advertisement $advertisement): bool
    {
        if ($advertisement->status instanceof \BackedEnum) {
            $status = $advertisement->status->value;
        } else {
            $status = $advertisement->status;
        }

        if ($status !== 'Published') {
            return false;
        }

        return $advertisement->user_id === $user->id || $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }

    public function update(User $user, Advertisement $advertisement): bool
    {
        $status = $advertisement->status instanceof \BackedEnum ? $advertisement->status->value : $advertisement->status;
        return ($advertisement->user_id === $user->id && in_array($status, ['Draft', 'NeedCorrection'], true))
            || $user->hasAnyRole(['admin', 'super-admin']);
    }

    public function delete(User $user, Advertisement $advertisement): bool
    {
        $status = $advertisement->status instanceof \BackedEnum ? $advertisement->status->value : $advertisement->status;
        return ($advertisement->user_id === $user->id && in_array($status, ['Draft', 'NeedCorrection'], true))
            || $user->hasAnyRole(['admin', 'super-admin']);
    }
}
