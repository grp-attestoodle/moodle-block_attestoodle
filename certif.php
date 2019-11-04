<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block attestoodle list of certificate.
 *
 * @package    block_attestoodle
 * @copyright  2019 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/tablelib.php');

use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\factories\learners_factory;

$id       = required_param('instanceid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$trainingid = required_param('trainingid', PARAM_INT);
$learnerid  = required_param('learnerid', PARAM_INT);

// Determine course and context.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = CONTEXT_COURSE::instance($courseid);

// Set up page parameters.
$PAGE->set_course($course);
$PAGE->set_url('/blocks/attestoodle/certif.php',
        array(
            'instanceid' => $id,
            'courseid'   => $courseid,
            'trainingid' => $trainingid,
            'learnerid'  => $learnerid
        ));

$PAGE->set_context($context);
$title = get_string('lstcertificate', 'block_attestoodle');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->set_pagelayout('report');

// Check user is logged in and capable of accessing the Overview.
require_login($course, false);

// Start page output.
echo $OUTPUT->header();
echo $OUTPUT->heading($title, 2);
echo $OUTPUT->container_start('block_attestoodle');

// By the end of the period from the most recent to the oldest.
$req = "select *
          from {tool_attestoodle_certif_log} c
          join {tool_attestoodle_launch_log} l on c.launchid = l.id
         where trainingid = ? and learnerid = ?
      order by enddate desc";
$records = $DB->get_records_sql($req, array($trainingid, $learnerid));

// Fill the table.
$table = new html_table();
$table->head = array(get_string('certificate', 'block_attestoodle'),
                    get_string('timegenerated', 'block_attestoodle'),
                    get_string('timestart', 'block_attestoodle'),
                    get_string('timeend', 'block_attestoodle'));
$table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap');
$table->size = array('', '12%', '12%', '12%');

$zr = '';
$lstdate = array();

$numbervalid = 0;
$fs = get_file_storage();
$usercontext = \context_user::instance($learnerid);
$gendate = new \DateTime();

foreach ($records as $record) {
    if ($record->filename != $zr) {
        $zr = $record->filename;

        $numbervalid++;
        $file = $fs->get_file(
                $usercontext->id,
                'tool_attestoodle',
                'certificates',
                0,
                '/',
                $record->filename);

        $url = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename());

        $downloadlnk = "<a href='" . $url . "' target='_blank'>" .
                    get_string('learner_details_download_certificate_link', 'tool_attestoodle') .
                    "</a>";

        $thedate = \DateTime::createFromFormat('Y-m-d', $record->enddate);
        $begdate = \DateTime::createFromFormat('Y-m-d', $record->begindate);
        $gendate->setTimestamp($record->timegenerated);

        $table->data[] = array($downloadlnk, $gendate->format("d/m/Y"), $begdate->format("d/m/Y"),
            $thedate->format("d/m/Y"));

        // Take the end date if it is less than the generation date.
        if (!is_bool($thedate) && $thedate->getTimestamp() < $record->timegenerated) {
            $lstdate[] = $thedate->getTimestamp();
        } else {
            $lstdate[] = $record->timegenerated;
        }
    }
}

echo html_writer::table($table);

echo get_string('numbercertificate', 'block_attestoodle') . $numbervalid;
$now = new \DateTime();
$lstdate[] = $now->getTimestamp();

// Sort the date array.
sort($lstdate);

// Calculate the data of the GRAPH.
trainings_factory::get_instance()->create_training_by_category(0, $trainingid);
$training = trainings_factory::get_instance()->retrieve_training_by_id($trainingid);
$training->get_learners();
$learner = learners_factory::get_instance()->retrieve_learner($learnerid);

$enddate = $training->get_end();
$duration = $training->get_duration();
$startdate = $training->get_start();

if ($enddate == null) {
    $enddate = -1;
}
if ($duration == null) {
    $duration = -1;
}

if ($enddate > 0 && $duration > 0) {
    $totaltimeduration = $enddate - $startdate;
    $serievalues = array();
    $serievalues[] = 0;
    $categoryid = $training->get_categoryid();
    $labels = array();
    $datestart = new \DateTime();
    $datestart->setTimestamp($startdate);

    $labels[] = $datestart->format("d/m/Y");
    // Calculation for each edition.
    foreach ($lstdate as $curent) {
        $dat = new \DateTime();
        $dat->setTimestamp($curent);
        $totalmarkerperiod = $learner->get_total_milestones($categoryid, null, $dat);
        $ecoule = $curent - $startdate;
        $pctimepast = round (($ecoule / $totaltimeduration) * 100.0, 2);
        if ($pctimepast > 100.00) {
            $pctimepast = 100.00;
        }
        $pccredited = round(($totalmarkerperiod / $duration) * 100.0, 2);
        $serievalues[] = $pccredited - $pctimepast;
        $labels[] = $dat->format("d/m/Y");
    }
    $serie1 = new core\chart_series(get_string('yourprogress', 'block_attestoodle'), $serievalues);

    $chart = new \core\chart_line();
    $chart->set_smooth(true);
    $chart->add_series($serie1);
    $chart->set_labels($labels);

    echo "<div style='width:75%'>";
    echo $OUTPUT->render($chart, false);
    echo "</div>";
}

echo $OUTPUT->container_end();
echo $OUTPUT->footer();
