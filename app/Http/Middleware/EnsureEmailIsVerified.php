<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( $request->fullUrl() != route('email.request.verification') && 
           ( ! $request->user() || ! $request->user()->hasVerifiedEmail() ) )
        {
            return response()->json(
                [ 'status' => 403,
                  'message' => 'Silahkan periksa email '.$request->user()->email.' untuk melakukan aktivasi akun Anda. Pastikan memeriksa semua kotak masuk serta folder Spam pada email tersebut.']
                , 403);
        }
        return $next($request);
    }
}