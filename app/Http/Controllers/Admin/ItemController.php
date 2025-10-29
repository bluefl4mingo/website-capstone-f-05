<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $category = trim((string) $request->get('category', ''));
        $location = trim((string) $request->get('location', ''));

        $items = Item::query()
            ->withCount(['audioFiles', 'nfcTags'])
             ->with([
            'audioFiles:id,item_id,nama_file,format_file,lokasi_penyimpanan,created_at',
            'nfcTags:id,item_id,kode_tag,created_at',
             ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('nama_item', 'like', "%{$q}%")
                       ->orWhere('deskripsi', 'like', "%{$q}%")
                       ->orWhere('kategori', 'like', "%{$q}%")
                       ->orWhere('lokasi_pameran', 'like', "%{$q}%");
                });
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('kategori', $category);
            })
            ->when($location !== '', function ($query) use ($location) {
                $query->where('lokasi_pameran', $location);
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        // Get unique categories and locations for filters
        $categories = Item::distinct()
            ->pluck('kategori')
            ->filter(fn($value) => !is_null($value) && $value !== '')
            ->sort()
            ->values();
            
        $locations = Item::distinct()
            ->pluck('lokasi_pameran')
            ->filter(fn($value) => !is_null($value) && $value !== '')
            ->sort()
            ->values();

        return view('admin.items.index', compact('items', 'q', 'category', 'location', 'categories', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_item' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'lokasi_pameran' => ['nullable', 'string', 'max:255'],
            'tanggal_penambahan' => ['required', 'date'],
        ]);

        return DB::transaction(function () use ($validated) {
            $item = Item::create($validated);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'create_item',
                'waktu_aktivitas' => now(),
                'context' => ['item_id' => $item->id, 'nama_item' => $item->nama_item],
            ]);

            return redirect()
                ->route('admin.items.index')
                ->with('status', 'Item berhasil dibuat.');
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'nama_item' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'lokasi_pameran' => ['nullable', 'string', 'max:255'],
            'tanggal_penambahan' => ['required', 'date'],
        ]);

        return DB::transaction(function () use ($item, $validated) {
            $item->update($validated);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'update_item',
                'waktu_aktivitas' => now(),
                'context' => ['item_id' => $item->id, 'nama_item' => $item->nama_item],
            ]);

            return redirect()
                ->route('admin.items.index')
                ->with('status', 'Item berhasil diperbarui.');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        return DB::transaction(function () use ($item) {
            $itemId = $item->id;
            $itemName = $item->nama_item;

            $item->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'delete_item',
                'waktu_aktivitas' => now(),
                'context' => ['item_id' => $itemId, 'nama_item' => $itemName],
            ]);

            return redirect()
                ->route('admin.items.index')
                ->with('status', 'Item berhasil dihapus.');
        });
    }
}
