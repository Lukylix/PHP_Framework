<?php

class Controller
{
  protected $post;
  protected $get;

  function __construct()
  {
    $this->post = $_POST;
    $this->get = $_GET;
  }
  //Create a vue and inject data inside it
  final protected function render(string $viewPath, $data = null)
  {
    if (is_array($data)){
      foreach ($data as $key => $val) {
        ${$key} = $val;
      }
    }
    $extensions = ['php', 'html'];
    //Remove '/' at the start of the string
    $viewPath = preg_replace('/^\//', '', $viewPath);
    foreach ($extensions as $ext) {
      if (file_exists("views/$viewPath.$ext")) return include("views/$viewPath.$ext");
    }
    return false;
  }
  //Json output
  final protected function renderJson($data , $prettyJson = true){
    header('Content-Type: application/json');
    if ($prettyJson){
      echo json_encode($data, JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);
    }else{
      echo json_encode($data, JSON_FORCE_OBJECT);
    }
  }
  function inputget()
  {
    return $this->get;
  }
  function inputPost()
  {
    return $this->post;
  }
}
