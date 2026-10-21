<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreventDuplicateSubmission
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->validate(['request_key' => ['required', 'uuid']])['request_key'];

        // Serialize submissions for this clinic, including simultaneous requests.
        return DB::transaction(function () use ($request, $next, $key) {
            $clinic = (int) $request->user()->clinic_id;
            DB::table('clinics')->where('clinic_id', $clinic)->lockForUpdate()->firstOrFail();
            $scope = $request->route()->getName().':'.($request->route('order')?->getKey() ?? '');
            $payload = $request->except(['_token', 'request_key']);
            $hash = hash('sha256', json_encode([$scope, $payload], JSON_THROW_ON_ERROR));
            $previous = DB::table('submission_receipts')->where('clinic_id', $clinic)->where('request_key', $key)->first();
            if ($previous) {
                abort_unless((int) $previous->user_id === (int) $request->user()->user_id && hash_equals($previous->payload_hash, $hash), 409, 'This submission key has already been used for different details. Reload the form.');

                return redirect($previous->redirect_url)->with('success', 'Already saved. No duplicate payment or order was created.');
            }
            $response = $next($request);
            if ($response->isRedirection() && ! in_array('errors', session()->get('_flash.new', []), true)) {
                DB::table('submission_receipts')->insert([
                    'clinic_id' => $clinic, 'user_id' => $request->user()->user_id,
                    'request_key' => $key, 'payload_hash' => $hash,
                    'redirect_url' => $response->headers->get('Location'), 'created_at' => now(),
                ]);
            }

            return $response;
        }, 3);
    }
}
