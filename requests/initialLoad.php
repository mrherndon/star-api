<?php

echo json_encode([
    "schools" => schoolData::getRows(),
    "grades" => gradesData::getRows(),
    "programListings" => programListingData::getCurrentlyEnrollingPrograms($_POST['testing'] ?? false),
    "schedules" => scheduleData::getCurrentlyEnrollingSchedules($_POST['testing'] ?? false),
    "extendedSchedules" => extendedScheduleData::getCurrentlyEnrollingExtendedSchedules($_POST['testing'] ?? false),
    "programTypes" => programTypesData::getRows(),
    "gradeGroups" => gradesData::getRowsGrouped(),
]);
exit;