<?php

namespace Domotique\Repository;

use \PDO;

class GpioRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = new PDO('sqlite:database.db');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function findAllGpio()
    {
        $stmt = $this->db->query("SELECT * FROM gpio");
        $result = $stmt->fetchAll();

        return $result;
    }

    public function newGpio($pin, $name)
    {
        $stmt = $this->db->prepare('INSERT INTO gpio(pin, name, state) VALUES(:pin, :name, 0)');
        $stmt->bindValue(':pin', $pin, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function updateGpio($id, $pin, $name)
    {
        $stmt = $this->db->prepare('UPDATE gpio SET pin = :pin, name = :name WHERE id = :id');
        $stmt->bindValue(':pin', $pin, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function deleteGpio($id)
    {
        $stmt = $this->db->prepare('DELETE FROM gpio WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }
}