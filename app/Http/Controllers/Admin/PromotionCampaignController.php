<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PromotionCampaignType;
use App\Enums\PromotionDiscountType;
use App\Enums\PromotionPopupDesign;
use App\Enums\PromotionPublication;
use App\Enums\PromotionTargetType;
use App\Http\Controllers\Controller;
use App\Models\PromotionCampaign;
use App\Models\PromotionCoupon;
use App\Services\Admin\ActivityLogger;
use App\Services\Promotions\PromotionEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromotionCampaignController extends Controller
{
    public function __construct(private PromotionEngine $promotions) {}

    public function index(Request $request): View
    {
        $matched = PromotionCampaign::query()->when($request->filled('q'), function ($query) use ($request) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($query) use ($term) {
                $query->where('name', 'like', $term)->orWhere('slug', 'like', $term);
            });
        })->when($request->filled('type'), fn ($query) => $query->where('campaign_type', $request->string('type')->toString()));

        $counts = ['all' => (clone $matched)->count()];
        foreach (['active', 'scheduled', 'draft', 'ended'] as $state) {
            $countQuery = clone $matched;
            $this->whereState($countQuery, $state);
            $counts[$state] = $countQuery->count();
        }
        [$sort, $dir] = admin_sort($request, ['name', 'discount_value', 'starts_at', 'ends_at'], 'starts_at', 'desc');
        $status = $request->string('status')->toString();
        $campaigns = $matched->withCount('redemptions')
            ->with('coupons')
            ->when($status !== '', fn ($query) => $this->whereState($query, $status))
            ->orderBy($sort, $dir)
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.promotions.campaigns.index', [
            'campaigns' => $campaigns,
            'counts' => $counts,
            'status' => $status,
            'sort' => $sort,
            'dir' => $dir,
            'types' => PromotionCampaignType::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.campaigns.form', $this->formData(new PromotionCampaign));
    }

    public function store(Request $request): RedirectResponse
    {
        $campaign = PromotionCampaign::query()->create($this->validated($request) + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);
        $this->syncTargets($campaign, $request);
        $this->promotions->forgetCache();
        ActivityLogger::log('create', 'promotions', $campaign, 'Campaign created');

        return redirect()->route('admin.promotions.campaigns.edit', $campaign)->with('success', 'Campaign saved as '.$campaign->state().'.');
    }

    public function edit(PromotionCampaign $campaign): View
    {
        $campaign->load('targets', 'coupons');

        return view('admin.promotions.campaigns.form', $this->formData($campaign));
    }

    public function update(Request $request, PromotionCampaign $campaign): RedirectResponse
    {
        $before = [
            'discount_type' => $campaign->discount_type?->value,
            'discount_value' => (string) $campaign->discount_value,
            'starts_at' => $campaign->starts_at?->toDateTimeString(),
            'ends_at' => $campaign->ends_at?->toDateTimeString(),
        ];
        $campaign->update($this->validated($request, $campaign) + ['updated_by' => $request->user()->id]);
        $this->syncTargets($campaign, $request);
        $this->promotions->forgetCache();
        ActivityLogger::log('update', 'promotions', $campaign, 'Campaign updated', [
            'discount_before' => $before,
            'discount_after' => [
                'discount_type' => $campaign->discount_type?->value,
                'discount_value' => (string) $campaign->discount_value,
                'starts_at' => $campaign->starts_at?->toDateTimeString(),
                'ends_at' => $campaign->ends_at?->toDateTimeString(),
            ],
        ]);

        return redirect()->route('admin.promotions.campaigns.edit', $campaign)->with('success', 'Campaign updated.');
    }

    public function publish(PromotionCampaign $campaign): RedirectResponse
    {
        $campaign->update(['publication_status' => PromotionPublication::Published, 'updated_by' => auth()->id()]);
        $this->promotions->forgetCache();
        ActivityLogger::log('update', 'promotions', $campaign, 'Campaign enabled');

        return back()->with('success', 'Campaign published. It applies only inside its schedule.');
    }

    public function disable(PromotionCampaign $campaign): RedirectResponse
    {
        $campaign->update(['publication_status' => PromotionPublication::Disabled, 'updated_by' => auth()->id()]);
        $this->promotions->forgetCache();
        ActivityLogger::log('update', 'promotions', $campaign, 'Campaign disabled');

        return back()->with('success', 'Campaign disabled.');
    }

    public function duplicate(PromotionCampaign $campaign): RedirectResponse
    {
        $campaign->load('targets', 'coupons');
        $copy = $campaign->replicate(['deleted_at']);
        $copy->name = $campaign->name.' copy';
        $copy->slug = $this->uniqueSlug(Str::slug($copy->name));
        $copy->publication_status = PromotionPublication::Draft;
        $copy->created_by = auth()->id();
        $copy->updated_by = auth()->id();
        $copy->save();

        foreach ($campaign->targets as $target) {
            $copy->targets()->create($target->only(['mode', 'target_type', 'target_id', 'target_key']));
        }
        foreach ($campaign->coupons as $coupon) {
            $code = PromotionCoupon::normalize($coupon->code.'-COPY');
            if (PromotionCoupon::query()->where('code', $code)->exists()) {
                continue;
            }
            $copy->coupons()->create([
                'code' => $code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'starts_at' => $coupon->starts_at,
                'ends_at' => $coupon->ends_at,
                'minimum_amount' => $coupon->minimum_amount,
                'maximum_discount_amount' => $coupon->maximum_discount_amount,
                'usage_limit' => $coupon->usage_limit,
                'usage_limit_per_customer' => $coupon->usage_limit_per_customer,
                'is_active' => false,
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLogger::log('create', 'promotions', $copy, 'Campaign duplicated as draft');

        return redirect()->route('admin.promotions.campaigns.edit', $copy)->with('success', 'Draft copy created. Usage history was not copied.');
    }

    public function preview(PromotionCampaign $campaign): View
    {
        $campaign->load('coupons');

        return view('admin.promotions.campaigns.preview', [
            'campaign' => $campaign,
            'presentation' => $this->promotions->present($campaign, false),
        ]);
    }

    public function destroy(PromotionCampaign $campaign): RedirectResponse
    {
        $campaign->delete();
        $this->promotions->forgetCache();
        ActivityLogger::log('delete', 'promotions', $campaign, 'Campaign archived');

        return redirect()->route('admin.promotions.campaigns.index')->with('success', 'Campaign deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function whereState($query, string $status): void
    {
        $now = now();

        if ($status === 'active') {
            $query->where('publication_status', PromotionPublication::Published->value)
                ->where('starts_at', '<=', $now)
                ->where('ends_at', '>=', $now);
        } elseif ($status === 'scheduled') {
            $query->where('publication_status', PromotionPublication::Published->value)
                ->where('starts_at', '>', $now);
        } elseif ($status === 'draft') {
            $query->where('publication_status', PromotionPublication::Draft->value);
        } elseif ($status === 'ended') {
            $query->where(function ($query) use ($now) {
                $query->where('publication_status', PromotionPublication::Disabled->value)
                    ->orWhere(function ($query) use ($now) {
                        $query->where('publication_status', PromotionPublication::Published->value)
                            ->where('ends_at', '<', $now);
                    });
            });
        }
    }

    private function formData(PromotionCampaign $campaign): array
    {
        return [
            'campaign' => $campaign,
            'types' => PromotionCampaignType::options(),
            'designs' => PromotionPopupDesign::options(),
            'discounts' => PromotionDiscountType::options(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?PromotionCampaign $campaign = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('promotion_campaigns', 'slug')->ignore($campaign)],
            'campaign_type' => ['required', Rule::in(array_keys(PromotionCampaignType::options()))],
            'festival_name' => ['nullable', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:180'],
            'subheadline' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'discount_type' => ['required', Rule::in(array_keys(PromotionDiscountType::options()))],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'publication_status' => ['required', Rule::in(array_column(PromotionPublication::cases(), 'value'))],
            'popup_delay_seconds' => ['nullable', 'integer', 'min:0', 'max:60'],
            'popup_scroll_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'popup_frequency' => ['required', Rule::in(['session', 'daily', 'campaign', 'cooldown'])],
            'popup_cooldown_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'popup_repeat_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'popup_design' => ['nullable', Rule::in(array_keys(PromotionPopupDesign::options()))],
            'preview_days' => ['nullable', 'integer', 'min:0', 'max:60'],
            'announcement_text' => ['nullable', 'string', 'max:220'],
            'badge_text' => ['nullable', 'string', 'max:80'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ]);

        if ($data['discount_type'] === PromotionDiscountType::Percentage->value && (float) $data['discount_value'] > 100) {
            throw ValidationException::withMessages(['discount_value' => 'A percentage discount cannot exceed 100.']);
        }

        $data['slug'] = $this->uniqueSlug($data['slug'] ?: Str::slug($data['name']), $campaign?->id);
        $data['popup_delay_seconds'] = (int) ($data['popup_delay_seconds'] ?? 4);
        $data['popup_cooldown_hours'] = (int) ($data['popup_cooldown_hours'] ?? 24);
        $data['popup_repeat_minutes'] = (int) ($data['popup_repeat_minutes'] ?? 1);
        $data['preview_days'] = (int) ($data['preview_days'] ?? 0);
        $data['popup_design'] = $data['popup_design'] ?: PromotionPopupDesign::Split->value;
        foreach ([
            'coupon_required', 'show_popup', 'show_announcement_bar', 'show_pricing_badge', 'show_countdown',
            'show_before_start', 'popup_exit_intent', 'announcement_dismissible',
        ] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        return $data;
    }

    private function syncTargets(PromotionCampaign $campaign, Request $request): void
    {
        $campaign->targets()->delete();
        $rows = [];
        foreach ([
            'include_all_website' => PromotionTargetType::AllWebsitePackages->value,
            'include_all_marketing' => PromotionTargetType::AllMarketingPackages->value,
        ] as $input => $type) {
            if (! $request->boolean($input)) {
                continue;
            }
            $rows[] = [
                'promotion_id' => $campaign->id,
                'mode' => 'include',
                'target_type' => $type,
                'target_id' => null,
                'target_key' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            $campaign->targets()->insert($rows);
        }
    }

    private function uniqueSlug(string $slug, ?int $ignore = null): string
    {
        $base = Str::slug($slug) ?: 'offer';
        $candidate = $base;
        $i = 2;
        while (PromotionCampaign::withTrashed()->where('slug', $candidate)->when($ignore, fn ($query) => $query->where('id', '!=', $ignore))->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
