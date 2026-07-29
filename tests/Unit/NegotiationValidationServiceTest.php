<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Advertisements\Models\Advertisement;
use Modules\Negotiation\Enums\NegotiationStatus;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Services\NegotiationValidationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NegotiationValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rejects_self_negotiation_for_the_advertisement_owner(): void
    {
        $buyer = new User();
        $buyer->forceFill(['id' => 1]);
        $advertisement = new Advertisement();
        $advertisement->forceFill([
            'id' => 10,
            'user_id' => 1,
            'seller_user_id' => 1,
            'status' => 'Published',
            'visibility' => 'Public',
        ]);

        $service = new NegotiationValidationService();

        $this->expectException(\InvalidArgumentException::class);
        $service->validateNegotiationCreation($buyer, $advertisement);
    }

    #[Test]
    public function it_allows_negotiation_for_valid_published_advertisement(): void
    {
        $buyer = new User();
        $buyer->forceFill(['id' => 2]);
        $advertisement = new Advertisement();
        $advertisement->forceFill([
            'id' => 11,
            'user_id' => 1,
            'seller_user_id' => 1,
            'status' => 'Published',
            'visibility' => 'Public',
        ]);

        $service = new NegotiationValidationService();

        $service->validateNegotiationCreation($buyer, $advertisement);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_rejects_a_second_active_negotiation_for_the_same_advertisement(): void
    {
        $buyer = new User();
        $buyer->forceFill(['id' => 2]);
        $advertisement = new Advertisement();
        $advertisement->forceFill([
            'id' => 12,
            'user_id' => 1,
            'seller_user_id' => 1,
            'status' => 'Published',
            'visibility' => 'Public',
        ]);
        $advertisement->save();

        Negotiation::query()->create([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'advertisement_id' => $advertisement->id,
            'buyer_id' => 99,
            'seller_id' => 1,
            'status' => NegotiationStatus::Active,
        ]);

        $service = new NegotiationValidationService();

        $this->expectException(\InvalidArgumentException::class);
        $service->validateNegotiationCreation($buyer, $advertisement);
    }
}
