<?php

namespace Modules\Advertisements\Policies;

use App\Models\User;
use Modules\Advertisements\Models\Advertisement;

/**
 * View Advertisement Policy
 */
class ViewAdvertisementPolicy
{
    public function view(User $user, Advertisement $advertisement): bool
    {
        // Owner can always view own advertisements
        if ($advertisement->user_id === $user->id) {
            return true;
        }

        // Only published and non-deleted can be viewed by others
        if ($advertisement->status->value === 'Published') {
            return true;
        }

        // Operators and above can view pending
        if (in_array($advertisement->status->value, ['PendingReview', 'NeedCorrection'])) {
            return $user->hasAnyRole(['operator', 'senior-operator', 'moderator', 'admin', 'super-admin']);
        }

        // Admins can view anything
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        return false;
    }
}

/**
 * Create Advertisement Policy
 */
class CreateAdvertisementPolicy
{
    public function create(User $user): bool
    {
        // Users can create advertisements
        return $user->hasRole('user') || $user->hasAnyRole(['admin', 'super-admin']);
    }
}

/**
 * Update Advertisement Policy
 */
class UpdateAdvertisementPolicy
{
    public function update(User $user, Advertisement $advertisement): bool
    {
        // Owner can update their own draft advertisements
        if ($advertisement->user_id === $user->id && $advertisement->status->value === 'Draft') {
            return true;
        }

        // Admins can update any advertisement
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        return false;
    }
}

/**
 * Delete Advertisement Policy
 */
class DeleteAdvertisementPolicy
{
    public function delete(User $user, Advertisement $advertisement): bool
    {
        // Owner can delete their own draft advertisements
        if ($advertisement->user_id === $user->id && $advertisement->status->value === 'Draft') {
            return true;
        }

        // Admins can delete
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        return false;
    }
}

/**
 * Approve Advertisement Policy
 */
class ApproveAdvertisementPolicy
{
    public function approve(User $user, Advertisement $advertisement): bool
    {
        // Only pending review advertisements can be approved
        if ($advertisement->status->value !== 'PendingReview') {
            return false;
        }

        // Operators and above can approve
        return $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }
}

/**
 * Reject Advertisement Policy
 */
class RejectAdvertisementPolicy
{
    public function reject(User $user, Advertisement $advertisement): bool
    {
        // Only pending review advertisements can be rejected
        if ($advertisement->status->value !== 'PendingReview') {
            return false;
        }

        // Operators and above can reject
        return $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }
}

/**
 * Publish Advertisement Policy
 */
class PublishAdvertisementPolicy
{
    public function publish(User $user, Advertisement $advertisement): bool
    {
        // Only approved advertisements can be published
        if ($advertisement->status->value !== 'Approved') {
            return false;
        }

        // Operators and above can publish
        return $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }
}

/**
 * Archive Advertisement Policy
 */
class ArchiveAdvertisementPolicy
{
    public function archive(User $user, Advertisement $advertisement): bool
    {
        $archivableStates = ['Published', 'Rejected', 'Expired', 'Sold'];

        // Only specific states can be archived
        if (!in_array($advertisement->status->value, $archivableStates)) {
            return false;
        }

        // Owner can archive their own
        if ($advertisement->user_id === $user->id) {
            return true;
        }

        // Operators and above can archive
        if ($user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin'])) {
            return true;
        }

        return false;
    }
}

/**
 * Restore Advertisement Policy
 */
class RestoreAdvertisementPolicy
{
    public function restore(User $user, Advertisement $advertisement): bool
    {
        // Only archived advertisements can be restored
        if ($advertisement->status->value !== 'Archived') {
            return false;
        }

        // Senior operators and admins can restore
        return $user->hasAnyRole(['senior-operator', 'admin', 'super-admin']);
    }
}

/**
 * Pause Advertisement Policy
 */
class PauseAdvertisementPolicy
{
    public function pause(User $user, Advertisement $advertisement): bool
    {
        // Only published advertisements can be paused
        if ($advertisement->status->value !== 'Published') {
            return false;
        }

        // Owner can pause their own
        if ($advertisement->user_id === $user->id) {
            return true;
        }

        // Operators and above can pause
        return $user->hasAnyRole(['operator', 'senior-operator', 'admin', 'super-admin']);
    }
}
