<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthUserResource;
use App\Models\User;
use App\Services\EmailValidationService;
use App\Services\UploadService;
use Google_Client;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $validEmail = (new EmailValidationService)->verify($data['email']);

        if (!$validEmail['overall']) {
            throw ValidationException::withMessages([
                __('auth.wrong_domain')
            ]);
        }

        $data['password'] = Hash::make($request->password);

        DB::beginTransaction();
        $user = User::create($data);

        // $user->assignRole('user');
        Db::commit();

        event(new Registered($user));
        $message = trans('messages.create_user');
        $response = [
            'token' => $user->createToken('API TOKEN')->plainTextToken,
            'user' => new AuthUserResource($user),
        ];
        return response()->json([
            'message' => $message,
            'data' => $response,
            'status' => 200
        ]);
    }

    public function login(LoginRequest $request)
    {
        $request->validated();

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed')
            ]);
        }

        $response = [
            'token' => $user->createToken('API TOKEN')->plainTextToken,
            'user' => new AuthUserResource($user),
        ];

        return response()->json([
            'data' => $response,
            'message' => __('messages.user_login'),
            'status' => 200
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $message = trans('messages.user_logout');

        return response()->json([
            'message' => $message,
            'status' => 200
        ]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {

        if (!request()->get('source')) {
            $user = Socialite::driver('google')->stateless()->user();
        } else if (request()->get('source') == 'one-tap') {
            $client = new Google_Client([
                'client_id' => env('GOOGLE_CLIENT_ID')
            ]);

            $user = (object) $client->verifyIdToken(request()->code);
        }

        if (!$user) {
            return response()->noContent();
        }

        DB::beginTransaction();
        $id = $user->sub ?? $user->id;
        $unsplashURL = $user->picture ?? $user->avatar;

        $user = User::firstOrCreate([
            'email' => $user->email,
        ], [
            'username' => $user->email,
            'first_name' => $user->given_name ?? $user->user['given_name'],
            'last_name' => $user->family_name ?? $user->user['family_name'],
            'email' => $user->email,
            'social_id' => "google-$id",
            'social_token' => $user->token ?? null,
        ]);


        if (!$user->avatar && $unsplashURL) {
            (new UploadService)->publicUploader($unsplashURL, User::class, $user->id, 'avatars');
        }

        // $user->assignRole('user');
        DB::commit();

        $token = $user->createToken('API TOKEN')->plainTextToken;
        $response = [
            'user' => new AuthUserResource($user),
            'token' => $token
        ];
        return response()->json($response);
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookCallback()
    {
        $user = Socialite::driver('facebook')->stateless()->user();

        $token = $user->createToken('API TOKEN')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response()->json($response);
    }

    public function forgot(ForgetPasswordRequest $request)
    {
        //send password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json(['message' => __($status), 'status' => 200]);
    }

    //this method will allow you to create new password
    public function reset(request $request)
    {
        //validate the request
        $request->validate([
            'token' => "required",
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        //reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) {
                $user->forcefill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return response()->json(["message" => __($status), "status" => 200]);
    }

    public function getUser()
    {
        return new AuthUserResource(auth('user')->user());
    }
}
