<?php

namespace App\Http\Middleware;

use App\Models\Cpmk;
use App\Models\JoinMkUser;
use App\Models\KontrakMk;
use App\Models\Mk;
use App\Models\Nilai;
use App\Models\Penugasan;
use App\Models\Subcpmk;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMkAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $targetMkId = $this->resolveTargetMkId($request);
        if (!$targetMkId) {
            return $next($request);
        }

        $hasJoinMkAccess = JoinMkUser::query()
            ->where('mk_id', $targetMkId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasJoinMkAccess) {
            abort(403, 'Anda tidak memiliki akses ke mata kuliah ini.');
        }

        if ($this->requiresKontrakAccess($request)) {
            $hasKontrakAccess = KontrakMk::query()
                ->where('mk_id', $targetMkId)
                ->where('user_id', $user->id)
                ->exists();

            if (!$hasKontrakAccess) {
                abort(403, 'Penilaian belum dapat diakses karena Anda belum tercatat pada kontrak mata kuliah ini.');
            }
        }

        return $next($request);
    }

    private function requiresKontrakAccess(Request $request): bool
    {
        return $request->routeIs(
            'mks.nilais.*',
            'mks.workclouds.*',
            'mks.achievements.*',
            'mks.ketercapaians.*',
            'mks.spyderweb',
            'settings.import.nilais*'
        );
    }

    private function resolveTargetMkId(Request $request): ?string
    {
        $routeMk = $request->route('mk');
        if ($routeMk) {
            $mk = $routeMk instanceof Mk
                ? $routeMk
                : Mk::query()->find($routeMk);

            return $mk?->id;
        }

        $routeCpmk = $request->route('cpmk');
        if ($routeCpmk) {
            $cpmk = $routeCpmk instanceof Cpmk
                ? $routeCpmk
                : Cpmk::query()->find($routeCpmk);

            return $cpmk?->mk_id;
        }

        $routeSubcpmk = $request->route('subcpmk');
        if ($routeSubcpmk) {
            $subcpmk = $routeSubcpmk instanceof Subcpmk
                ? $routeSubcpmk
                : Subcpmk::query()->find($routeSubcpmk);

            return $subcpmk?->mk_id;
        }

        $routePenugasan = $request->route('penugasan');
        if ($routePenugasan) {
            $penugasan = $routePenugasan instanceof Penugasan
                ? $routePenugasan
                : Penugasan::query()->find($routePenugasan);

            return $penugasan?->mk_id;
        }

        $routeNilai = $request->route('nilai');
        if ($routeNilai) {
            $nilai = $routeNilai instanceof Nilai
                ? $routeNilai
                : Nilai::query()->find($routeNilai);

            return $nilai?->mk_id;
        }

        $requestMkId = $request->input('mk_id');
        if ($requestMkId) {
            return (string) $requestMkId;
        }

        return null;
    }
}
