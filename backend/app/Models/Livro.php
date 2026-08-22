<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Livro extends Model
{
    protected $fillable = ['titulo', 'ano_publicacao', 'autor_id'];
    public function autor(): BelongsTo 
    {
        return $this->BelongsTo(Autor::class);
    }
}
