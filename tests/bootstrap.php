<?php

if(file_exists(__DIR__ . '/../vendor/autoload.php')){
    require_once __DIR__ . '/../vendor/autoload.php';
}
if(file_exists(__DIR__ . '/../vendor/autoload.php')){
    require_once __DIR__ . '/../../../../autoload.php';
}
// Testable classes


// Test useful classes
require_once 'Grabbag/sourceData/Leaf1.php';
require_once 'Grabbag/sourceData/Leaf2.php';
require_once 'Grabbag/sourceData/List1.php';
require_once 'Grabbag/sourceData/SourceDataHelper.php';
require_once 'Grabbag/testData/TestDataHelper.php';