<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Part;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartImportController extends Controller
{
    public function form(): View
    {
        return view('parts.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'update_existing' => 'boolean',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->with('error', 'Could not read uploaded file.');
        }

        $header = array_map('strtolower', array_map('trim', fgetcsv($handle) ?: []));
        $required = ['part_number', 'description_en', 'brand_code'];
        foreach ($required as $col) {
            if (! in_array($col, $header, true)) {
                fclose($handle);

                return back()->with('error', "Missing required column: {$col}");
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row)) === 0) {
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));
            $partNumber = trim((string) ($data['part_number'] ?? ''));
            if ($partNumber === '') {
                $skipped++;

                continue;
            }

            $brandCode = trim((string) ($data['brand_code'] ?? ''));
            $brand = Brand::where('code', $brandCode)->orWhere('name', $brandCode)->first();
            if (! $brand) {
                $brand = Brand::create([
                    'code' => strtoupper(substr($brandCode ?: 'GEN', 0, 10)),
                    'name' => $brandCode ?: 'General',
                    'is_active' => true,
                ]);
            }

            $payload = [
                'brand_id' => $brand->id,
                'description_en' => trim((string) ($data['description_en'] ?? $partNumber)),
                'description_ar' => trim((string) ($data['description_ar'] ?? '')) ?: null,
                'oem_no' => trim((string) ($data['oem_no'] ?? '')) ?: null,
                'barcode' => trim((string) ($data['barcode'] ?? '')) ?: null,
                'list_price' => (float) ($data['list_price'] ?? 0),
                'price_2' => (float) ($data['price_2'] ?? 0),
                'price_3' => (float) ($data['price_3'] ?? 0),
                'cost_price' => (float) ($data['cost_price'] ?? 0),
                'is_active' => true,
            ];

            $existing = Part::where('part_number', $partNumber)->first();
            if ($existing) {
                if ($request->boolean('update_existing')) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    $skipped++;
                }

                continue;
            }

            try {
                Part::create(array_merge($payload, ['part_number' => $partNumber]));
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "{$partNumber}: ".$e->getMessage();
            }
        }

        fclose($handle);

        $message = "Import complete: {$created} created, {$updated} updated, {$skipped} skipped.";
        if ($errors) {
            $message .= ' Errors: '.implode('; ', array_slice($errors, 0, 5));
        }

        return back()->with('success', $message);
    }
}
