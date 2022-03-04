<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description'
    ];

    // For many to many relationships, category has many products and a product can belong to many categories
    public function products(){
        return $this->belongsToMany(Product::class);
    }
}
