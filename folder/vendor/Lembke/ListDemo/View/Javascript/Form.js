/**
 * Renders and handles a form. read/write/clear data in the form.
 * @category ListDemo
 * @package ListDemo
 * @copyright Copyright (c) 2019, Peter Lembke
 * @author Peter Lembke <peter@teamfakta.se>
 * @license ListDemo is distributed under the terms of the GNU General Public License
 * ListDemo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * ListDemo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with ListDemo.    If not, see <https://www.gnu.org/licenses/>.
 */
function Form($base) {

    let $domId = '';
    let $fieldsData = {};

    /**
     * DOM Id, where the form will be rendered in the DOM
     * @param $data
     */
    this.setDomId = function ($data) {
        $domId = $data;
    };

    /**
     * fieldsData contain the form definition
     * @param $data
     */
    this.setFieldsData = function ($data) {
        $fieldsData = $data;
    };

    /**
     * Render the form
     */
    this.render = function () {

        let $formFieldsArray = [];
        for (let $key in $fieldsData.fields) {
            if ($fieldsData.fields.hasOwnProperty($key) === false) {
                continue;
            }

            const $oneFieldData = $fieldsData.fields[$key];

            if ($oneFieldData.form_visible === false) {
                continue;
            }

            let $readonly = '';
            if ($oneFieldData.form_readonly === true) {
                $readonly = 'readonly';
            }

            const $label = $oneFieldData.label + '<br>';
            const $input = '<input class="textfield" type="text" id="' + $key + '" name="' + $key + '" value="" ' + $readonly + '><br>';
            $formFieldsArray.push($label + $input);
        }

        const $name = $fieldsData.name;
        const $html = '<form id="' + $name + '" action="#"><fieldset>' + $formFieldsArray.join('') + '</fieldset></form>';

        $base.view($domId, $html);
    };

    /**
     * Read data from the form
     */
    this.read = function () {

        let $formData = {};

        for (let $key in $fieldsData.fields) {
            if ($fieldsData.fields.hasOwnProperty($key) === false) {
                continue;
            }

            const $formValue = $base.getFormValue($key);
            $formData[$key] = $formValue;
        }

        return $formData;
    };

    /**
     * Write data to the form
     */
    this.write = function ($formData) {

        for (let $key in $formData) {
            if ($formData.hasOwnProperty($key) === false) {
                continue;
            }
            $base.setFormValue($key, $formData[$key]);
        }

    };

    /**
     * Clear the form fields
     */
    this.clear = function () {

        for (let $key in $fieldsData.fields) {
            if ($fieldsData.fields.hasOwnProperty($key) === false) {
                continue;
            }
            $base.setFormValue($key, '');
        }

    };

    /**
     * Get the ID of the current post in the form
     */
    this.getId = function () {
        const $idFieldName = $fieldsData.id_field_name;
        const $id = $base.getFormValue($idFieldName);
        return $id;
    };


}
//# sourceURL=Form.js
