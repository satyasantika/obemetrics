<?php

namespace App\Actions;

use App\Models\Mk;
use Illuminate\Support\Collection;

class ResolveMkSemester
{
    /**
     * Resolve the active semester for an MK page, persisting the choice to session.
     *
     * @return array{0: \App\Models\Semester|null, 1: string|null}
     */
    public static function resolve(Mk $mk, ?string $requestedId, Collection $options): array
    {
        $key = 'mk_semester_' . $mk->id;

        if ($requestedId !== null && $requestedId !== '') {
            if ($options->firstWhere('id', $requestedId)) {
                session([$key => $requestedId]);
            }
        }

        $stored = session($key);

        $selected = ($requestedId ? $options->firstWhere('id', $requestedId) : null)
            ?? ($stored ? $options->firstWhere('id', $stored) : null)
            ?? $options->firstWhere('status_aktif', true)
            ?? $options->first();

        if ($selected) {
            session([$key => (string) $selected->id]);
        }

        return [$selected, $selected?->id];
    }
}
