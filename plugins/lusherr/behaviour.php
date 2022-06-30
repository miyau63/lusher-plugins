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
 * Question behaviour for the Lusher Test mode.
 *
 * @package    qbehaviour
 * @subpackage Lusher
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Question behaviour for Lusher Test mode.
 *
 * This is new Question Behaviour
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_lusherr extends question_behaviour_with_multiple_tries
{
    //to pick it for the test
    // чтобы выбрать именно этот тип вопроса, когда нам предлагается на странице
    const IS_ARCHETYPAL = true;

   /* public function is_compatible_question(question_definition $question)
    {
        return $question instanceof question_automatically_gradable;
    }*/
    public function is_compatible_question(question_definition $question) {
        return true;
    }

    public function get_expected_data()
    {
        if ($this->qa->get_state()->is_active()) {
            return array('submit' => PARAM_BOOL);
        }

        return parent::get_expected_data();
    }

    public function get_state_string($showcorrectness)
    {
        $laststep = $this->qa->get_last_step();
        if ($laststep->has_behaviour_var('_try')) {
            $state = question_state::graded_state_for_fraction(
                $laststep->get_behaviour_var('_rawfraction'));
            return $state->default_string(true);
        }
        $this->allanswered_check();
        $state = $this->qa->get_state();
        if ($state == question_state::$todo) {
            return '';
        } else {
            return parent::get_state_string('');
        }
    }
    //Работает только на завершение, на продолжение никак не влияет
    public function allanswered_check()
    {
        //getting url
        $url = $_SERVER['REQUEST_URI'];
        //checking if we are on a summary page
        $is_summary = stripos($url, 'summary');

        if ($is_summary) {
            $flag = 1;
            //preparing new url
            $new_url = str_replace('summary', 'attempt', $url);
            //getting question attempt
            $k = $this->qa;

            $allow_change=$k->get_question();
            $answer=$k->get_last_qt_data();

            if (!$allow_change->is_complete_response($answer)) {
                $wqid = $k->get_question()->id;
                   header('Location: ' . $new_url . '&wqid=' . $wqid.'&page=1#');
            }

        }
    }
//что не надо, то и не  надо
    public function get_right_answer_summary()
    {
        return '';//return $this->question->get_right_answer_summary();
    }

    public function adjust_display_options(question_display_options $options)
    {
        // Save some bits so we can put them back later.
        $save = clone($options);

        // Do the default thing.
        parent::adjust_display_options($options);
        // Then, if they have just Checked an answer, show them the applicable bits of feedback.
        if (!$this->qa->get_state()->is_finished() &&
            $this->qa->get_last_behaviour_var('_try')) {
        }
    }

    public function process_action(question_attempt_pending_step $pendingstep)
    {
        if ($pendingstep->has_behaviour_var('finish')) {
            return $this->process_finish($pendingstep);
        } else {
            return $this->process_save($pendingstep);
        }
    }

    public function summarise_action(question_attempt_step $step)
    {
        if ($step->has_behaviour_var('comment')) {
            return $this->summarise_manual_comment($step);
        } else if ($step->has_behaviour_var('finish')) {
            return $this->summarise_finish($step);
        } else {
            return $this->summarise_save($step);
        }
    }

    public function process_save(question_attempt_pending_step $pendingstep)
    {
        $status = parent::process_save($pendingstep);
        $pendingstep->set_state(question_state::$todo);

        return $status;
    }



    public function process_finish(question_attempt_pending_step $pendingstep)
    {
        if ($this->qa->get_state()->is_finished()) {
            return question_attempt::DISCARD;
        }
        $prevtries = $this->qa->get_last_behaviour_var('_try', 0);
        $laststep = $this->qa->get_last_step();
        $response = $laststep->get_qt_data();
        $state = question_state::$finished;
        if (!$this->question->is_gradable_response($response)) {
            $state = question_state::$gaveup;
        } else {
            $pendingstep->set_behaviour_var('_try', $prevtries + 1);
            $pendingstep->set_new_response_summary($this->question->summarise_response($response));
        }
        $pendingstep->set_state($state);
        return question_attempt::KEEP;
    }
}
