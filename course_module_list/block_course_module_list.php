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
 * Course module list  block plugin.
 *
 * @package    course_module_list
 * @author     Amit Bhardwaj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

/**
 * The Course module block class
 */
class block_course_module_list extends block_list {

    public function init() {
        $this->title = get_string('module_lists', 'block_course_module_list');
    }

    public function get_content() {
        global $COURSE, $CFG, $USER;
        $context = context_course::instance($COURSE->id);
        $roles = get_user_roles($context, $USER->id, true);
        if (isloggedin()) {
            $modulename = '';
            $modinfo = get_fast_modinfo($COURSE->id);
            $this->content = new stdClass();
            $this->content->items = array();
            $this->content->icons = array();
            $this->content->footer = "";
            $completion = new completion_info($COURSE);

            foreach ($modinfo->cms as $cm) {
                $coursemod = $modinfo->get_cm($cm->id);
                if (!$cm->uservisible or ! $cm->has_view()) {
                    continue;
                }
                if ($coursemod->name == 'label') {
                    continue;
                }

                $modulecompletiondata = $completion->get_data($cm, true, $USER->id);
                if ($modulecompletiondata->completionstate == 1) {
                    $modulecompletionstate = get_string('completed', 'block_course_module_list');
                } else {
                    $modulecompletionstate = get_string('notompleted', 'block_course_module_list');
                }
                $url = new moodle_url($CFG->wwwroot . '/mod/' . $coursemod->modname . '/view.php', array('id' => $coursemod->id));
                $modulecompletiondate = userdate($coursemod->added, $format = '%d %B %Y');
                $modulename = $coursemod->id;
                $modulename .= get_string('dash', 'block_course_module_list', $coursemod->name);
                $modulename .= get_string('dash', 'block_course_module_list', $modulecompletiondate);
                $modulename .= get_string('dash', 'block_course_module_list', $modulecompletionstate);
                $this->content->items[] = html_writer::link($url, $modulename);
            }

            if (empty($this->content->items)) {
                $this->content->items[] = get_string('modulenotfound', 'block_course_module_list');
            }
        }
    }

    /**
     * Returns the format.
     *
     * @return array
     */ 
    public function applicable_formats() {
        return array(
            'course-view' => true,
            'course-view-social' => false
        );
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('module_lists', 'block_course_module_list');
            } else {
                $this->title = $this->config->title;
            }
            if (empty($this->config->text)) {
                $this->config->text = get_string('module_lists', 'block_course_module_list');
            }
        }
    }

}
