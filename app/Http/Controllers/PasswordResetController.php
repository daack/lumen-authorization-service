<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    public function email(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $user = $this->getUser($request->email, 'email');

        $this->resetter([
            'table'  => 'password_resets',
            'expire' => 60,
        ])->create($user);

        //SEND EMAIL

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $tokens = $this->resetter([
            'table'  => 'password_resets',
            'expire' => 60,
        ]);

        $user = $this->getUser($request->email, 'email');

        if ( ! $tokens->exists($user, $request->token))
        {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'token does not exist');
        }

        $user->update($request->all());

        $tokens->delete($request->token);

        return response($this->transform($user, User::class), Response::HTTP_OK);
    }
}
