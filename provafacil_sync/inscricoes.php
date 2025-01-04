<?php
require_once('../../config.php');

require_once($CFG->libdir . '/filelib.php'); // Biblioteca para usar a classe curl
require_login();

global $DB, $PAGE, $OUTPUT;

// Função para obter o key a partir do legacy_key.
function local_provafacil_get_key($url, $legacy_key, $apitoken) {
    $headers = [
        'Authorization: Token ' . $apitoken,
        'Content-Type: application/json',
    ];

    $curl = new curl();

    $ch = curl_init($url . '?legacy_key=' . urlencode($legacy_key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);


    $data = json_decode($response, true);

    if (!empty($data) && isset($data[0]['key'])) {
        
        return $data[0]['key'];
    }
    var_dump("Dados decodificados: " . print_r($data, true));
    // Retorna 'Não encontrado' caso a chave não exista
    return 'Não encontrado';


}

// Função para inscrever estudante na disciplina via API.
function local_provafacil_enroll_student($candidate_key, $academic_key, $apitoken) {
    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academicxcandidate/';
    $data = [
        'candidate' => $candidate_key,
        'academic' => $academic_key,
    ];
    $json_data = json_encode($data);
    $curl = new curl();
    $response = $curl->post($url, $json_data, [
        'CURLOPT_HTTPHEADER' => [
            'Authorization: Token ' . $apitoken,
            'Content-Type: application/json',
        ],
    ]);
    return json_decode($response, true);
}

$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$apitoken = '#apitoken';

$categories = $DB->get_records('course_categories', ['visible' => 1], 'name ASC');
$selected_category = optional_param('categoryid', null, PARAM_INT);
$selected_course = optional_param('courseid', null, PARAM_INT);
$show_students = optional_param('showstudents', false, PARAM_BOOL);

$PAGE->set_url('/local/provafacil_sync/inscricoes.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Inscrição Prova Fácil');
$PAGE->set_heading('Inscrição de Estudantes-> Selecione a disciplina e estudantes para Inscrição ');

echo $OUTPUT->header();

// Função para obter hierarquia de categorias
function get_category_hierarchy($categoryid, $path = '') {
    global $DB;
    $category = $DB->get_record('course_categories', ['id' => $categoryid], 'id, name, parent');
    if (!$category) {
        return $path;
    }
    $path = ($path ? $category->name . '/' . $path : $category->name);
    if ($category->parent) {
        return get_category_hierarchy($category->parent, $path);
    }
    return $path;
}

function get_sorted_categories() {
    global $DB;
    $categories = $DB->get_records('course_categories', ['visible' => 1], 'sortorder ASC');
    $hierarchical_categories = [];
    foreach ($categories as $category) {
        $hierarchical_categories[$category->id] = get_category_hierarchy($category->id);
    }
    asort($hierarchical_categories);
    return $hierarchical_categories;
}

// Obter categorias organizadas
$categories = get_sorted_categories();
echo html_writer::start_tag('form', ['method' => 'get']);
echo html_writer::start_div('form-group');

// Menu de categorias
echo html_writer::tag('label', 'Selecione uma categoria:', ['for' => 'categoryid']);
echo html_writer::start_tag('select', ['name' => 'categoryid', 'id' => 'categoryid', 'onchange' => 'this.form.submit();', 'class' => 'form-control']);
echo html_writer::tag('option', 'Selecione uma categoria', ['value' => '']);
foreach ($categories as $id => $hierarchy) {
    $selected = ($selected_category == $id) ? 'selected' : '';
    echo html_writer::tag('option', $hierarchy, ['value' => $id]);
}
echo html_writer::end_tag('select');

if ($selected_category) {
    $courses = $DB->get_records('course', ['category' => $selected_category, 'visible' => 1], 'fullname ASC');
    echo html_writer::tag('label', 'Selecione um curso:', ['for' => 'courseid']);
    echo html_writer::start_tag('select', ['name' => 'courseid', 'id' => 'courseid', 'onchange' => 'this.form.submit();', 'class' => 'form-control']);
    echo html_writer::tag('option', 'Selecione um curso', ['value' => '']);
    foreach ($courses as $course) {
        echo html_writer::tag('option', $course->fullname, ['value' => $course->id]);
    }
    echo html_writer::end_tag('select');
}

echo html_writer::end_div();
echo html_writer::end_tag('form');

if ($courseid) {
    $sql = "SELECT u.id, u.username, u.email, u.idnumber
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} c ON c.id = ra.contextid
            JOIN {course} cr ON cr.id = e.courseid
            WHERE e.courseid = :courseid
            AND ra.roleid = 5
            AND c.contextlevel = 50";
    $students = $DB->get_records_sql($sql, ['courseid' => $courseid]);

    echo '<form method="post" action="validacao.php">';  // Modificar o action para apontar para a página de confirmação
echo '<input type="hidden" name="courseid" value="' . $courseid . '">';

echo '<input type="checkbox" id="selectAll" >Selecionar Todos</button>';

echo '<table>';
echo '<tr><th>Nome</th><th>Email</th><th>ID</th><th>Selecionar</th></tr>';

foreach ($students as $student) {
    echo '<tr>';
    echo "<td>{$student->username}</td>";
    echo "<td>{$student->email}</td>";
    echo "<td>{$student->idnumber}</td>";
    echo "<td><input type='checkbox' name='students[]' value='{$student->id}'></td>";
    echo '</tr>';
}

echo '</table>';
echo '<button type="submit" name="action" value="enroll" class="btn btn-primary">Ir para a confirmação</button>';
echo '</form>';

echo '
<script>
document.getElementById("selectAll").addEventListener("click", function() {
    var checkboxes = document.querySelectorAll("input[type=checkbox]");
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = true;
    });
});
</script>';
}

echo $OUTPUT->footer();
?>
