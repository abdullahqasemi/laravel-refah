<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $per_page = $request->per_page ?? 10;
            $search = $request->search;

            $users = User::orderBy('created_at', 'desc')
                ->where('name', 'LIKE', "%" . strtolower($search) . "%")
                ->orWhere('email', 'LIKE', "%" . strtolower($search) . "%")
                ->orWhere('id', $search)
                ->paginate($per_page);

            return response()->json([
                'total' => $users->total(),
                'data' => $users->items(),
            ], 200,);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500,);
        }
    }


    public function uniqueness(Request $request)
    {
        $email = $request->email;
        $isUpdate = $request->get('isUpdate');
        $userID = $request->userID;
        $user = null;

        if ($isUpdate == 'true') {
            $user = User::where('email', $email)->whereNot('id', $userID)->first();
        } else {
            $user = User::where('email', $email)->first();
        }


        if ($user) {
            return response()->json([
                'message' => 'Email invalid',
            ], 500,);
        } else {
            return response()->json([
                'message' => 'Email valid',
            ], 200,);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'file' => 'required',
                ]
            );

            $file = $request->file('file');
            $path = $file->store('uploads', 'public');

            $user = User::create(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'profile' => $path,
                ]
            );

            return response()->json([
                'data' =>  $user
            ], 201,);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500,);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $request->validate(
                [
                    'name' => 'required',
                    'email' => 'required',
                ]
            );
            $path = null;
            $file = $request->file('file');

            if ($file) {
                $path = $file->store('uploads', 'public');
            }

            $user = User::find($id);

            $user->update($path != null ? [
                'name' => $request->name,
                'email' => $request->email,
                'profile' => $path
            ] : [
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $user->save();

            return response()->json([
                'data' =>  $user
            ], 202,);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500,);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id,)
    {
        try {
            $user_ids = $request->user_ids;

            User::whereIn('id', $user_ids)->delete();

            return response()->json([], 204,);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500,);
        }
    }
}