<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateProductImages extends Command
{
    protected $signature = 'winner:migrate-product-images {--dry-run}';

    protected $description = 'Safely copy legacy product images into public product storage';

    public function handle(): int
    {
        $copied = 0;
        $skipped = 0;
        $unmatched = [];
        Product::whereNotNull('image')->each(function (Product $product) use (&$copied, &$skipped, &$unmatched) {
            if (Storage::disk('public')->exists($product->image)) {
                $skipped++;

                return;
            }
            $basename = basename(str_replace('\\', '/', $product->image));
            $candidates = [resource_path('images/'.$basename), public_path('images/'.$basename), resource_path('images/'.$product->slug.'.'.pathinfo($basename, PATHINFO_EXTENSION))];
            $source = collect($candidates)->first(fn ($path) => is_file($path));
            if (! $source) {
                $unmatched[] = $product->name.' -> '.$product->image;

                return;
            }
            $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
            $destination = 'products/'.Str::slug($product->slug).'-'.substr(hash_file('sha256', $source), 0, 12).'.'.$extension;
            if (! $this->option('dry-run')) {
                if (! Storage::disk('public')->exists($destination)) {
                    Storage::disk('public')->put($destination, file_get_contents($source));
                }
                if (! Storage::disk('public')->exists($destination)) {
                    $unmatched[] = $product->name.' -> copy verification failed';

                    return;
                }
                $product->update(['image' => $destination]);
            }
            $copied++;
        });
        $this->info("Copied or matched: {$copied}; already in storage: {$skipped}; unmatched: ".count($unmatched));
        foreach ($unmatched as $line) {
            $this->warn($line);
        }

        return self::SUCCESS;
    }
}
