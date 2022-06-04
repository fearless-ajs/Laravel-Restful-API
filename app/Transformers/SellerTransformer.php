<?php

namespace App\Transformers;

use App\Models\Seller;
use League\Fractal\TransformerAbstract;

class SellerTransformer extends TransformerAbstract
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
     * @param Seller $seller
     * @return array
     */
    public function transform(Seller $seller)
    {
        return [
            'identifier'    =>  (int)$seller->id,
            'name'          =>  (string)$seller->name,
            'email'         =>  (string)$seller->email,
            'isVerified'    =>  (int)$seller->verified,
            'createdDate'   =>  $seller->created_at,
            'lastChange'    =>  $seller->updated_at,
            'deletedDate'   =>  isset($seller->deleted_at) ? (string) $seller->deleted_at : null, // For columns that can null anytime
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            'identifier'    =>  'id',
            'name'          =>  'name',
            'email'         =>  'email',
            'isVerified'    =>  'verified',
            'createdDate'   =>  'created_at',
            'lastChange'    =>  'updated_at',
            'deletedDate'   =>  'deleted_at ' // For columns that can null anytime
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index)
    {
        $attributes = [
            'id'           =>    'identifier',
            'name'         =>    'name',
            'email'        =>    'email',
            'verified'     =>    'isVerified',
            'created_at'   =>    'createdDate',
            'updated_at'   =>    'lastChange',
            'deleted_at'   =>    'deletedDate',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
