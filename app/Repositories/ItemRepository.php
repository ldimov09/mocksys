<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    public function listByUser($userId)
    {
        return Item::where('user_id', $userId)->get();
    }

    public function create(array $data)
    {
        return Item::create($data);
    }

    public function update(Item $item, array $data)
    {
        $item->update($data);
        return $item;
    }

    public function delete(Item $item)
    {
        return $item->delete();
    }

    public function findByIdForUser($id, $userId)
    {
        return Item::where('id', $id)->where('user_id', $userId)->first();
    }

    public function checkNumber($number, $businessId)
    {
        return !Item::where('number', $number)->where('user_id', $businessId)->first();
    }

    public function findById($id)
    {   
        return Item::find($id);
    }
}
