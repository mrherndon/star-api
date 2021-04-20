<?php
include_once('../../ini.php');
use STAR\post\mapper;
use STAR\post\response;

$mapper = new mapper();
$response = new response();

if(!isset($_POST['student'])) $response->return('Student not sent.');
if(!isset($_POST['action'])) $response->return('No action given.');

$requestStudent = json_decode($_POST['student'], true);

$student = $mapper->map($requestStudent, studentData::getRowById($requestStudent['id']));

if($_POST['action'] == 'update') {
    $student->save();
    $response->object = $student;
    $response->message = $student->firstName.' updated!';
}


$response->return();