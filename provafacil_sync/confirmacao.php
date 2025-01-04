<?php
require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php'); // Biblioteca para usar a classe curl

$apitoken = '#apitoken';

require_login();

$PAGE->set_url('/local/provafacil_sync/confirmacao.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Resultados de Inscrição');
$PAGE->set_heading('Resultados de Inscrição');

echo $OUTPUT->header();
echo '<h3>Resultados de Inscrição</h3>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['enrollments'])) {
    echo '<table border="1" style="width: 100%; border-collapse: collapse;">';
    echo '<tr>
            <th>Email</th>
            <th>Código do Curso</th>
            <th>Nome do Curso</th>
            <th>Resposta da API</th>
            <th>Código HTTP</th>
          </tr>';

    $enrollments = array_map('json_decode', $_POST['enrollments']);
    foreach ($enrollments as $enrollment) {
        $result = local_provafacil_enroll_student($enrollment->candidate, $enrollment->academic);

        echo '<tr>';
        echo '<td>' . htmlspecialchars($enrollment->email) . '</td>';
        echo '<td>' . htmlspecialchars($enrollment->course_code) . '</td>';
        echo '<td>' . htmlspecialchars($enrollment->course_name) . '</td>';
        echo '<td>' . htmlspecialchars($result['response']) . '</td>';
        echo '<td>' . htmlspecialchars($result['http_code']) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo '<p>Nenhum dado recebido para processar.</p>';
}

echo $OUTPUT->footer();

function local_provafacil_enroll_student($candidate_key, $academic_key) {
    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academicxcandidate/';
    $apitoken = '#apitoken';
    $data = [
        'candidate' => $candidate_key,
        'academic' => $academic_key,
    ];

    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $headers = [
        'Authorization: Token ' . $apitoken,
        'Content-Type: application/json',
    ];

    $curl = new curl();
    $response = $curl->post($url, $json_data, ['CURLOPT_HTTPHEADER' => $headers]);
    $http_code = $curl->get_info()['http_code'];

    return [
        'http_code' => $http_code,
        'response' => $response,
    ];
}
?>
