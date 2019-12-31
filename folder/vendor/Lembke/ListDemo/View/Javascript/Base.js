/**
 * Class with for me commonly used commands.
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
function Base() {

    /**
     * Make sure you get all variables you expect, at least with default values, and the right datatype.
     * example: $in = _Default($default,$in);
     * Used by: EVERY function to let go of all references. We MUST be sure that we can modify the incoming data
     * without affecting the array on the outside of the function.
     * @version 2015-01-29
     * @since   2013-09-05
     * @author  Peter Lembke
     * @param $default
     * @param $in
     * @return new object
     */
    this.default = function ($default, $in)
    {
        "use strict";

        let $callback;
        if (this.isSet($in.callback)) {
            $callback = $in.callback;
        }

        if (Array.isArray($in)) {
            $in = {};
        }

        if (typeof $default !== 'object' && typeof $in !== 'object') {
            return {};
        }

        if (typeof $default !== 'object' && typeof $in === 'object') {
            return this.byVal($in);
        }

        if (typeof $default === 'object' && typeof $in !== 'object') {
            return this.byVal($default);
        }

        let $answer = this.byVal($in);

        // Set all missing keys from the default object
        for (let $key in $default) {
            if ($default.hasOwnProperty($key) === false) {
                continue;
            }
            if (typeof $answer[$key] === 'undefined') {
                $answer[$key] = $default[$key];
            }
        }

        let $type;

        // Delete keys that are not in the default object
        // If wrong data type then copy the data key value from default
        for (let $key in $answer) {
            if ($answer.hasOwnProperty($key) === false) {
                continue;
            }
            if (typeof $default[$key] === 'undefined') {
                delete $answer[$key];
                continue;
            }
            if ($default[$key] === null && $answer[$key] === null) {
                $answer[$key] = '';
                continue;
            }
            if (typeof $answer[$key] !== typeof $default[$key])
            {
                if ($default[$key] === null) {
                    continue;
                }
                $answer[$key] = $default[$key];
                if ($default[$key] !== null) {
                    $type = typeof $answer[$key];
                }
                continue;
            }
            if (typeof $default[$key] !== 'object') {
                continue;
            }
            if (this.count($default[$key]) === 0) {
                continue;
            }
            $answer[$key] = this.default($default[$key], $answer[$key]);
        }

        if (this.isSet($callback)) {
            $answer.callback = $callback;
        }

        return $answer;
    };

    /**
     * Merge two objects, everything from obj2 goes into obj1.
     * example: $in = _Merge($default,$in);
     * Starts with $default and adds all keys from $in.
     * Used by you
     * @version 2015-01-17
     * @since   2013-09-05
     * @author  Peter Lembke
     * @param $object1
     * @param $object2
     * @return new object
     */
    this.merge = function ($object1, $object2)
    {
        "use strict";
        var $newObject = {};

        if (typeof $object1 === 'object') {
            for (let $key in $object1) {
                if ($object1.hasOwnProperty($key)) {
                    $newObject[$key] = $object1[$key];
                }
            }
        }

        if (typeof $object2 === 'object') {
            for (let $key in $object2) {
                if ($object2.hasOwnProperty($key)) {
                    $newObject[$key] = $object2[$key];
                }
            }
        }

        return this.byVal($newObject);
    };

    /**
     * Let go of the references to the object or array
     * @version 2015-01-17
     * @since   2014-01-03
     * @author  Peter Lembke
     * @param $object
     * @return new object
     */
    this.byVal = function ($object)
    {
        "use strict";

        if (!($object instanceof Object)) {
            return {};
        }

        return _MiniClone($object);
    };

    /**
     * Update HTML in the DOM
     * @param $id
     * @param $html
     */
    this.view = function ($id, $html) {
        let $box = document.getElementById($id);
        if (this.isSet($box)) {
            $box.innerHTML = $html;
        }
    };

    /**
     * Set a form value in a form element
     * @param $id
     * @param $html
     */
    this.setFormValue = function ($id, $html) {
        let $box = document.getElementById($id);
        if (this.isSet($box)) {
            $box.value = $html;
        }
    };

    /**
     * Get a value from a form element
     * @param $id
     * @param $html
     */
    this.getFormValue = function ($id) {
        let $value = '';
        let $box = document.getElementById($id);
        if (this.isSet($box)) {
            $value = $box.value;
        }

        return $value;
    };

    /**
     * Check if variable is set or not
     * @param $data
     * @returns {boolean}
     */
    this.isSet = function () {
        "use strict";

        var $arguments = arguments,
            $undefined;

        if ($arguments.length === 0) {
            return false;
        }

        if ($arguments[0] === $undefined || $arguments[0] === null) {
            return false;
        }

        return true;
    };

    /**
     * My definition of an empty variable
     *
     * @param $object
     * @returns {boolean}
     */
    this.empty = function ($object)
    {
        "use strict";

        if (typeof $object === 'undefined' || $object === null) {
            return true;
        }
        if (typeof $object === 'object' && _Count($object) === 0) {
            return true;
        }
        if (typeof $object === 'string' && $object === '') {
            return true;
        }

        return false;
    };


    this.count = function ($object)
    {
        "use strict";

        if (Array.isArray($object)) {
            return $object.length;
        }

        if ($object) {
            return Object.getOwnPropertyNames($object).length;
        }

        return 0;
    };

    /**
     * Wrapper so it is easier to change the places where json is used.
     * @param $data
     * @return string
     */
    this.jsonEncode = function ($data)
    {
        "use strict";

        // const $space = '\t'; // Pretty print with tab
        const $space = '    '; // Pretty print with space
        const $replacer = null;

        const $row = JSON.stringify($data, $replacer, $space); // Pretty print

        return $row;
    };

    /**
     * Wrapper so it is easier to change the places where json is used.
     * @param $row string
     * @return string
     */
    this.jsonDecode = function ($row)
    {
        "use strict";

        if (_GetDataType($row) !== 'string') {
            return $row;
        }

        if ($row.substring(0, 1) !== '{') {
            return {};
        }

        const $data = JSON.parse($row);

        return $data;
    };

    /**
     * Object deep clone
     * https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API/Structured_clone_algorithm#Another_way_deep_copy%E2%80%8E
     * @param objectToBeCloned
     * @returns {*}
     * @private
     */
    var _MiniClone = function (objectToBeCloned)
    {
        if (!(objectToBeCloned instanceof Object)) { return objectToBeCloned; }

        var $property,
            Constructor = objectToBeCloned.constructor,
            objectClone = new Constructor();

        for ($property in objectToBeCloned) {
            if (objectToBeCloned.hasOwnProperty($property)) {
                objectClone[$property] = _MiniClone(objectToBeCloned[$property]);
            }
        }

        return objectClone;
    };

    /**
     * Get variable data type name in lower case
     * Example:
     * @param obj
     * @returns {string}
     * @private
     */
    var _GetDataType = function (obj) {
        return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
    };

}
//# sourceURL=Base.js
