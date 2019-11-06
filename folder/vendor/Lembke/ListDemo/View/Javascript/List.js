/**
 * Renders a list with data.
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
function List($base) {

    let $domId = '';
    let $fieldsData = {};
    let $mainEventVariable = '';

    /**
     * DOM Id where the list is
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
     * Where we will send the event
     * @param $data
     */
    this.setMainEventVariable = function ($data) {
        $mainEventVariable = $data;
    };


    /**
     * Render a html grid with fields defined in $fieldsData
     * and rows from $itemsData
     *
     * @param $id
     * @param $fieldsData
     * @param $itemsData
     */
    this.render = function ($items)
    {
        const $default = {
        };

        let $idFieldName = '';
        if ($base.isSet($fieldsData.id_field_name)) {
            $idFieldName = $fieldsData.id_field_name;
        }

        let $rowArray = [];
        for (let $key in $items) {

            if ($items.hasOwnProperty($key) === false) {
                continue;
            }

            const $item = $items[$key];
            // const $item = $base.default($default, $items[$key]);

            let $valueArray = [];
            for (let $fieldName in $fieldsData.fields) {
                if ($item.hasOwnProperty($fieldName) === false) {
                    continue;
                }

                let $value = '';

                if ($base.isSet($item[$fieldName])) {
                    $value = $item[$fieldName];
                }

                $valueArray.push($value);
            }

            let $id = 0;
            if ($base.isSet($item[$idFieldName])) {
                $id = $item[$idFieldName];
            }
            const $event = $mainEventVariable.replace('{id}', $id);
            const $button =  '<button type="button" onclick="' + $event + '">View</button>';
            $valueArray.push($button);

            const $fieldsHTML = $valueArray.join('</td><td>');
            const $rowHtml = '<tr><td>' + $fieldsHTML + '</td></tr>';

            $rowArray.push($rowHtml);
        }

        const $allRowsHTML = $rowArray.join('');
        const $headHtml = renderHead();
        const $html = '<table>' + $headHtml + $allRowsHTML + '</table>';
        $base.view($domId, $html);
    };

    /**
     * Render the table head
     * @returns {string}
     */
    var renderHead = function()
    {
        let $labelArray = [];
        for (let $key in $fieldsData.fields) {
            const $label = $fieldsData.fields[$key].label;
            $labelArray.push($label);
        }
        $labelArray.push('View');

        const $fieldsHTML = $labelArray.join('</th><th>');
        const $html = '<tr><th>' + $fieldsHTML + '</th></tr>';

        return $html;
    }

}
//# sourceURL=List.js
