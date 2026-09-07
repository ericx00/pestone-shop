<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CatalogImport extends Command
{
    protected $signature = 'catalog:import
        {--file= : Path to a catalog.json (defaults to database/data/catalog.json)}
        {--markup= : Override markup percent for computed retail prices}
        {--fresh : Delete existing products/categories/brands first}';

    protected $description = 'Import the product catalogue from the DN Solutions price list JSON';

    public function handle(): int
    {
        $path = $this->option('file') ?: database_path('data/catalog.json');

        if (! is_file($path)) {
            $this->error("Catalogue file not found: {$path}");

            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($path), true);
        if (! $data || empty($data['products'])) {
            $this->error('Catalogue file is empty or invalid.');

            return self::FAILURE;
        }

        $markup = $this->option('markup') !== null
            ? (float) $this->option('markup')
            : (float) Setting::value('markup_percent', 10);

        // Supplier "Sale Price" is treated as Pestone's ex-VAT cost. Retail =
        // cost + markup, then VAT added on top when the shop shows VAT-inclusive prices,
        // so the displayed price still yields the full markup as margin.
        $vatFactor = Setting::value('prices_include_vat', true)
            ? 1 + (float) Setting::value('vat_rate', 16) / 100
            : 1.0;

        if ($this->option('fresh')) {
            $this->warn('Wiping existing catalogue…');
            InventoryMovement::query()->delete();
            Product::query()->delete();
            Category::query()->delete();
            Brand::query()->delete();
        }

        // --- brands -------------------------------------------------------
        $brands = [];
        foreach (collect($data['products'])->pluck('brand')->unique()->filter() as $name) {
            $brands[$name] = Brand::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            )->id;
        }

        // --- categories (parents first) ---------------------------------
        $cats = [];
        $rows = collect($data['categories'] ?? []);
        foreach ($rows->whereNull('parent_slug') as $c) {
            $cats[$c['slug']] = Category::updateOrCreate(
                ['slug' => $c['slug']],
                ['name' => $c['name'], 'source_sheet' => $c['source_sheet'] ?? null, 'is_active' => true],
            )->id;
        }
        foreach ($rows->whereNotNull('parent_slug') as $c) {
            $cats[$c['slug']] = Category::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'parent_id' => $cats[$c['parent_slug']] ?? null,
                    'source_sheet' => $c['source_sheet'] ?? null,
                    'is_active' => true,
                ],
            )->id;
        }

        // --- products ---------------------------------------------------
        $created = 0;
        $updated = 0;
        $usedSlugs = Product::pluck('slug')->flip();
        $bar = $this->output->createProgressBar(count($data['products']));

        foreach ($data['products'] as $row) {
            $cost = (int) round($row['cost_price']);

            $lookupBySku = ! empty($row['sku']);
            $existingForSlug = $lookupBySku ? Product::where('sku', $row['sku'])->first() : Product::where('slug', $row['slug'])->first();
            if (! $existingForSlug) {
                $slug = $row['slug'];
                $i = 1;
                while ($usedSlugs->has($slug)) {
                    $slug = $row['slug'].'-'.(++$i);
                }
                $row['slug'] = $slug;
            }
            $usedSlugs->put($row['slug'], true);
            $computed = (int) (round($cost * (1 + $markup / 100) * $vatFactor / 10) * 10);

            $existing = $existingForSlug;

            $attributes = [
                'sku' => $row['sku'] ?: null,
                'name' => $row['name'],
                'slug' => $row['slug'],
                'brand_id' => $brands[$row['brand']] ?? null,
                'category_id' => $cats[$row['category_slug']] ?? null,
                'short_description' => Str::limit($row['description'], 240),
                'description' => $row['description'],
                'cost_price' => $cost,
                'stock_status' => $row['stock_status'] ?? 'on_request',
                'availability_label' => $row['availability_label'] ?? null,
                'source_sheet' => $row['source_sheet'] ?? null,
                'is_active' => true,
            ];

            // never overwrite a manually-set price
            if (! $existing || ! $existing->price_locked) {
                $attributes['price'] = $computed;
            }

            if (! $existing) {
                $attributes['stock_qty'] = (int) ($row['stock_qty'] ?? 0);
                $product = Product::create($attributes);
                if ($product->stock_qty > 0) {
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'change' => $product->stock_qty,
                        'reason' => 'import',
                        'note' => 'Initial import',
                    ]);
                }
                $created++;
            } else {
                $existing->fill($attributes)->save();
                $updated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Catalogue imported — {$created} created, {$updated} updated, markup {$markup}%.");
        $this->line('Brands: '.count($brands).'  Categories: '.count($cats).'  Products: '.Product::count());

        return self::SUCCESS;
    }
}
