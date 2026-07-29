<?php

namespace Modules\Banks\Tests\Unit;

use Modules\Banks\Services\BankPlanService;
use PHPUnit\Framework\TestCase;

class BankPlanServiceTest extends TestCase
{
    public function test_it_groups_bank_plans_by_bank_code(): void
    {
        $service = new BankPlanService();

        $banks = collect([
            (object) ['code' => 'melli', 'name' => 'بانک ملی ایران'],
            (object) ['code' => 'mellat', 'name' => 'بانک ملت'],
        ]);

        $plans = collect([
            (object) ['bank_code' => 'melli', 'name' => 'اعتبار ملی'],
            (object) ['bank_code' => 'melli', 'name' => 'طرح رفاه'],
            (object) ['bank_code' => 'mellat', 'name' => 'طرح رفاه ملت'],
        ]);

        $result = $service->groupPlansByBankCode($banks, $plans);

        $this->assertSame([
            'melli' => ['اعتبار ملی', 'طرح رفاه'],
            'mellat' => ['طرح رفاه ملت'],
        ], $result);
    }
}
