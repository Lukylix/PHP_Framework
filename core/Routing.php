<?php
class Routing
{
  private array $config;
  private array $uri;
  private string $route;
  private array $arg;
  function __construct()
  {
    $this->config = json_decode(file_get_contents("./config/routes.json"), true);
    $this->execute();
  }
  function getConfig()
  {
    return $this->config;
  }
  private function execute()
  {
    $this->uri = $this->splitString($_SERVER['REQUEST_URI']);
    foreach ($this->config as $route => $value) {
      $routeTab = $this->splitString($route);
      if ($this->isEqual($routeTab, $this->uri)) {
        if ($this->compare($routeTab, $this->uri)) {
          $this->route = "$route";
          $this->invoke();
        }
      }
    }
  }
  private function isEqual(array $tab, array $tab2)
  {
    return count($tab) == count($tab2) ? true : false;
  }
  private function addArgument($index)
  {
    $this->arg[] = $this->uri[$index];
  }
  private function compare(array $tabConfig, array $tabUri)
  {
    for ($i = 0; $i < count($tabConfig); $i++) {
      if ($tabConfig[$i] !== $tabUri[$i]) {
        if ($tabConfig[$i] == "(:)") {
          $this->addArgument($i);
        } else return false;
      }
    }
    return true;
  }

  //Create object controler et use targetMethod
  private function invoke()
  {
    $target = $this->getTarget();
    $target = preg_match('/:/', $target) ? $this->splitString($target, '/:/') : false;
    $targetClass = $target ? $target[0] : false;
    $targetMethod = count($target) > 1 ? $target[1] : false;
    if ($targetClass && $targetMethod) {
      $this->includeClass($targetClass);
      //If class imported
      if (class_exists($targetClass)) {
        $object = new $targetClass();
        if (Method_exists($object, $targetMethod)) {
          if (isset($this->arg) && count($this->arg)) return $object->{$targetMethod}(...$this->arg);
          return $object->{$targetMethod}();
        }
      } else {
        echo "<p>Class $targetClass not found !</p>";
        return;
      }
    }
    echo "<p>Wrong route config missing " . ($targetClass ? "targetMethod " . $targetClass . ':' . $targetMethod : "class $targetClass") . '</p>';
  }

  private function getTarget()
  {
    $target = $this->config[$this->route];
    if (is_array($target)) {
      foreach ($target as $httpMethod => $val) {
        if ($httpMethod == $_SERVER['REQUEST_METHOD']) {
          $target = $val;
          break;
        }
      }
      if (is_array($target)) {
        echo "<p>No matching route for" . $_SERVER['REQUEST_METHOD'] . " HTTP verb</p>";
        return false;
      }
    }
    return $target;
  }

  private function includeClass($targetClass){
    $searchFolders = ["controller", "models", "core"];
      if (!class_exists($targetClass)) {
        foreach ($searchFolders as $folder) {
          if ($this->includeInFolder($folder, $targetClass . '.php')) break;
        }
      }
  }
  //Search recursively in folder to include a php file
  private function includeInFolder(string $path, string $filename)
  {
    //Get all items in folder
    $folderContent = scandir($path);
    //Remove relative directory . & ..
    $folderContent = preg_grep('/^\.{1,2}$/', $folderContent, PREG_GREP_INVERT);

    foreach ($folderContent as $item) {
      if ($item == $filename) {
        include_once "$path/$filename";
        return true;
      }
      //If is folder search in folder
      if (is_dir($path . "/$item")) {
        return $this->includeInFolder($path . "/$item", $filename);
      }
    }
    return false;
  }

  private function splitString(string $string, $regex = '/\//')
  {
    return preg_split($regex, $string,  -1, PREG_SPLIT_NO_EMPTY);
  }
}
