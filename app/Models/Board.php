<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    protected $fillable = ['name', 'slug'];

    public function columns(): HasMany
    {
        return $this->hasMany(BoardColumn::class, 'board_id');
    }
}
