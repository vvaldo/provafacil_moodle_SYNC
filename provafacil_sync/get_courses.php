<?php
require_once(__DIR__ . '/../../config.php');

global $DB;

// Obter o ID da subcategoria selecionada
$subcategoryid = required_param('subcategoryid', PARAM_INT);

// Consultar cursos da subcategoria
$courses = $DB->get_records('course', ['category' => $subcategoryid, 'visible' => 1], 'fullname ASC');

// Gerar o JSON para retornar
$result = [];
foreach ($courses as $course) {
    $result[] = [
        'id' => $course->id,
        'name' => $course->fullname,
    ];
}

header('Content-Type: application/json');
echo json_encode(['courses' => $result]);
exit;
