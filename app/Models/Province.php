<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'name_en', 'slug'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
