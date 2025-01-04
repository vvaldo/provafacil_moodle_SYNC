<?php
require_once('../../config.php');

require_login();

global $PAGE, $OUTPUT, $DB;

$courseid = required_param('courseid', PARAM_INT); // Curso selecionado
$apitoken = 'b37fa95a203ad53482fe40b0a26093bd8749536a';

// Configuração da página.
$PAGE->set_url('/local/provafacil_sync/candidate_keys.php', ['courseid' => $courseid]);
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title('Chaves dos Candidatos');
$PAGE->set_heading('Lista de Chaves dos Candidatos Inscritos');


// Função para buscar chave de um candidato pela API usando o idnumber.
function get_candidate_key_by_idnumber($idnumber, $apitoken) {
    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/tm/rest/candidate/';
    $apitoken = 'b37fa95a203ad53482fe40b0a26093bd8749536a';  // O token de autorização
$headers = [
    'Authorization: Token ' . $apitoken,
    'Content-Type: application/json',
];
    $curl = new curl();

    $ch = curl_init($url . '?legacy_key=' . urlencode($idnumber));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    // Faz a requisição GET à API
    /*$response = $curl->get($url . '?legacy_key=' . urlencode($idnumber), [
        'CURLOPT_HTTPHEADER' => [
            'Authorization: Token ' . $apitoken,
            'Content-Type: application/json',
        ]
    ]);*/

    // Log da resposta para depuração
    // var_dump("Resposta da API para o IDNumber {$idnumber}: {$response}");

    // Decodifica o JSON
    $data = json_decode($response, true);
    //var_dump("Dados decodificados: " . print_r($data, true));
    // Verifica se a resposta é válida e contém o campo 'key'
    if (!empty($data) && isset($data[0]['key'])) {
        
        return $data[0]['key'];
    }
    var_dump("Dados decodificados: " . print_r($data, true));
    // Retorna 'Não encontrado' caso a chave não exista
    return 'Não encontrado';

    


}



// Buscar os estudantes inscritos no curso.
$sql = "SELECT u.id, u.idnumber, u.firstname, u.lastname
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        WHERE e.courseid = :courseid";
$params = ['courseid' => $courseid];
$students = $DB->get_records_sql($sql, $params);

echo $OUTPUT->header();

// Exibir a tabela com os dados.
echo '<h3>Lista de Chaves dos Candidatos Inscritos</h3>';
echo '<table border="1">';
echo '<tr><th>IDNumber</th><th>Nome</th><th>Key</th></tr>';
foreach ($students as $student) {
    $key = get_candidate_key_by_idnumber($student->idnumber, $apitoken);
    echo '<tr>';
    echo "<td>{$student->idnumber}</td>";
    echo "<td>{$student->firstname} {$student->lastname}</td>";
    echo "<td>{$key}</td>";
    echo '</tr>';
}
echo '</table>';
$apitoken = 'b37fa95a203ad53482fe40b0a26093bd8749536a';  // O token de autorização
$headers = [
    'Authorization: Token ' . $apitoken,
    'Content-Type: application/json',
];


/* * $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/tm/rest/candidate/';
    $idnumber = '675656777'; // Substituir pelo IDNumber real
    
    $ch = curl_init($url . '?legacy_key=' . urlencode($idnumber));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    if($response === false) {
        var_dump('Erro no cURL: ' . curl_error($ch));
    } else {
        var_dump('Resposta da API: ' . $response);
    }
    if (curl_error($ch)) {
        var_dump("Erro no cURL: " . curl_error($ch));
    }
    curl_close($ch); */
echo $OUTPUT->footer();
?>
