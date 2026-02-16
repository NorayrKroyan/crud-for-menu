<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountingMenu;
use Illuminate\Http\Request;

class AccountingMenuController extends Controller
{
    // GET /api/accounting-menus?show_inactive=0
    // Returns ALL menus (including parent_id NULL), with parent_name via self-join
    public function index(Request $request)
    {
        $showInactive = (int) $request->query('show_inactive', 0);

        $q = AccountingMenu::query()
            ->from('accounting_menus as m')
            ->leftJoin('accounting_menus as p', 'p.id', '=', 'm.parent_id')
            ->select([
                'm.id',
                'm.name',
                'm.parent_id',
                'p.name as parent_name',
                'm.url',
                'm.component',
                'm.sort_order',
                'm.is_active',
                'm.icon',
                'm.permission',
            ])
            // sort parents first, then children nicely
            ->orderByRaw('CASE WHEN m.parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('m.parent_id')
            ->orderBy('m.sort_order')
            ->orderBy('m.name');

        if (!$showInactive) {
            $q->where('m.is_active', 1);
        }

        return $q->get();
    }

    // GET /api/accounting-menus/parents?show_inactive=0
    // Returns only top-level menus (parent_id NULL) for dropdown
    public function parents(Request $request)
    {
        $showInactive = (int) $request->query('show_inactive', 0);

        $q = AccountingMenu::query()
            ->whereNull('parent_id')
            ->select(['id', 'name', 'sort_order', 'is_active'])
            ->orderBy('sort_order')
            ->orderBy('name');

        if (!$showInactive) {
            $q->where('is_active', 1);
        }

        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if (!isset($data['is_active'])) $data['is_active'] = 1;
        if (!isset($data['sort_order'])) $data['sort_order'] = 0;

        $row = AccountingMenu::create($data);
        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $row = AccountingMenu::findOrFail($id);
        $data = $this->validated($request);

        $row->fill($data)->save();
        return response()->json($row);
    }

    // Deactivate
    public function destroy($id)
    {
        $row = AccountingMenu::findOrFail($id);
        $row->is_active = 0;
        $row->save();

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer'],
            'url' => ['nullable', 'string', 'max:255'],
            'component' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'integer', 'in:0,1'],
            'icon' => ['nullable', 'string', 'max:100'],
            'permission' => ['nullable', 'string', 'max:150'],
        ]);
    }
}
