<?php

namespace Domotique;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Predis\Client;

class Server implements MessageComponentInterface {
    protected $clients;
    protected $gpio;
    protected $predis;
    protected $pinState = [
        '0' => 'off',
        '2' => 'off'
    ];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->gpio = new Gpio();
        $this->predis = new Client();

        foreach ($this->pinState as $pin => $state) {
            $this->pinState[$pin] = $this->predis->get('pin'.$pin);
            $this->gpio->init($pin);
            $this->gpio->write($pin, $this->pinState[$pin]);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";

        foreach ($this->pinState as $pin => $state) {
            $conn->send(json_encode(['pin' => $pin, 'state' => $state]));
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message '{$msg}' broadcasted from {$from->resourceId}\n";

        $pin = $msg;
        $this->pinState[$pin] = ($this->pinState[$pin] == 'on') ? 'off' : 'on';
        $this->gpio->write($pin, $this->pinState[$pin]); // Write the GPIO pin state
        $this->predis->set('pin'.$pin, $this->pinState[$pin]); // Save the new state in Predis

        foreach ($this->clients as $client) {
            $client->send(json_encode(['pin' => $pin, 'state' => $this->pinState[$pin]]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
