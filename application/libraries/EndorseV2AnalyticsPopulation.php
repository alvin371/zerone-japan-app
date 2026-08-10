<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Boundary constants only. Readers provide the selected observation set. */
class EndorseV2AnalyticsPopulation
{
    public const ENDORSE_ROW = 'endorse_row';
    public const CANONICAL_CONTENT = 'canonical_content';

    public static function rowKey(int $endorseId, int $generation): string
    {
        return $endorseId . ':' . $generation;
    }
}
