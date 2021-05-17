<?php
include_once('../../ini.php');
use STAR\post\mapper;
use STAR\post\response;

$mapper = new mapper();
$response = new response();

if(!isset($_POST['studentsSchedules'])) $response->return('Student schedules not sent.');
if(!isset($_POST['action'])) $response->return('No action given.');

$requestStudentsSchedules = json_decode($_POST['studentsSchedules'], true);
$studentsSchedules = [];

foreach($requestStudentsSchedules as $studentSchedule) {
    $studentScheduleData = $mapper->map($studentSchedule, studentsSchedulesData::getByStudentIdAndScheduleId($studentSchedule['studentId'], $studentSchedule['scheduleId']));
    if($studentScheduleData->updatedBy == 0)$studentScheduleData->updatedBy = $user->id;
    $studentsSchedules[] = $studentScheduleData;
}

if($_POST['action'] == 'enroll') {
    // $student->save();
    $response->array = $studentsSchedules;
    $response->message = ' updated!';
}


$response->return();