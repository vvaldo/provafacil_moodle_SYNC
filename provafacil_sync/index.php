<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php'); // Importa as funções auxiliares


global $DB, $PAGE, $OUTPUT;

// Verificar permissões de administrador
require_login();
require_capability('moodle/site:config', context_system::instance());

// Configurações da página
$PAGE->set_url(new moodle_url('/local/provafacil_sync/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Sincronizar Estudantes >> Prova facil');
$PAGE->set_heading('Sincronizar Estudantes >> Prova facil');



// Obter todas as categorias visíveis
// $categories = $DB->get_records('course_categories', ['visible' => 1], 'name ASC');
// Obter categorias disponíveis
$categories = $DB->get_records('course_categories', ['visible' => 1], 'name ASC');

// Inicializar variáveis
$selected_category = optional_param('categoryid', null, PARAM_INT);
$selected_course = optional_param('courseid', null, PARAM_INT);
$show_students = optional_param('showstudents', false, PARAM_BOOL);

// Processar sincronização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['syncstudents']) && $selected_course) {
    require_once(__DIR__ . '/sync.php');
    $success = sync_students_to_provafacil($selected_course);

    if ($success) {
        \core\notification::success('Estudantes sincronizados com sucesso!');
    } else {
        \core\notification::error('Erro ao sincronizar estudantes. Verifique os logs para mais detalhes.');
    }

    // Redirecionar para evitar reenvio de formulário
    redirect($PAGE->url);
}

echo $OUTPUT->header();

// Formulário para seleção de categorias e cursos
echo html_writer::start_tag('form', ['method' => 'get']);
echo html_writer::start_div('form-group');

// Obter a hierarquia de categorias no formato desejado
function get_category_hierarchy($categoryid, $path = '') {
    global $DB;

    $category = $DB->get_record('course_categories', ['id' => $categoryid], 'id, name, parent');
    if (!$category) {
        return $path;
    }

    // Adicionar o nome da categoria atual ao caminho
    $path = ($path ? $category->name . '/' . $path : $category->name);

    // Verificar se há um pai (categoria acima)
    if ($category->parent) {
        return get_category_hierarchy($category->parent, $path);
    }

    return $path;
}

function get_sorted_categories() {
    global $DB;

    // Buscar todas as categorias
    $categories = $DB->get_records('course_categories', ['visible' => 1], 'sortorder ASC');

    $hierarchical_categories = [];
    foreach ($categories as $category) {
        $hierarchical_categories[$category->id] = get_category_hierarchy($category->id);
    }

    // Ordenar pelo nome da hierarquia ascendente
    asort($hierarchical_categories);

    return $hierarchical_categories;
}

// Obter categorias hierárquicas organizadas
$categories = get_sorted_categories();
// Menu de categorias
echo html_writer::tag('label', 'Selecione uma categoria:', ['for' => 'categoryid']);
echo html_writer::start_tag('select', ['name' => 'categoryid', 'id' => 'categoryid', 'onchange' => 'this.form.submit();', 'class' => 'form-control']);
echo html_writer::tag('option', 'Selecione uma categoria', ['value' => '']);
foreach ($categories as $id => $hierarchy) {
    $selected = ($selected_category == $id) ? 'selected' : '';
    echo html_writer::tag('option', $hierarchy, ['value' => $id]);
}
echo html_writer::end_tag('select');

// Menu de cursos (se uma categoria for selecionada)
if ($selected_category) {
    $courses = $DB->get_records('course', ['category' => $selected_category, 'visible' => 1], 'fullname ASC');
    echo html_writer::tag('label', 'Selecione um curso:', ['for' => 'courseid']);
    echo html_writer::start_tag('select', ['name' => 'courseid', 'id' => 'courseid', 'onchange' => 'this.form.submit();','class' => 'form-control']);
    echo html_writer::tag('option', 'Selecione um curso', ['value' => '']);
    foreach ($courses as $course) {
        // $selected = ($selected_course == $course->id) ? 'selected' : '';
        echo html_writer::tag('option', $course->fullname, ['value' => $course->id]);
    }
    echo html_writer::end_tag('select');
}

echo html_writer::end_div();
echo html_writer::end_tag('form');

// Exibir estudantes (se o curso for selecionado e a opção estiver marcada)
if ($selected_course && $show_students) {
    $students = $DB->get_records_sql("
    SELECT u.id, u.firstname, u.lastname, u.email
    FROM {user} u
    JOIN {user_enrolments} ue ON ue.userid = u.id
    JOIN {enrol} e ON e.id = ue.enrolid
    JOIN {role_assignments} ra ON ra.userid = u.id
    JOIN {context} c ON c.id = ra.contextid
    JOIN {course} cr ON cr.id = e.courseid
    WHERE e.courseid = :courseid
    AND ra.roleid = :roleid
    AND c.contextlevel = 50
", ['courseid' => $selected_course, 'roleid' => 5]);

    if ($students) {
        echo html_writer::tag('h3', 'Estudantes no curso:');
        
        echo html_writer::start_tag('ul');
        foreach ($students as $student) {
            echo html_writer::tag('li', "{$student->firstname} {$student->lastname} ({$student->email})");
        }
        echo html_writer::end_tag('ul');
    } else {
        echo html_writer::tag('p', 'Nenhum estudante encontrado neste curso.');
    }
}

// Botão para exibir lista de estudantes
if ($selected_course) {
    echo html_writer::start_tag('form', ['method' => 'get']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'categoryid', 'value' => $selected_category]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $selected_course]);
echo html_writer::checkbox('showstudents', true, $show_students, 'Mostrar lista de estudantes antes de sincronizar <br />');
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Carregar Estudantes ', 'br']);


echo html_writer::end_tag('form');
}

// Botão para sincronizar
if ($selected_course && $show_students) {
    echo html_writer::start_tag('form', ['method' => 'post']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'categoryid', 'value' => $selected_category]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $selected_course]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'syncstudents', 'value' => true]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Sincronizar Estudantes']);
echo html_writer::end_tag('form');
}

echo $OUTPUT->footer();
