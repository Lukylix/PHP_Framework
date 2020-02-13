<?php
interface Repository {
  function getAll(string $table);
  function getAllBy(array $request);
}