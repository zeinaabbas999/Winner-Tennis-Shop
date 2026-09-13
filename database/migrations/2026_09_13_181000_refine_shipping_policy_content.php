<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $policy = DB::table('policy_pages')->where('key', 'shipping')->first();
        if (! $policy) {
            return;
        }

        DB::table('policy_pages')->where('id', $policy->id)->update([
            'intro' => 'Clear delivery expectations for WINNER orders across Egypt.',
            'sections' => json_encode([
                ['heading' => 'Processing and delivery', 'content' => 'Orders are normally prepared and delivered within 3-5 working days. Timing may vary by governorate, product availability and courier conditions.'],
                ['heading' => 'Delivery fees', 'content' => 'Any applicable delivery fee is calculated for the selected governorate and shown before the order is placed. Eligible orders may receive free shipping when the displayed threshold is met.'],
                ['heading' => 'Accurate delivery details', 'content' => 'Customers should provide a complete address and reachable phone number. Incorrect or incomplete details may delay delivery. WINNER will share order updates when available.'],
            ]),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Editorial content is intentionally not overwritten during rollback.
    }
};
