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

        jQuery('div.holdem-container').each(function(i, container) {
        });
    });

})();

