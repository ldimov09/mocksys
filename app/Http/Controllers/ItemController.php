<?php

namespace App\Http\Controllers;

use App\Repositories\ItemRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;

class ItemController extends Controller
{
    public function __construct(
        private ItemRepository $items,
        private UserRepository $users
    ) {}

    public function index(Request $request)
    {
        $selectedUserId = $request->query('user_id');
        $businessUsers = $this->users->listByRole('business');
        $items = [];

        if ($selectedUserId) {
            $items = $this->items->listByUser($selectedUserId);
        }

        return view('admin.items.index', compact('businessUsers', 'selectedUserId', 'items'));
    }

    public function create(Request $request)
    {
        $userId = $request->query('user_id');
        return view('admin.items.create', compact('userId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:16',
            'price' => 'required|numeric|min:0',
            'number' => 'required|digits:6|unique:items,number',
            'unit' => 'required|string|max:8',
        ]);

        $this->items->create($validated);
        return redirect()->route('admin.items.index', ['user_id' => $validated['user_id']])
                         ->with('success', 'Item created successfully.');
    }

    public function edit(int $id)
    {
        $item = $this->items->findById($id);
        return view('admin.items.edit', compact('item'));
    }

    public function update(int $id, Request $request)
    {
        $item = $this->items->findById($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:16',
            'price' => 'required|numeric|min:0',
            'number' => 'required|digits:6|unique:items,number,' . $id,
            'unit' => 'required|string|max:8',
        ]);

        $this->items->update($item, $validated);
        return redirect()->route('admin.items.index', ['user_id' => $item->user_id])
                         ->with('success', 'Item updated successfully.');
    }

    public function destroy(int $id)
    {
        $item = $this->items->findById($id);
        $userId = $item->user_id;
        $this->items->delete($item);

        return redirect()->route('admin.items.index', ['user_id' => $userId])
                         ->with('success', 'Item deleted successfully.');
    }
}
