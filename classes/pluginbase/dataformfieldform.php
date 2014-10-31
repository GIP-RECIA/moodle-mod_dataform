<?php
// This file is part of Moodle - http://moodle.org/.
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
 * @package dataformfield
 * @copyright 2013 Itamar Tzadok
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_dataform\pluginbase;

defined('MOODLE_INTERNAL') or die;

require_once("$CFG->libdir/formslib.php");

/**
 *
 */
class dataformfieldform extends \moodleform {
    protected $_field = null;

    /**
     *
     */
    public function __construct($field, $action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true) {
        $this->_field = $field;

        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }

    /**
     *
     */
    public function definition() {
        $mform = &$this->_form;

        // Buttons.
        $this->add_action_buttons();
        // General.
        $this->definition_general();
        // Specific settings.
        $this->definition_settings();
        // Default content.
        $this->definition_defaults();
        // Buttons.
        $this->add_action_buttons();
    }

    /**
     *
     */
    protected function definition_general() {
        $mform = &$this->_form;

        // Header.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '32'));
        $mform->addRule('name', null, 'required', null, 'client');

        // Description.
        $mform->addElement('text', 'description', get_string('description'), array('size' => '64'));

        // Visible.
        $options = array(
            dataformfield::VISIBLE_NONE => get_string('fieldvisiblenone', 'dataform'),
            dataformfield::VISIBLE_OWNER => get_string('fieldvisibleowner', 'dataform'),
            dataformfield::VISIBLE_ALL => get_string('fieldvisibleall', 'dataform'),
        );
        $mform->addElement('select', 'visible', get_string('visible'), $options);
        $mform->setDefault('visible', dataformfield::VISIBLE_ALL);

        // Editable.
        $options = array(-1 => get_string('yes'), 0 => get_string('no'));
        $mform->addElement('select', 'editable', get_string('fieldeditable', 'dataform'), $options);
        $mform->setDefault('editable', -1);

        // Template.
        $mform->addElement('textarea', 'label', get_string('fieldtemplate', 'dataform'), array('cols' => 60, 'rows' => 5));
        $mform->addHelpButton('label', 'fieldtemplate', 'dataform');

        // Strings strip tags.
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
            $mform->setType('description', PARAM_TEXT);
            $mform->setType('label', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
            $mform->setType('description', PARAM_CLEANHTML);
            $mform->setType('label', PARAM_CLEANHTML);
        }
    }

    /**
     * The field settings fieldset. Contains a header and calls the hook method
     * {@link dataformfieldform::field_definition()}.
     *
     * @return void
     */
    protected function definition_settings() {
        $mform = &$this->_form;

        // Header.
        $mform->addElement('header', 'settingshdr', get_string('settings'));
        $mform->setExpanded('settingshdr');
        // Field settings.
        $this->field_definition();
    }

    /**
     * A hook method for field specific settings. Called from {@link dataformfieldform::definition_settings()}
     * so should not contain an opening header unless definition_settings has been overridden.
     *
     * @return void
     */
    protected function field_definition() {
    }

    /**
     * The field default content fieldset. Contains a header and calls the hook methods
     * {@link dataformfieldform::definition_default_settings()} and
     * {@link dataformfieldform::definition_default_content()}.
     *
     * @return void
     */
    protected function definition_defaults() {
        $mform = &$this->_form;

        // Header.
        $mform->addElement('header', 'defaultcontenthdr', get_string('fielddefaultcontent', 'dataform'));
        $mform->setExpanded('defaultcontenthdr');
        // Settings.
        $this->definition_default_settings();
        // Content.
        $this->definition_default_content();
    }

    /**
     * A hook method for field default settings. Called from {@link dataformfieldform::definition_defaults()}
     * so should not contain an opening header unless definition_defaults has been overridden.
     *
     * @return void
     */
    protected function definition_default_settings() {
        $mform = &$this->_form;

        // Apply defaults.
        $options = array(
            // New entries only.
            dataformfield::DEFAULT_NEW => get_string('fielddefaultnew', 'dataform'),
            // Every empty content.
            dataformfield::DEFAULT_ANY => get_string('fielddefaultany', 'dataform'),
        );
        $mform->addElement('select', 'defaultcontentmode', get_string('fieldapplydefault', 'dataform'), $options);
    }

    /**
     * A hook method for field default content. Needs to be overridden in any field that displays
     * form element in entry editing mode. Called from {@link dataformfieldform::definition_defaults()}
     * so should not contain an opening header unless definition_defaults has been overridden.
     *
     * @return void
     */
    protected function definition_default_content() {
    }

    /**
     *
     */
    public function add_action_buttons($cancel = true, $submit = null) {
        $mform = &$this->_form;

        $buttonarray = array();
        // save and display
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        // save and continue
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savecont', 'dataform'));
        // cancel
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * A hook method for compiling field default content on saving field definition.
     * Needs to be overridden in any field whose content definition may require further processing.
     * Called from {@link dataformfieldform::get_data()}.
     *
     * @param stdClass $data
     * @return stdClass
     */
    protected function get_data_default_content(\stdClass $data) {
        $field = $this->_field;

        $content = array();
        foreach ($field->content_names() as $name) {
            $delim = $name ? '_' : '';
            $contentname = 'contentdefault'. $delim. $name;
            if (isset($data->$contentname) and !$field->content_is_empty($contentname, $data->$contentname)) {
                $content[$name] = $data->$contentname;
            }
        }

        if ($content) {
            $data->defaultcontent = base64_encode(serialize($content));
        } else {
            $data->defaultcontent = null;
        }

        return $data;
    }

    /**
     *
     */
    public function get_data() {
        if ($data = parent::get_data()) {
            $data = $this->get_data_default_content($data);
        }
        return $data;
    }

    /**
     * A hook method for validating field default content. The method modifies an argument array
     * of errors that is then returned in the validation method.
     * Should be overridden in any field whose default content depends on some settings.
     * Called from {@link dataformfieldform::validation()}.
     *
     * @param array The form data
     * @param array The list of errors
     * @return void
     */
    protected function validation_default_content(array $data, array &$errors) {
    }

    /**
     *
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $df = \mod_dataform_dataform::instance($this->_field->dataid);

        // Validate name.
        if ($df->name_exists('fields', $data['name'], $this->_field->id)) {
            $errors['name'] = get_string('invalidname', 'dataform', get_string('field', 'dataform'));
        }

        // Validate default content.
        $this->validation_default_content($data, $errors);

        return $errors;
    }

}
