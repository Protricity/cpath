/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var onResize = function() {};

    var VERBOSE = 0x01; // Verbose message meant for the developers to see

    var WARNING = 0x10; // Warning log entry
    var ERROR = 0x20;   // Error log entry

    jQuery(document).ready(function() {
        jQuery(window).resize(onResize);
        onResize();

        jQuery('div.log-container').each(function(i, container) {
            container = jQuery(container);
            var target = 'body';
            if(container.data('target'))
                target = container.data('target');

            var reverse = container.hasClass('reverse-order');

            var log = function(message, flags) {
                var div = jQuery('<div class="log-entry">' + message + '</div>');
                if ((flags & VERBOSE))
                    div.addClass('verbose');
                if ((flags & WARNING))
                    div.addClass('warning');
                if ((flags & ERROR))
                    div.addClass('error');

                if(reverse) {
                    var height = container.height();
                    height += container.offset().top;
                    container.append(div);
                    container.scrollTop(height);

                } else {
                    container.prepend(div);
                    container.scrollTop(0);

                }
            };

            jQuery(target).on('log', function(e, message) {
                if(message instanceof Error)
                    log(message, ERROR);
                else
                    log(message, 0);
            });
        });
    });

})();

