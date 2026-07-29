<?php

namespace Modules\Advertisements\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Advertisements\Actions\ArchiveAdvertisementAction;
use Modules\Advertisements\Actions\CreateAdvertisementAction;
use Modules\Advertisements\Actions\DeleteAdvertisementAction;
use Modules\Advertisements\Actions\PauseAdvertisementAction;
use Modules\Advertisements\Actions\ResumeAdvertisementAction;
use Modules\Advertisements\Actions\SubmitAdvertisementAction;
use Modules\Advertisements\Actions\UpdateAdvertisementAction;
use Modules\Advertisements\DTO\AdvertisementDTO;
use Modules\Advertisements\DTO\LoanOfferDTO;
use Modules\Advertisements\Http\Requests\StoreAdvertisementRequest;
use Modules\Advertisements\Http\Requests\SubmitAdvertisementRequest;
use Modules\Advertisements\Http\Requests\UpdateAdvertisementRequest;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Resources\AdvertisementDetailResource;
use Modules\Advertisements\Resources\AdvertisementListResource;
use Modules\Banks\Models\BankLoanProduct;
use Modules\Banks\Models\LoanProduct;
use Modules\Shared\Base\BaseController;

class AdvertisementController extends BaseController
{
    public function __construct(
        protected CreateAdvertisementAction $createAdvertisementAction,
        protected UpdateAdvertisementAction $updateAdvertisementAction,
        protected SubmitAdvertisementAction $submitAdvertisementAction,
        protected PauseAdvertisementAction $pauseAdvertisementAction,
        protected ResumeAdvertisementAction $resumeAdvertisementAction,
        protected ArchiveAdvertisementAction $archiveAdvertisementAction,
        protected DeleteAdvertisementAction $deleteAdvertisementAction,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) ($request->query('per_page', 15));

        $page = Advertisement::query()
            ->where('user_id', $user->id)
            ->with(['loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AdvertisementListResource::collection($page),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function store(StoreAdvertisementRequest $request): JsonResponse
    {
        $loanOffer = null;
        $loanOfferData = $request->input('loan_offer');

        if ($this->loanOfferHasValue($loanOfferData)) {
            $planData = $this->resolveLoanOfferPlanData((int) $request->input('loan_offer.loan_plan_id'));

            $installmentCount = $planData['installment_count'] ?? (int) $request->input('loan_offer.installment_count');
            $salePrice = $this->normalizeLoanOfferAmount($request->input('loan_offer.sale_price'));
            $loanAmount = $this->normalizeLoanOfferAmount($request->input('loan_offer.loan_amount'));
            $monthlyInstallment = $salePrice > 0 && $installmentCount > 0
                ? round($salePrice / $installmentCount, 2)
                : $this->normalizeLoanOfferAmount($request->input('loan_offer.monthly_installment'));

            $loanOffer = new LoanOfferDTO(
                bankId: $request->input('loan_offer.bank_id'),
                loanPlanId: $request->input('loan_offer.loan_plan_id'),
                branchId: $request->input('loan_offer.branch_id'),
                loanTypeId: $request->input('loan_offer.loan_type_id'),
                loanAmount: $loanAmount,
                salePrice: $salePrice,
                interestRate: $planData['interest_rate'] ?? null,
                installmentCount: $installmentCount,
                monthlyInstallment: $monthlyInstallment,
                totalRepayment: $this->normalizeLoanOfferAmount($request->input('loan_offer.total_repayment')),
                remainingInstallments: (int) $request->input('loan_offer.remaining_installments'),
                guarantorRequired: (bool) $request->input('loan_offer.guarantor_required', false),
                guarantorCount: (int) $request->input('loan_offer.guarantor_count', 0),
                checkRequired: (bool) $request->input('loan_offer.check_required', false),
                promissoryNoteRequired: (bool) $request->input('loan_offer.promissory_note_required', false),
                collateralRequired: (bool) $request->input('loan_offer.collateral_required', false),
                transferFee: (float) $request->input('loan_offer.transfer_fee', 0),
                additionalCost: (float) $request->input('loan_offer.additional_cost', 0),
                isNegotiable: (bool) $request->input('loan_offer.is_negotiable', false),
                escrowEnabled: (bool) $request->input('loan_offer.escrow_enabled', false),
                vipGuarantee: (bool) $request->input('loan_offer.vip_guarantee', false),
                contractReady: (bool) $request->input('loan_offer.contract_ready', false),
                isOnline: (bool) $request->input('loan_offer.is_online', true),
                isInPerson: (bool) $request->input('loan_offer.is_in_person', true),
            );
        }

        $dto = new AdvertisementDTO(
            title: $request->input('title'),
            description: $request->input('description'),
            shortDescription: $request->input('short_description'),
            provinceId: $request->input('province_id'),
            cityId: $request->input('city_id'),
            status: 'Draft',
            visibility: $request->input('visibility', 'Public'),
            priority: (int) $request->input('priority', 0),
            userId: $request->user()?->id,
            loanProductId: $request->input('loan_product_id'),
            loanOffer: $loanOffer,
        );

        try {
            $result = $this->createAdvertisementAction->execute($dto);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $result, 'message' => 'Advertisement created successfully.']);
    }

    protected function loanOfferHasValue($loanOffer): bool
    {
        return is_array($loanOffer) && collect($loanOffer)->filter(function ($value) {
            return $value !== null && $value !== '' && !(is_array($value) && empty($value));
        })->isNotEmpty();
    }

    protected function normalizeLoanOfferAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', (string) $value);
        }

