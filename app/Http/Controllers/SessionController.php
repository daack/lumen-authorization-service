<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required',
            'password' => 'required',
        ]);

        $user = $this->getUser($request->email, 'email');

        if (app('hash')->check($request->password, $user->password))
        {
            $token = $this->token();
            $redis = $this->redis();

            $redis->set($token, json_encode($user));
            //24 hours expire
            $redis->expire($token, 86400);

            return response([
                'data' => [
                    'session_token' => $token,
                ]
            ], Response::HTTP_CREATED);
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
            'session_token' => 'required',
        ]);

        $this->redis()->del($request->session_token);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
