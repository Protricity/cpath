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
        var basePath = jQuery('base').attr('href');
        jQuery('form.fragment-form').each(function(i, frag) {
            var form = jQuery(this);
            var Form = new CPath.Form(form);
            var API = Form.getAPI();

            var btnSubmit = form.find('.form-button-submit');
            var btnSubmitJSON = form.find('.form-button-submit-json');
            var btnSubmitXML = form.find('.form-button-submit-xml');
            var btnSubmitTEXT = form.find('.form-button-submit-text');

            if(!btnSubmit.length && !btnSubmit.length && !btnSubmit.length && !btnSubmit.length)
                throw new Error("No inputs were detected");

            btnSubmit.click(function() { Form.submit({dataType: 'json'}); });
            btnSubmitJSON.click(function() { Form.submit({dataType: 'json'}); });
            btnSubmitXML.click(function() { Form.submit({dataType: 'xml'}); });
            btnSubmitTEXT.click(function() { Form.submit({dataType: 'text'}); });
        });
    });
})();

