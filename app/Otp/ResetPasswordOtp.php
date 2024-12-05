<?php

namespace App\Otp;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use SadiqSalau\LaravelOtp\Contracts\OtpInterface as Otp;

class ResetPasswordOtp implements Otp
{
    /**
     * Constructs Otp class
     */
    public function __construct(protected string $email)
    {
        //
    }

    /**
     * Processes the Otp
     *
     * @return mixed
     */
    public function process()
    {
        $user = User::where('email', $this->email)->first();

        event(new PasswordReset($user));

        return $user;
    }
}
