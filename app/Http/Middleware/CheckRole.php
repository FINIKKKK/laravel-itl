<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole {
    public function handle($request, Closure $next, ...$roles) {
        $user = Auth::user();
        $companyId = $request->route('company_id'); // Предполагается, что в маршруте присутствует параметр 'company_id'

        // Проверяем, имеет ли пользователь указанную роль в данной компании
        if ($user->hasRoleInCompany($roles, $companyId)) {
            return $next($request);
        }

        return abort(403, 'Unauthorized');
    }
}
