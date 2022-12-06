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
 * Block attestoodle is defined here.
 *
 * @package     block_attestoodle
 * @copyright   2019 Marc Leconte <Marc.Leconte@univ-lemans.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

use tool_attestoodle\factories\trainings_factory;
use tool_attestoodle\factories\learners_factory;
require_once(dirname(__FILE__).'/../../admin/tool/attestoodle/lib.php');

/**
 * attestoodle block.
 *
 * @package    block_attestoodle
 * @copyright  2019 Marc Leconte <Marc.Leconte@univ-lemans.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_attestoodle extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_attestoodle');
    }

    /**
     * Act on instance data.
     */
    public function specialization() {
        if (!$this->config) {
            $this->config = new stdClass();
            $this->config->displayratiomilestones = true;
            $this->config->displaynextcertificate = true;
            $this->config->displayprogress = true;
            $this->config->displaycertificatelist = true;
        }
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $CFG, $OUTPUT, $USER, $DB, $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $idtraining = optional_param('idtraining', '-1', PARAM_INT);

        // Get training's value if student of one training.
        $request = "select * from {tool_attestoodle_training}
                 where id in (select trainingid from {tool_attestoodle_milestone} where course = ?)
                   and id in (select trainingid from {tool_attestoodle_learner} where userid = ?)";
        $records = $DB->get_records_sql($request, array($COURSE->id, $USER->id));

        $nametraining = "";
        $categoryid = "";
        $trainingid = -1;
        $enddate = -1;
        $startdate = -1;
        $duration = -1;
        $student = 0;
        $nextlaunch = 0;
        $trainingsarray = array();
        $pass = true;
        foreach ($records as $record) {
            if ($idtraining != -1) {
                $pass = $idtraining == $record->id;
            }

            $trainingsarray[$record->id] = $record->name;

            if ($pass) {
                $nametraining = $record->name;
                $categoryid = $record->categoryid;
                $trainingid = $record->id;
                $enddate = $record->enddate;
                $startdate = $record->startdate;
                $duration = $record->duration;
                $nextlaunch = $record->nextlaunch;
                $student++;
            }
        }

        // Learner not enrolled in the training.
        if ($student == 0) {
            $coursecontext = context_course::instance($COURSE->id);
            if (!has_capability('moodle/course:viewhiddensections', $coursecontext)) {
                return $this->content;
            }
        }

        // All milestones of all trainings (Teacher).
        if ($student == 0) {
            $records = $DB->get_records('tool_attestoodle_milestone', array('course' => $COURSE->id));
        } else {
            $records = $DB->get_records('tool_attestoodle_milestone', array('course' => $COURSE->id, 'trainingid' => $trainingid));
        }
        $nb = 0;
        $tab = array();
        $tabjalons = '[';
        $tabcredit = '[';
        $tabformation = array();
        $tabtraining = array();
        foreach ($records as $record) {
            if (!isset($tabtraining[$record->trainingid])) {
                $tabtraining[$record->trainingid] = 1;
            }
            $nb++;
            if (!isset($tab[$record->moduleid])) {
                $tab[$record->moduleid] = $record->moduleid;
                $tabjalons .= '"' . $record->moduleid . '",';
                if ($student == 0) {
                    $tabcredit .= '"Jalon",';
                } else {
                    $tabcredit .= '"' . $record->creditedtime . ' min.",';
                }
            }
            if (!isset($tabformation[$record->trainingid])) {
                $tabformation[$record->trainingid] = $record->creditedtime;
            } else {
                $tabformation[$record->trainingid] += $record->creditedtime;
            }
        }
        $tabjalons .= ']';
        $tabcredit .= ']';
        $scrpt = '<script langage="javascript">
            var lienok =' . $tabjalons . ';
            var infocredit = ' . $tabcredit . ';

            var liens = document.getElementsByClassName("activity");
            for (var i = 0; i < liens.length; i++) {
                var status = liens[i].getAttribute("id");
                var reste = status.split("module-");
                for (var j = 0; j < lienok.length; j++) {
                    if (lienok[j] == reste[1]) {
                        img = document.createElement("img");
                        img.src =  "../blocks/attestoodle/pix/neo2.gif";
                        img.title = infocredit[j];

                        listdiv = liens[i].getElementsByClassName("actions");
                        if (listdiv.length > 0) {
                            lsdivs = listdiv[0].getElementsByTagName("div");
                            if (lsdivs.length >0) {
                                lsdivs[0].appendChild(img);
                            } else {
                                listdiv[0].appendChild(img);
                            }
                        } else {
                            liens[i].appendChild(img);
                        }
                    }
                }
            }
        </script>';
        $this->content->text = $scrpt;

        if ($student == 0 && $this->page->user_is_editing() && $nb > 0) {
            $this->content->text .= '<div class="p-3 mb-2 bg-danger text-white">';
            $this->content->text .= get_string('warning', 'block_attestoodle') . '</div>';
            return $this->content;
        }

        $tabtrainingids = array();
        foreach ($tabtraining as $key => $value) {
            $tabtrainingids[] = $key;
        }
        $resulttrainings = $DB->get_records_list('tool_attestoodle_training', 'id', $tabtrainingids, $sort = 'name');

        if ($student == 0) {
            $this->content->text .= get_string('numbertraining', 'block_attestoodle') . count($tabtraining) . "<br/>";
            $this->content->text .= get_string('numbermilestones', 'block_attestoodle') . $nb ."<br/>";
            // No training for this course.
            if ($nb == 0) {
                return $this->content;
            }
            $this->content->text .= "<br/><b>" . get_string('traininglist', 'block_attestoodle') ."</b><br/>";
            foreach ($resulttrainings as $res) {
                $this->content->text .= $res->name . "<br/>";
            }
        }

        $format = get_string('dateformat', 'block_attestoodle');
        if ($student > 0) {
            if (count($trainingsarray) > 1) {
                foreach ($trainingsarray as $key => $res) {
                    if ($key != $trainingid) {
                        $lnk = \html_writer::link(
                            new \moodle_url('/course/view.php', array('id' => $COURSE->id, 'idtraining' => $key)),
                            $res);
                        $this->content->text .= $lnk."</br>";
                    }
                }
            }

            $this->content->text .= "<p class='bg-info'>" . $nametraining ."</p>";
            // Time credited for training.
            trainings_factory::get_instance()->create_training_by_category($categoryid, $trainingid);
            $training = trainings_factory::get_instance()->retrieve_training_by_id($trainingid);
            $training->get_learners();
            $learner = learners_factory::get_instance()->retrieve_learner($USER->id);
            $now = new \DateTime();
            $totalmarkerperiod = $learner->get_total_milestones($categoryid, null, $now);

            // Past activities in this course.
            $validatedactivities = $learner->get_validated_activities();
            $nbact = 0;
            $tabcourse = array();
            foreach ($validatedactivities as $va) {
                $act = $va->get_activity();
                $idcourse = $act->get_course()->get_id();
                if ($idcourse == $COURSE->id && $act->get_milestone() > 0) {
                    $nbact++;
                }
                if (!isset($tabcourse[$idcourse])) {
                    $tabcourse[$idcourse] = $act->get_course()->get_name();
                }
            }
            if ($this->config->displayratiomilestones) {
                $this->content->text .= get_string('coursemilestones', 'block_attestoodle') . $nbact;
                $this->content->text .= " / " . count($tab) . "<br/>";
            }

            $totalmarkers = parse_minutes_to_hours($totalmarkerperiod);
            $libtotal = get_string('timecredited', 'tool_attestoodle');
            $this->content->text .= $libtotal . ' : ' . $totalmarkers;

            if ($this->config->displaynextcertificate && $nextlaunch != 0 && $nextlaunch != null) {
                $this->content->text .= "<br/>". get_string('nextdeadline', 'block_attestoodle') . date($format, $nextlaunch);
            }

            if ($this->config->displayprogress && $enddate > 0 && $duration > 0) {
                $totaltimeduration = $enddate - $startdate;
                $ecoule = $now->getTimestamp() - $startdate;
                $pctimepast = round (($ecoule / $totaltimeduration) * 100.0, 2);
                if ($pctimepast > 100.00) {
                    $pctimepast = 100.00;
                }
                $pccredited = round(($totalmarkerperiod / $duration) * 100.0, 2);
                $diff = $pccredited - $pctimepast;
                $etatdiff = "<p class='bg-warning'>" . get_string('intime', 'block_attestoodle') . $diff . " % </p>";

                if ($diff > 5) {
                    $etatdiff = "<p class='bg-success'>" . get_string('inadvance', 'block_attestoodle') . $diff . " % </p>";
                }

                if ($diff < -5) {
                    $diff = -$diff;
                    $etatdiff = "<p class='bg-danger'>" . get_string('lateby', 'block_attestoodle') . $diff . " % </p>";
                }
                $this->content->text .= $etatdiff;
            }

            // Links to courses from the same training.
            $request = "select * from {course}
                 where id in (select course from {tool_attestoodle_milestone} where trainingid = ?)";
            $records = $DB->get_records_sql($request, array($trainingid));
            if (count($records) > 1) {
                $this->content->text .= "<hr/><p class='bg-secondary'>" . get_string('coursestraining', 'block_attestoodle');
                $this->content->text .= "</p>";
                foreach ($records as $record) {
                    if ($record->id != $COURSE->id) {
                        $this->content->text .= '<a href="./view.php?id=' . $record->id .'">'. $record->shortname .'</a><br/>';
                    } else {
                        $this->content->text .= $record->shortname .'<br/>';
                    }
                }
            }

            if ($this->config->displaycertificatelist && $this->existcertif($trainingid, $USER->id)) {
                $params = array('instanceid' => $this->instance->id, 'courseid' => $this->page->course->id,
                                'trainingid' => $trainingid, 'learnerid' => $USER->id);
                $urlcertif = new moodle_url('/blocks/attestoodle/certif.php', $params);
                $labelcertif = get_string('certificate', 'block_attestoodle');
                $optionsbtn = array('class' => 'overviewButton');

                $this->content->text .= '<br/><center>';
                $this->content->text .= $OUTPUT->single_button($urlcertif, $labelcertif, 'get', $optionsbtn);
                $this->content->text .= '</center>';
            }
        }
        return $this->content;
    }

    /**
     * Tests whether the learner has certificates for the training.
     * @param int $trainingid The training identifier.
     * @param int $userid The learner's identifier.
     * @return boolean True if the learner has certificates for this training.
     */
    public function existcertif($trainingid, $userid) {
        global $DB;
        $req = "select *
                  from {tool_attestoodle_certif_log} c
                  join {tool_attestoodle_launch_log} l on c.launchid = l.id
                 where trainingid = ? and learnerid = ?
              order by enddate desc";
        $records = $DB->get_records_sql($req, array($trainingid, $userid));
        return count($records);
    }

    /**
     * Where loack can be add.
     */
    public function applicable_formats() {
        return array('all' => false, 'course-view' => true);
    }

    /**
     * No multiple instance for this block.
     */
    public function instance_allow_multiple() {
          return false;
    }

    /**
     * No file settings.php for this block.
     */
    public function has_config() {
        return false;
    }
}
