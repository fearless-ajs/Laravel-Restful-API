<?php

namespace App\Models;

use App\Transformers\CategoryTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    public $transformer = CategoryTransformer::class;

    protected $fillable = [
        'name',
        'description'
    ];

    protected $hidden = [
      'pivot'
    ];

    // For many to many relationships, category has many products and a product can belong to many categories
    public function products(){
        return $this->belongsToMany(Product::class);
    }
}
