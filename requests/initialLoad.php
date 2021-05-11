<?php
$testing = $_POST['testing'] ?? false;
echo json_encode([
    "schools" => schoolData::getRows(),
    "grades" => gradesData::getRows(),
    "enrollingSchools" => schoolData::getCurrentlyEnrollingSchools($testing),
    "programListings" => programListingData::getCurrentlyEnrollingPrograms($testing),
    "registrationWindows" => registrationWindowData::getCurrentlyEnrolling($testing),
    "schedules" => scheduleData::getCurrentlyEnrollingSchedules($testing),
    "extendedSchedules" => extendedScheduleData::getCurrentlyEnrollingExtendedSchedules($testing),
    "programTypes" => programTypesData::getRows(),
    "gradeGroups" => gradesData::getRowsGrouped(),
]);
exit;