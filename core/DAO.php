<?php
require_once "./core/interfaces/CRUDInterface.php";
require_once "./core/interfaces/RepositoryInterface.php";

class DAO implements CRUD, Repository
{
  protected $pdo;

  function __construct()
  {
    $dbConfig = file_get_contents("./config/database.json");
    $dbConfig = json_decode($dbConfig, true);
    $dsn = "$dbConfig[driver]:host=$dbConfig[host];port=$dbConfig[port];dbname=$dbConfig[dbname];";
    try {
      $this->pdo = new PDO($dsn, $dbConfig["username"], $dbConfig["password"], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    } catch (PDOException $e) {
      throw new \PDOException($e->getMessage(), (int) $e->getCode());
    }
  }
  // ##############
  // # Repository #
  // ##############  
  function getAll(string $entityName)
  {
    //Get all elements  coresponding to the entity
    $stmt = $this->pdo->query('SELECT * FROM ' . strtolower($entityName));
    //Fetch and return the result
    return $stmt->fetchAll();
  }

  function getAllBy(EntityModel $entity, string $request)
  {
    //Initialise a reflection object to check entity properties later
    $r = new ReflectionClass($entity);
    //Format the request string to be more consitent and remove some typos errors
    $request = explode(" ", preg_replace(
      ['/>=|<=|!=|=|>|<|LIKE|\(|\)|AND|OR/', '/\s{2,}/', '/^\s|\s$|\'|"|`|WHERE/'],
      [' $0 ', ' ', ''],
      $request
    ));
    $query = 'SELECT * FROM ' . strtolower(lcfirst(get_class($entity))) . ' WHERE';
    foreach ($request as $key => $word) {
      if (preg_match('/^(\(|\)|AND|OR)$/', $word)) {
        $query .= " $word";
      } elseif (
        preg_match('/^(>=|<=|!=|=|>|<|LIKE)$/', $word)
        && $r->hasProperty($request[$key - 1])
      ) {
        $query .= " " . $request[$key - 1] . " $word ?";
        $values[] = $request[$key + 1];
      }
    }
    $stmt = $this->pdo->prepare($query);
    $stmt->execute($values);
    return $stmt->fetchAll();
  }
  // ############
  // #   CRUD   #
  // ############
  function retrive(string $entityName, int $id)
  {
    //Prepare a querry statement coresponding to the entity for execution after binding
    $stmt = $this->pdo->prepare('SELECT * FROM ' . lcfirst($entityName) . ' WHERE id = ?');
    //Precise the value to replace inside the querry and make sure it's a INT
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    //Send the formated querry to the database
    $stmt->execute();
    //Fetch the result and return it
    return $stmt->fetch();
  }
  function delete(string $entityName, int $id)
  {
    $stmt = $this->pdo->prepare('DELETE FROM ' . lcfirst($entityName) . ' WHERE id = ?');
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
  }
  function update(EntityModel $entity)
  {
    $properties = array_diff_key((new ReflectionClass($entity))->getDefaultProperties(),
      array_flip(['id', 'dao'])
    );
    $query = 'UPDATE ' . lcfirst(get_class($entity)) . ' SET';
    foreach ($properties as $column => $null) {
      $query .= (isset($firstLoop) ? ',' : $firstLoop = FALSE) . " $column = ?";
      $values[] = $entity->{'get' . ucfirst($column)}();
    }
    $values[] = $entity->getId();
    $stmt = $this->pdo->prepare($query . " WHERE id = ?");
    $stmt->execute($values);
  }
  function create(EntityModel $entity)
  {
    //Get all entity properties exept id and dao
    $properties = array_diff_key((new ReflectionClass($entity))->getDefaultProperties(),
      array_flip(['id', 'dao'])
    );
    //Build the querry string
    foreach ($properties as $column => $null) {
      //Build the string to présice all the afected columns
      $columns .= (isset($firstLoop) ? ',' : '') . '`' . $column . '`';
      //Build the string to présice the places of values
      $data .= (isset($firstLoop) ? ',' : $firstLoop = FALSE) . '?';
      //Make a tab that contains all the values to be bound form getters
      $values[] = $entity->{'get' . ucfirst($column)}();
    }
    //Form the final querry with boundable values
    $stmt = $this->pdo->prepare('INSERT INTO ' . lcfirst(get_class($entity)) .
      '(' . $columns .
      ') VALUES (' . $data . ')');
    //Execute the querry with the coresponding values
    $stmt->execute($values);
  }
}
