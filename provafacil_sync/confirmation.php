<?php
require_once('../../config.php');

require_login();

global $DB, $PAGE, $OUTPUT;


$courseid = optional_param('courseid', 0, PARAM_INT);
$students = optional_param_array('students', [], PARAM_INT);
$apitoken = '#apitoken';
$urlacademic = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academic/';
$apitoken = '#apitoken';

$PAGE->set_url('/local/provafacil_sync/confirmation.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Confirmação de Inscrição');
$PAGE->set_heading('Revisão de Inscrição de Estudantes');


function local_provafacil_enroll_student($candidate_key, $academic_key, $apitoken) {
   

    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academicxcandidate/';
    var_dump ("Recebido na função: Candidate Key = {$candidate_key}, Academic Key = {$academic_key}, Token = {$apitoken}", DEBUG_DEVELOPER);

    $data = [
        'candidate' => $candidate_key,
        'academic' => $academic_key,
    ];

    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    var_dump("JSON enviado: {$json_data}", DEBUG_DEVELOPER);

    $headers = [
        'Authorization: Token ' . $apitoken,
        'Content-Type: application/json',
    ];

    $curl = new curl();
    $response = $curl->post($url, $json_data, ['CURLOPT_HTTPHEADER' => $headers]);
    $http_code = $curl->get_info()['http_code'];

    var_dump("HTTP Code: {$http_code}", DEBUG_DEVELOPER);
    var_dump("Resposta da API: {$response}", DEBUG_DEVELOPER);

    return [
        'http_code' => $http_code,
        'response' => $response,
    ];
}


 /*   $curl = new curl();
    $response = $curl->post($url, json_encode($data), [
        'CURLOPT_HTTPHEADER' => [
            'Authorization: Token ' . $apitoken,
            'Content-Type: application/json',
        ],
    ]);
    return json_decode($response, true);
}*/

echo $OUTPUT->header();

/**
 * Função para obter o `key` a partir do `legacy_key`.
 */

function local_provafacil_get_key($url, $legacy_key, $apitoken) {
    $headers = [
        'Authorization: Token ' . $apitoken,
        'Content-Type: application/json',
    ];
    
    $curl = new curl();
    $curl = new curl();

    $ch = curl_init($url . '?legacy_key=' . urlencode($legacy_key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    /*
    $response = $curl->get($url . '?legacy_key=' . urlencode($legacy_key), [
        'CURLOPT_HTTPHEADER' => [
            'Authorization: Token ' . $apitoken,
            'Content-Type: application/json',
        ],
    ]);

    $data = json_decode($response, true);
    if (isset($data[0]['key'])) {
        return $data[0]['key'];
    }

    var_dump("Erro ao buscar key: " . print_r($data, true));
    return null;
    */
    if (!empty($data) && isset($data[0]['key'])) {
        
        return $data[0]['key'];
    }
    var_dump("Dados decodificados: " . print_r($data, true));
    // Retorna 'Não encontrado' caso a chave não exista
    return 'Não encontrado';
}




// Exibir tabela de revisão
echo '<h3>Revisar Inscrição</h3>';
echo '<form method="post" action="test_enroll.php">';
echo '<table border="1"><tr><th>Estudante</th><th>email</th><th>Candidate Key</th><th>Academic Key</th><th>codigo da disciplina</th><th>disciplina</th></tr>';

foreach ($students as $userid) {
    $user = $DB->get_record('user', ['id' => $userid]);
    $course = $DB->get_record('course', ['id' => $courseid]);
    $apitoken = '#apitoken';
    // Obter `candidate_key` e `academic_key`
    $candidate_key = local_provafacil_get_key(
        'https://unisced.provafacilnaweb.com.br/unisced/api/v1/tm/rest/candidate/',
        $user->idnumber,
        $apitoken
    );

    $academic_key = local_provafacil_get_key(
        'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academic/',
        $course->idnumber,
        $apitoken
    );

    echo '<tr>';
    echo "<td>{$user->firstname} {$user->lastname}</td>";
    echo "<td>{$user->email}</td>";
    echo "<td>{$candidate_key}</td>";
    echo "<td>{$academic_key}</td>";
    echo "<td>{$course->idnumber}</td>";
    echo "<td>{$course->fullname}</td>";
    echo '</tr>';
    /* echo "<input type='hidden' name='candidate_key' value='{$candidate_key}'>";
    echo "<input type='hidden' name='academic_key' value='{$academic_key}'>";
    echo "<input type='hidden' name='apitoken' value='{$apitoken}'>"; */
   // Envie os dados como parte de um array
   echo "<input type='hidden' name='enrollments[]' value='" . json_encode([
    'candidate' => $candidate_key,
    'academic' => $academic_key,
    'student_name' => "{$user->firstname} {$user->lastname}",
    'email' => $user->email,
    'course_code' => $course->idnumber,
    'course_name' => $course->fullname,
    'apitoken' => $apitoken

]) . "'>";
    // Inscrever estudante
 //  if ($_POST['action'] == 'confirm_enroll' && $candidate_key && $academic_key) {
//        debugging("Chamando local_provafacil_enroll_student com Candidate Key: {$candidate_key}, Academic Key: {$academic_key}", DEBUG_DEVELOPER);
 //       $result = local_provafacil_enroll_student($candidate_key, $academic_key, $apitoken);
  //      if ($result) {
 //           echo "<tr><td colspan='3'>Inscrição concluída para {$user->firstname}</td></tr>";
 //       } else {
//            echo "<tr><td colspan='3'>Erro ao inscrever {$user->firstname}</td></tr>";
 //       }
  //  }
    
}

echo '</table>';
echo '<br />';
echo '<button type="submit" class="btn btn-primary">Confirmar e Testar Inscrição</button>';
echo '<button type="submit" name="action" value="confirm_enroll" class="btn btn-primary">Confirmar Inscrição</button>';
echo '<br />';
echo '</form>';
/**
 * Função para inscrever estudantes.
 */
/*
*/
echo $OUTPUT->footer();
?>
