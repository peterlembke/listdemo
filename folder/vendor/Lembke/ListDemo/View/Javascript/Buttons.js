/**
 * Renders buttons
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
function Buttons($base) {

    /**
     * Render buttons that you have in the buttonsDefinition
     *
     * @param $id | DOM element id where to view the rendered html
     * @param $buttonsDefinition | object with button id as key
     */
    this.render = function ($id, $buttonsDefinition)
    {
        const $html = renderHtml($buttonsDefinition);
        $base.view($id, $html);
    };

    /**
     * In case you only want the html
     * @param $buttonsDefinition
     * @returns {string}
     */
    this.getHtml = function ($buttonsDefinition) {
        const $html = renderHtml($buttonsDefinition);
        return $html;
    };

    var renderHtml = function ($buttonsDefinition) {
        const $default = {
            'label': '',
            'event': ''
        };

        let $buttonArray = [];

        for (let $key in $buttonsDefinition)
        {
            if ($buttonsDefinition.hasOwnProperty($key) === false) {
                continue;
            }

            const $data = $base.default($default, $buttonsDefinition[$key]);
            const $button =  '<button type="button" class="button" onclick="' + $data.event + '">' + $data.label + '</button>';
            $buttonArray.push($button);
        }

        const $html = $buttonArray.join('');
        return $html;
    }

}
//# sourceURL=Buttons.js
