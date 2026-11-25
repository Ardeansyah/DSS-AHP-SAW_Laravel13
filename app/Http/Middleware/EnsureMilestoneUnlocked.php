<?php

namespace App\Http\Middleware;

use App\Services\MilestoneGatekeeper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMilestoneUnlocked
{
    public function __construct(protected MilestoneGatekeeper $gatekeeper) {}

    public function handle(Request $request, Closure $next, int $step): Response
    {
        $milestones = $this->gatekeeper->getMilestones();

        if (!isset($milestones[$step]) || !$milestones[$step]['unlocked']) {
            $fallbackRoute = 'spk.kriteria';
            foreach ($milestones as $m) {
                if ($m['unlocked']) {
                    $fallbackRoute = $m['route'];
                }
            }

            return redirect()
                ->route($fallbackRoute)
                ->with('error', $milestones[$step]['lock_msg'] ?? 'Selesaikan milestone sebelumnya terlebih dahulu.');
        }

        return $next($request);
    }
}