<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\PromotionCampaign;
use App\Models\PromotionCoupon;
use App\Models\PromotionEvent;
use App\Models\PromotionRedemption;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionReportController extends Controller
{
    public function usage(Request $request): View
    {
        $matched = PromotionRedemption::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($query) use ($term) {
                    $query->whereHas('campaign', fn ($query) => $query->where('name', 'like', $term))
                        ->orWhereHas('coupon', fn ($query) => $query->where('code', 'like', $term))
                        ->orWhereHas('lead', fn ($query) => $query->where('name', 'like', $term));
                });
            })
            ->when($request->filled('campaign'), fn ($query) => $query->where('promotion_id', $request->integer('campaign')));
        $counts = [
            'all' => (clone $matched)->count(),
            'coupon' => (clone $matched)->whereNotNull('coupon_id')->count(),
            'automatic' => (clone $matched)->whereNull('coupon_id')->count(),
        ];
        $kind = $request->string('kind')->toString();
        [$sort, $dir] = admin_sort($request, ['redeemed_at', 'original_amount', 'discount_amount', 'final_amount'], 'redeemed_at', 'desc');
        $redemptions = $matched->with(['campaign', 'coupon', 'lead'])
            ->when($kind === 'coupon', fn ($query) => $query->whereNotNull('coupon_id'))
            ->when($kind === 'automatic', fn ($query) => $query->whereNull('coupon_id'))
            ->orderBy($sort, $dir)
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.promotions.usage', [
            'redemptions' => $redemptions,
            'counts' => $counts,
            'kind' => $kind,
            'sort' => $sort,
            'dir' => $dir,
            'campaigns' => PromotionCampaign::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function analytics(Request $request): View
    {
        $campaigns = PromotionCampaign::query()->withCount('redemptions')->orderByDesc('id')->get()->map(function (PromotionCampaign $campaign) {
            $events = PromotionEvent::query()->where('promotion_id', $campaign->id);
            $leads = Lead::query()->where('promotion_id', $campaign->id);
            $clicks = (clone $events)->where('event', 'promotion_cta_click')->count();
            $leadCount = (clone $leads)->count();

            return [
                'campaign' => $campaign,
                'impressions' => (clone $events)->where('event', 'promotion_impression')->count(),
                'popup_views' => (clone $events)->where('event', 'promotion_popup_view')->count(),
                'clicks' => $clicks,
                'coupon_applications' => (clone $events)->where('event', 'coupon_applied')->count(),
                'leads' => $leadCount,
                'qualified' => (clone $leads)->where('status', 'Qualified')->count(),
                'won' => (clone $leads)->where('status', 'Won')->count(),
                'visitors' => (clone $events)->whereNotNull('visitor_uuid')->distinct('visitor_uuid')->count('visitor_uuid'),
                'discount' => (float) PromotionRedemption::query()->where('promotion_id', $campaign->id)->sum('discount_amount'),
                'conversion' => $clicks > 0 ? round(($leadCount / $clicks) * 100, 1) : null,
            ];
        });

        $coupons = PromotionCoupon::query()->withCount('redemptions')->orderBy('code')->get()->map(function (PromotionCoupon $coupon) {
            $leads = Lead::query()->where('coupon_id', $coupon->id);

            return [
                'coupon' => $coupon,
                'attempts' => PromotionEvent::query()->where('coupon_id', $coupon->id)->where('event', 'coupon_attempt')->count(),
                'applied' => PromotionEvent::query()->where('coupon_id', $coupon->id)->where('event', 'coupon_applied')->count(),
                'redemptions' => $coupon->redemptions_count,
                'discount' => (float) $coupon->redemptions()->sum('discount_amount'),
                'leads' => (clone $leads)->count(),
                'won' => (clone $leads)->where('status', 'Won')->count(),
            ];
        });

        $search = mb_strtolower($request->string('q')->toString());
        if ($search !== '') {
            $campaigns = $campaigns->filter(fn (array $row) => str_contains(mb_strtolower($row['campaign']->name), $search));
            $coupons = $coupons->filter(function (array $row) use ($search) {
                return str_contains(mb_strtolower($row['coupon']->code), $search)
                    || str_contains(mb_strtolower((string) $row['coupon']->campaign?->name), $search);
            });
        }

        $counts = [
            'all' => $campaigns->count(),
            'active' => $campaigns->filter(fn (array $row) => $row['campaign']->state() === 'active')->count(),
            'scheduled' => $campaigns->filter(fn (array $row) => $row['campaign']->state() === 'scheduled')->count(),
            'draft' => $campaigns->filter(fn (array $row) => $row['campaign']->state() === 'draft')->count(),
            'ended' => $campaigns->filter(fn (array $row) => in_array($row['campaign']->state(), ['expired', 'disabled'], true))->count(),
        ];
        $status = $request->string('status')->toString();
        if ($status === 'ended') {
            $campaigns = $campaigns->filter(fn (array $row) => in_array($row['campaign']->state(), ['expired', 'disabled'], true));
        } elseif (in_array($status, ['active', 'scheduled', 'draft'], true)) {
            $campaigns = $campaigns->filter(fn (array $row) => $row['campaign']->state() === $status);
        }

        $table = $request->string('table')->toString() === 'coupons' ? 'coupons' : 'campaigns';
        if ($table === 'coupons') {
            [$sort, $dir] = admin_sort($request, ['code', 'attempts', 'applied', 'redemptions', 'discount', 'leads', 'won'], 'code');
            $coupons = $coupons->sortBy(fn (array $row) => $sort === 'code' ? $row['coupon']->code : $row[$sort], SORT_NATURAL | SORT_FLAG_CASE);
            if ($dir === 'desc') {
                $coupons = $coupons->reverse();
            }
        } else {
            [$sort, $dir] = admin_sort($request, ['name', 'visitors', 'impressions', 'popup_views', 'clicks', 'coupon_applications', 'leads', 'qualified', 'won', 'conversion', 'discount'], 'name');
            $campaigns = $campaigns->sortBy(function (array $row) use ($sort) {
                if ($sort === 'name') {
                    return $row['campaign']->name;
                }

                return $row[$sort] ?? -1;
            }, SORT_NATURAL | SORT_FLAG_CASE);
            if ($dir === 'desc') {
                $campaigns = $campaigns->reverse();
            }
        }

        return view('admin.promotions.analytics', [
            'campaigns' => $campaigns->values(),
            'coupons' => $coupons->values(),
            'counts' => $counts,
            'status' => $status,
            'sort' => $sort,
            'dir' => $dir,
            'table' => $table,
            'totals' => [
                'visitors' => $campaigns->sum('visitors'),
                'leads' => $campaigns->sum('leads'),
                'discount' => $campaigns->sum('discount'),
            ],
        ]);
    }
}
