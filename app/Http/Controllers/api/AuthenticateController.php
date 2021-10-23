<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthenticateController extends Controller
{
    private $success = false;
    private $message = '';
    private $data = [];

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $input = $request->all();
        $validations = [
            'password' => 'required',
            'email' => 'required',
        ];
        $validator = \Validator::make($input, $validations);
        if ($validator->fails()) {
            $this->message = formatErrors($validator->errors()->toArray());
        } else {
            $email = $request->input('email');
            $user = User::where('email', $email)->where('otp_verify', 1)->first();
            if (!empty($user)) {
                $this->message = 'Password does not match';
                if (\Hash::check($request->input('password'), $user->password)) {
                    $user = User::find($user->id);
                    $token = $user->createToken('assessment')->accessToken;
                    $user = $user->toArray();
                    $this->data['token'] = 'Bearer '.$token;
                    $this->data['user'] = $user;
                    $this->success = true;
                    $this->message = 'Login successfully';

                }
            } else {
                $this->message = 'Email not verified. Please Sign up !';
            }
        }
        return response()->json(['success' => $this->success, 'data' => $this->data, 'message' => $this->message]);
    }

    /**
     * signup api
     *
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        $input = $request->all();
        $validations = [
            'name' => ['required', 'string', 'min:4', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];
        $validator = \Validator::make($input, $validations);
        if ($validator->fails()) {
            $this->message = formatErrors($validator->errors()->toArray());
        } else {
            $input['otp_number'] = rand(100000, 999999);
            $input['registered_at'] = now();
            $input['password'] = Hash::make($input['password']);
            $input['user_type'] = 1;
            $user = User::create($input);
            Mail::to($input['email'])->send(new OtpVerificationMail($user->otp_number));
            if (!empty($user)) {
                $token = $user->createToken('assessment')->accessToken;
                $this->data['user_id'] = $user->id;
                $this->data['name'] = $user->name;
                $this->data['email'] = $user->email;
                $this->data['token'] = 'Bearer '.$token;
                $this->success = true;
                $this->message = 'Otp sent successfully. Please check your email!';
            }
        }

        return response()->json(['success' => $this->success, 'message' => $this->message, 'data' => $this->data]);
    }

    /**
     * This is used to test otp
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function otpVerification(Request $request)
    {
        $otp = $request->input('otp');
        $id = $request->user()->id;
        if (!empty($otp)) {
            $obj = User::where('otp_number', $otp)->where('id', $id)->first();
            if (!empty($obj)) {
                $obj->otp_verify = 1;
                $obj->save();

                $this->message = 'User registered successfully';
                $this->success = true;
            } else {
                $this->message = 'Please enter valid otp number';
                $this->success = false;
            }
        }

        return response()->json(['success' => $this->success, 'message' => $this->message]);
    }

    /**
     * This is used to update user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $id = $request->user()->id;
        $obj = User::find($id);
        if ($obj) {
            if (!empty($request->input('image'))) {
                $image = preg_replace('/^data:image\/\w+;base64,/i', '', $request->input('image'));
                $image = str_replace(' ', '+', $image);
                $fileName = createImageUniqueName('jpg');
                $destinationPath = public_path(MobileUserImagePath);
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $tempFile = $destinationPath . '/' . $fileName;
                file_put_contents($tempFile, base64_decode($image));
                $obj->avatar = $fileName;
            }
            if (!empty($request->input('name'))) {
                $obj->name = $request->input('name');
            }
            if(!empty($request->input('email'))) {
                $obj->email = $request->input('email');
            }
            if (!empty($request->input('password'))){
                $obj->password = Hash::make($request->input('password'));
            }
            if ($obj->save()) {
                $this->data['avatar'] =  asset(MobileUserImagePath.'/'.$obj->avatar);
                $this->success = true;
                $this->message = 'Profile is updated successfully';
            }
        }

        return response()->json(['success' => $this->success, 'data' => $this->data, 'message' => $this->message]);
    }
}
