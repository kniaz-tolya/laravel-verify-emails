<?php

namespace LaravelVerifyEmails\Foundation\Auth;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use LaravelVerifyEmails\Support\Facades\VerifyEmail;

trait VerifiesEmails
{
    /**
     * Display a message about the user's unverified email address.
     *
     * @return \Illuminate\Http\Response
     */
    public function showUnverifiedForm()
    {
        return view('auth.verify-emails.unverified');
    }

    /**
     * Send another verification email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user->getVerified()) {
            return redirect()->back();
        }

        $response = VerifyEmail::sendVerificationLink($user, function (Message $message) use ($user) {
            $message->subject($user->getVerifyEmailSubject());
        });

        switch ($response) {
            case VerifyEmail::VERIFY_LINK_SENT:
                return redirect()->back()->with('status', trans($response));
        }
    }

    /**
     * Attempt to verify a user.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function verify($token)
    {
        $response = VerifyEmail::verify(Auth::user(), $token);

        switch ($response) {
            case VerifyEmail::EMAIL_VERIFIED:
                return redirect($this->redirectPath())->with('status', trans($response));

            default:
                return redirect()->back()
                            ->withErrors(['email' => trans($response)]);
        }
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}
