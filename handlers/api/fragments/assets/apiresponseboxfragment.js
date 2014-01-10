/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){

    var lastHeaders = '';
    function setPrettyJSON(json, elm) {
        try {
            json = vkbeautify.json(json);
            elm.html(json);
        } catch (e) {
            elm.text(json);
            console.error("Error: " + e);
        }
    }

    function setPrettyXML(xml, elm) {
        try {
            xml = vkbeautify.xml(xml);
            elm.text(xml);
        } catch (e) {
            elm.text(xml);
        }
    }

    jQuery(document).ready(function() {
        jQuery('.apiresponsebox-fragment').each(function(i, divResponseContainer) {
            divResponseContainer = jQuery(divResponseContainer);

            var form = divResponseContainer.siblings('form');
            if(form.length == 0)
                 throw new Error("Could not find form");

            var Form = new CPath.Form(form);
            var API = Form.getAPI();

            var divRequestHeaders = divResponseContainer.find('.request-headers .fragment-content');
            var divResponseHeaders = divResponseContainer.find('.response-headers .fragment-content');
            var divResponseContent = divResponseContainer.find('.response-content .fragment-content');

            var content = jQuery.trim(divResponseContent.text());
            if(content)
                setPrettyJSON(content, divResponseContent);

            jQuery(Form).on('api-response', function(evt, response, API, jqXHR) {
                var content = jqXHR.responseText;

                switch(content.substr(0, 1)) {
                    case '{':
                        setPrettyJSON(content, divResponseContent);
                        break;
                    case '<':
                        setPrettyXML(content, divResponseContent);
                        break;
                    default :
                        divResponseContent.text(content);
                        response = new CPath.API.Response(content);
                        break;
                }

                divResponseContainer.fadeIn();
                divRequestHeaders.html(API.getMethod() + ' ' + API.getPath(true) + " HTTP/1.1\n" + lastHeaders);
                divResponseHeaders.html(jqXHR.status + ' ' + jqXHR.statusText + "\n" + jqXHR.getAllResponseHeaders());
                console.log(arguments);
            });
        });
    });
})();



