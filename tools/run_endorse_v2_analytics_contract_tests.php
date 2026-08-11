<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/application/libraries/EndorseV2ObservedAnalytics.php';
require dirname(__DIR__) . '/application/libraries/EndorseV2AnalyticsPopulation.php';
$failures=[]; $assertSame=static function($expected,$actual,$name)use(&$failures){if($expected!==$actual)$failures[]="$name expected ".var_export($expected,true).' got '.var_export($actual,true);};
$observations=[
 ['population_key'=>'1:1','observation_date'=>'2026-08-01','observed_at'=>'2026-08-01 01:00:00','views_before'=>150,'views_after'=>150,'is_baseline'=>1],
 ['population_key'=>'1:1','observation_date'=>'2026-08-02','observed_at'=>'2026-08-02 01:00:00','views_before'=>150,'views_after'=>170,'is_baseline'=>0],
 ['population_key'=>'2:1','observation_date'=>'2026-08-01','observed_at'=>'2026-08-01 02:00:00','views_before'=>50,'views_after'=>50,'is_baseline'=>1],
 ['population_key'=>'2:1','observation_date'=>'2026-08-03','observed_at'=>'2026-08-03 01:00:00','views_before'=>50,'views_after'=>50,'is_baseline'=>0],
];
$report=EndorseV2ObservedAnalytics::calculate($observations,'2026-08-01','2026-08-04',220);
$assertSame(200,$report['opening_observed_total'],'multi-content opening');
$assertSame(20,$report['observed_growth'],'observed growth');
$assertSame(220,$report['current_trusted_total'],'trusted current supplied by caller');
$assertSame(null,$report['daily'][3]['observed_total'],'missing day is null');
$assertSame(null,$report['daily'][3]['observed_growth'],'missing day growth is null');
$assertSame(0,$report['daily'][0]['observed_growth'],'baseline observed zero growth');
$assertSame('baseline',$report['daily'][0]['provenance'],'baseline provenance');
$afterBaseline=EndorseV2ObservedAnalytics::calculate($observations,'2026-08-02','2026-08-03');
$assertSame(200,$afterBaseline['opening_observed_total'],'range after baseline uses predecessor');
$assertSame(null,EndorseV2ObservedAnalytics::calculate([],'2026-08-01','2026-08-02')['observed_growth'],'no observations remain null');
if($failures){fwrite(STDERR,implode(PHP_EOL,$failures).PHP_EOL);exit(1);} echo "Endorse V2 analytics contract tests passed\n";
