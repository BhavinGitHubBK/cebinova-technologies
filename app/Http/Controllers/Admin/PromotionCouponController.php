<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PromotionDiscountType;
use App\Http\Controllers\Controller;
use App\Models\PromotionCampaign;
use App\Models\PromotionCoupon;
use App\Services\Admin\ActivityLogger;
use App\Services\Promotions\PromotionEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromotionCouponController extends Controller
{
    public function __construct(private PromotionEngine $promotions) {}

    public function index(Request $request): View
    {
        $matched = PromotionCoupon::query()->when($request->filled('q'), function ($query) use ($request) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($query) use ($term) {
                $query->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhereHas('campaign', fn ($query) => $query->where('name', 'like', $term));
            });
        });
        $counts = [
            'all' => (clone $matched)->count(),
            'active' => (clone $matched)->where('is_active', true)->count(),
            'off' => (clone $matched)->where('is_active', false)->count(),
        ];
        $status = $request->string('status')->toString();
        [$sort, $dir] = admin_sort($request, ['code', 'redemptions_count', 'is_active'], 'code');
        $coupons = $matched->with('campaign')
            ->withCount('redemptions')
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'off', fn ($query) => $query->where('is_active', false))
            ->orderBy($sort, $dir)
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.promotions.coupons.index', compact('coupons', 'counts', 'status', 'sort', 'dir'));
    }

    public function create(): View
    {
        return view('admin.promotions.coupons.form', [
            'coupon' => new PromotionCoupon,
            'campaigns' => PromotionCampaign::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coupon = PromotionCoupon::query()->create($this->validated($request) + ['created_by' => $request->user()->id]);
        $this->promotions->forgetCache();
        ActivityLogger::log('create', 'promotions', $coupon, 'Coupon created');

        return redirect()->route('admin.promotions.coupons.edit', $coupon)->with('success', 'Coupon created.');
    }

    public function edit(PromotionCoupon $coupon): View
    {
        return view('admin.promotions.coupons.form', [
            'coupon' => $coupon,
            'campaigns' => PromotionCampaign::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, PromotionCoupon $coupon): RedirectResponse
    {
        $coupon->update($this->validated($request, $coupon));
        $this->promotions->forgetCache();
        ActivityLogger::log('update', 'promotions', $coupon, $coupon->is_active ? 'Coupon updated' : 'Coupon disabled');

        return redirect()->route('admin.promotions.coupons.edit', $coupon)->with('success', 'Coupon updated.');
    }

    public function destroy(PromotionCoupon $coupon): RedirectResponse
    {
        $code = $coupon->code;
        $coupon->delete();
        $this->promotions->forgetCache();
        ActivityLogger::log('delete', 'promotions', $coupon, 'Coupon '.$code.' deleted');

        return redirect()->route('admin.promotions.coupons.index')->with('success', 'Coupon deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?PromotionCoupon $coupon = null): array
    {
        $data = $request->validate([
            'promotion_id' => ['nullable', 'integer', 'exists:promotion_campaigns,id'],
            'code' => ['required', 'string', 'max:40', Rule::unique('promotion_coupons', 'code')->ignore($coupon)],
            'name' => ['nullable', 'string', 'max:160'],
            'discount_type' => ['nullable', Rule::in(array_keys(PromotionDiscountType::options()))],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['code'] = PromotionCoupon::normalize($data['code']);
        if (! preg_match('/^[A-Z0-9-]{2,40}$/', $data['code'])) {
            throw ValidationException::withMessages(['code' => 'Use letters, numbers, or hyphens.']);
        }
        $data['is_active'] = $request->boolean('is_active');
        if (($data['discount_type'] ?? null) === PromotionDiscountType::Percentage->value && (float) ($data['discount_value'] ?? 0) > 100) {
            throw ValidationException::withMessages(['discount_value' => 'A percentage discount cannot exceed 100.']);
        }

        return $data;
    }
}
