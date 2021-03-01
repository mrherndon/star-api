<?php
include_once('../ini.php');
echo '<pre>';

$regWindow = registrationWindowData::getById(1);

var_dump($regWindow);

$badRegwindow = registrationWindowData::getById(0);

var_dump($badRegwindow);