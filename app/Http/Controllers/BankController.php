<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Modules\Banks\Models\Bank;
use Modules\Banks\Models\BankLoanProduct;
use Modules\Banks\Models\LoanProduct;
use Modules\Banks\Services\BankPlanService;

class BankController extends Controller
{
    public function __construct(protected BankPlanService $bankPlanService)
    {
    }

    public function loadLoans(): View
    {
        return view('ads.ads', [
            'banks' => $this->resolveBanks(),
            'bankPlans' => $this->resolveBankPlans(),
        ]);
    }

    public function banks(): JsonResponse
    {
        return response()->json([
            'banks' => $this->resolveBanks(),
            'bank_plans' => $this->resolveBankPlans(),
        ]);
    }

    protected function resolveBanks()
    {
        if (! Schema::hasTable('banks')) {
            return collect();
        }

        return Bank::query()
            ->select('id', 'code', 'name')
            ->orderBy('name')
            ->get();
    }

    protected function resolveBankPlans(): array
    {
        if (! Schema::hasTable('banks')) {
            return [];
        }

        $banks = Bank::query()->select('code', 'name')->orderBy('name')->get();

        if ($banks->isEmpty()) {
            return [];
        }

        if (Schema::hasTable('bank_loan_products')) {
            $planRows = BankLoanProduct::query()
                ->join('loan_products as lp', 'lp.id', '=', 'bank_loan_products.loan_product_id')
                ->join('banks', 'banks.id', '=', 'bank_loan_products.bank_id')
                ->where('bank_loan_products.status', 'active')
                ->select(
                    'bank_loan_products.id as id',
                    'banks.id as bank_id',
                    'banks.code as bank_code',
                    'bank_loan_products.name as name',
                    'bank_loan_products.duration_months',
                    'bank_loan_products.installment_count',
                    'bank_loan_products.interest_rate',
                    'bank_loan_products.down_payment_percent',
                    'lp.description as description'
                )
                ->orderBy('banks.code')
                ->orderBy('bank_loan_products.name')
                ->get();
        } elseif (Schema::hasTable('loan_products')) {
            $planRows = LoanProduct::query()
                ->join('banks', 'banks.id', '=', 'loan_products.bank_id')
                ->where('loan_products.status', 'active')
                ->where('loan_products.is_public', true)
                ->select(
                    'loan_products.id as id',
                    'banks.id as bank_id',
                    'banks.code as bank_code',
                    'loan_products.name as name',
                    'loan_products.duration_months',
                    'loan_products.interest_rate',
                    'loan_products.min_amount',
                    'loan_products.max_amount',
                    'loan_products.description as description'
                )
                ->orderBy('banks.code')
                ->orderBy('loan_products.name')
                ->get();
        } else {
            return [];
        }

        return $this->bankPlanService->groupPlansByBankCode($banks, $planRows);
    }
}
