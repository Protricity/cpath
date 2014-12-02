/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){

    var EVENTS = 'log error keydown';

    var onResize = function() {
//        var Console = jQuery('.html-console');
//        var windowHeight = jQuery(window).height();
//        var windowScroll = jQuery(window).scrollTop();
//        var bodyHeight = jQuery('body').height();
//        var consoleHeight = Console.height();
//        if(Console.hasClass('fixed')) {
//            if(windowHeight + windowScroll > bodyHeight + consoleHeight)
//                Console.removeClass('fixed')
//                    .parent().removeClass('fixed');
//        } else {
//
//            if(windowHeight + windowScroll < bodyHeight)
//                Console.addClass('fixed')
//                    .parent().addClass('fixed');
//        }
    };

    var eventHandler = function(e, arg) {
        var Target = jQuery(e.target);
        var type = e.type;

        switch(type) {
            case 'log':
                break;
            case 'error':
                break;
            case 'keydown':
                break;
        }

    };



    jQuery(document).ready(function() {
        var Body = jQuery('body');
        Body.on(EVENTS, eventHandler);

        jQuery(window).resize(onResize);
        onResize();
//        jQuery(window).resize(onResize);
//        onResize();


        jQuery('div.html-console').each(function(i, container) {
            container = jQuery(container);

            var input = container.find('.html-console-input-text');
            if(input.length === 0) throw new Error("Console input not found");

            var inputPath = container.find('.html-console-input-path');
            if(inputPath.length === 0) throw new Error("Console path input not found");

            var inputDomain = container.find('.html-console-input-domain');
            if(inputDomain.length === 0) throw new Error("Console domain input not found");

            var logContainer = container.find('.html-console-log');
            if(logContainer.length === 0) throw new Error("Console log not found");

            var markerText = container.find('.html-console-marker').text();

            //var requestPath = document.location.href.split('?')[0];

            var historyPos = 0;
            var history = [];

            var body = jQuery('body');
            var target = body;

            container.click(function() {
                input.focus();
            });

            var send = function(text) {
                container.trigger( "log", [markerText + ' ' + text]);

                var ajaxConfig = {
                    url: inputDomain.val() + inputPath.val() + '?' + text,
                    type: 'CLI',
                    dataType: 'text',
                    accepts: 'text/plain',
                    complete: function(jqXHR, textStatus) {
                        var content = jqXHR.responseText;
                        target.trigger( "log", [content, jqXHR.statusText, jqXHR]);
                        container.trigger( "log", [inputPath.val()]);

                    },
                    success: function(data, textStatus, jqXHR) {
                        target.trigger( "log-success", [data, jqXHR.statusText, jqXHR]);

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        target.trigger( "log-error", [errorThrown, jqXHR]);

                    }
                };

                jQuery.ajax(ajaxConfig);
            };

            target.bind('keydown', function(e) {
                switch(e.keyCode) {
                    case 9:     // tab
                        var active = document.activeElement === body[0]
                            || document.activeElement === input[0];
                        if(active)
                            container.show();
                        else
                            container.hide();
                        target.trigger( "log", [active, document.activeElement]);
                        break;
                    case 192:   // ~
                        container.toggle();
                        break;
                }
            });

            input.bind("keydown", function(e) {
                //console.log(e.keyCode);
                var text = input.val();
                var addHistory = function(text) {
                    if(text && history.indexOf(text) === -1) {
                        historyPos++;
                        history[history.length] = text;
                    }
                };

                switch(e.keyCode) {
                    case 13:
                        addHistory(text);
                        input.val('');
                        send(text);
                        break;

                    case 38: // Up
                        addHistory(text);
                        historyPos++;
                        if(historyPos >= history.length)
                            historyPos =  history.length - 1;
                        input.val(history[historyPos]);

                        break;

                    case 40: // Down
                        addHistory(text);
                        historyPos--;
                        if(historyPos < 0)
                            historyPos = 0;
                        input.val(history[historyPos]);

                        break;
                }
            });
        });
    });

})();

