<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function show($board)
    {
        return response()->json(['board' => ['id' => $board]]);
    }

    public function storeCard(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'column' => ['required', 'string'],
        ]);

        // Placeholder: replace with actual model create logic
        return response()->json(['card' => $data], 201);
    }

    public function updateCard(Request $request, $card)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string'],
            'column' => ['sometimes', 'string'],
        ]);

        return response()->json(['card' => array_merge(['id' => $card], $data)]);
    }

    public function moveCard(Request $request, $card)
    {
        $data = $request->validate([
            'to_column' => ['required', 'string'],
            'position' => ['nullable', 'integer'],
        ]);

        return response()->json(['moved' => ['id' => $card, 'to' => $data]]);
    }

    public function destroyCard($card)
    {
        // Placeholder: delete the card
        return response()->noContent();
    }
}
