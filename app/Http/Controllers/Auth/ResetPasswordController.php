<?php

namespace App\Http\Controllers\Auth;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected function redirectTo()
    {
        $user = Auth::user();
        if($user->hasRole('superadministrator')) {
            return route('manage.index');
        }
        if($user->hasRole(['customer', 'provider'])) {
            return route('mainpage.index');
        }

        if($user->hasRole('guide')) {
            if(empty($user->detail)) {
                return route('guide.profile.index', ['user' => $user->id, 'name' => $user->name]);
            }

            else {
                return route('guide.dashboard.index', ['user' => $user->id, 'name' => $user->name]);
            }
            return route('mainpage.index');
        }

        return route('mainpage.index'); 
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showResetForm(Request $request, $token = null)
    {
        $setting = Setting::first();

        return view('frontend.theme.medicio.other_pages.auth.password.reset', compact('setting'))->with(
            ['token' => $token, 'email' => $request->email]
        );
    }
}
