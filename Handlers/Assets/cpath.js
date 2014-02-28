/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var THIS = {};

    jQuery(document).ready(function() {

    });

    window.CPath = THIS = {
        Param: {},
        Cookie: {},
        Themes: {
            Utils: {}
        },
        API: {},
        Form: {},

        /**
         * Parse a query string into an object
         * @param query String the query string to parse
         * @param split String the value to split on or '&' if empty
         * @returns {{}} the parsed query string
         */
        parseQueryString: function(query, split) {
            if(!split) split = '&';
            var param = {};
            if(!query)
                return param;
            var vars = query.split(split);
            for (var i = 0; i < vars.length; i++) {
                if(!vars[i]) continue;
                var pair = vars[i].split('=', 2);
                pair[0] = jQuery.trim(pair[0]);
                param[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
            }
            return param;
        },

        /**
         * Set a cookie
         * @param key String the cookie name
         * @param value String the cookie value
         * @param [expHours] Integer the number of hours the cookie will expire in
         * @param [path] String the cookie path
         */
        setCookie: function(key, value, expHours, path) {
            if(!path) path = '/';
            expires = "";
            if (expHours) {
                var date = new Date();
                date.setTime(date.getTime()+(expHours*60*60*1000));
                expires = "; expires="+date.toGMTString();
            }
            document.cookie = key + "=" + value + expires + "; path=" + path;
            NewAer.Cookie[key] = value;
        },

        /**
         * Clear a cookie
         * @param key String the name of the cookie to clear
         */
        clearCookie: function(key) {
            NewAer.setCookie(key, "", -1);
            delete NewAer.Cookie[key];
        }
    };

    // Parse the current window's query string into parameters
    THIS.Param = jQuery.extend(
        THIS.parseQueryString(window.location.hash.substring(1), '&'),
        THIS.parseQueryString(window.location.search.substring(1), '&')
    );

    THIS.Cookie = THIS.parseQueryString(document.cookie, ';');


})();

