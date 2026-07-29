<?php

namespace Modules\Advertisements\Models;

use App\Models\User;
use App\Traits\HasJalaliDates;
use Illuminate\Database\Eloquent\Model;

class AdvertisementContact extends Model
{
    use HasJalaliDates;

    protected $table = 'advertisement_contacts';

    protected $fillable = [
        'advertisement_id',
        'user_id',
        'name',
        'email',
        'phone',
        'message',
        'status',
        'ip',
        'session_id',
        'device',
        'responded_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Relationship: belongs to Advertisement
     */
    public function advertisement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id');
    }

    /**
     * Relationship: belongs to User (nullable - for guest inquiries)
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Get pending contacts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get responded contacts
     */
    public function scopeResponded($query)
    {
        return $query->where('status', 'responded');
    }

    /**
     * Scope: Get closed contacts
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope: Get contacts for specific advertisement
     */
    public function scopeForAdvertisement($query, $advertisementId)
    {
        return $query->where('advertisement_id', $advertisementId);
    }

    /**
     * Mark contact as responded
     */
    public function markAsResponded(): void
    {
        $this->update([
            'status' => 'responded',
            'responded_at' => now(),
        ]);
    }

    /**
     * Mark contact as closed
     */
    public function markAsClosed(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Get created_at in Jalali format
     */
    public function getCreatedAtJalali(): ?string
    {
        return $this->getJalaliDate('created_at');
    }

    /**
     * Get created_at in Jalali format with month name
     */
    public function getCreatedAtJalaliFormatted(string $format = 'd M Y H:i'): ?string
    {
        $jalaliDate = $this->getJalaliDate('created_at');
        if (!$jalaliDate) {
            return null;
        }

        // For datetime, we need to extract time as well
        $carbon = $this->getAttribute('created_at');
        if ($carbon) {
            $time = $carbon->format('H:i');
            $formatted = self::formatJalaliDate($jalaliDate, 'd M Y');
            return "$formatted $time";
        }

        return self::formatJalaliDate($jalaliDate, $format);
    }
}
