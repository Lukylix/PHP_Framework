<?php
interface Repository {
  function getAll(string $entityName);
  function getAllBy(EntityModel $entityName,string $request);
}