<?php

namespace Domotique\Repository;

use \PDO;

class TempRepository
{
    protected $db;

    public function __construct($dbFile)
    {
        $this->db = new PDO('sqlite:'.$dbFile);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function findAllSensors()
    {
        $stmt = $this->db->query("SELECT * FROM sensor");

        return $stmt->fetchAll();
    }

    public function newSensor($device, $name)
    {
        $stmt = $this->db->prepare('INSERT INTO sensor(device, name) VALUES(:device, :name)');
        $stmt->bindValue(':device', $device, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function updateSensor($id, $device, $name)
    {
        $stmt = $this->db->prepare('UPDATE sensor SET device = :device, name = :name WHERE id = :id');
        $stmt->bindValue(':device', $device, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function deleteSensor($id)
    {
        $stmt = $this->db->prepare('DELETE FROM sensor WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function insertNewTemp($sensorId, $temp)
    {
        $stmt = $this->db->prepare("INSERT INTO temps_log(sensor_id, date, temp) VALUES (:sensor_id, datetime('now', 'localtime'), :temp)");
        $stmt->bindValue(':sensor_id', $sensorId, SQLITE3_INTEGER);
        $stmt->bindValue(':temp', $temp, SQLITE3_FLOAT);
        $result = $stmt->execute();
    }

    public function findLastTemps($sensorId, $limit, $modulo)
    {
        $stmt = $this->db->prepare("SELECT * FROM (SELECT id, date, temp FROM temps_log WHERE sensor_id = :sensor_id AND id % :modulo = 0 ORDER BY id DESC LIMIT :lim) ORDER BY id ASC");
        $stmt->bindValue(':sensor_id', $sensorId, SQLITE3_INTEGER);
        $stmt->bindValue(':modulo', $modulo, SQLITE3_INTEGER);
        $stmt->bindValue(':lim', $limit, SQLITE3_INTEGER);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}