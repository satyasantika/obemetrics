<?php

namespace App\Http\Middleware;

use App\Models\Bk;
use App\Models\Cpl;
use App\Models\ProdiUser;
use App\Models\Kurikulum;
use App\Models\Mk;
use App\Models\Prodi;
use App\Models\Profil;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKurikulumAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $targetProdiId = $this->resolveTargetProdiId($request);
        if (!$targetProdiId) {
            return $next($request);
        }

        $hasAccess = ProdiUser::query()
            ->where('user_id', $user->id)
            ->where('prodi_id', $targetProdiId)
            ->where('status_pimpinan', true)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke kurikulum pada prodi ini.');
        }

        return $next($request);
    }

    private function resolveTargetProdiId(Request $request): ?string
    {
        $routeProdi = $request->route('prodi');
        if ($routeProdi) {
            $prodi = $routeProdi instanceof Prodi
                ? $routeProdi
                : Prodi::query()->find($routeProdi);

            return $prodi?->id;
        }

        $routeKurikulum = $request->route('kurikulum');
        if ($routeKurikulum) {
            $kurikulum = $routeKurikulum instanceof Kurikulum
                ? $routeKurikulum
                : Kurikulum::query()->find($routeKurikulum);

            return $kurikulum?->prodi_id;
        }

        $routeMk = $request->route('mk');
        if ($routeMk) {
            $mk = $routeMk instanceof Mk
                ? $routeMk
                : Mk::query()->find($routeMk);

            return $mk?->kurikulum?->prodi_id;
        }

        $routeProfil = $request->route('profil');
        if ($routeProfil) {
            $profil = $routeProfil instanceof Profil
                ? $routeProfil
                : Profil::query()->find($routeProfil);

            return $profil?->kurikulum?->prodi_id;
        }

        $routeCpl = $request->route('cpl');
        if ($routeCpl) {
            $cpl = $routeCpl instanceof Cpl
                ? $routeCpl
                : Cpl::query()->find($routeCpl);

            return $cpl?->kurikulum?->prodi_id;
        }

        $routeBk = $request->route('bk');
        if ($routeBk) {
            $bk = $routeBk instanceof Bk
                ? $routeBk
                : Bk::query()->find($routeBk);

            return $bk?->kurikulum?->prodi_id;
        }

        $requestProdiId = $request->input('prodi_id');
        if ($requestProdiId) {
            return (string) $requestProdiId;
        }

        $requestKurikulumId = $request->input('kurikulum_id');
        if ($requestKurikulumId) {
            return Kurikulum::query()->whereKey($requestKurikulumId)->value('prodi_id');
        }

        $requestMkId = $request->input('mk_id');
        if ($requestMkId) {
            return Mk::query()
                ->whereKey($requestMkId)
                ->with('kurikulum:id,prodi_id')
                ->first()?->kurikulum?->prodi_id;
        }

        return null;
    }
}
