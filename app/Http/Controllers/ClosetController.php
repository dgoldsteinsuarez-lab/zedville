<?php

// ============================================================
// ZEDVILLE — CLOSET (DURABLE GOODS) — Laravel Controller
// File: app/Http/Controllers/ClosetController.php
//
// HOW TO INTEGRATE:
//   1. Register routes in routes/web.php (see closet_routes.php)
//   2. Auth: uses Laravel's auth()->user() — role must be on the
//      users table as a string: 'student' | 'tutor' | 'admin'
//
// KEY INTEGRATION POINT:
//   When a student completes a spending activity that purchases
//   a durable good, call ClosetController::addItem() from your
//   existing checkout/purchase logic:
//
//     use App\Http\Controllers\ClosetController;
//     ClosetController::addItem($studentId, $catalogItemId, $pricePaid);
// ============================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClosetController extends Controller
{
    // ── AUTH HELPERS ──────────────────────────────────────────

    private function requireAuth()
    {
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }
    }

    private function requireAdminOrTutor()
    {
        if (!auth()->user()->isAdminOrTutor()) {
            abort(403, 'Forbidden');
        }
    }

    private function requireAdmin()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Forbidden');
        }
    }

    // ── SHARED HELPER — also callable statically from other controllers ──

    /**
     * addItem($studentId, $catalogItemId, $pricePaidZeds)
     *
     * Call this from your existing purchase/checkout logic when a student
     * buys a durable good.
     *
     * Example (from another controller):
     *   use App\Http\Controllers\ClosetController;
     *   ClosetController::addItem('student_001', 3, 320);
     */
    public static function addItem(string $studentId, int $catalogItemId, int $pricePaidZeds): int
    {
        return DB::table('student_closet')->insertGetId([
            'student_id' => $studentId,
            'catalog_item_id' => $catalogItemId,
            'price_paid_zeds' => $pricePaidZeds,
            'purchase_date' => now()->toDateString(),
            'purchased_at' => now(),
        ]);
    }

    // ── STUDENT ROUTES ────────────────────────────────────────

    /**
     * GET /closet/mine
     * Returns the logged-in student's full closet as JSON.
     *
     * Response: { items: [ ClosetItem ], totalValue: number }
     */
    public function mine()
    {
        $this->requireAuth();
        $studentId = auth()->id();     // TODO: adjust if your PK differs

        $rows = $this->fetchCloset($studentId);
        $items = $rows->map(fn($r) => $this->formatClosetItem($r));

        return response()->json([
            'items' => $items,
            'totalValue' => $items->sum('pricePaidZeds'),
        ]);
    }

    /**
     * GET /closet/student/{studentId}
     * Tutor/admin: view any student's closet.
     */
    public function studentCloset(string $studentId)
    {
        $this->requireAuth();
        $this->requireAdminOrTutor();
        // Staff only see students in their own scope (Tutor = own classes, School Admin = own school).
        abort_unless(auth()->user()->canManageStudent($studentId), 403);

        $rows = $this->fetchCloset($studentId);
        $items = $rows->map(fn($r) => $this->formatClosetItem($r));

        return response()->json([
            'items' => $items,
            'totalValue' => $items->sum('pricePaidZeds'),
        ]);
    }

    // ── CATALOG ROUTES ────────────────────────────────────────

    /**
     * GET /closet/catalog
     * Returns all active durable goods in the catalog.
     */
    public function catalog()
    {
        $this->requireAuth();

        $rows = DB::table('durable_goods_catalog')
            ->where('is_active', 1)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'items' => $rows->map(fn($r) => $this->formatCatalogItem($r)),
        ]);
    }

    /**
     * POST /closet/catalog
     * Admin: add a new item to the catalog.
     * Body (JSON): { name, category, icon, priceZeds, lifespanYears, description? }
     */
    public function catalogStore(Request $request)
    {
        $this->requireAuth();
        $this->requireAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:128',
            'category' => 'required|string|in:tech,transport,home,clothing,tools,other',
            'icon' => 'required|string|max:8',
            'priceZeds' => 'required|integer|min:1',
            'lifespanYears' => 'required|integer|min:3',
            'description' => 'nullable|string',
        ]);

        $id = DB::table('durable_goods_catalog')->insertGetId([
            'name' => $data['name'],
            'category' => $data['category'],
            'icon' => $data['icon'],
            'price_zeds' => $data['priceZeds'],
            'lifespan_years' => $data['lifespanYears'],
            'description' => $data['description'] ?? null,
            'is_active' => 1,
            'created_at' => now(),
        ]);

        $item = DB::table('durable_goods_catalog')->find($id);

        return response()->json(['item' => $this->formatCatalogItem($item)], 201);
    }

    /**
     * PUT /closet/catalog/{id}
     * Admin: update a catalog item.
     */
    public function catalogUpdate(Request $request, int $id)
    {
        $this->requireAuth();
        $this->requireAdmin();

        $data = $request->validate([
            'name' => 'sometimes|string|max:128',
            'category' => 'sometimes|string|in:tech,transport,home,clothing,tools,other',
            'icon' => 'sometimes|string|max:8',
            'priceZeds' => 'sometimes|integer|min:1',
            'lifespanYears' => 'sometimes|integer|min:3',
            'description' => 'nullable|string',
            'isActive' => 'sometimes|boolean',
        ]);

        $update = array_filter([
            'name' => $data['name'] ?? null,
            'category' => $data['category'] ?? null,
            'icon' => $data['icon'] ?? null,
            'price_zeds' => $data['priceZeds'] ?? null,
            'lifespan_years' => $data['lifespanYears'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['isActive']) ? (int) $data['isActive'] : null,
        ], fn($v) => !is_null($v));

        if (empty($update)) {
            return response()->json(['error' => 'No fields to update'], 400);
        }

        $affected = DB::table('durable_goods_catalog')->where('id', $id)->update($update);

        if (!$affected) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $item = DB::table('durable_goods_catalog')->find($id);
        return response()->json(['item' => $this->formatCatalogItem($item)]);
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────

    private function fetchCloset($studentId)
    {
        return DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.product_name', '=', 'order_items.name')
            ->where('orders.user_id', $studentId)
            ->where('products.goods_type', 'Durable Goods')
            ->select([
                'products.id',
                'products.product_name as name',
                'products.category',
                'products.image',
                'orders.created_at as purchase_date',
                'order_items.price as price_paid_zeds',
                DB::raw('3 as lifespan_years'),
                DB::raw('"" as description'),
                DB::raw('ROUND(TIMESTAMPDIFF(MONTH, orders.created_at, CURDATE()) / 12,1) as age_years')
            ])
            ->get();
    }
    private function formatClosetItem(object $row): array
    {
        $ageYears = (float) ($row->age_years ?? 0);
        $lifespanYears = (int) ($row->lifespan_years ?? 3); // Default 3 years

        $lifeUsedPct = min(
            100,
            (int) round(($ageYears / max($lifespanYears, 1)) * 100)
        );

        $costPerYear = (int) round(($row->price_paid_zeds ?? 0) / max($lifespanYears, 1));

        return [
            'id' => $row->id,
            'purchaseDate' => $row->purchase_date,
            'pricePaidZeds' => (int) ($row->price_paid_zeds ?? 0),
            'ageYears' => $ageYears,
            'name' => $row->name,
            'category' => $row->category,

            // Your products table has "image", not "icon"
            'icon' => $row->image ?? '',

            'lifespanYears' => $lifespanYears,
            'description' => $row->description ?? '',
            'lifeUsedPct' => $lifeUsedPct,
            'costPerYear' => $costPerYear,
        ];
    }

    private function formatCatalogItem(object $row): array
    {
        return [
            'id' => $row->id,
            'name' => $row->name,
            'category' => $row->category,

            // Your products table has "image"
            'icon' => $row->image ?? '',

            'priceZeds' => (int) ($row->price_zeds ?? $row->price ?? 0),
            'lifespanYears' => (int) ($row->lifespan_years ?? 3),
            'description' => $row->description ?? '',
            'isActive' => (bool) ($row->is_active ?? true),
        ];
    }
}
