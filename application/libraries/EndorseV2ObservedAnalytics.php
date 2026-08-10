<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pure observed-only calculation. The caller supplies population keys and does
 * not expose a population choice through this class.
 */
class EndorseV2ObservedAnalytics
{
    public static function calculate(array $observations, string $from, string $until, ?int $currentTrustedTotal = null): array
    {
        if ($from > $until) {
            throw new InvalidArgumentException('from must not be after until');
        }

        $byKey = [];
        foreach ($observations as $observation) {
            if (empty($observation['population_key']) || empty($observation['observation_date'])) {
                throw new InvalidArgumentException('population_key and observation_date are required');
            }
            $byKey[(string) $observation['population_key']][] = $observation;
        }

        foreach ($byKey as &$rows) {
            usort($rows, static function (array $a, array $b): int {
                return strcmp((string) ($a['observation_date'] . ' ' . ($a['observed_at'] ?? '')), (string) ($b['observation_date'] . ' ' . ($b['observed_at'] ?? '')));
            });
        }
        unset($rows);

        $daily = [];
        $cursor = new DateTimeImmutable($from, new DateTimeZone('Asia/Jakarta'));
        $last = new DateTimeImmutable($until, new DateTimeZone('Asia/Jakarta'));
        while ($cursor <= $last) {
            $daily[$cursor->format('Y-m-d')] = ['date' => $cursor->format('Y-m-d'), 'observed_total' => null, 'observed_growth' => null, 'provenance' => 'unavailable'];
            $cursor = $cursor->modify('+1 day');
        }

        $opening = 0;
        $openingKnown = false;
        $lastSuccessful = null;
        foreach ($byKey as $rows) {
            $beforeRange = null;
            $firstInRange = null;
            foreach ($rows as $row) {
                $date = (string) $row['observation_date'];
                if ($date < $from) {
                    $beforeRange = $row;
                    continue;
                }
                if ($date > $until) {
                    continue;
                }
                if ($firstInRange === null) {
                    $firstInRange = $row;
                }
                if (!isset($daily[$date]['_rows'])) {
                    $daily[$date]['_rows'] = [];
                }
                $daily[$date]['_rows'][] = $row;
                if (!empty($row['observed_at']) && ($lastSuccessful === null || $row['observed_at'] > $lastSuccessful)) {
                    $lastSuccessful = $row['observed_at'];
                }
            }
            $openingSource = $beforeRange ?: $firstInRange;
            if ($openingSource !== null && array_key_exists($beforeRange ? 'views_after' : 'views_before', $openingSource)) {
                $value = $openingSource[$beforeRange ? 'views_after' : 'views_before'];
                if ($value !== null) {
                    $opening += (int) $value;
                    $openingKnown = true;
                }
            }
        }

        foreach ($daily as &$bucket) {
            $rows = $bucket['_rows'] ?? [];
            unset($bucket['_rows']);
            if (!$rows) {
                continue;
            }
            $total = 0;
            $growth = 0;
            $hasValue = false;
            $hasGrowth = false;
            $hasBaseline = false;
            foreach ($rows as $row) {
                if (array_key_exists('views_after', $row) && $row['views_after'] !== null) {
                    $total += (int) $row['views_after'];
                    $hasValue = true;
                }
                if ($row['views_before'] !== null && $row['views_after'] !== null) {
                    $growth += (int) $row['views_after'] - (int) $row['views_before'];
                    $hasGrowth = true;
                }
                $hasBaseline = $hasBaseline || !empty($row['is_baseline']);
            }
            $bucket['observed_total'] = $hasValue ? $total : null;
            $bucket['observed_growth'] = $hasGrowth ? $growth : null;
            $bucket['provenance'] = $hasBaseline ? 'baseline' : 'observed';
        }
        unset($bucket);

        $inRange = array_values(array_filter($daily, static fn(array $bucket): bool => $bucket['observed_total'] !== null));
        $end = $inRange ? $inRange[count($inRange) - 1]['observed_total'] : null;
        $growth = 0;
        $growthKnown = false;
        foreach ($daily as $bucket) {
            if ($bucket['observed_growth'] !== null) {
                $growth += $bucket['observed_growth'];
                $growthKnown = true;
            }
        }

        return [
            'opening_observed_total' => $openingKnown ? $opening : null,
            'observed_total_at_range_end' => $end,
            'observed_growth' => $growthKnown ? $growth : null,
            'current_trusted_total' => $currentTrustedTotal,
            'last_successful_observation_at' => $lastSuccessful,
            'daily' => array_values($daily),
        ];
    }
}
