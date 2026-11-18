<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\KanbanCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_the_board(): void
    {
        $this->getJson('/api/boards/main')->assertUnauthorized();
        $this->postJson('/api/boards/main/columns', ['name' => 'Later'])
            ->assertUnauthorized();
    }

    public function test_authenticated_users_can_view_the_board_in_position_order(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/boards/main');

        $response->assertOk()
            ->assertJsonPath('board.name', 'Mijn werk')
            ->assertJsonPath('board.columns.0.name', 'Te doen')
            ->assertJsonPath('board.columns.0.cards.0.title', 'Nieuwe portfolio-content');
    }

    public function test_authenticated_users_can_manage_columns_and_cards(): void
    {
        $this->actingAs(User::factory()->create());

        $board = Board::query()->where('slug', 'main')->firstOrFail();
        $column = BoardColumn::query()->where('board_id', $board->id)->firstOrFail();

        $newColumnResponse = $this->postJson('/api/boards/main/columns', [
            'name' => 'Review',
        ]);
        $newColumnResponse->assertCreated()->assertJsonPath('column.name', 'Review');
        $newColumnId = $newColumnResponse->json('column.id');

        $newCardResponse = $this->postJson('/api/cards', [
            'column_id' => $column->id,
            'title' => 'Test nieuwe kaart',
            'description' => 'Blijft opgeslagen in de backend.',
        ]);
        $newCardResponse->assertCreated()
            ->assertJsonPath('card.title', 'Test nieuwe kaart');

        $cardId = $newCardResponse->json('card.id');

        $this->patchJson("/api/cards/{$cardId}/move", [
            'column_id' => $newColumnId,
        ])->assertOk()->assertJsonPath('card.column_id', $newColumnId);

        $this->deleteJson("/api/cards/{$cardId}")->assertNoContent();
        $this->assertDatabaseMissing('cards', ['id' => $cardId]);

        $cascadingCardId = $this->postJson('/api/cards', [
            'column_id' => $newColumnId,
            'title' => 'Kaart die met de kolom wordt verwijderd',
        ])->assertCreated()->json('card.id');

        $this->deleteJson("/api/boards/main/columns/{$newColumnId}")->assertNoContent();
        $this->assertDatabaseMissing('columns', ['id' => $newColumnId]);
        $this->assertDatabaseMissing('cards', ['id' => $cascadingCardId]);
    }

    public function test_cards_cannot_be_moved_between_different_boards(): void
    {
        $this->actingAs(User::factory()->create());

        $firstColumn = BoardColumn::query()->whereHas('board', fn ($query) => $query->where('slug', 'main'))->firstOrFail();
        $firstCard = KanbanCard::query()->where('column_id', $firstColumn->id)->firstOrFail();
        $otherBoard = Board::query()->create(['name' => 'Other', 'slug' => 'other']);
        $otherColumn = $otherBoard->columns()->create(['name' => 'Other', 'position' => 0]);

        $this->patchJson("/api/cards/{$firstCard->id}/move", [
            'column_id' => $otherColumn->id,
        ])->assertUnprocessable();
    }
}
