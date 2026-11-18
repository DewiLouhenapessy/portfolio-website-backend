<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['board_id', 'position']);
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained('columns')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['column_id', 'position']);
        });

        $now = now();
        $boardId = DB::table('boards')->insertGetId([
            'name' => 'Mijn werk',
            'slug' => 'main',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $todoColumnId = DB::table('columns')->insertGetId([
            'board_id' => $boardId,
            'name' => 'Te doen',
            'position' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $progressColumnId = DB::table('columns')->insertGetId([
            'board_id' => $boardId,
            'name' => 'Bezig',
            'position' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $doneColumnId = DB::table('columns')->insertGetId([
            'board_id' => $boardId,
            'name' => 'Afgerond',
            'position' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('cards')->insert([
            [
                'column_id' => $todoColumnId,
                'title' => 'Nieuwe portfolio-content',
                'description' => 'Projectbeschrijvingen voor de hoofdpagina bijwerken.',
                'position' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'column_id' => $progressColumnId,
                'title' => 'Contactformulier testen',
                'description' => 'Controleer of de verzending in productie werkt.',
                'position' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'column_id' => $doneColumnId,
                'title' => 'Adminomgeving opleveren',
                'description' => 'Beheerscherm en kanbanbord zijn beschikbaar.',
                'position' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
        Schema::dropIfExists('columns');
        Schema::dropIfExists('boards');
    }
};