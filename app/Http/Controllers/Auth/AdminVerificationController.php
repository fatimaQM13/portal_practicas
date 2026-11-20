<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminVerificationController extends Controller
{
    /**
     * Muestra la pantalla para ingresar el código de 6 dígitos.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $adminId = $request->session()->get('admin_user_id');

        if (! $adminId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Primero inicia sesión con tu cuenta de administrador.']);
        }

        return view('auth.verify-admin-code');
    }

    /**
     * Valida el código de 6 dígitos y su vigencia de 5 minutos.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Debes ingresar el código de verificación.',
            'code.digits' => 'El código debe contener exactamente 6 dígitos.',
        ]);

        $adminId = $request->session()->get('admin_user_id');

        if (! $adminId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'La sesión de verificación caducó. Vuelve a iniciar sesión.']);
        }

        /** @var \App\Models\User|null $user */
        $user = User::find($adminId);

        if (! $user || ! $user->two_factor_code || ! $user->two_factor_expires_at) {
            $request->session()->forget('admin_user_id');

            return redirect()->route('login')
                ->withErrors(['email' => 'No hay un código activo. Inicia sesión nuevamente.']);
        }

        // Verifica si ya pasaron los 5 minutos de vigencia
        if (now()->greaterThan($user->two_factor_expires_at)) {
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->save();

            $request->session()->forget('admin_user_id');

            return redirect()->route('login')
                ->withErrors(['email' => 'El código ha expirado (vigencia de 5 minutos). Inicia sesión otra vez.']);
        }

        // Verifica que el código coincida exactamente
        if ($request->input('code') !== $user->two_factor_code) {
            return back()
                ->withErrors(['code' => 'El código ingresado es incorrecto.']);
        }

        // Código válido y dentro de los 5 minutos: limpia campos y abre sesión oficial
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $request->session()->forget('admin_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}