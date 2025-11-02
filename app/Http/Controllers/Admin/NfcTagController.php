<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\NfcTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NfcTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $tags = NfcTag::query()
            ->with('item:id,nama_item,kategori,lokasi_pameran')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('kode_tag', 'like', "%{$q}%")
                    ->orWhereHas('item', function ($q2) use ($q) {
                        $q2->where('nama_item', 'like', "%{$q}%");
                    });
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $items = Item::orderBy('nama_item')
            ->whereDoesntHave('nfcTags')
            ->get(['id', 'nama_item']);

        return view('admin.nfc.index', compact('tags', 'q', 'items'));
    }

     /**
     * Store a newly created NFC tag.
     * UI should only hit this when the item does NOT already have a tag.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id'  => ['required', 'exists:items,id'],
            'kode_tag' => ['required', 'string', 'max:255', 'unique:nfc_tags,kode_tag'],
        ]);

        // OPTIONAL: block if the item already has a tag (keeps one-to-one)
        if (NfcTag::where('item_id', $validated['item_id'])->exists()) {
            return back()->withErrors(['item_id' => 'Item tersebut sudah memiliki NFC Tag.'])->withInput();
        }

        return DB::transaction(function () use ($validated) {
            $tag = NfcTag::create($validated);

            ActivityLog::create([
                'user_id'         => auth()->id(),
                'aktivitas'       => 'create_nfc_tag',
                'waktu_aktivitas' => now(),
                'context'         => [
                    'nfc_tag_id' => $tag->id,
                    'item_id'    => $tag->item_id,
                    'kode_tag'   => $tag->kode_tag,
                ],
            ]);

            return redirect()
                ->route('admin.nfc.index')
                ->with('status', 'NFC Tag berhasil dibuat.');
        });
    }

    /**
     * Explicit update flow for a specific tag record.
     * Keeps uniqueness on kode_tag and lets you reassign tag to a different item if needed.
     */
    public function update(Request $request, NfcTag $nfcTag)
    {
        $validated = $request->validate([
            'item_id'  => ['required', 'exists:items,id'],
            'kode_tag' => [
                'required', 'string', 'max:255',
                Rule::unique('nfc_tags', 'kode_tag')->ignore($nfcTag->id),
            ],
        ]);

        return DB::transaction(function () use ($nfcTag, $validated) {
            $old = [
                'item_id'  => $nfcTag->item_id,
                'kode_tag' => $nfcTag->kode_tag,
            ];

            // If you want to enforce "one tag per item" strictly here too:
            // If moving this tag to an item that *already has another tag*, you might want to block:
            $conflict = NfcTag::where('item_id', $validated['item_id'])
                ->where('id', '!=', $nfcTag->id)
                ->first();

            if ($conflict) {
                return back()
                    ->withErrors(['item_id' => 'Item tersebut sudah memiliki NFC Tag lain.'])
                    ->withInput();
            }

            $nfcTag->update([
                'item_id'  => $validated['item_id'],
                'kode_tag' => $validated['kode_tag'],
            ]);

           ActivityLog::create([
                'user_id'         => auth()->id(),
                'aktivitas'       => 'update_nfc_tag',
                'waktu_aktivitas' => now(),
                'context'         => [
                    'nfc_tag_id'    => $nfcTag->id,
                    'old_item_id'   => $old['item_id'],
                    'new_item_id'   => $validated['item_id'],
                    'old_kode_tag'  => $old['kode_tag'],
                    'new_kode_tag'  => $validated['kode_tag'],
                    'kode_tag'      => $validated['kode_tag'],   
                ],
            ]);
            
            return redirect()
                ->route('admin.nfc.index')
                ->with('status', 'NFC Tag berhasil diperbarui.');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NfcTag $nfcTag)
    {
        return DB::transaction(function () use ($nfcTag) {
            $tagId = $nfcTag->id;
            $kodeTag = $nfcTag->kode_tag;

            $nfcTag->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'delete_nfc_tag',
                'waktu_aktivitas' => now(),
                'context' => [
                    'nfc_tag_id' => $tagId,
                    'kode_tag' => $kodeTag,
                ],
            ]);

            return redirect()
                ->route('admin.nfc.index')
                ->with('status', 'NFC Tag berhasil dihapus.');
        });
    }
}
