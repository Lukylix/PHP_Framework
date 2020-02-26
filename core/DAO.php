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
    //Then turn it as a indexed array of words
    $request = explode(" ", preg_replace(
      ['/>=|<=|!=|=|>|<|LIKE|\(|\)|AND|OR/', '/\s{2,}/', '/^\s|\s$|\'|"|`|WHERE/'],
      [' $0 ', ' ', ''],
      $request
    ));
    //Start to build the querry coresponding to the entity
    $query = 'SELECT * FROM ' . strtolower(lcfirst(get_class($entity))) . ' WHERE';
    foreach ($request as $key => $word) {
      //If the word is a valid délimeter append it to querry
      if (preg_match('/^(\(|\)|AND|OR)$/', $word)) {
        $query .= " $word";
      }
      //Else if the word is an opérator and the word before corespond to a entity property
      elseif (
        preg_match('/^(>=|<=|!=|=|>|<|LIKE)$/', $word)
        && $r->hasProperty($request[$key - 1])
      ) {
        //Appen the entity property (table column) and the opérator to the querry
        $query .= " " . $request[$key - 1] . " $word ?";
        //Add the coreponding value to the array of values
        $values[] = $request[$key + 1];
      }
    }
    //Prepare the querry statement to be bound
    $stmt = $this->pdo->prepare($query);
    //Execute the querry with the replaced values
    $stmt->execute($values);
    //Fetch the results and return it
    return $stmt->fetchAll();
  }
  // ############
  // #   CRUD   #
  // ############
  function retrive(string $entityName, int $id)
  {
    //Prepare the querry statement coresponding to the entity for execution after binding
    $stmt = $this->pdo->prepare('SELECT * FROM ' . lcfirst($entityName) . ' WHERE id = ?');
    //Precise the value to replace inside the querry and make sure it's a INT
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    //Execute the final querry to the database
    $stmt->execute();
    //Fetch the result and return it
    return $stmt->fetch();
  }
  function delete(string $entityName, int $id)
  {
    //Prepare the querry statement coresponding to the entity for execution after binding
    $stmt = $this->pdo->prepare('DELETE FROM ' . lcfirst($entityName) . ' WHERE id = ?');
    //Precise the value to replace inside the querry and make sure it's a INT
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    //Execute the final querry to the database
    $stmt->execute();
  }
  function update(EntityModel $entity)
  {
    //Get all entity properties exept id and dao
    $properties = array_diff_key((new ReflectionClass($entity))->getDefaultProperties(),
      array_flip(['id', 'dao'])
    );
    //Start to build the querry coresponding to the entity
    $query = 'UPDATE ' . lcfirst(get_class($entity)) . ' SET';
    foreach ($properties as $column => $null) {
      //Build the string to présice the places of values and table columns
      $query .= (isset($firstLoop) ? ',' : $firstLoop = FALSE) . " $column = ?";
      //Make an array that contain all the values to be bound form getters
      $values[] = $entity->{'get' . ucfirst($column)}();
    }
    //Add the id value (last value to be bound)
    $values[] = $entity->getId();
    //Prepare the querry for binding
    $stmt = $this->pdo->prepare($query . " WHERE id = ?");
    //Excute the final querry with the replaced values
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
      //Make an array that contain all the values to be bound form getters
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
