<?php

require 'vendor/autoload.php';

use Rubik\RubiksCube;

$cube = new RubiksCube();

$cube->front()->back()->dump();
