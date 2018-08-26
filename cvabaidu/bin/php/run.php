<?php
/**
 * Created by PhpStorm.
 * User: lee
 * Date: 17/12/8
 * Time: 下午12:25
 */

$class = $argv[1];
$func = $argv[2];
$classFile = $class . ".php";

require $classFile;

$class::$func();

