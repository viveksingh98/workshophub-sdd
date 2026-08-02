<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SetupController extends Controller
{
    /**
     * Unit 44: the setup wizard — connection check, owner account, public
     * details, class types + pricing, theme — then the site is live.
     */
    public function show(): View|RedirectResponse
    {
        if (User::query()->count() > 0) {
            return redirect()->route('home');
        }

        $connectionOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $connectionOk = false;
        }

        return view('workshophub.setup.wizard', [
            'settings' => Setting::map(),
            'connectionOk' => $connectionOk,
            'themes' => ['studio' => 'Studio', 'garden' => 'Garden', 'chalk' => 'Chalk', 'night' => 'Night', 'paper' => 'Paper'],
        ]);
    }

    public function install(Request $request): RedirectResponse
    {
        if (User::query()->count() > 0) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'owner_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'studio_name' => ['required', 'string', 'max:100'],
            'tagline' => ['required', 'string', 'max:140'],
            'address' => ['required', 'string', 'max:180'],
            'class_types' => ['required', 'string', 'max:300'],
            'pricing_in_studio' => ['required', 'string', 'max:100'],
            'pricing_online' => ['required', 'string', 'max:100'],
            'theme' => ['required', 'in:studio,garden,chalk,night,paper'],
        ]);

        $user = User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['email'],
            'phone' => preg_replace('/\D+/', '', $validated['phone']),
            'password' => $validated['password'],
        ]);

        foreach ([
            'studio_name' => $validated['studio_name'],
            'owner_name' => $validated['owner_name'],
            'logo_text' => mb_strtoupper(mb_substr($validated['studio_name'], 0, 2)),
            'contact_email' => $validated['email'],
            'tagline' => $validated['tagline'],
            'address' => $validated['address'],
            'class_types' => $validated['class_types'],
            'pricing_in_studio' => $validated['pricing_in_studio'],
            'pricing_online' => $validated['pricing_online'],
            'theme' => $validated['theme'],
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'WorkshopHub is installed — the public site is live with your data.');
    }
}
