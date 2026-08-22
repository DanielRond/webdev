<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Autor extends Model
{
    protected $table = 'autores';

    protected $fillable = ['nome', 'nacionalidade'];
    
    public function livros(): HasMany
    {
        return $this->hasMany(Livro::class);
    }
}