        if (! is_numeric($value)) {
            return null;
        }

        $numeric = (float) $value;
        if ($numeric <= 0) {
            return 0.0;
        }

        $scaled = $numeric >= 1000000000 ? $numeric : $numeric * 1000000;
        $bounded = min(max($scaled, 0), 999999999999999.99);

        return round($bounded, 2);
    }

    protected function resolveLoanOfferPlanData(int $loanPlanId): array
    {
        if (! $loanPlanId) {
            return [];
        }

        if (
            Schema::hasTable('bank_loan_products') &&
            $plan = BankLoanProduct::query()->find($loanPlanId)
        ) {
            return [
                'interest_rate' => $plan->interest_rate,
                'installment_count' => $plan->installment_count,
                'duration_months' => $plan->duration_months,
            ];
        }

        if (
            Schema::hasTable('loan_products') &&
            $plan = LoanProduct::query()->find($loanPlanId)
        ) {
            return [
                'interest_rate' => $plan->interest_rate,
                'installment_count' => $plan->installment_count,
                'duration_months' => $plan->duration_months,
            ];
        }

        return [];
    }

    public function show(string $uuid): JsonResponse
    {
        $advertisement = Advertisement::where('uuid', $uuid)
            ->with(['loanOffer.bank', 'loanOffer.loanPlan', 'province', 'city'])
            ->firstOrFail();

        $this->authorize('view', $advertisement);

        return response()->json([
            'success' => true,
            'data' => new AdvertisementDetailResource($advertisement),
            'message' => 'Advertisement retrieved successfully.',
        ]);
    }

    public function update(UpdateAdvertisementRequest $request, string $uuid): JsonResponse
    {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $advertisement);

        $data = $request->validated();
        if ($request->has('loan_offer')) {
            $data['loan_offer'] = $request->input('loan_offer');
        }

        $result = $this->updateAdvertisementAction->execute($advertisement->id, $data);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Advertisement updated successfully.',
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();
        $this->authorize('delete', $advertisement);

        $this->deleteAdvertisementAction->execute($advertisement->id);

        return response()->json([
            'success' => true,
            'message' => 'Advertisement deleted successfully.',
        ]);
    }

    public function submit(SubmitAdvertisementRequest $request, string $uuid): JsonResponse
    {
        $advertisement = Advertisement::where('uuid', $uuid)->firstOrFail();

        $result = $this->submitAdvertisementAction->execute($request->user(), $advertisement, [
            'reason' => $request->input('reason'),
            'comment' => $request->input('comment'),
            'ip' => $request->ip(),
            'device' => $request->header('User-Agent'),
        ]);

        return response()->json(['success' => true, 'data' => $result, 'message' => 'Advertisement submitted successfully.']);
    }

    public function pause(Request $request, int $id): JsonResponse
    {
        $result = $this->pauseAdvertisementAction->execute($id);

        return response()->json(['success' => true, 'data' => $result, 'message' => 'Advertisement paused successfully.']);
    }

    public function resume(Request $request, int $id): JsonResponse
    {
        $result = $this->resumeAdvertisementAction->execute($id);

        return response()->json(['success' => true, 'data' => $result, 'message' => 'Advertisement resumed successfully.']);
    }

    public function archive(Request $request, int $id): JsonResponse
    {
        $result = $this->archiveAdvertisementAction->execute($id);

        return response()->json(['success' => true, 'data' => $result, 'message' => 'Advertisement archived successfully.']);
    }
}

