<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
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
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'identifier'    =>  (int)$user->id,
            'name'          =>  (string)$user->name,
            'email'         =>  (string)$user->email,
            'isVerified'    =>  (int)$user->verified,
            'isAdmin'       =>  ($user->admin === 'true'),
            'createdDate'   =>  $user->created_at,
            'lastChange'    =>  $user->updated_at,
            'deletedDate'   =>  isset($user->deleted_at) ? (string) $user->deleted_at : null, // For columns that can null anytime


            'links'         =>  [
                [
                    'rel'   =>  'self',
                    'href'  =>  route('users.show', $user->id),
                ],
            ]
        ];
    }

    public static function originalAttribute($index)
    {
        $attributes = [
            'identifier'    =>  'id',
            'name'          =>  'name',
            'email'         =>  'email',
            'isVerified'    =>  'verified',
            'isAdmin'       =>  'admin',
            'createdDate'   =>  'created_at',
            'lastChange'    =>  'updated_at',
            'deletedDate'   =>  'deleted_at ' // For columns that can null anytime
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index)
    {
        $attributes = [
             'id'           =>      'identifier',
             'name'         =>      'name',
             'email'        =>      'email',
             'verified'     =>      'isVerified',
             'admin'        =>      'isAdmin',
             'created_at'   =>      'createdDate',
             'updated_at'   =>      'lastChange',
             'deleted_at'   =>      'deletedDate',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }


}
