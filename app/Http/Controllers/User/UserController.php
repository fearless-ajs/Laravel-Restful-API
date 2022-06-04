<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('transform.input:' . UserTransformer::class)->only(['store', 'update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return $this->showAll($users);
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
        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, User $user)
    {
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
                return $this->errorResponse('Only verified user can modify the admin field', 409);
            }
            $user->admin = $request->admin;
        }

        // Check if the model changed, i'e if the user changed something, supplied something different
        // to what is the database record
        if (!$user->isDirty()){
            return $this->errorResponse('You need to specify a different value to update', 422);
        }

        // Finally save the user's update
        $user->save();
        // Return response with the updated data
        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        // Return response with the deleted data
        return response()->json([
            'data' => $user
        ], 200);
    }


    public function verify($token){
        // firstOrFail() returns model not found defined in our exception handler if user not found
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token   = null;

        $user->save();

        return $this->showMessage('The account has been verified successfully');
    }

    public function resend(User $user){

        if ($user->isVerified()){
            return $this->errorResponse('This user is already verified', 409);
        }

        retry(5, function () use ($user) {
            Mail::to($user->email)->send(new UserCreated($user));
        });
        return $this->showMessage('The verification email has been sent');
    }
}
