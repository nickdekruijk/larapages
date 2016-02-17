<?php

namespace NickDeKruijk\LaraPages;

use Closure;

class LaraPagesAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!session('larapages_user')) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/'.config('larapages.adminpath').'/login');
            }
        }

        return $next($request);
    }
    
    static public function user() {
	    $user=session('larapages_user');
	    
		if (empty($user['name'])) 
			$user['name']=ucfirst($user['username']);
			
	    return (object)$user;
    }
}
