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
        requestHeaders = jQuery('.request-headers .fragment-content');
        responseHeaders = jQuery('.response-headers .fragment-content');
        responseContent = jQuery('.response-content .fragment-content');
        responseContainer = jQuery('div.response-container');
        basePath = jQuery('base').attr('href');

        var content = responseContent.text();
        if(content)
            APIView.setPrettyJSON(content, responseContent);

        jQuery('form').on("cpath-submit", function(event, url) {
            THIS.submit(url, jQuery(this), 'json', 'GET');
        });
    });

    window.APIView = THIS = {
        accepts: '*/*',
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
        submit: function(path, form, dataType, method, asObject, accepts) {
            form = jQuery(form);
            var params = CPath.parseQueryString(path.split('?')[1]);
            jQuery.each(params, function(key, value) {
                form.find('input[name=' + key + ']').val(value);
            });

            var data = asObject ? JSON.stringify(THIS.formToObject(form)) : form.serialize();
            lastHeaders = '';
            //THIS.hackXHR();
            responseContainer.show();
            requestHeaders.html("Loading...");
            responseHeaders.html("Loading...");
            responseContent.html("Loading...");
            if(!accepts)
                accepts = THIS.accepts;
            switch(dataType) {
                case 'json':
                    accepts = 'application/json';
                    break;
                case 'xml':
                    accepts = 'text/xml';
                    break;
            }
            jQuery.ajax({
                url: path,
                type: method,
                dataType: dataType,
                data: data,
                accepts: accepts,
                contentType: asObject ? 'application/json' : null,
                headers: {
                    Accept : accepts + "; charset=utf-8",
                    "Content-Type": asObject ? 'application/json' : null
                },
                complete: function(jqXHR, textStatus) {
                    //THIS.unhackXHR();
                    var content = jqXHR.responseText;
                    var call = 'html';
                    var response;

                    switch(dataType) {
                        case 'json':
                            THIS.setPrettyJSON(content, responseContent);
                            response = new CPath.API.JSONSearchResponse(jQuery.parseJSON(content));
                            form.trigger( "cpath-response-json", [response, content], jqXHR);
                            break;
                        case 'xml':
                            THIS.setPrettyXML(content, responseContent);
                            response = new CPath.API.XMLSearchResponse(jQuery.parseXML(content));
                            form.trigger( "cpath-response-xml", [response, content], jqXHR);
                            break;
                        default :
                            responseContent.text(content);
                            response = new CPath.API.Response(content);
                            break;
                    }

                    form.trigger( "cpath-response", [response, content] , jqXHR);

                    requestHeaders.html(method + ' ' + path + " HTTP/1.1\n" + lastHeaders);
                    responseHeaders.html(jqXHR.status + ' ' + jqXHR.statusText + "\n" + jqXHR.getAllResponseHeaders());
                    console.log(arguments);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                }})
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

