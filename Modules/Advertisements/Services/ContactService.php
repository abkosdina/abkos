<?php

namespace Modules\Advertisements\Services;

use Illuminate\Support\Facades\DB;
use Modules\Advertisements\Events\AdvertisementContactCreated;
use Modules\Advertisements\Models\AdvertisementContact;

class ContactService
{
    /**
     * Record a contact inquiry for an advertisement
     *
     * @param int $advertisementId
     * @param int|null $userId
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param string $message
     * @param string|null $ip
     * @param string|null $device
     * @param string|null $sessionId
     * @return bool
     */
    public function recordContact(
        int $advertisementId,
        ?int $userId,
        string $name,
        string $email,
        ?string $phone,
        string $message,
        ?string $ip = null,
        ?string $device = null,
        ?string $sessionId = null
    ): bool {
        try {
            // Create the contact record
            $contact = AdvertisementContact::create([
                'advertisement_id' => $advertisementId,
                'user_id' => $userId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'ip' => $ip,
                'device' => $device,
                'session_id' => $sessionId,
                'status' => 'pending',
            ]);

            // Increment the contacts_count on the advertisement
            DB::table('advertisements')->where('id', $advertisementId)->increment('contacts_count');

            // Dispatch event for listeners (notifications, emails, etc.)
            event(new AdvertisementContactCreated($contact));

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to record advertisement contact', [
                'advertisement_id' => $advertisementId,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
