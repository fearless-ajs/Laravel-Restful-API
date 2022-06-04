<?php

namespace App\Transformers;

use App\Models\Transaction;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
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
     * @param Transaction $transaction
     * @return array
     */
    public function transform(Transaction $transaction)
    {
        return [
            'identifier'    =>  (int)$transaction->id,
            'quantity'      => (int)$transaction->quantity,
            'buyer'         => (int)$transaction->buyer_id,
            'product'       => (int)$transaction->product_id,
            'createdDate'   =>  $transaction->created_at,
            'lastChange'    =>  $transaction->updated_at,
            'deletedDate'   =>  isset($transaction->deleted_at) ? (string) $transaction->deleted_at : null, // For columns that can null anytime


            'links'         =>  [
                [
                    'rel'   =>  'self',
                    'href'  =>  route('transactions.show', $transaction->id),
                ],
                [
                    'rel'   =>  'transaction.categories',
                    'href'  =>  route('transactions.categories.index', $transaction->id)
                ],
                [
                    'rel'   =>  'transaction.seller',
                    'href'  =>  route('transactions.sellers.index', $transaction->id)
                ],
                [
                    'rel'   =>  'buyer',
                    'href'  =>  route('buyers.show', $transaction->buyer_id)
                ],
                [
                    'rel'   =>  'product',
                    'href'  =>  route('products.show', $transaction->product_id)
                ],
            ]
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            'identifier'    =>  'id',
            'quantity'      =>  'quantity',
            'buyer'         =>  'buyer_id',
            'product'       =>  'product_id',
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
            'quantity'     =>  'quantity',
            'buyer_id'     =>  'buyer',
            'product_id'   =>  'product',
            'created_at'   =>  'createdDate',
            'updated_at'   =>  'lastChange',
            'deleted_at'   =>  'deletedDate'// For columns that can null anytime
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
