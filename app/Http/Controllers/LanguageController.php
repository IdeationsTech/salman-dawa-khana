<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate(['locale' => ['required', Rule::in(['en', 'ur'])], 'context' => ['required', Rule::in(['web', 'platform_admin'])]]);
        $request->session()->put('locale', $data['locale']);
        if ($user = Auth::guard($data['context'])->user()) {
            DB::table($user->getTable())->where($user->getKeyName(), $user->getKey())->update(['preferred_locale' => $data['locale']]);
        }
        return back();
    }
}
