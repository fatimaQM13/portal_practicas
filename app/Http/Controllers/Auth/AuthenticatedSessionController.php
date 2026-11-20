<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión y bifurca según el rol del usuario.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Valida correo y contraseña
        $request->authenticate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 2. Flujo de 3 etapas para Administrador
        if ($user->role === 'admin') {
            $code = (string) rand(100000, 999999);

            $user->two_factor_code = $code;
            $user->two_factor_expires_at = now()->addMinutes(5);
            $user->save();

            // Guardamos el ID temporalmente en sesión para la pantalla de código
            session(['admin_user_id' => $user->id]);

            // Cerramos la sesión activa hasta que valide el código de 6 dígitos
            Auth::logout();

            return redirect()
                ->route('admin.verify')
                ->with('status', 'Código de verificación generado para ' . $user->email . ': ' . $code . ' (vigente por 5 minutos).');
        }

        // 3. Flujo directo para Empleado
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}