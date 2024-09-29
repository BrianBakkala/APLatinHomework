<?php
require 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'GenerateNotesandVocab.php';
require_once 'ForwardHTTPS.php';

require_once 'globals.php';
require_once 'FontStyles.php';
require_once 'HomeworkViewerStyles.php';
require_once 'SQLConnection.php';
require_once 'autoload.php';

use eftec\bladeone\BladeOne;

require_once 'SQLConnection.php';
require_once 'utility/debug.php';

$views = __DIR__ . '/views';
$cache = __DIR__ . '/cache';
$blade = new BladeOne($views, $cache, BladeOne::MODE_DEBUG); // MODE_DEBUG allows to pinpoint troubles.

$HW_number = $_GET['hw'];
$Data = getHomeworkAssignment($HW_number);

echo $blade->run("hello", array(

    "HW_number" => $HW_number,

    'HWAssignment' => $Data['Assignment'],

    'HWLines' => $Data['Lines'],

    'TargetedDictionary' => $Data['Dictionary'],

    'HWStartId' => $Data['StartID'],

    'HWEndId' => $Data['EndID'],

)); // it calls /views/hello.blade.php
