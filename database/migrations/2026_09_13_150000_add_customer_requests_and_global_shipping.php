<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('application_settings')->updateOrInsert(
            ['key' => 'free_shipping_threshold'],
            ['value' => null, 'created_at' => now(), 'updated_at' => now()],
        );

        Schema::create('return_exchange_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->text('reason');
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['order_item_id', 'type']);
        });

        Schema::create('customer_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->text('question');
            $table->string('status', 20)->default('open')->index();
            $table->text('admin_response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        foreach (['returns', 'exchanges'] as $key) {
            $page = DB::table('policy_pages')->where('key', $key)->first();
            if (! $page) {
                continue;
            }
            $sections = json_decode($page->sections, true) ?: [];
            $message = ' Eligible requests can be submitted from delivered order details and are subject to review. Approval does not itself confirm an immediate refund, and items must meet the stated conditions.';
            if ($sections !== [] && ! str_contains($sections[array_key_last($sections)]['content'], 'delivered order details')) {
                $sections[array_key_last($sections)]['content'] .= $message;
                DB::table('policy_pages')->where('id', $page->id)->update(['sections' => json_encode($sections), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_questions');
        Schema::dropIfExists('return_exchange_requests');
        DB::table('application_settings')->where('key', 'free_shipping_threshold')->delete();
    }
};
