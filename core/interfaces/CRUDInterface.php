<?php
interface CRUD
{
  function retrive(string $entityName, int $id);
  function update(EntityModel $entity);
  function delete(string $entityName, int $id);
  function create(EntityModel $entity);
}
