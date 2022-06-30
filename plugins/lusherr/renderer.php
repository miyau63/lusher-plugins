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
 * @package    qbehaviour
 * @subpackage lusherr
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


class qbehaviour_lusherr_renderer extends qbehaviour_renderer {

    public $text = "";
    public function controls(question_attempt $qa, question_display_options $options) {
      global $PAGE;
        //getting url
        $url = $_SERVER['REQUEST_URI'];
        //checking if we are on a summary page
        $isnt_first = stripos($url, 'page');
        $result = "";
        //$rev = strpos($url, 'review');

        if(!$isnt_first && ($qa->get_slot()==1) ) {
            $result .= html_writer:: start_tag('table', array('id' => 'time', 'hidden' => 'true'));
            $result .= html_writer::start_tag('td', array('class' => 'minutes', 'style' => 'height:100px; width:100px;text-align: right;padding: 10px 0; font-size: 50px;'));
            $result .= html_writer:: end_tag('td');
            $result .= html_writer::start_tag('td', array('style' => 'text-align:right;padding: 10px 0; font-size: 50px;')) . ':';
            $result .= html_writer:: end_tag('td');
            $result .= html_writer::start_tag('td', array('class' => 'seconds', 'style' => 'height:100px; width:100px;text-align:left;padding:0; font-size: 50px;'));
            $result .= html_writer:: end_tag('td');

            $result .= html_writer:: end_tag('table');

            $result .= html_writer:: start_tag('button', array('id' => 'butTT','class'=>'butTT', 'type' => 'button', 'style' => 'width:200px;height:40px', 'hidden'=>'true')) . 'Продолжить';

            if(!strpos($url, 'review')){
                $PAGE->requires->js_call_amd('qbehaviour_lusherr/countdown', 'initialise');
            }

            $result .= html_writer:: end_tag('button');
        }


        if($isnt_first && !strpos($url, 'review')){
            $PAGE->requires->js_call_amd('qbehaviour_lusherr/nobutton', 'init');
        }



        if(!$isnt_first && !strpos($url, 'review')) {

            $_SESSION['questiontext'] = $qa->get_question()->questiontext;
        }
        if(array_key_exists('questiontext',$_SESSION) && !empty($_SESSION['questiontext'])) {

            if (stripos($url, 'page=1')) {
                $PAGE->requires->js_call_amd('qbehaviour_lusherr/experiment', 'initialise', array($_SESSION['questiontext']));
            }

        }

        return $result;
    }

    public function mark_summary(question_attempt $qa, core_question_renderer $qoutput,
                                 question_display_options $options) {
        return "";
    }

    public function feedback(question_attempt $qa, question_display_options $options) {

        return '';
    }
}
