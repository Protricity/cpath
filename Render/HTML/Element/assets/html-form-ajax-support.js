/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var EVENTS = 'submit request';
    var FORM_AJAX_CLASS = 'ajax';

    var pending = 0;
    var Body = null;
    var onResize = function() {};

    var HTTP_SUCCESS = 200;

    var HTTP_SEE_OTHER = 303;
    var HTTP_TEMPORARY_REDIRECT = 307;

    var HTTP_ERROR = 400;
    var HTTP_NOT_FOUND = 404;
    var HTTP_CONFLICT = 409;

    var deferTrigger = function(elm, eventName, args) {
        setTimeout(function() {
            elm.trigger(eventName, args);
        }, 0)
    };

    var sendJSONAjaxRequest = function(e, ajax) {
        var CurrentTarget = jQuery(e.currentTarget);
        var Target = jQuery(e.target);
        ajax = jQuery.extend(ajax, {

            dataType: 'json',
            headers: {
                Accept : "application/json; charset=utf-8"
            },
            complete: function(jqXHR, textStatus) {
                pending--;

                var content = jqXHR.responseText;
                Target.trigger("response-content", [content, jqXHR.statusText, jqXHR]);

                try {
                    var jsonContent = jQuery.parseJSON(content);
                    Target.trigger("response-json", [jsonContent, jqXHR.statusText, jqXHR]);
                    if(typeof jsonContent.message !== "undefined") {
                        Target.trigger('log', [jsonContent.message, jsonContent.code]);
                    }

                } catch (e) {
                    Target.trigger('log', [e, HTTP_ERROR]);
                }
            },
            success: function(data, textStatus, jqXHR) {
                Target.trigger("success", [data, jqXHR.statusText, jqXHR]);
                Target.trigger("log", [jqXHR.statusText]);

            },
            error: function(jqXHR, textStatus, errorThrown) {
                Target.trigger("error", [errorThrown, jqXHR]);
                Target.trigger("log", [new Error(errorThrown)]);

            }
        });

        e.preventDefault();
        e.stopPropagation();
        jQuery.ajax(ajax);
    };

    var submitForm = function(e, Form) {
        if(pending > 1)
            throw new Error("Too many pending requests");
        pending++;

        var ajax = {
            data: {},
            url: Form.attr('action') || document.location.href.split('?')[0],
            type: Form.attr('method') || 'GET'
        };
        jQuery.each(Form.serializeArray(), function(i, obj) {
            ajax.data[obj.name] = obj.value;
        });

        e.preventDefault();
        e.stopPropagation();
        Form.trigger('request', [ajax]);
    };

    var eventHandler = function(e, arg) {
        var Target = jQuery(e.target);
        var type = e.type;

        switch(type) {
            case 'submit':
                var Form = Target;
                if(!Form.is('form'))
                    Form = Form.parents('form');
                if(Form.hasClass(FORM_AJAX_CLASS)) {
                    submitForm(e, Form);
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                break;
            case 'request':
                sendJSONAjaxRequest(e, arg);
                e.stopPropagation();
                e.preventDefault();
                return;
            case 'log':
                break;
        }
    };

    jQuery(document).ready(function() {
        jQuery(window).resize(onResize);
        onResize();

        Body = jQuery('body');
        Body.on(EVENTS, eventHandler);

//            Form.find('input[type=submit]').click(
//                function(e) {
//                    var input = jQuery(this);
//                    formValues[input.attr('name')] = input.val();
//                }
//            );
    });

})();

