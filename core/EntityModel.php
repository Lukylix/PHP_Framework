<?php
class EntityModel
{
  private $dao;
  private $id;
  function __construct($id = null)
  {
    $this->id = $id;
    $this->dao = new DAO();
  }

  function getId()
  {
    return $this->id;
  }
  function getAll()
  {
    return $this->dao->getAll(get_class($this));
  }
  function getAllBy(string $request){
    return $this->dao->getAllBy($this,$request);
  }

  function load()
  {
    return $this->dao->retrive(get_class($this), $this->id);
  }
  function update()
  {
    if (isset($this->id)) {
      return $this->dao->update($this);
    }
    return $this->dao->create($this);
  }
  function remove()
  {
    $this->dao->delete(get_class($this), $this->id);
  }
}
