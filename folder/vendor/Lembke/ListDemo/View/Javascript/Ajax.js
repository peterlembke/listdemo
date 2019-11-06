/**
 * Handle the communication with the server
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
function Ajax($base) {

    /**
     * Does an ajax call to the server.
     * Sends the ready made package string.
     * handles the response, cleans up the messages and send them in an event to the main loop.
     * @param $in
     * @returns {{answer: string, message: string}}
     */
    this.send = function ($in)
    {
        "use strict";

        const $default = {
            'url': 'Lembke/ListDemo/AjaxRequest',
            'package': {},
            'callback': null,
            'function': ''
        };
        $in = $base.default($default, $in);

        const xmlHttp = new XMLHttpRequest();
        const $maxWaitTimeMS = 8000.0;

        const $baseUrl = document.location.origin;
        const $url = $baseUrl + '/' + $in.url;

        var noResponseTimer = setTimeout(function() {
            xmlHttp.abort();
        }, $maxWaitTimeMS);

        let $packageJson = JSON.stringify($in.package);
        const $parameters = 'package=' + encodeURIComponent($packageJson);
        const $async = true;

        xmlHttp.open('POST', $url, $async);

        xmlHttp.onreadystatechange = function () {
            if (xmlHttp.readyState === 4) {
                if (xmlHttp.status === 200) {

                    let $incomingData = xmlHttp.responseText;
                    clearTimeout(noResponseTimer); // We got a response before the timeout

                    if ($incomingData !== '') {
                        try {
                            if ($incomingData[0] === '<') {
                                window.alert($incomingData);
                                return;
                            }
                            let $incomingObject = JSON.parse($incomingData);
                            if ($in.function !== '') {
                                $in.callback[$in.function]($incomingObject);
                            }
                        } catch ($err) {
                            window.alert($err.message + $err.stack);
                        }
                    }
                }
            }
        };

        xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlHttp.send($parameters);

        return {
            'answer': 'true',
            'message': 'Sent message with ' + $parameters.length + ' bytes of data'
        };
    };



}
//# sourceURL=Ajax.js
