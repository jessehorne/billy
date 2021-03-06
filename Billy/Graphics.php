<?php

namespace Billy;

use Billy;

class Graphics {
    public function rectangle($mode, $x, $y, $width, $height) {
        // This is done to save space, so that we can fit more data into
        // requests.
        $x = floor($x);
        $y = floor($y);
        $width = floor($width);
        $height = floor($height);

        Game::add_event([
            "c" => Game::$protocol["rectangle"],
            "a" => [$mode, $x, $y, $width, $height]
        ]);
    }

    public function set_color($rgba) {
        Game::add_event([
            "c" => Game::$protocol["setColor"],
            "a" => $rgba
        ]);
    }

    public function new_font($name, $path, $size) {
        Game::add_event([
            "c" => Game::$protocol["newFont"],
            "a" => [$name, $path, $size]
        ]);
    }

    public function set_font($name) {
        Game::add_event([
            "c" => Game::$protocol["setFont"],
            "a" => $name
        ]);
    }

    public function arc($mode, $x, $y, $radius, $angle1, $angle2, $segments) {
        Game::add_event([
            "c" => Game::$protocol["arc"],
            "a" => [$mode, $x, $y, $radius, $angle1, $angle2, $segments]
        ]);
    }

    public function circle($mode, $x, $y, $radius, $segments) {
        Game::add_event([
            "c" => Game::$protocol["circle"],
            "a" => [$mode, $x, $y, $radius, $segments]
        ]);
    }

    public function clear() {
        Game::add_event([
            "c" => Game::$protocol["clear"]
        ]);
    }

    public function line() {
        Game::add_event([
            "c" => Game::$protocol["line"],
            "a" => func_get_args()
        ]);
    }

    public function point($x, $y) {
        Game::add_event([
            "c" => Game::$protocol["point"],
            "a" => [$x, $y]
        ]);
    }

    public function polygon() {
        Game::add_event([
            "c" => Game::$protocol["polygon"],
            "a" => func_get_args()
        ]);
    }

    public function present() {
        Game::add_event([
            "c" => Game::$protocol["present"]
        ]);
    }

    public function print_string() {
        Game::add_event([
            "c" => Game::$protocol["print"],
            "a" => func_get_args()
        ]);
    }

    public function new_image() {
        Game::add_event([
            "c" => Game::$protocol["newImage"],
            "a" => func_get_args()
        ]);
    }

    public function draw_image() {
        $args = func_get_args();
        $path = array_splice($args, 0, 1);
        Game::add_event([
            "c" => Game::$protocol["drawImage"],
            "i" => $path[0],
            "a" => $args
        ]);
    }
}
