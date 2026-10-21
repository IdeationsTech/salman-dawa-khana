<?php

namespace App\Http\Middleware;

use App\Support\ClinicPermissions;
use Closure;
use Illuminate\Http\Request;

class EnsureClinicPermissions
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(ClinicPermissions::allows($request->user(), $request->route()->getName()), 403, 'Your role does not have permission for this action.');

        return $next($request);
    }
}
