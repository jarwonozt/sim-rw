<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Http\Resources\InventoryItemResource;
use App\Models\InventoryItem;
use App\Models\MasterRt;
use App\Services\InventoryCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class InventoryItemController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json(InventoryItemResource::collection($items)->response()->getData(true));
    }

    public function store(StoreInventoryItemRequest $request, InventoryCodeGenerator $codeGenerator): JsonResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id'] ?? null);
        $data['code'] = $codeGenerator->generate(Carbon::now());
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventory-items', 'public');
        }

        $item = InventoryItem::create($data);

        return response()->json(['data' => new InventoryItemResource($item)], 201);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): JsonResponse
    {
        $data = $request->validated();
        $data['rt_id'] = $this->resolveRtId($request, $data['rt_id'] ?? null);

        $borrowed = (int) $inventoryItem->loans()->where('status', 'dipinjam')->sum('quantity_borrowed');

        if ($data['quantity'] < $borrowed) {
            return response()->json([
                'message' => "Jumlah tidak boleh kurang dari {$borrowed} unit yang sedang dipinjam.",
                'errors' => ['quantity' => ["Jumlah tidak boleh kurang dari {$borrowed} unit yang sedang dipinjam."]],
            ], 422);
        }

        if ($request->hasFile('photo')) {
            if ($inventoryItem->photo) {
                Storage::disk('public')->delete($inventoryItem->photo);
            }
            $data['photo'] = $request->file('photo')->store('inventory-items', 'public');
        }

        $inventoryItem->update($data);

        return response()->json(['data' => new InventoryItemResource($inventoryItem)]);
    }

    public function destroy(InventoryItem $inventoryItem): JsonResponse
    {
        if ($inventoryItem->loans()->exists()) {
            return response()->json([
                'message' => 'Barang tidak bisa dihapus karena memiliki riwayat peminjaman.',
            ], 422);
        }

        if ($inventoryItem->photo) {
            Storage::disk('public')->delete($inventoryItem->photo);
        }

        $inventoryItem->delete();

        return response()->json(['message' => 'Barang inventaris berhasil dihapus.']);
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
