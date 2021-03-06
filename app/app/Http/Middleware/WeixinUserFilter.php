<?php
namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Session;


class WeixinUserFilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Session::forget('user');
        $currentUser = Session::get('user');
        if (!$currentUser) {
            $wxUser = Session::get('wxUser');
            $user = User::getByWxUser($wxUser->id);
            if (!$user) {
                dd(action('Weixin\BindController@index'));
                return redirect(action('Weixin\BindController@index'));
            }
            Session::put('user', $user);
        }
        return $next($request);
    }
}
