<?php
/** Small dependency-free contract suite for pure Endorse V2 policies. */
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/application/libraries/EndorseV2ObservationPolicy.php';
require dirname(__DIR__) . '/application/libraries/EndorseV2MetricTrustPolicy.php';

$failures = [];
$assertSame = static function ($expected, $actual, string $name) use (&$failures): void {
    if ($expected !== $actual) $failures[] = $name . ' expected ' . var_export($expected, true) . ' got ' . var_export($actual, true);
};

$metrics = ['views'=>150000,'likes'=>20,'comments'=>3,'shares'=>null,'saves'=>null];
$baseline = EndorseV2ObservationPolicy::build($metrics, null, null);
$assertSame(true, $baseline['is_baseline'], 'first observation baseline');
$assertSame(150000, $baseline['before']['views'], 'legacy current is ignored for baseline storage');
$assertSame('first_generation_observation', $baseline['baseline_reason'], 'baseline reason');

$sameDay = EndorseV2ObservationPolicy::build(['views'=>160000,'likes'=>22,'comments'=>4,'shares'=>null,'saves'=>null], ['views_after'=>150000,'likes_after'=>20,'comments_after'=>3,'shares_after'=>null,'saves_after'=>null,'is_baseline'=>1,'baseline_reason'=>'first_generation_observation'], null);
$assertSame(true, $sameDay['is_baseline'], 'same-day baseline provenance retained');
$assertSame(150000, $sameDay['before']['views'], 'same-day growth uses first observed value');

$predecessor = EndorseV2ObservationPolicy::build(['views'=>110000,'likes'=>20,'comments'=>3,'shares'=>1,'saves'=>2], null, ['views_after'=>90000,'likes_after'=>10,'comments_after'=>2,'shares_after'=>1,'saves_after'=>1]);
$assertSame(false, $predecessor['is_baseline'], 'imported predecessor is not baseline');
$assertSame(90000, $predecessor['before']['views'], 'predecessor views used');

$trusted = EndorseV2MetricTrustPolicy::trusted(['views'=>200000,'likes'=>100,'comments'=>10,'share_save'=>8], ['views'=>150000,'likes'=>0,'comments'=>null,'shares'=>null,'saves'=>null], []);
$assertSame(200000, $trusted['views'], 'lower provider views retain trusted value');
$assertSame(0, $trusted['likes'], 'provider zero likes remains explicit zero');
$assertSame(10, $trusted['comments'], 'missing comments retain prior trusted value');
$assertSame(8, $trusted['share_save'], 'missing shares and saves retain combined prior value');

if ($failures) { fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL); exit(1); }
echo "Endorse V2 writer contract tests passed\n";
