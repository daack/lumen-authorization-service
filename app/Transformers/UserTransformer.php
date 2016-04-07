<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * User formatting data for response
     *
     * @param  User   $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id'    => (int) $user->id,
            'email' => $user->email,
        ];
    }
}
