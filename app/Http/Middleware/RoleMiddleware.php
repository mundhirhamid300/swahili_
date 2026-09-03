<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Hulinda njia kwa kuruhusu majukumu maalumu ya watumiaji pekee. */
class RoleMiddleware
{
    /** Kagua mtumiaji, jukumu lake na hali ya akaunti kabla ya kuendelea na ombi. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user(); // Pata mtumiaji aliyeingia kutoka kwenye ombi.

        if (! $user) { // Zuia ombi lisilo na mtumiaji aliyeingia.
            return redirect()->route('login'); // Mpeleke mgeni kwenye ukurasa wa kuingia.
        }

        if (! in_array($user->role, $roles, true)) { // Hakikisha jukumu la mtumiaji linaruhusiwa na njia hii.
            $dashboard = match ($user->role) { // Chagua dashboard salama inayolingana na jukumu lake.
                'super_admin', 'admin' => 'admin.dashboard', // Wasimamizi hutumia dashboard ya usimamizi.
                'student' => 'student.dashboard', // Wanafunzi hutumia dashboard yao.
                default => null, // Jukumu lisilotambulika halina ukurasa wa kuelekezwa.
            };

            if ($dashboard) { // Kama kuna dashboard inayofaa, elekeza mtumiaji huko.
                return redirect()->route($dashboard)
                    ->with('warning', 'You have been redirected to your authorized dashboard.'); // Hifadhi ujumbe wa onyo kwa ukurasa unaofuata.
            }

            abort(403, 'Unauthorized access.'); // Kataa kabisa jukumu lisiloruhusiwa.
        }

        if ($user->status !== 'active') { // Ruhusu akaunti zilizo hai pekee.
            abort(403, $user->status === 'pending' // Toa sababu sahihi ya kukataliwa.
                ? 'Your account is awaiting admin approval.' // Akaunti bado inasubiri idhini.
                : 'Your account has been deactivated.'); // Akaunti imezimwa.
        }

        return $next($request); // Endelea na handler inayofuata baada ya ukaguzi kupita.
    }
}
