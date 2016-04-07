<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('recaptcha', [
            'only' => [
                'store',
            ]
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create($request->all());

        return response($this->transform($user, User::class), Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $user = $this->getUser($id);

        return response($this->transform($user, User::class), Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUser($id);

        $this->validate($request, [
            'email'    => 'sometimes|required|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|confirmed|min:6',
        ]);

        $user->update($request->all());

        return response($this->transform($user, User::class), Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $user = $this->getUser($id);

        $user->delete();

        return response($this->transform($user, User::class), Response::HTTP_OK);
    }
}
