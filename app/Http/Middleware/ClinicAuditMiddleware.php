<?php

namespace App\Http\Middleware;

use App\Services\ClinicAuditService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClinicAuditMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        if (
            $request->hasSession()
            && in_array(
                'errors',
                $request->session()->get('_flash.new', []),
                true
            )
        ) {
            return $response;
        }

        $actor = $request->user();

        if (!$actor) {
            return $response;
        }

        $routeName = $request->route()?->getName() ?? '';

        if (str_starts_with($routeName, 'notifications.')) {
            return $response;
        }

        $directUpdates = [
            'settings.update',
            'settings.account.update',
            'settings.security.password.update',
        ];

        if (
            $request->attributes->get('clinic_model_audited')
            && !in_array($routeName, $directUpdates, true)
        ) {
            return $response;
        }

        $entityType = explode('.', $routeName)[0] ?: 'clinic';
        $entityId = null;

        foreach ($request->route()->parameters() as $parameter) {
            if ($parameter instanceof Model) {
                $entityType = $parameter->getTable();
                $entityId = (int) $parameter->getKey();
                break;
            }
        }

        if (str_starts_with($routeName, 'settings.')) {
            $entityType = $routeName === 'settings.update' ? 'clinics' : 'users';
            $entityId = $entityType === 'clinics'
                ? (int) $actor->clinic_id
                : (int) $actor->user_id;
        }

        $fields = array_diff(
            array_keys($request->except([
                '_token',
                '_method',
                'token',
                'remember_token',
            ])),
            ['password', 'password_confirmation']
        );

        app(ClinicAuditService::class)->record(
            (int) $actor->clinic_id,
            $routeName . '.completed',
            $entityType,
            $entityId,
            [
                'submitted_fields' => array_values($fields),
                'password_changed' =>
                    $routeName === 'settings.security.password.update',
            ]
        );

        return $response;
    }
}