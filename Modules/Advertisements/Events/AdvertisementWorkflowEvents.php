<?php

namespace Modules\Advertisements\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Advertisements\Models\Advertisement;

/**
 * Advertisement Submitted Event
 */
class AdvertisementSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Approved Event
 */
class AdvertisementApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Rejected Event
 */
class AdvertisementRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Correction Requested Event
 */
class AdvertisementCorrectionRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Published Event
 */
class AdvertisementPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Paused Event
 */
class AdvertisementPaused
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Resumed Event
 */
class AdvertisementResumed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Archived Event
 */
class AdvertisementArchived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Restored Event
 */
class AdvertisementRestored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Expired Event
 */
class AdvertisementExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}

/**
 * Advertisement Sold Event
 */
class AdvertisementSold
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Advertisement $advertisement
    ) {}
}
