<?php

namespace Billy;

include("./Billy/Helper.php");
include("./Billy/Graphics.php");

use Billy\Game;
use Billy\Helper;

abstract class Game {
    public static $sock;
    public static $config;
    public static $queue = [];
    public static $buffer = [];
    public static $frameNumber = 0;

    public static $protocol = [];

    protected $running = false;

    private $data = [];

    abstract protected function update($dt);
    abstract protected function draw();

    function __construct() {
        // Protocol
        // This 'protocol' array contains events AND commands
        self::$protocol = [
            "rectangle" => 1,
            "setColor" => 2,
            "setTitle" => 3,
            "incoming" => 4,
            "config" => 5,
            "keyPressed" => 6,
            "keyReleased" => 7,
            "quit" => 8,
            "print" => 9,
            "newFont" => 10,
            "setFont" => 11,
            "newSound" => 12,
            "playSound" => 13,
            "arc" => 14,
            "circle" => 15,
            "clear" => 16,
            "line" => 17,
            "point" => 18,
            "polygon" => 19,
            "present" => 20,
            "print" => 21,
            "newImage" => 22,
            "drawImage" => 23
        ];

        // Configuration file
        if (file_exists("config.php")) {
            self::$config = include("config.php");
        }
        else {
            self::$config = [
                "title" => "Unamed",
                "width" => 500,
                "height" => 500,
                "server_port" => 6565,
                "client_port" => 6464
            ];
        }

        // Socket
        self::$sock = socket_create(AF_INET, SOCK_DGRAM, 0);
        if (!self::$sock) {
            Helper::socket_error();
        }
        else {
            socket_set_option(self::$sock, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 0, "usec" => 1000]);
            if (!socket_bind(self::$sock, "0.0.0.0", self::$config["server_port"])) {
                Helper::socket_error();
            }
        }
    }

    // init
    // This function starts and handles the game loop, and sends the Configuration
    // to the Client Implementation
    protected function init() {
        // This sleep should be removed ASAP. It's here because of a bug. Comment
        // out this line, and run your game a couple of times, and you should
        // notice a problem.
        sleep(1);

        $this->running = true;

        // Will be used later for getting the $dt (delta-time)
        $oldTime = microtime(true);
        $drawTime = 0;
        $frameLimit = 1/60;

        // Send Config Details
        Game::send_config();

        // Game Loop
        while ($this->running) {
            // Deltatime
            $newTime = microtime(true);
            $dt = $newTime - $oldTime;
            $oldTime = $newTime;

            // Handle Events sent from the client implementation to here
            $data = "";
            $from = "";
            $port = 0;

            socket_recvfrom(self::$sock, $data, 50, 0, $from, $port);
            if ($data) {
                $data = json_decode($data);
                if ($data->e == self::$protocol["keyPressed"]) $this->key_pressed($data->k);
                if ($data->e == self::$protocol["keyReleased"]) $this->key_released($data->k);
            }

            // Update
            $this->update($dt);

            // Draw
            $drawTime += $dt;
            if ($drawTime > $frameLimit) {
                // echo "$drawTime\n";
                $this->draw();
                Game::send();
                $drawTime = 0;
            }
            sleep(0.001);
        }
    }

    // add_event
    // used when adding requests to the buffer that will be sent to the Client
    // Implementation at a rate defined in $frameLimit
    // See also: send
    public function add_event($event) {
        self::$buffer[] = $event;
    }

    // send
    // This function breaks up the buffer into an array, sends an 'incoming'
    // request to the Client Implementation which tells it what to expect
    public function send() {
        self::$frameNumber += 1;

        self::$queue = array_chunk(self::$buffer, 80);

        Game::incoming(sizeof(self::$queue));

        foreach (self::$queue as $item) {
            $packet = [
                "f" => self::$frameNumber,
                "q" => $item
            ];

            $data = Helper::packet($packet);
            // echo "$data\n";

            if ($data) {
                socket_sendto(self::$sock, $data, strlen($data), 0, "127.0.0.1", self::$config["client_port"]);
            }
        }

        self::$buffer = [];
        self::$queue = [];
    }

    public function set_title($title) {
        Game::add_event([
            "c" => self::$protocol["setTitle"],
            "a" => $title
        ]);
    }

    public function incoming($num) {
        $packet = [
            "c" => self::$protocol["incoming"],
            "a" => $num
        ];

        $data = Helper::packet($packet);

        socket_sendto(self::$sock, $data, strlen($data), 0, "127.0.0.1", self::$config["client_port"]);
    }

    public function send_config() {
        $packet = [
            "c" => self::$protocol["config"],
            "a" => self::$config
        ];

        $data = Helper::packet($packet);

        socket_sendto(self::$sock, $data, strlen($data), 0, "127.0.0.1", self::$config["client_port"]);
    }

    public function new_sound($name, $path, $option) {
        Game::add_event([
            "c" => self::$protocol["newSound"],
            "a" => [$name, $path, $option]
        ]);
    }

    public function play_sound($name) {
        Game::add_event([
            "c" => self::$protocol["playSound"],
            "a" => $name
        ]);
    }
}
