<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserController
{
    public function index(Request $request)
    {
        $data = $request->user();
        return $data;
    }

    public function update(UserUpdateRequest $request)
    {
        if ($request->has('name')) {
            $request->user()->name = $request->name;
        }

        if ($request->has('password')) {
            $request->user()->password = bcrypt($request->password);
        }

        $request->user()->save();

        return response()->json([
            'message' => 'User updated successfully',
        ]);
    }
}
