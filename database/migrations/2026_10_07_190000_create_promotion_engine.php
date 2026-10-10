<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 12, 2);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('publication_status', 20)->default('draft');
            $table->boolean('coupon_required')->default(false);
            $table->boolean('show_popup')->default(false);
            $table->boolean('show_announcement_bar')->default(false);
            $table->boolean('show_pricing_badge')->default(true);
            $table->boolean('show_countdown')->default(false);
            $table->unsignedSmallInteger('popup_repeat_minutes')->default(1);
            $table->string('cta_text')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['publication_status', 'starts_at', 'ends_at']);
        });

        Schema::create('promotion_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotion_campaigns')->cascadeOnDelete();
            $table->string('mode', 10)->default('include');
            $table->string('target_type', 40);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_key', 40)->nullable();
            $table->timestamps();
            $table->index(['promotion_id', 'mode', 'target_type']);
        });

        Schema::create('promotion_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->nullable()->constrained('promotion_campaigns')->nullOnDelete();
            $table->string('code', 40);
            $table->string('name')->nullable();
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->decimal('minimum_amount', 12, 2)->nullable();
            $table->decimal('maximum_discount_amount', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique('code');
        });

        Schema::create('promotion_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->nullable()->constrained('promotion_campaigns')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('promotion_coupons')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('visitor_uuid', 64)->nullable();
            $table->string('customer_key', 190)->nullable();
            $table->decimal('original_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('final_amount', 12, 2);
            $table->json('snapshot');
            $table->dateTime('redeemed_at');
            $table->timestamps();
            $table->index(['coupon_id', 'customer_key']);
            $table->index('promotion_id');
        });

        Schema::create('promotion_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 40);
            $table->foreignId('promotion_id')->nullable()->constrained('promotion_campaigns')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('promotion_coupons')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('visitor_uuid', 64)->nullable();
            $table->string('path', 2048)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['event', 'promotion_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('pricing_snapshot')->constrained('promotion_campaigns')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->after('promotion_id')->constrained('promotion_coupons')->nullOnDelete();
            $table->json('promotion_snapshot')->nullable()->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('promotion_snapshot');
        });

        Schema::dropIfExists('promotion_events');
        Schema::dropIfExists('promotion_redemptions');
        Schema::dropIfExists('promotion_coupons');
        Schema::dropIfExists('promotion_targets');
        Schema::dropIfExists('promotion_campaigns');
    }
};
