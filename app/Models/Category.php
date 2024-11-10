<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'name',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}
