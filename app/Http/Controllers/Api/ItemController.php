<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ItemRepository;
use App\Models\Item;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function __construct(private ItemRepository $repo) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->repo->listByUser($request->user()->id)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'short_name' => 'required|string|max:16',
            'price' => 'required|numeric|min:0',
            'number' => [
                'sometimes',
                'digits:6',
                Rule::unique('items')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user()->id);
                })
            ],
            'unit' => 'required|string|max:8',
        ]);

        $validated['user_id'] = $request->user()->id;

        return response()->json(
            $this->repo->create($validated),
            201
        );
    }

    public function show($id, Request $request)
    {
        $item = $this->repo->findByIdForUser($id, $request->user()->id);
        return response()->json($item);
    }

    public function update($id, Request $request)
    {
        $item = $this->repo->findByIdForUser($id, $request->user()->id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'short_name' => 'sometimes|string|max:16',
            'price' => 'sometimes|numeric|min:0',
            'number' => [
                'sometimes',
                'digits:6',
                Rule::unique('items')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user()->id);
                })->ignore($item->id)
            ],
            'unit' => 'sometimes|string|max:8',
        ]);

        return response()->json($this->repo->update($item, $validated));
    }

    public function destroy($id, Request $request)
    {
        $item = $this->repo->findByIdForUser($id, $request->user()->id);
        $this->repo->delete($item);

        return response()->noContent();
    }
}
