/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var THIS = {};
    window.APIInfo = THIS = {
        submit: function(path, form, dataType, method) {
            form = jQuery(form);
            jQuery.ajax({
                url: path,
                type: method,
                dataType: dataType,
                data: form.serialize(),
                complete: function(jqXHR, textStatus) {
                    var content = jqXHR.responseText;
                    var call = 'html';

                    switch(dataType) {
                        case 'json':
                            content = vkbeautify.json(content);
                            break;
                        case 'xml':
                            content = vkbeautify.xml(content);
                            call = 'text';
                            break;
                    }

                    jQuery('div.response-header')
                        .html(jqXHR.status + ' ' + jqXHR.statusText + "\n" + jqXHR.getAllResponseHeaders())
                        .show();
                    jQuery('div.response-content')
                        [call](content)
                        .show();
                    console.log(arguments);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                }})
        }
    }
})();

