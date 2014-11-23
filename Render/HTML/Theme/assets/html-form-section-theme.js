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
        var pending = 0;

        jQuery('.html-form-section-theme').each(function(i, section) {
            var Section = jQuery(section);
            var Content = Section.children(':not(legend)');
            var Legend = Section.children('legend');
            if(Legend.length === 0)
                throw new Error("legend not found");
            Legend = Legend.first();

            var ToggleSign = jQuery('<span style="float: right; font-size: 8pt;"></span>');
            Legend.append(ToggleSign);
            ToggleSign.text('{}');

            var open = function() {
                Legend.removeClass('closed');
                Content.slideDown();
                ToggleSign.slideUp(function() {
                    ToggleSign.text('{}');
                    ToggleSign.slideDown();
                });
            };

            var close = function() {
                Legend.addClass('closed');
                Content.slideUp();
                ToggleSign.slideUp(function() {
                    ToggleSign.text('{..}');
                    ToggleSign.slideDown();
                });
            };

            var toggle = function() {
                if(Legend.hasClass('closed')) {
                    open();
                } else {
                    close();
                }
            };
            Legend.on('click keydown', toggle);
            Section.on('keydown', function() {
                if(Legend.hasClass('closed'))
                    open();
            });

            Section.on('section-close', close);
            Section.on('section-open', open);

            if(Legend.hasClass('closed'))
                close();

            Legend.css('cursor', 'pointer');
        });
    });
})();

