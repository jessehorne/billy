<?php

namespace Billy;

include("./Billy/Game.php");

class MyGame extends Game {

    function __construct() {
        parent::__construct();

        echo "Your minimal game has been started.\n";

        $this->init();
    }

    protected function update($dt) {

    }

    protected function draw() {
        Graphics::print_string("You have successfully created a game! Now get to work.", 100, 100);
    }

    protected function key_pressed($key) {

    }

    protected function key_released($key) {

    }

}

$game = new MyGame();
