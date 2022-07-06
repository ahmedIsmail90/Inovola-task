<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Validator;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return Response
     */
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'email' =>Rule::unique('users')->where(function ($query) use ($input) {
                return $query->where('type', $input['type']);
            }), // email is unique per user type
            'password' => 'required',
            'c_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }



        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'type'     => 'required' // need type because same email could be merchant and user
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password,'type'=>$request->type])) { // auth with three fields
            $user = Auth::user();

            $token=  $user->createToken('MyApp')->plainTextToken;
            if ($user->type =='merchant') {
                $token = $user->createToken('Myapp', ['merchant'])->plainTextToken; //create token has ability merchant
            } elseif ($user->type =='customer') {
                $token = $user->createToken('Myapp', ['customer'])->plainTextToken; //create token has ability customer
            }

            $success['name'] =  $user->name;
            $success['token'] =  $token;

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    /**
     * user types api
     *
     * @return Response
     */
    public function getTypes(Request $request)
    {
        $userypes = Config::get('enum.user_types');
        /*
         * @todo admin abilities
         */
        return $this->sendResponse($userypes, 'user can register with these types');
    }
}
