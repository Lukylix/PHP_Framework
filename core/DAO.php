<?php
require_once "./core/CRUDInterface.php";
require_once "./core/RepositoryInterface.php";
abstract class DAO implements CRUD, Repository
{
  private $pdo;

  function __construct()
  {
    $dbConfig = file_get_contents("./config/database.json");
    $dbConfig = json_decode($dbConfig, true);

    $dsn =  "$dbConfig[driver]:host=$dbConfig[host];port=$dbConfig[port];dbname=$dbConfig[dbname];";
    try {
      $this->pdo = new PDO($dsn, $dbConfig["username"], $dbConfig["password"]);
    } catch (PDOException $e) {
      var_dump($e->getMessage());
      throw new \PDOException($e->getMessage(), (int) $e->getCode());
    }
  }

  function __get($property)
  {
    if ("pdo" === $property) {
      return $this->pdo;
    } else {
      throw new Exception('Propriété invalide !');
    }
  }
  
  function retrive(int $id)
  {
  }
  function update(int $id)
  {
  }
  function delete(int $id)
  {
  }
  function create(array $request)
  {
  }
  function getAll(string $table)
  {
  }
  function getAllBy(array $request)
  {
  }
}
