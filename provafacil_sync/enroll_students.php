<?php

require_once(__DIR__ . '/../../config.php'); // Configuração do Moodle.
require_login(); // Garante que o usuário está logado.

$context = context_system::instance();
require_capability('moodle/site:config', $context); // Apenas administradores podem acessar.

// Parâmetros opcionais.
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

// Configurações da página.
$url = new moodle_url('/local/provafacil_sync/enroll_students.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('Inscrição de Estudantes');
$PAGE->set_heading('Inscrição de Estudantes');
$PAGE->set_pagelayout('admin');

// Cabeçalho da página.
echo $OUTPUT->header();

// Etapa 1: Escolher Categoria.
if (!$categoryid) {
    echo $OUTPUT->heading('Selecione uma Categoria');
    $categories = $DB->get_records('course_categories', null, 'name ASC');
    echo html_writer::start_tag('ul');
    foreach ($categories as $category) {
        $link = new moodle_url($url, ['categoryid' => $category->id]);
        echo html_writer::tag('li', html_writer::link($link, $category->name));
    }
    echo html_writer::end_tag('ul');
    echo $OUTPUT->footer();
    exit;
}

// Etapa 2: Escolher Curso.
if (!$courseid) {
    echo $OUTPUT->heading('Selecione uma Disciplina');
    $courses = $DB->get_records('course', ['category' => $categoryid], 'fullname ASC');
    echo html_writer::start_tag('ul');
    foreach ($courses as $course) {
        $link = new moodle_url($url, ['categoryid' => $categoryid, 'courseid' => $course->id]);
        echo html_writer::tag('li', html_writer::link($link, $course->fullname));
    }
    echo html_writer::end_tag('ul');
    echo $OUTPUT->footer();
    exit;
}

// Etapa 3: Mostrar Estudantes e Inscrição.
echo $OUTPUT->heading('Estudantes Inscritos na Disciplina');

// Obter todos os estudantes inscritos.
$context = context_course::instance($courseid);
$students = get_enrolled_users($context, 'mod/assign:submit');

// Mostrar tabela com os dados dos estudantes.
$table = new html_table();
$table->head = ['Nome', 'Email', 'Código', 'Estado', 'Ações'];
$table->data = [];

foreach ($students as $student) {
    $status = $DB->record_exists('user', ['idnumber' => $student->idnumber]) ? 'Pronto' : 'Faltando ID';
    $actions = html_writer::tag('button', 'Inscrever', [
        'type' => 'button',
        'class' => 'enroll-button',
        'data-student-id' => $student->id,
    ]);

    $table->data[] = [
        fullname($student),
        $student->email,
        $student->idnumber ?? 'Sem ID',
        $status,
        $actions,
    ];
}

echo html_writer::table($table);

// Adiciona script para inscrição.
$enroll_url = new moodle_url('/local/provafacil_sync/ajax/enroll_student.php');
echo "<script>
    document.querySelectorAll('.enroll-button').forEach(button => {
        button.addEventListener('click', () => {
            const studentId = button.getAttribute('data-student-id');
            fetch('{$enroll_url}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({studentid: studentId, courseid: {$courseid}})
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      button.textContent = 'Inscrito';
                      button.disabled = true;
                  } else {
                      alert('Falha ao inscrever: ' + data.message);
                  }
              });
        });
    });
</script>";

// Rodapé da página.
echo $OUTPUT->footer();
