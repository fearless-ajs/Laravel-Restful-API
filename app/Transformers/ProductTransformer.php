<?php

namespace App\Transformers;

use App\Models\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @param Product $product
     * @return array
     */
    public function transform(Product $product)
    {
        return [
            'identifier'    =>  (int)$product->id,
            'title'         =>  (string)$product->name,
            'details'       =>  (string)$product->description,
            'stock'         =>  (int)$product->quantity,
            'situation'     =>  (string)$product->status,
            'picture'       =>  url("img/{$product->image}"),
            'seller'        =>  (int)$product->seller_id,
            'createdDate'   =>  $product->created_at,
            'lastChange'    =>  $product->updated_at,
            'deletedDate'   =>  isset($product->deleted_at) ? (string) $product->deleted_at : null, // For columns that can null anytime

            'links'         =>  [
                [
                    'rel'   =>  'self',
                    'href'  =>  route('products.show', $product->id),
                ],
                [
                    'rel'   =>  'product.buyers',
                    'href'  =>  route('products.buyers.index', $product->id)
                ],
                [
                    'rel'   =>  'product.categories',
                    'href'  =>  route('products.categories.index', $product->id)
                ],
                [
                    'rel'   =>  'product.transactions',
                    'href'  =>  route('products.transactions.index', $product->id)
                ],
                [
                    'rel'   =>  'seller',
                    'href'  =>  route('sellers.show', $product->seller_id)
                ],
            ]
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            'identifier'    =>  'id',
            'title'         =>  'name',
            'details'       =>  'description',
            'stock'         =>  'quantity',
            'situation'     =>  'status',
            'picture'       =>  'image',
            'seller'        =>  'seller_id',
            'createdDate'   =>  'created_at',
            'lastChange'    =>  'updated_at',
            'deletedDate'   =>  'deleted_at ' // For columns that can null anytime
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index)
    {
        $attributes = [
            'id'           =>  'identifier',
            'name'         =>  'title',
            'description'  =>  'details',
            'quantity'     =>  'stock',
            'status'       =>  'situation',
            'image'        =>  'picture',
            'seller_id'    =>  'seller',
            'created_at'   =>  'createdDate',
            'updated_at'   =>  'lastChange',
            'deleted_at'   =>  'deletedDate',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
