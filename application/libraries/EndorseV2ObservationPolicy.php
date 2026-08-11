<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Pure storage policy for one daily V2 observation. */
class EndorseV2ObservationPolicy
{
    /**
     * Existing is the current row for the same business date. Predecessor is
     * the latest row before that date. Metrics are already trusted values.
     */
    public static function build(array $metrics, ?array $existing, ?array $predecessor): array
    {
        $source = $existing ?: $predecessor;
        $isBaseline = $source === null;
        $before = [];
        foreach (['views', 'likes', 'comments', 'shares', 'saves'] as $metric) {
            $afterColumn = $metric . '_after';
            $before[$metric] = $isBaseline
                ? ($metrics[$metric] ?? null)
                : ($source[$afterColumn] ?? null);
        }

        // A same-day update retains its initial-baseline provenance while its
        // before values stay anchored to the first trustworthy observation.
        if ($existing !== null && !empty($existing['is_baseline'])) {
            $isBaseline = true;
        }

        return [
            'before' => $before,
            'is_baseline' => $isBaseline,
            'baseline_reason' => $isBaseline
                ? ($existing['baseline_reason'] ?? 'first_generation_observation')
                : null,
        ];
    }
}
