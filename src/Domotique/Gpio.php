<?php

namespace Domotique;

class Gpio {

    public function init($pin) {
        exec('gpio mode '.$pin.' out');
    }

    public function write($pin, $state) {
        $state = ($state == 'on') ? 0 : 1;
        exec('gpio write '.$pin.' '.$state);
    }

    public function read($pin) {
        $output = [];
        exec('gpio read '.$pin, $output);

        return ($pin[0] == 0) ? 'on' : 'off';
    }
}
