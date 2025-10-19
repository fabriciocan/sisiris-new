<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMembroStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Se não há usuário autenticado, deixa passar (será tratado por outro middleware)
        if (!$user) {
            return $next($request);
        }

        // Verifica se o usuário tem um perfil de membro
        if ($user->membro) {
            $membro = $user->membro;

            // Se a membro está afastada ou desligada, faz logout
            if (in_array($membro->status, ['afastada', 'desligada'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redireciona para login com mensagem
                return redirect()
                    ->route('filament.admin.auth.login')
                    ->with('error', 'Seu acesso foi bloqueado. Status: ' . ucfirst($membro->status));
            }
        }

        return $next($request);
    }
}
