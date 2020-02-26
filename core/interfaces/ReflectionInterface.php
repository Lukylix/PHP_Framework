<?php
interface Reflection
{
  function getClassProperty(object $obecjt);
  function getClassName(object $object);
  function getAnotation($property);
}


/**
  *
  * to get the Class DocBlock
  * echo $reflector->getDocComment()
  *
  * to get the Method DocBlock
  * $reflector->getMethod('fn')->getDocComment();
  *
  *?>
 */