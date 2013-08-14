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
    var requestHeaders, responseHeaders, responseContent, responseContainer;
    var basePath;
    jQuery(document).ready(function() {
        requestHeaders = jQuery('div.request-headers');
        responseHeaders = jQuery('div.response-headers');
        responseContent = jQuery('div.response-content');
        responseContainer = jQuery('div.response-container');
        basePath = jQuery('base').attr('href');
    });
    window.APIInfo = THIS = {
        submit: function(path, form, dataType, method) {
            form = jQuery(form);
            lastHeaders = '';
            THIS.hackXHR();
            responseContainer.show();
            requestHeaders.html("Loading...");
            responseHeaders.html("Loading...");
            responseContent.html("Loading...");
            jQuery.ajax({
                url: path,
                type: method,
                dataType: dataType,
                data: form.serialize(),
                complete: function(jqXHR, textStatus) {
                    THIS.unhackXHR();
                    var content = jqXHR.responseText;
                    var call = 'html';

                    switch(dataType) {
                        case 'json':
                            try {
                                content = vkbeautify.json(content);
                            } catch (e) {}
                            break;
                        case 'xml':
                            try {
                                content = vkbeautify.xml(content);
                            } catch (e) {}
                            call = 'text';
                            break;
                    }

                    requestHeaders.html(method + ' ' + path + " HTTP/1.1\n" + lastHeaders);
                    responseHeaders.html(jqXHR.status + ' ' + jqXHR.statusText + "\n" + jqXHR.getAllResponseHeaders());
                    responseContent[call](content);
                    console.log(arguments);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                }})
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
        }
    }


})();

