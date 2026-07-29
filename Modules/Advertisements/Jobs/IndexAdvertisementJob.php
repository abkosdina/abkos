<?php

namespace Modules\Advertisements\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IndexAdvertisementJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $advertisementId;

    public function __construct(int $advertisementId)
    {
        $this->advertisementId = $advertisementId;
    }

    public function handle(): void
    {
        // Best-effort: if a Search module exists, try to call its indexer
        try {
            if (class_exists('\Modules\\Search\\Services\\Indexer')) {
                $indexer = app('\Modules\\Search\\Services\\Indexer');
                $indexer->indexAdvertisement($this->advertisementId);
            } else {
                Log::info('IndexAdvertisementJob no-op: Search module missing', ['id' => $this->advertisementId]);
            }
        } catch (\Throwable $e) {
            Log::error('IndexAdvertisementJob failed: ' . $e->getMessage());
        }
    }
}
