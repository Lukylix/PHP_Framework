<?php

Class Controller {
  private $post;
  private $get;

  function __construct()
  {
    $this->post = $_POST;
    $this->get = $_GET;
  }
  function render(string $vuePath, $data){

  }
  function getGet(){
    return $this->get;
  }
  function getPost(){
    return $this->post;
  }
}