/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var onResize = function() {};

    jQuery(document).ready(function() {
        jQuery(window).resize(onResize);
        onResize();
        var pending = 0;

        jQuery('form.html-form-ajax').each(function(i, form) {
            form = jQuery(form);

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
                    form.trigger( "response-content", [content, jqXHR.statusText, jqXHR]);

                    var jsonContent = jQuery.parseJSON(content);
                    form.trigger( "response-json", [jsonContent, jqXHR.statusText, jqXHR]);

                },
                success: function(data, textStatus, jqXHR) {
                    form.trigger( "success", [data, jqXHR.statusText, jqXHR]);
                    form.trigger( "log", [jqXHR.statusText]);

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    form.trigger( "error", [errorThrown, jqXHR]);
                    form.trigger( "log", [new Error(errorThrown)]);

                }
            };

            var submit = function(values) {
                var ajax = jQuery.extend({
                    data: values || form.serialize()
                }, ajaxConfig);

                if(pending > 3)
                    throw new Error("Too many pending requests");
                pending++;

                form.trigger( "request", [ajax.url, ajax.data]);
                form.trigger( "log", [ajax.url + '?' + jQuery.param(ajax.data)]);
                jQuery.ajax(ajax);
            };

            form.find('button[type=submit]').click(
                function(e) {
                    var input = jQuery(this);
                    formValues[input.attr('name')] = input.val();
                }
            );

            form.submit(function( event ) {
                var values = jQuery.extend(form.serialize(), formValues);
                event.preventDefault();
                submit(values);
            });
        });
    });

})();

