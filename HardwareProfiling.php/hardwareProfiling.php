<?php


$inputFile = 'input.txt';
$outputFile = 'hardware-profiled.csv';
set_time_limit ( 0 );
ini_set('memory_limit', '6G');

date_default_timezone_set("Europe/Amsterdam");
$currentYear = (int)date("Y");


// Sources:
//* https://www.reddit.com/r/pcmasterrace/comments/4wzer6/i_made_a_chart_explaining_amd_and_nvidias_gpu/
//* http://www.overclock.net/t/692616/nvidias-geforce-naming-scheme-explained-outdated

function findTheNumber($str)
{
    preg_match("/([0-9]{3,})/",$str,$m);
    if (isset($m[1]) && isset($m[1][0]) && !empty($m[1][0])) {
        echo $m[1][0];
        return $m[1][0];
    }
    return false;
}



function estimateGFXCardQuality($gfxName, $outputHandle)
{
    global $currentYear;

    $estimatedAge = 100; //In years
    $estimatedQuality = 5;//Quality estimate from 1-10, where 1=Low end and 10=Ultra High End.


    $theNumber = findTheNumber($gfxName);
    if (!$theNumber) return;

    if (strpos($gfxName,"NVIDIA") !== false)
    {
        // Detect number, if 4-digits then it's from before 2008
        // 3 digits >2008

        if (strlen($theNumber)==4) {
            $estimatedAge = $currentYear-2008;

            // Subtract a year for every generation of NVidia release version
            list($generation,$make) = str_split($theNumber,1);

            $estimatedAge -= $generation;
            $estimatedQuality = $make;
        }
        else {

            // Subtract a year for every generation of NVidia release version, where the 5 series was released in 2011
            list($generation,$make) = str_split($theNumber,1);
            echo "<<<<<<<<<<<<<<" . $theNumber . ">>>>>>>>>>>>>>>>>>>>";

            $estimatedAge = $currentYear - $generation + 6;
            $estimatedQuality = $make;
        }

        // The GTX version is the better version and the GS is the worse version
        if (strpos("GTX",$gfxName)!== false) $estimatedQuality += 1;
        if (strpos("GS",$gfxName)!== false) $estimatedQuality -= 1;

    }
    elseif (strpos($gfxName,"ATI") !== false || strpos($gfxName,"AMD") !== false)
    {
        if (strpos($gfxName,"ATI") !== false) {
            $estimatedAge = $currentYear - 2007;
        }

        list($generation,$make) = str_split($theNumber,1);

        if (strlen($theNumber)==4) {
            $estimatedAge -= (5 + $generation);
            $estimatedQuality = $make - 1;
        }
        else {
            // can't reliably estimate year on newer AMD's, just say 3 years
            $estimatedAge = 3;
            $estimatedQuality = $make - 1;
        }

    }
    elseif (strpos($gfxName,"Intel") !== false)
    {
        // Who cares about Intel, it's not even real
        $estimatedAge = 5;
        $estimatedQuality = 5;
    }
    else
    {
        // Can't make an estimation
        return;
    }


    fputcsv($outputHandle, array($gfxName, $estimatedAge, $estimatedQuality));
}

$inputHandle = fopen($inputFile, "r");
if ($inputHandle) {

    $outputHandle = fopen($outputFile,'w');

    while (($line = fgets($inputHandle)) !== false) {

        // Input file should contain unique GFX Card names
        estimateGFXCardQuality(trim($line),$outputHandle);


    }

    fclose($outputHandle);
    fclose($inputHandle);
}