/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var THIS = {};
    var lastHeaders = '';

    jQuery(document).ready(function() {
        var basePath = jQuery('base').attr('href');


//        jQuery('form').on("cpath-submit", function(event, url) {
//            THIS.submit(url, jQuery(this), 'json', 'GET');
//        });
        jQuery('.apiview-form').each(function() {
            var form = jQuery(this);
            var Form = new CPath.Form(form);
            var btnSubmit = form.find('.form-button-submit');
            var btnSubmitJSON = form.find('.form-button-submit-json');
            var btnSubmitXML = form.find('.form-button-submit-xml');
            var btnSubmitTEXT = form.find('.form-button-submit-text');


            var divResponseContainer = form.parent().find('.apiview-response');
            if(divResponseContainer.length != 1)
                throw new Error("Could not find response container");
            var divRequestHeaders = divResponseContainer.find('.request-headers .fragment-content');
            var divResponseHeaders = divResponseContainer.find('.response-headers .fragment-content');
            var divResponseContent = divResponseContainer.find('.response-content .fragment-content');

            btnSubmit.click(function() { Form.submit({dataType: 'json'}); });
            btnSubmitJSON.click(function() { Form.submit({dataType: 'json'}); });
            btnSubmitXML.click(function() { Form.submit({dataType: 'xml'}); });
            btnSubmitTEXT.click(function() { Form.submit({dataType: 'text'}); });

            var content = jQuery.trim(divResponseContent.text());
            if(content)
                APIView.setPrettyJSON(content, divResponseContent);

            jQuery(Form).on('api-response', function(evt, response, API, jqXHR) {
                var content = jqXHR.responseText;

                switch(content.substr(0, 1)) {
                    case '{':
                        THIS.setPrettyJSON(content, divResponseContent);
                        break;
                    case '<':
                        THIS.setPrettyXML(content, divResponseContent);
                        break;
                    default :
                        divResponseContent.text(content);
                        response = new CPath.API.Response(content);
                        break;
                }

                divResponseContainer.fadeIn();
                divRequestHeaders.html(API.getMethod() + ' ' + API.getPath() + " HTTP/1.1\n" + lastHeaders);
                divResponseHeaders.html(jqXHR.status + ' ' + jqXHR.statusText + "\n" + jqXHR.getAllResponseHeaders());
                console.log(arguments);
            });
        });
    });

    window.APIView = THIS = {
        setPrettyJSON: function(json, elm) {
            try {
                json = vkbeautify.json(json);
                elm.html(json);
            } catch (e) {
                elm.text(json);
                console.error("Error: " + e);
            }
        },
        setPrettyXML: function(xml, elm) {
            try {
                xml = vkbeautify.xml(xml);
                elm.text(xml);
            } catch (e) {
                elm.text(xml);
            }
        },

        updateURL: function(form) {
            form = jQuery(form);
            var parts = document.location.href.split('#');
            var parts2 = parts[0].split('?');
            document.location.href = parts2[0] + '?' + form.serialize() + (parts[1] ? '#' + parts[1] : '');
        },

        hackXHR: function() {
            if(XMLHttpRequest.prototype.wrappedSetRequestHeader) {
                console.error("wrappedSetRequestHeader already exists in XHR");
                return;
            }
            XMLHttpRequest.prototype.wrappedSetRequestHeader =
                XMLHttpRequest.prototype.setRequestHeader;

            XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
                this.wrappedSetRequestHeader(header, value);
                lastHeaders += header + ": " + value + "\n";
            }
        },

        unhackXHR: function() {
            XMLHttpRequest.prototype.setRequestHeader = XMLHttpRequest.prototype.wrappedSetRequestHeader;
            delete XMLHttpRequest.prototype.wrappedSetRequestHeader;
        },

        formToObject: function(form) {
            form = jQuery(form);
            var o = {};
            var a = form.serializeArray();
            $.each(a, function() {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        }
    }


})();

