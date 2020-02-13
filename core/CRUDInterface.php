<?php
interface CRUD
{
  function retrive(int $id);
  function update(int $id);
  function delete(int $id);
  function create(array $request);
}
