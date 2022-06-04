<?php

namespace App\Models;

use App\Transformers\ProductTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    const AVAILABLE_PRODUCT = 'available';
    const UNAVAILABLE_PRODUCT = 'unavailable';

    public $transformer = ProductTransformer::class;

    protected $fillable = [
      'name',
      'description',
      'quantity',
      'status',
      'image',
      'seller_id'
    ];


    protected $hidden = [
        'pivot'
    ];
    /**
     * @var mixed
     */
    private $categories;


    public function isAvailable(){
        return $this->status == Product::AVAILABLE_PRODUCT;
    }

    public function seller(){
        return $this->belongsTo(Seller::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    // For many to many relationships, category has many products and a product can belong to many categories
    public function categories(){
        return $this->belongsToMany(Category::class);
    }
}
