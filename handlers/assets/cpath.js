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

    // API

    THIS = null;

    window.CPath.API = THIS = {
        Response: function(data) {
            var context = this;
            this.getData = function() { return data; };
            this.getStatus = function() { throw new Error("Unimplemented: getStatus"); };
            this.getMessage = function() { throw new Error("Unimplemented: getMessage"); };
            this.getResponse = function() { throw new Error("Unimplemented: getResponse"); };
            //this.getSearchResults = function() { throw new Error("Unimplemented: getSearchResults"); };
        },

        JSONResponse: function(data) {
            this.base = THIS.Response;
            this.base(data);
            var context = this;
            this.getStatus = function() { return data.status; };
            this.getMessage = function() { return data.msg; };
            this.getResponse = function() { return data.response; };
        },

        XMLResponse: function(data) {
            var context = this;
            this.base = THIS.Response;
            this.base(data);
            var dom = jQuery(data).children('root');
            this.getDOM = function() { return dom; };
            this.getStatus = function() { return dom.children('status').val(); };
            this.getMessage = function() { return dom.children('msg').val(); };
            this.getResponse = function() { return dom.children('response'); };
        },

        JSONSearchResponse: function(data) {
            var context = this;
            this.base = THIS.JSONResponse;
            this.base(data);
            this.getSearchResults = function(callback) {
                var rows = context.getResponse();
                for(var i=0; i < rows.length; i++) {
                    callback(rows[i], rows[i]);
                }
            };
            this.getStats = function() { return data.stats; };
            this.getPageIDs = function() { return context.getStats().pages; };
        },

        XMLSearchResponse: function(data) {
            var context = this;
            this.base = THIS.XMLResponse;
            this.base(data);
            this.getSearchResults = function(callback) {
                var rows = this.getResponse().children();
                for(var i=0; i < rows.length; i++) {
                    var attr = rows[i].attributes;
                    var attrObj = {};
                    Array.prototype.slice.call(attr).forEach(function(item) {
                        attrObj[item.name] = item.value;
                    });
                    callback(attrObj, rows[i]);
                }
            };
            this.getStats = function() {
                var stats = this.getDOM().children('stats');
                var attr = stats[0].attributes;
                var attrObj = {};
                Array.prototype.slice.call(attr).forEach(function(item) {
                    attrObj[item.name] = item.value;
                });
                attrObj['pages'] = {};
                stats.children('page').each(function() {
                    var elm = jQuery(this);
                    attrObj['pages'][elm.attr('id')] = elm.text();
                });
                return attrObj;
            };
            this.getPageIDs = function() { return context.getStats().pages; };
        }
    };

})();

