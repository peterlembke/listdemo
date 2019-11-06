/**
 * Main class that handle all logic and events.
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
function Main($base, $form, $list, $buttons, $ajax)
{
    let $mainEventVariable;
    let $fieldsName;
    let $fieldsData;

    /**
     * Initialization function
     * @param $in
     */
    this.initialize = function ($in)
    {
        "use strict";

        let $default = {
            'main_event_variable': '',
            'fields_name': ''
        };
        $in = $base.default($default, $in);

        $mainEventVariable = $in.main_event_variable;
        $fieldsName = $in.fields_name;

        $ajax.send({
            'package': {
                'command': 'getFieldsData',
                'fields_name': $fieldsName
            },
            'callback': this,
            'function': 'setFieldsDataAndRender'
        });

        $ajax.send({
            'package': {
                'command': 'setup',
                'fields_name': $fieldsName
            }
        });
    };

    /**
     * Callback for ajax call that get fieldsData from the server
     * @param $in
     */
    this.setFieldsDataAndRender = function ($in)
    {
        $fieldsData = $in;
        this.render();
    };

    /**
     * Render the graphical user interface
     */
    this.render = function ()
    {
        $base.view('title', $fieldsData.title);

        $form.setFieldsData($fieldsData);
        $form.setDomId('form');
        $form.render();

        let $buttonsDefinition = {
            'new': {
                'label': 'New',
                'event': $mainEventVariable + ".event('new')"
            },
            'delete': {
                'label': 'Delete',
                'event': $mainEventVariable + ".event('delete')"
            },
            'save': {
                'label': 'Save',
                'event': $mainEventVariable + ".event('save')"
            }
        };
        $buttons.render('form_buttons', $buttonsDefinition);

        let $items = [];
        $list.setFieldsData($fieldsData);
        $list.setDomId('list');
        $list.setMainEventVariable($mainEventVariable + ".event('view|{id}')");
        $list.render($items);

        $buttonsDefinition = {
            'refresh': {
                'label': 'Refresh',
                'event': $mainEventVariable + ".event('refresh')"
            }
        };
        $buttons.render('list_buttons', $buttonsDefinition);
    };

    /**
     * All click events on the template end up here
     * @param $event | The event string in the button: new, delete, save, refresh, view
     */
    this.event = function ($event)
    {
        if ($event === 'new') {
            $form.clear();
            $base.view('form_message', 'Cleared the form');
        }

        if ($event === 'delete') {
            const $id = $form.getId();
            $form.clear();
            if ($id > 0) {
                $ajax.send({
                    'package': {
                        'command': 'deletePost',
                        'fields_name': $fieldsName,
                        'id': $id
                    },
                    'callback': this,
                    'function': 'showMessage'
                });
            } else {
                $base.view('form_message', 'Cleared the form');
            }
        }

        if ($event === 'save') {
            const $formData = $form.read();

            if (hasData($formData) === false) {
                $base.view('form_message', 'Please write data in all fields');
                return;
            }

            $ajax.send({
                'package': {
                    'command': 'putPost',
                    'fields_name': $fieldsName,
                    'data': $formData
                },
                'callback': this,
                'function': 'showMessage'
            });
        }

        if ($event === 'refresh') {
            $ajax.send({
                'package': {
                    'command': 'getList',
                    'fields_name': $fieldsName
                },
                'callback': this,
                'function': 'renderList'
            });
        }

        const $parts = $event.split('|');
        if ($parts.length === 2) {
            if ($parts[0] === 'view') {
                const $id = $parts[1];
                $ajax.send({
                    'package': {
                        'command': 'getPost',
                        'fields_name': $fieldsName,
                        'id': $id
                    },
                    'callback': this,
                    'function': 'viewFormData'
                });
            }
        }
    };

    /**
     * Check that the object has data in all fields
     * @param $object
     */
    var hasData = function ($object)
    {
        const $idFieldName = $fieldsData.id_field_name;

        let $hasData = true;

        for (let $key in $object)
        {
            if ($object.hasOwnProperty($key) === false) {
                continue;
            }

            if ($key === $idFieldName) {
                continue;
            }

            const $value = $object[$key];
            if ($base.empty($value)) {
                return false;
            }
        }

        return true;
    };

    /**
     * Render the list after a refresh
     */
    this.renderList = function ($response)
    {
        if ($response.answer === true) {
            $list.render($response.data);

            const $numberOfItems = $base.count($response.data);
            $base.view('list_count', 'Rows: ' + $numberOfItems);
            $base.view('form_message', 'List updated');
        }
    };

    /**
     * View form data we got from the server
     */
    this.viewFormData = function ($response)
    {
        if ($response.answer === true) {
            $form.write($response.data);
            $base.view('form_message', 'Updated the form data');
        }
    };

    /**
     * View the message we got back
     */
    this.showMessage = function ($response)
    {
        $base.view('form_message', $response.message);
    };
}
//# sourceURL=Main.js
