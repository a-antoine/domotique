<?php
    
use Domotique\Repository\TempRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

$tempRepo = new TempRepository('/var/www/Domotique/web/database.db');

$sensors = $tempRepo->findAllSensors();

foreach ($sensors as $sensor) {
    exec('cat /sys/bus/w1/devices/'.$sensor['device'].'/w1_slave | grep "t=" | awk -F "t=" \'{print $2/1000}\'', $output);
    $tempRepo->insertNewTemp($sensor['id'], $output[0]);
    echo $output[0]."\n";
}
    