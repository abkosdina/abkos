<?php

namespace Modules\Advertisements\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Advertisements\Events\AdvertisementCreated;
use Modules\Advertisements\Events\AdvertisementUpdated;
use Modules\Advertisements\Events\AdvertisementSubmitted;
use Modules\Advertisements\Events\AdvertisementApproved;
use Modules\Advertisements\Events\AdvertisementRejected;
use Modules\Advertisements\Events\AdvertisementPublished;
use Modules\Advertisements\Events\AdvertisementPaused;
use Modules\Advertisements\Events\AdvertisementResumed;
use Modules\Advertisements\Events\AdvertisementArchived;
use Modules\Advertisements\Events\AdvertisementDeleted;

class CreateActivityLogListener
{
    public function handle(object $event): void
    {
        $action = match (true) {
            $event instanceof AdvertisementCreated => 'created',
            $event instanceof AdvertisementUpdated => 'updated',
            $event instanceof AdvertisementSubmitted => 'submitted',
            $event instanceof AdvertisementApproved => 'approved',
            $event instanceof AdvertisementRejected => 'rejected',
            $event instanceof AdvertisementPublished => 'published',
            $event instanceof AdvertisementPaused => 'paused',
            $event instanceof AdvertisementResumed => 'resumed',
            $event instanceof AdvertisementArchived => 'archived',
            $event instanceof AdvertisementDeleted => 'deleted',
            default => 'unknown',
        };

        Log::info('Advertisement event', ['action' => $action, 'advertisement_id' => $event->advertisement->id]);
    }
}
