<?php

namespace App\Transformers;

use App\Models\Buyer;
use League\Fractal\TransformerAbstract;

class BuyerTransformer extends TransformerAbstract
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
     * @param Buyer $buyer
     * @return array
     */
    public function transform(Buyer $buyer)
    {
        return [
            'identifier'    =>  (int)$buyer->id,
            'name'          =>  (string)$buyer->name,
            'email'         =>  (string)$buyer->email,
            'isVerified'    =>  (int)$buyer->verified,
            'createdDate'   =>  $buyer->created_at,
            'lastChange'    =>  $buyer->updated_at,
            'deletedDate'   =>  isset($buyer->deleted_at) ? (string) $buyer->deleted_at : null, // For columns that can null anytime
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
