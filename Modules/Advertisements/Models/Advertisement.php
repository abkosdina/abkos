<?php

namespace Modules\Advertisements\Models;

use App\Models\User;
use App\Traits\HasJalaliDates;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Advertisements\Database\Factories\AdvertisementFactory;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Enums\AdvertisementVisibility;
use Modules\Advertisements\Models\AdvertisementDocument;
use Modules\Advertisements\Models\AdvertisementWorkflowAudit;

class Advertisement extends Model
{
    use HasFactory, SoftDeletes, HasJalaliDates;

    protected $table = 'advertisements';

    protected $fillable = [
        'uuid',
        'advertisement_number',
        'priority',
        'loan_product_id',
        'user_id',
        'seller_user_id',
        'title',
        'slug',
        'short_description',
        'description',
        'price',
        'currency',
        'status',
        'workflow_instance_id',
        'published_at',
        'expires_at',
        'visibility',
        'created_by',
        'province_id',
        'city_id',
        'updated_by',
        'deleted_by',
        'views_count',
        'contacts_count',
    ];

    protected $casts = [
        'status' => AdvertisementStatus::class,
        'visibility' => AdvertisementVisibility::class,
        'priority' => 'integer',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'published_at_jalali',
        'published_at_label',
        'expires_at_jalali',
        'expires_at_label',
        'created_at_jalali',
        'created_at_label',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $advertisement): void {
            if (empty($advertisement->uuid)) {
                $advertisement->uuid = (string) Str::uuid();
            }

            if (empty($advertisement->advertisement_number)) {
                $advertisement->advertisement_number = 'ADV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
            }

            if (empty($advertisement->title)) {
                $advertisement->title = 'Untitled Advertisement';
            }

            if (empty($advertisement->slug)) {
                $advertisement->slug = Str::slug($advertisement->title) . '-' . $advertisement->id;
            }
        });

        static::created(function (self $advertisement): void {
            try {
                app(\Modules\Advertisements\Adapters\AdvertisementWorkflowAdapter::class)
                    ->ensureWorkflowInstance($advertisement);
            } catch (\Throwable $e) {
                // Keep advertisement creation successful even if workflow seeding fails.
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', AdvertisementStatus::Published->value);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('visibility', AdvertisementVisibility::Public->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at')->whereNull('deleted_at');
    }

    public function scopeOwnedBy(Builder $query, int|string $userId): Builder
    {
        return $query->where(function ($q) use ($userId): void {
            $q->where('user_id', $userId)
                ->orWhere('seller_user_id', $userId);
        });
    }

    public function scopeCreatedOn(Builder $query, ?string $date = null): Builder
    {
        return $query->whereDate('created_at', $date ?? now()->toDateString());
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function province(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Province::class, 'province_id');
    }

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class, 'city_id');
    }

    public function loanOffer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LoanOffer::class, 'advertisement_id');
    }

    /**
     * Get the workflow instance for this advertisement
     * 
     * Links to the generic workflow engine
     */
    public function workflowInstance(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\WorkflowInstance::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdvertisementDocument::class, 'advertisement_id');
    }

    public function workflowAudits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdvertisementWorkflowAudit::class, 'advertisement_uuid', 'uuid');
    }

    public function getPublishedAtJalaliAttribute(): ?string
    {
        return $this->getJalaliDate('published_at');
    }

    public function getPublishedAtLabelAttribute(): ?string
    {
        return $this->getJalaliDateFormatted('published_at', 'd M Y');
    }

    public function getExpiresAtJalaliAttribute(): ?string
    {
        return $this->getJalaliDate('expires_at');
    }

    public function getExpiresAtLabelAttribute(): ?string
    {
        return $this->getJalaliDateFormatted('expires_at', 'd M Y');
    }

    public function getCreatedAtJalaliAttribute(): ?string
    {
        return $this->getJalaliDate('created_at');
    }

    public function getCreatedAtLabelAttribute(): ?string
    {
        return $this->getJalaliDateFormatted('created_at', 'd M Y');
    }

    public function setPublishedAtAttribute($value): void
    {
        $this->attributes['published_at'] = $this->normalizeJalaliDateInput($value);
    }

    public function setExpiresAtAttribute($value): void
    {
        $this->attributes['expires_at'] = $this->normalizeJalaliDateInput($value);
    }

    protected function normalizeJalaliDateInput($value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}/', $trimmed)) {
            return $trimmed;
        }

        $year = (int) substr($trimmed, 0, 4);
        if ($year >= 1300 && $year <= 1600) {
            $gregorian = self::jalaliToGregorian($trimmed);
            return $gregorian ? $gregorian->toDateTimeString() : $trimmed;
        }

        return Carbon::parse($trimmed)->toDateTimeString();
    }

    /**
     * Relationship: has many contacts/inquiries
     */
    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdvertisementContact::class, 'advertisement_id');
    }

    /**
     * Increment contacts count
     */
    public function incrementContactsCount(): void
    {
        $this->increment('contacts_count');
    }

    /**
     * Increment views count
     */
    public function incrementViewsCount(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get published_at date in Jalali format
     */
    public function getPublishedAtJalali(): ?string
    {
        return $this->getJalaliDate('published_at');
    }

    /**
     * Get published_at date in Jalali format with month name
     */
    public function getPublishedAtJalaliFormatted(string $format = 'd M Y H:i'): ?string
    {
        return $this->getJalaliDateFormatted('published_at', $format);
    }

    /**
     * Get expires_at date in Jalali format
     */
    public function getExpiresAtJalali(): ?string
    {
        return $this->getJalaliDate('expires_at');
    }

    /**
     * Get expires_at date in Jalali format with month name
     */
    public function getExpiresAtJalaliFormatted(string $format = 'd M Y H:i'): ?string
    {
        return $this->getJalaliDateFormatted('expires_at', $format);
    }

    /**
     * Get created_at date in Jalali format
     */
    public function getCreatedAtJalali(): ?string
    {
        return $this->getJalaliDate('created_at');
    }

    /**
     * Get created_at date in Jalali format with month name
     */
    public function getCreatedAtJalaliFormatted(string $format = 'd M Y H:i'): ?string
    {
        return $this->getJalaliDateFormatted('created_at', $format);
    }

    protected static function newFactory()
    {
        return AdvertisementFactory::new();
    }
}
