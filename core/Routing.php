<?php
class Routing
{
  private array $config;

  function __construct()
  {
    $this->config = json_decode(file_get_contents("./config/routes.json"), true);
  }
  function getConfig()
  {
    return $this->config;
  }
}
