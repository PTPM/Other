<?php


$inputFile = 'deaths2.log';
$outputFile = 'pmSurvivalRate.csv';
set_time_limit ( 0 );
ini_set('memory_limit', '6G');

date_default_timezone_set("Europe/Amsterdam");
$pmSurvivedRound = true;
$firstDeathOfRoundTime = 0;
$prev = array("","","","");

$inputHandle = fopen($inputFile, "r");
if ($inputHandle) {

    $outputHandle = fopen($outputFile,'w');

    while (($line = fgets($inputHandle)) !== false) {

        list($roundId,$time,$name,$vicSkin) = explode("¶",$line);
		
		if ($vicSkin==147) {
			$pmSurvivedRound = false;
		}
		
		
		if ($prev[0] != $roundId) {
			fputcsv($outputHandle, array($prev[1], $prev[0], ($pmSurvivedRound?"SURVIVED":"DECEASED"), $prev[2]));
			
			$pmSurvivedRound = true;
			$firstDeathOfRoundTime = $time;
		}
		
		
		$prev = array($roundId,$time,$name);
    }

    fclose($outputHandle);
    fclose($inputHandle);
}