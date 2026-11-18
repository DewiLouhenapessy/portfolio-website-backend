<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\KanbanCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BoardController extends Controller
{
    public function show(string $board): JsonResponse
    {
        $board = Board::query()
            ->with([
                'columns' => fn ($query) => $query->orderBy('position')->orderBy('id'),
                'columns.cards' => fn ($query) => $query->orderBy('position')->orderBy('id'),
            ])
            ->where('slug', $board)
            ->firstOrFail();

        return response()->json([
            'board' => [
                'id' => $board->id,
                'name' => $board->name,
                'columns' => $board->columns->map(fn (BoardColumn $column) => [
                    'id' => $column->id,
                    'name' => $column->name,
                    'position' => $column->position,
                    'cards' => $column->cards->map(fn (KanbanCard $card) => [
                        'id' => $card->id,
                        'column_id' => $card->column_id,
                        'title' => $card->title,
                        'description' => $card->description,
                        'position' => $card->position,
                    ])->values(),
                ])->values(),
            ],
        ]);
    }

    public function storeColumn(Request $request, string $board): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $board = Board::query()->where('slug', $board)->firstOrFail();

        $column = $board->columns()->create([
            ...$data,
            'position' => ($board->columns()->max('position') ?? -1) + 1,
        ]);

        return response()->json(['column' => $column], 201);
    }

    public function destroyColumn(string $board, BoardColumn $column): JsonResponse
    {
        $board = Board::query()->where('slug', $board)->firstOrFail();
        abort_unless($column->board_id === $board->id, 404);

        $column->delete();

        return response()->json(status: 204);
    }

    public function storeCard(Request $request): JsonResponse
    {
        $data = $request->validate([
            'column_id' => ['required', 'integer', Rule::exists('columns', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);
        $column = BoardColumn::query()->findOrFail($data['column_id']);
        $card = $column->cards()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'position' => ($column->cards()->max('position') ?? -1) + 1,
        ]);

        return response()->json(['card' => $card], 201);
    }

    public function updateCard(Request $request, KanbanCard $card): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
        ]);

        $card->update($data);

        return response()->json(['card' => $card->refresh()]);
    }

    public function moveCard(Request $request, KanbanCard $card): JsonResponse
    {
        $data = $request->validate([
            'column_id' => [
                'required',
                'integer',
                Rule::exists('columns', 'id')->where('board_id', $card->column->board_id),
            ],
        ]);
        $sourceColumn = $card->column;
        $destinationColumn = BoardColumn::query()->findOrFail($data['column_id']);

        if ($sourceColumn->is($destinationColumn)) {
            return response()->json(['card' => $card]);
        }

        DB::transaction(function () use ($card, $sourceColumn, $destinationColumn): void {
            $card->update([
                'column_id' => $destinationColumn->id,
                'position' => ($destinationColumn->cards()->max('position') ?? -1) + 1,
            ]);

            foreach ([$sourceColumn, $destinationColumn] as $column) {
                $column->cards()
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get()
                    ->each(fn (KanbanCard $card, int $position) => $card->update(['position' => $position]));
            }
        });

        return response()->json(['card' => $card->refresh()]);
    }

    public function destroyCard(KanbanCard $card): JsonResponse
    {
        $card->delete();

        return response()->json(status: 204);
    }
}
