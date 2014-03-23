/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var THIS = {};
    var TRIGGERS;

    var forms = jQuery();
    window.CPath.Form = function(formElm) {
        var context = this;
        var form = jQuery(formElm);
        if(form.length < 1)
            throw new Error("Form " + formElm + " not found");
        if(form.length > 1)
            throw new Error("Too many forms found: " + formElm);

        var url = form.attr('action') || document.location.href.split('?')[0];
        var method = form.attr('method') || 'GET';

        var API = new CPath.API(method, url);
        jQuery(API).on('response', function(evt, response, jqXHR) {
            jQuery([window.CPath.Form, context, formElm]).trigger('api-response', [response, context.getAPI(), jqXHR]);
        });

        this.submit = function(ajax) {
            if(typeof ajax != "object")
                ajax = {type: ajax};
            API.execute(form.serialize(), ajax);
        };

        this.getAPI = function() { return API; };

        if(!forms.has(form)) {
            forms.add(form);
            form.bind('submit', function() { return context.submit(); });
        }
    };

})();

