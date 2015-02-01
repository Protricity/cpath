/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/21/14
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var CLS_TBODY_SEQUENCE = 'tbody-sequence';

    var ready = function() {

        //jQuery('form[name=' + FORM_NAME + ']').each(function(i, form) {
        //    if (typeof form.createInit !== 'undefined')
        //        return;
        //    form.createInit = true;
        //
        //
        //    console.info("Transaction Form Found: ", Form);
        //    var Form = jQuery(form);
        //    Form.on('input', function(e) {
        //        var productID = Form.find('*[name=' + PARAM_PRODUCT_ID + ']').val();
        //        var ProductFieldSets = Form.find('.' + CLS_FIELDSET_PRODUCT);
        //        var ProductFieldSet = ProductFieldSets.filter('[data-' + PARAM_PRODUCT_ID + '=' + productID + ']');
        //        ProductFieldSets.not(ProductFieldSet).filter(':enabled').attr('disabled', 'disabled');
        //        ProductFieldSet.filter(':disabled').removeAttr('disabled');
        //    }).trigger('input');
        //});
    };
    jQuery(document).ready(function() {
        jQuery('body').on('ready', ready);
        ready();
    });
})();

