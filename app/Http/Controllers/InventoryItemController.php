<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\MasterRt;
use App\Services\InventoryCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InventoryItemController extends Controller
{
    public function index(Request $request): Response
    {
        $categoryId = $request->integer('inventory_category_id') ?: null;
        $rtId = $request->integer('rt_id') ?: null;
        $search = $request->string('search')->toString();

        $items = InventoryItem::query()
            ->with('category:id,name', 'rt:id,nomor_rt')
            ->withSum(['loans as borrowed_quantity' => fn ($query) => $query->where('status', 'dipinjam')], 'quantity_borrowed')
            ->when($categoryId, fn ($query) => $query->where('inventory_category_id', $categoryId))
            ->when($rtId, fn ($query) => $query->where('rt_id', $rtId))
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $items->getCollection()->transform(function (InventoryItem $item) {
            $item->available_quantity = $item->quantity - (int) $item->borrowed_quantity;

            return $item;
        });

        return Inertia::render('Inventory/Items/Index', [
            'items' => $items,
            'categoryOptions' => InventoryCategory::query()->orderBy('name')->get(['id', 'name']),
            'rtOptions' => $this->rtOptionsForCurrentUser($request),
            'filters' => ['inventory_category_id' => $categoryId, 'rt_id' => $rtId, 'search' => $search],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Inventory/Items/Create', [
            'categoryOptions' => InventoryCategory::query()->orderBy('name')->get(['id', 'name']),
            'rtOptions' => $this->rtOptionsForCurrentUser($request),
            'isKetuaRt' => $request->user()->role === 'ketua_rt',
        ]);
    }

    public function store(StoreInventoryItemRequest $request, InventoryCodeGenerator $codeGenerator): RedirectResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id'] ?? null);
        $data['code'] = $codeGenerator->generate(Carbon::now());
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventory-items', 'public');
        }

        InventoryItem::create($data);

        return to_route('inventory-items.index')->with('success', 'Barang inventaris berhasil ditambahkan.');
    }

    public function edit(Request $request, InventoryItem $inventoryItem): Response
    {
        return Inertia::render('Inventory/Items/Edit', [
            'item' => $inventoryItem,
            'categoryOptions' => InventoryCategory::query()->orderBy('name')->get(['id', 'name']),
            'rtOptions' => $this->rtOptionsForCurrentUser($request),
            'isKetuaRt' => $request->user()->role === 'ketua_rt',
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id'] ?? null);

        $borrowed = (int) $inventoryItem->loans()->where('status', 'dipinjam')->sum('quantity_borrowed');

        if ($data['quantity'] < $borrowed) {
            return back()->withErrors([
                'quantity' => "Jumlah tidak boleh kurang dari {$borrowed} unit yang sedang dipinjam.",
            ])->withInput();
        }

        if ($request->hasFile('photo')) {
            if ($inventoryItem->photo) {
                Storage::disk('public')->delete($inventoryItem->photo);
            }
            $data['photo'] = $request->file('photo')->store('inventory-items', 'public');
        }

        $inventoryItem->update($data);

        return to_route('inventory-items.index')->with('success', 'Barang inventaris berhasil diperbarui.');
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        if ($inventoryItem->loans()->exists()) {
            return back()->with('error', 'Barang tidak bisa dihapus karena memiliki riwayat peminjaman.');
        }

        if ($inventoryItem->photo) {
            Storage::disk('public')->delete($inventoryItem->photo);
        }

        $inventoryItem->delete();

        return to_route('inventory-items.index')->with('success', 'Barang inventaris berhasil dihapus.');
    }

    /**
     * @return Collection<int, MasterRt>
     */
    private function rtOptionsForCurrentUser(Request $request)
    {
        $user = $request->user();

        return MasterRt::query()
            ->when($user->role === 'ketua_rt', fn ($query) => $query->where('ketua_rt_id', $user->id))
            ->orderBy('nomor_rt')
            ->get(['id', 'nomor_rt']);
    }

    /**
     * Ketua RT hanya boleh mengelola barang milik RT-nya sendiri — abaikan
     * rt_id dari form dan paksa ke RT yang dipimpinnya (lihat docs/issues/001).
     */
    private function resolveRtId(Request $request, ?int $submittedRtId): ?int
    {
        $user = $request->user();

        if ($user->role !== 'ketua_rt') {
            return $submittedRtId;
        }

        return MasterRt::query()->where('ketua_rt_id', $user->id)->value('id') ?? abort(403);
    }
}
