<?php

echo json_encode([
    "schools" => schoolData::getRows(),
    "grades" => gradesData::getRows(),
    "programs" => programData::getCurrentlyEnrollingPrograms($_POST['testing'] ?? false),
    "programTypes" => programTypesData::getRows(),
    "gradeGroups" => gradesData::getRowsGrouped(),
]);
exit;