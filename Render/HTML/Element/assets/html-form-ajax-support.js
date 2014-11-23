/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
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

    jQuery(document).ready(function() {
        jQuery(window).resize(onResize);
        onResize();
        var pending = 0;

        jQuery('form .html-form-ajax-support').each(function(i, container) {
            container = jQuery(container);
            var form = container.parents('form');
            if(form.length === 0)
                throw new Error("Form not found");

            var setLegend = function(text, code) {
                container.removeClass('error');
                if(code !== HTTP_SUCCESS)
                    container.addClass('error');
                if(text.indexOf("\n") > -1)
                    text = '<p>' + text.split("\n").join("</p><p>") + '</p>';

                container.html(text);
                container.hide();
                container.fadeIn();
            };

            var formValues = {};
            var ajaxConfig = {
                url: form.attr('action') || document.location.href.split('?')[0],
                type: form.attr('method') || 'GET',
                dataType: 'json',
                accepts: 'application/json',
                //contentType: asObject ? 'application/json' : null,
                headers: {
                    Accept : "application/json; charset=utf-8"
                    //"Content-Type": asObject ? 'application/json' : null
                },
                complete: function(jqXHR, textStatus) {
                    pending--;

                    var content = jqXHR.responseText;
                    deferTrigger(form, "response-content", [content, jqXHR.statusText, jqXHR]);

                    try {
                        var jsonContent = jQuery.parseJSON(content);
                        deferTrigger(form, "response-json", [jsonContent, jqXHR.statusText, jqXHR]);
                        setLegend(jsonContent.message || "No Message", jsonContent.code || HTTP_ERROR);

                    } catch (e) {
                        setLegend(e.message + '<br/>' + content, HTTP_ERROR);

                    }
                },
                success: function(data, textStatus, jqXHR) {
                    deferTrigger(form, "success", [data, jqXHR.statusText, jqXHR]);
                    deferTrigger(form, "log", [jqXHR.statusText]);

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    deferTrigger(form, "error", [errorThrown, jqXHR]);
                    deferTrigger(form, "log", [new Error(errorThrown)]);

                }
            };

            var submit = function(values) {
                var ajax = jQuery.extend({
                    data: values || form.serialize()
                }, ajaxConfig);

                if(pending > 1)
                    throw new Error("Too many pending requests");
                pending++;

                deferTrigger(form, "request", [ajax.url, ajax.data]);
                //form.trigger( "log", [ajax.url + '?' + jQuery.param(ajax.data)]);
                jQuery.ajax(ajax);
            };

            form.find('button[type=submit]').click(
                function(e) {
                    var input = jQuery(this);
                    formValues[input.attr('name')] = input.val();
                }
            );

            form.submit(function( event ) {
                var values = {};
                jQuery.each(form.serializeArray(), function(i, obj) {
                    values[obj.name] = obj.value;
                });
                jQuery.extend(values, formValues);
                event.preventDefault();
                submit(values);
            });
        });
    });

})();

