<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Modules\Advertisements\Events\AdvertisementSubmitted;
use Modules\Advertisements\Events\AdvertisementApproved;
use Modules\Advertisements\Events\AdvertisementRejected;
use Modules\Advertisements\Events\AdvertisementCorrectionRequested;
use Modules\Advertisements\Events\AdvertisementPublished;
use Modules\Advertisements\Events\AdvertisementPaused;
use Modules\Advertisements\Events\AdvertisementResumed;
use Modules\Advertisements\Events\AdvertisementArchived;
use Modules\Advertisements\Events\AdvertisementRestored;
use Modules\Advertisements\Events\AdvertisementExpired;
use Modules\Advertisements\Events\AdvertisementSold;
use Modules\Advertisements\Models\AdvertisementLog;

/**
 * Activity Log Listener
 *
 * Logs all workflow state transitions to the activity log.
 */
class LogAdvertisementActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle submitted event
     */
    public function handleSubmitted(AdvertisementSubmitted $event): void
    {
        $this->logActivity($event->advertisement, 'submitted');
    }

    /**
     * Handle approved event
     */
    public function handleApproved(AdvertisementApproved $event): void
    {
        $this->logActivity($event->advertisement, 'approved');
    }

    /**
     * Handle rejected event
     */
    public function handleRejected(AdvertisementRejected $event): void
    {
        $this->logActivity($event->advertisement, 'rejected');
    }

    /**
     * Handle correction requested event
     */
    public function handleCorrectionRequested(AdvertisementCorrectionRequested $event): void
    {
        $this->logActivity($event->advertisement, 'correction_requested');
    }

    /**
     * Handle published event
     */
    public function handlePublished(AdvertisementPublished $event): void
    {
        $this->logActivity($event->advertisement, 'published');
    }

    /**
     * Handle paused event
     */
    public function handlePaused(AdvertisementPaused $event): void
    {
        $this->logActivity($event->advertisement, 'paused');
    }

    /**
     * Handle resumed event
     */
    public function handleResumed(AdvertisementResumed $event): void
    {
        $this->logActivity($event->advertisement, 'resumed');
    }

    /**
     * Handle archived event
     */
    public function handleArchived(AdvertisementArchived $event): void
    {
        $this->logActivity($event->advertisement, 'archived');
    }

    /**
     * Handle restored event
     */
    public function handleRestored(AdvertisementRestored $event): void
    {
        $this->logActivity($event->advertisement, 'restored');
    }

    /**
     * Handle expired event
     */
    public function handleExpired(AdvertisementExpired $event): void
    {
        $this->logActivity($event->advertisement, 'expired');
    }

    /**
     * Handle sold event
     */
    public function handleSold(AdvertisementSold $event): void
    {
        $this->logActivity($event->advertisement, 'sold');
    }

    /**
     * Log activity
     */
    protected function logActivity($advertisement, string $action): void
    {
        AdvertisementLog::create([
            'advertisement_id' => $advertisement->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'status' => $advertisement->status->value,
            ],
        ]);
    }
}

/**
 * Cache Refresh Listener
 *
 * Refreshes relevant caches when advertisements change state.
 */
class RefreshAdvertisementCache implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        $advertisement = $event->advertisement;

        // Clear advertisement cache
        Cache::forget("advertisement:{$advertisement->id}");
        Cache::forget("advertisement:uuid:{$advertisement->uuid}");

        // Clear listing caches
        Cache::forget('advertisements:published');
        Cache::forget('advertisements:pending');
        Cache::forget("advertisements:user:{$advertisement->user_id}");

        // Clear search index invalidation
        Cache::put("search_invalidate:advertisement:{$advertisement->id}", true, now()->addMinutes(5));
    }
}

/**
 * Search Index Refresh Listener
 *
 * Triggers search index refresh for published/unpublished advertisements.
 */
class RefreshSearchIndex implements ShouldQueue
{
    use InteractsWithQueue;

    protected $delay = 30; // Delay 30 seconds to batch updates

    public function handle($event): void
    {
        $advertisement = $event->advertisement;

        // Dispatch search index refresh job
        // This would integrate with Elasticsearch, Meilisearch, or similar
        // \Modules\Search\Jobs\IndexAdvertisementJob::dispatch($advertisement);
    }
}
