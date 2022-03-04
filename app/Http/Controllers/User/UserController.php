<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
           'data' => $users
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
          'name'    => 'required|string|max:255',
          'email'   => 'required|email|unique:users',
          'password'=> 'required|min:6|confirmed'
        ];

        // Run the user inputs against the validation rules
        $this->validate($request, $rules);

        // Do some data modification before storing
        $data                       = $request->all();
        $data['password']           = bcrypt($request->password);
        $data['verified']           = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationToken();
        $data['admin']              = User::ADMIN_USER;

        // Store the user data in the database
        $user  = User::create($data);

        // Return response as json to the client
        return response()->json([
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'data' => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $rules = [
            'name'    => 'string|max:255',
            'email'   => 'email|unique:users,' . $user->id, // Don't check the user with tha id
            'password'=> 'min:6|confirmed',
            'admin'   =>  'in:'.User::ADMIN_USER. ','.User::REGULAR_USER
        ];

        // Run the user inputs against the validation rules
        $this->validate($request, $rules);

        // Check for the supplied fields
        if ($request->has('name')){
            $user->name = $request->name;
        }

        // if email is supplied and not he same as the old email
        if ($request->has('email') && $user->amail != $request->email){
            $user->verified           = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationToken();
            $user->email              = $request->email;
        }

        if ($request->has('password')){
            $user->password = bcrypt($request->password);
        }

        // If the admin field is supplied
        if ($request->has('admin')){
            if (!$user->isVerified()){
                return response()->json([
                    'error' => 'Only verified user can modify the admin field',
                    'code'  => 409
                ], 409);
            }
            $user->admin = $request->admin;
        }

        // Check if the model changed, i'e if the user changed something, supplied something different
        // to what is the database record
        if (!$user->isDirty()){
            return response()->json([
                'error' => 'You need to specify a different value to update',
                'code'  => 422
            ], 422);
        }

        // Finally save the user's update
        $user->save();
        // Return response with the updated data
        return response()->json([
            'data' => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // Return response with the deleted data
        return response()->json([
            'data' => $user
        ], 200);
    }
}
