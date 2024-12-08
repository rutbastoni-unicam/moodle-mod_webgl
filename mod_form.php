<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * webgl activity form
 *
 * @package mod_webgl
 * @copyright  2020 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_webgl_mod_form extends moodleform_mod {
    /**
     * Storage engine azure.
     */
    const STORAGE_ENGINE_AZURE = 1;

    /**
     * Storage engine s3.
     */
    const STORAGE_ENGINE_S3 = 2;

    /**
     * Storage engine local disk.
     */
    const STORAGE_ENGINE_LOCAL_DISK = 3;

    /**
     * Storage engine s3 default location.
     */
    const STORAGE_ENGINE_S3_DEFAULT_LOCATION = 'ap-southeast-1';

    /**
     * Definition function of the class.
     *
     * return void
     */
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // WebGl contetn form portion goes here.
        $mform->addElement('header', 'webglcontent', get_string('header:content', 'webgl'));

        $isupdateform = $this->optional_param('update', 0, PARAM_INT);

        if ($isupdateform > 0) {
            $dataforform = $DB->get_record('course_modules', array('id' => $isupdateform));
            $moduledata = $DB->get_record('webgl', array('id' => $dataforform->instance));
            if ($moduledata->store_zip_file) {
                $filename = str_replace('index.html', $moduledata->webgl_file, $moduledata->index_file_url);
                $ancor = '<div id="fitem_id_webgl_file" class="form-group row  fitem">
                        <div class="col-md-3">
                            <label class="col-form-label d-inline " for="id_webgl_file">&nbsp;</label>
                        </div>
                        <div class="col-md-9 form-inline felement" data-fieldtype="text" id="id_webgl_file">
                            <a target="_blank" href="' . $filename . '">Download ' . $moduledata->webgl_file . '</a>
                        </div>
                    </div>';
            } else {
                $ancor = '<div id="fitem_id_webgl_file" class="form-group row  fitem">
                        <div class="col-md-3">
                            <label class="col-form-label d-inline " for="id_webgl_file">&nbsp;</label>
                        </div>
                        <div class="col-md-9 form-inline felement" data-fieldtype="text" id="id_webgl_file">
                            <p>Previously Uploaded file name : ' . $moduledata->webgl_file . '</p>
                        </div>
                    </div>';
            }
            $mform->addElement('html', $ancor);

        }

        $mform->addElement('filepicker', 'importfile', get_string('input:file', 'webgl'), null, ['accepted_types' => '.zip']);
        $mform->addHelpButton('importfile', 'ziparchive', 'webgl');

        if ($isupdateform > 0) {
            $mform->addElement('advcheckbox', 'update_webgl_content', get_string('content_advcheckbox', 'webgl'));
            $mform->addHelpButton('update_webgl_content', 'content_advcheckbox', 'webgl');
            $mform->disabledIf('importfile', 'update_webgl_content');
        } else {
            $mform->addRule('importfile', null, 'required');
        }

        $mform->addElement('text', 'iframe_height', get_string('iframe_height', 'webgl'));
        $mform->setType('iframe_height', PARAM_TEXT);
        $mform->addHelpButton('iframe_height', 'iframe_height', 'webgl');
        $mform->addRule('iframe_height', null, 'required', null, 'client');
        $iframeheight = get_config('webgl', 'iframe_height');
        $mform->setDefault('iframe_height', $iframeheight);

        $mform->addElement('text', 'iframe_width', get_string('iframe_width', 'webgl'));
        $mform->setType('iframe_width', PARAM_TEXT);
        $mform->addHelpButton('iframe_width', 'iframe_width', 'webgl');
        $mform->addRule('iframe_width', null, 'required', null, 'client');
        $iframewidth = get_config('webgl', 'iframe_width');
        $mform->setDefault('iframe_width', $iframewidth);

        $mform->addElement('advcheckbox', 'before_description', get_string('before_description', 'webgl'));
        $mform->addHelpButton('before_description', 'before_description', 'webgl');
        $mform->addRule('before_description', null, 'required', null, 'client');

        // Storage form fields goes here.
        $mform->addElement('header', 'storage', get_string('storage', 'webgl'));

        $mform->addElement('select', 'storage_engine', get_string('storage_engine', 'webgl'), [
            1 => 'Azure BLOB storage',
            2 => 'AWS Simple Cloud Storage (S3)',
            3 => get_string('local_file_system','mod_webgl'),
        ]);
        $mform->addHelpButton('storage_engine', 'storage_engine', 'webgl');
        $mform->addRule('storage_engine', null, 'required', null, 'client');
        $storageengine = get_config('webgl', 'storage_engine');
        $mform->setDefault('storage_engine', $storageengine);

        $mform->addElement('text', 'account_name', get_string('account_name', 'webgl'));
        $mform->setType('account_name', PARAM_TEXT);
        $mform->addHelpButton('account_name', 'account_name', 'webgl');

        $accountname = get_config('webgl', 'AccountName');
        $mform->setDefault('account_name', $accountname);

        $mform->addElement('text', 'account_key', get_string('account_key', 'webgl'));
        $mform->setType('account_key', PARAM_TEXT);
        $mform->addHelpButton('account_key', 'account_key', 'webgl');

        $accountkey = get_config('webgl', 'AccountKey');
        $mform->setDefault('account_key', $accountkey);

        $mform->addElement('text', 'container_name', get_string('container_name', 'webgl'));
        $mform->setType('container_name', PARAM_TEXT);
        $mform->addHelpButton('container_name', 'container_name', 'webgl');

        $containername = get_config('webgl', 'ContainerName');
        $mform->setDefault('container_name', $containername);

        $mform->hideIf('account_name', 'storage_engine', 'eq', '2');
        $mform->hideIf('account_key', 'storage_engine', 'eq', '2');
        $mform->hideIf('container_name', 'storage_engine', 'eq', '2');
        $mform->hideIf('account_name', 'storage_engine', 'eq', '3');
        $mform->hideIf('account_key', 'storage_engine', 'eq', '3');
        $mform->hideIf('container_name', 'storage_engine', 'eq', '3');
        $mform->disabledIf('account_name', 'storage_engine', 'eq', '2');
        $mform->disabledIf('account_key', 'storage_engine', 'eq', '2');
        $mform->disabledIf('container_name', 'storage_engine', 'eq', '2');
        $mform->disabledIf('account_name', 'storage_engine', 'eq', '3');
        $mform->disabledIf('account_key', 'storage_engine', 'eq', '3');
        $mform->disabledIf('container_name', 'storage_engine', 'eq', '3');

        $mform->addElement('text', 'access_key', get_string('access_key', 'webgl'));
        $mform->setType('access_key', PARAM_TEXT);
        $mform->addHelpButton('access_key', 'access_key', 'webgl');

        $accesskey = get_config('webgl', 'access_key');
        $mform->setDefault('access_key', $accesskey);

        $mform->addElement('text', 'secret_key', get_string('secret_key', 'webgl'));
        $mform->setType('secret_key', PARAM_TEXT);
        $mform->addHelpButton('secret_key', 'secret_key', 'webgl');

        $secretkey = get_config('webgl', 'secret_key');
        $mform->setDefault('secret_key', $secretkey);

        $endpointselect = require('possible_end_points.php');
        $mform->addElement('select', 'endpoint', get_string('endpoint', 'webgl'), $endpointselect);
        $mform->setDefault('endpoint', 's3.amazonaws.com'); // Default to US Endpoint.

        $mform->hideIf('access_key', 'storage_engine', 'eq', '1');
        $mform->hideIf('secret_key', 'storage_engine', 'eq', '1');
        $mform->hideIf('endpoint', 'storage_engine', 'eq', '1');
        $mform->disabledIf('access_key', 'storage_engine', 'eq', '1');
        $mform->disabledIf('secret_key', 'storage_engine', 'eq', '1');
        $mform->disabledIf('endpoint', 'storage_engine', 'eq', '1');
        $mform->hideIf('access_key', 'storage_engine', 'eq', '3');
        $mform->hideIf('secret_key', 'storage_engine', 'eq', '3');
        $mform->hideIf('endpoint', 'storage_engine', 'eq', '3');
        $mform->disabledIf('access_key', 'storage_engine', 'eq', '3');
        $mform->disabledIf('secret_key', 'storage_engine', 'eq', '3');
        $mform->disabledIf('endpoint', 'storage_engine', 'eq', '3');

        $mform->addElement('advcheckbox', 'store_zip_file', get_string('store_zip_file', 'webgl'));
        $mform->addHelpButton('store_zip_file', 'store_zip_file', 'webgl');
//        $mform->addRule('store_zip_file', null, 'required', null, 'client');
        $storezipfile = get_config('webgl', 'store_zip_file');
        $mform->setDefault('store_zip_file', $storezipfile);
        $mform->hideIf('store_zip_file', 'storage_engine', 'eq', '3');
        $mform->disabledIf('store_zip_file', 'storage_engine', 'eq', '3');

        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Validation function.
     *
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        $error = [];
        if ($data['storage_engine'] == self::STORAGE_ENGINE_AZURE) {
            if (empty($data['account_name'])) {
                $error['account_name'] = get_string('account_name_error', 'mod_webgl');
            }
            if (empty($data['account_key'])) {
                $error['account_key'] = get_string('account_key_error', 'mod_webgl');
            }
            if (empty($data['container_name'])) {
                $error['container_name'] = get_string('container_name_error', 'mod_webgl');
            }
        } else if ($data['storage_engine'] == self::STORAGE_ENGINE_S3) {
            if (empty($data['access_key'])) {
                $error['access_key'] = get_string('access_key_error', 'mod_webgl');
            }
            if (empty($data['secret_key'])) {
                $error['secret_key'] = get_string('secret_key_error', 'mod_webgl');
            }
            if (empty($data['endpoint'])) {
                $error['endpoint'] = get_string('endpoint_error', 'mod_webgl');
            }
        }
        return $error;
    }

    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        $suffix = $this->get_suffix();
        $completionminimumscoreenabledel = 'completionminimumscoreenabled' . $suffix;
        $completionminimumscoreel = 'completionminimumscore' . $suffix;
        $completionlevelsenabledel = 'completionlevelsenabled' . $suffix;
        $completionlevelselel = 'completionlevels' . $suffix;

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $defaultvalues[$completionminimumscoreenabledel] = !empty($defaultvalues[$completionminimumscoreel]) ? 1 : 0;
        if (empty($defaultvalues[$completionminimumscoreel])) {
            $defaultvalues[$completionminimumscoreel] = 100;
        }
        $defaultvalues[$completionlevelsenabledel] = !empty($defaultvalues[$completionlevelselel]) ? 1 : 0;
        if (empty($defaultvalues[$completionlevelselel])) {
            $defaultvalues[$completionlevelselel] = 1;
        }

    }


    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform = $this->_form;

        $suffix = $this->get_suffix();

        $group = [];
        $completionminimumscoreenabledel = 'completionminimumscoreenabled' . $suffix;
        $group[] =& $mform->createElement('checkbox', $completionminimumscoreenabledel, '', get_string('completionminimumscore', 'webgl'));
        $completionminimumscoreel = 'completionminimumscore' . $suffix;
        $group[] =& $mform->createElement('text', $completionminimumscoreel, '', ['size' => 4]);
        $mform->setType($completionminimumscoreel, PARAM_INT);
        $completionminimumscoregroupel = 'completionminimumscoregroup' . $suffix;
        $mform->addGroup($group, $completionminimumscoregroupel, '', ' ', false);
        $mform->hideIf($completionminimumscoreel, $completionminimumscoreenabledel, 'notchecked');

        $group = [];
        $completionlevelsenabledel = 'completionlevelsenabled' . $suffix;
        $group[] =& $mform->createElement(
            'checkbox',
            $completionlevelsenabledel,
            '',
            get_string('completionlevels', 'webgl')
        );
        $completionlevelsel = 'completionlevels' . $suffix;
        $group[] =& $mform->createElement('text', $completionlevelsel, '', ['size' => 3]);
        $mform->setType($completionlevelsel, PARAM_INT);
        $completionlevelsgroupel = 'completionlevelsgroup' . $suffix;
        $mform->addGroup($group, $completionlevelsgroupel, '', ' ', false);
        $mform->hideIf($completionlevelsel, $completionlevelsenabledel, 'notchecked');

        $completionpuzzlesolvedel = 'completionpuzzlesolved' . $suffix;
        $mform->addElement('advcheckbox', $completionpuzzlesolvedel, '', get_string('completionpuzzlesolved', 'webgl'), array(), array(0, 1));

        return [$completionminimumscoregroupel, $completionlevelsgroupel, $completionpuzzlesolvedel];
    }

    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        return (!empty($data['completionminimumscoreenabled' . $suffix]) && $data['completionminimumscore' . $suffix] != 0) ||
            (!empty($data['completionlevelsenabled' . $suffix]) && $data['completionlevels' . $suffix] != 0) ||
            (!empty($data['completionpuzzlesolved' . $suffix]));
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Turn off completion settings if the checkboxes aren't ticked.
        if (!empty($data->completionunlocked)) {
            $suffix = $this->get_suffix();
            $completion = $data->{'completion' . $suffix};
            $autocompletion = !empty($completion) && $completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->{'completionminimumscoreenabled' . $suffix}) || !$autocompletion) {
                $data->{'completionminimumscore' . $suffix} = 0;
            }
            if (empty($data->{'completionlevelsenabled' . $suffix}) || !$autocompletion) {
                $data->{'completionlevels' . $suffix} = 0;
            }
            if (!$autocompletion) {
                $data->{'completionpuzzlesolved' . $suffix} = 0;
            }
        }
    }
}
