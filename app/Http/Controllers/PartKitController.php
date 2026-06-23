<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\PartAlternative;
use App\Models\PartKit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartKitController extends Controller
{
    public function edit(Part $part): View
    {
        $part->load(['kitsAsKit.componentPart', 'alternatives.alternativePart']);

        return view('parts.kits', [
            'part' => $part,
            'parts' => Part::where('is_active', true)->where('id', '!=', $part->id)->orderBy('part_number')->get(),
            'kits' => PartKit::where('kit_part_id', $part->id)->with('componentPart')->get(),
            'alternatives' => PartAlternative::where('part_id', $part->id)->with('alternativePart')->get(),
        ]);
    }

    public function storeKit(Request $request, Part $part): RedirectResponse
    {
        $data = $request->validate([
            'component_part_id' => 'required|exists:parts,id|different:part',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        PartKit::updateOrCreate(
            ['kit_part_id' => $part->id, 'component_part_id' => $data['component_part_id']],
            ['quantity' => $data['quantity']]
        );

        return back()->with('success', 'Kit component added.');
    }

    public function destroyKit(Part $part, PartKit $kit): RedirectResponse
    {
        abort_unless($kit->kit_part_id === $part->id, 404);
        $kit->delete();

        return back()->with('success', 'Kit component removed.');
    }

    public function storeAlternative(Request $request, Part $part): RedirectResponse
    {
        $data = $request->validate([
            'alternative_part_id' => 'required|exists:parts,id|different:part',
            'notes' => 'nullable|string|max:255',
        ]);

        PartAlternative::updateOrCreate(
            ['part_id' => $part->id, 'alternative_part_id' => $data['alternative_part_id']],
            ['notes' => $data['notes'] ?? null]
        );

        return back()->with('success', 'Alternative part added.');
    }

    public function destroyAlternative(Part $part, PartAlternative $alternative): RedirectResponse
    {
        abort_unless($alternative->part_id === $part->id, 404);
        $alternative->delete();

        return back()->with('success', 'Alternative removed.');
    }
}
