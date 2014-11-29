/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var EVENTS = 'remove insert change submit request navigate navigation-complete';

    var pending = 0;
    var Body = null;
    var onResize = function() {};

    var HTTP_SUCCESS = 200;

    var HTTP_SEE_OTHER = 303;
    var HTTP_TEMPORARY_REDIRECT = 307;

    var HTTP_ERROR = 400;
    var HTTP_NOT_FOUND = 404;
    var HTTP_CONFLICT = 409;

    var allowCache = false;
    jQuery.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
        if(!allowCache)
            return;
        if ( options.dataType == 'script' || originalOptions.dataType == 'script' ) {
            options.cache = true;
        }
    });

    var getAttributeString = function(elm) {
        var str = '';
        var attr = elm.attributes;
        for (var i in attr) {
            var p = attr[i];
            if (typeof p.value !== 'undefined')
                str += ' ' + p.nodeName + '="' + p.value + '"';
        }
        return str;
    };

    var getDOMPath = function(elm) {
        var rightArrowParents = [];
        $(elm).parents().not('html').each(function() {
            var entry = this.tagName.toLowerCase();
            if (this.className) {
                entry += "." + this.className.replace(/ /g, '.');
            }
            rightArrowParents.push(entry);
        });
        rightArrowParents.reverse();
        return rightArrowParents.join(" ");
    };

    var updateDOM = function(OldElements, NewElements) {
        var LastElm = null;
        var Container = OldElements.parent();
        for(var i=0; i<NewElements.length; i++) {
            var newElm = NewElements[i];
            if(newElm.nodeName.toLowerCase() === '#text')
                continue;
            var NewElm = jQuery(newElm);
            var found = false;
            for(var j=0; j<OldElements.length; j++) {
                var oldElm = OldElements[j];
                if(newElm.nodeName === oldElm.nodeName) {
                    var OldElm = jQuery(oldElm);
                    switch (newElm.nodeName.toLowerCase()) {
                        case 'script':
                            if(!NewElm.attr('src') || NewElm.attr('src') !== OldElm.attr('src'))
                                continue;
                            break;
                        case 'link':
                            if(!NewElm.attr('href') || NewElm.attr('href') !== OldElm.attr('href'))
                                continue;
                            break;
                        case 'label':
                            if(!NewElm.attr('for') || NewElm.attr('for') !== OldElm.attr('for'))
                                continue;
                            break;
                        default:
                            if(NewElm.attr('class') && NewElm.attr('class') === OldElm.attr('class'))
                                break;

                            var newAttr = getAttributeString(newElm);
                            var oldAttr = getAttributeString(oldElm);
                            if(newAttr === oldAttr)
                                break;

                            continue;
                    }
                    //console.log("Matched: ", oldElm, newElm);

                    updateDOM(OldElm.children(), OldElm.children());
                    found = true;
                    OldElements = OldElements.not(OldElm);
                    LastElm = OldElm;
                    //OldElm.trigger('change');
                    break;

                }
            }
            if(!found) {
                if(newElm.nodeName.toLowerCase() !== '#text')
                    console.log("Inserting: ", newElm);
                if(LastElm) {
                    LastElm = LastElm.after(newElm);
                } else {
                    LastElm = Container.prepend(newElm);
                }
                LastElm.trigger('insert');
            }
        }

        for(i=0; i<OldElements.length; i++) {
            console.log("Removing: ", OldElements[i]);
            jQuery(OldElements[i]).trigger('remove');
        }
    };

    var eventHandler = function(e, arg) {
        var Target = jQuery(e.target);
        var type = e.type;

        switch(type) {
            case 'insert':
                Target.hide();
                Target.fadeIn();
                e.stopPropagation();
                break;

            case 'remove':
                Target.fadeOut(function() {
                    Target.remove();
                });
                e.stopPropagation();
                break;

            case 'update':
//                Target.slideUp(function() {
//                    Target.slideDown();
//                });
                Target.fadeIn();
                e.stopPropagation();
                break;

            case 'submit':
                var Form = Target;
                if(!Form.is('form'))
                    Form = Form.parents('form');
                if(Form.is('form')) {
                    arg = jQuery.extend({
                        data: {},
                        url: Form.attr('action') || document.location.href.split('?')[0],
                        type: Form.attr('method') || 'GET'
                    }, arg || {});
                    jQuery.each(Form.serializeArray(), function(i, obj) {
                        arg.data[obj.name] = obj.value;
                    });
                    Form.trigger('navigate', [arg]);
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                break;
            case 'navigate':
                if(pending > 1)
                    throw new Error("Too many pending requests");
                pending++;

                if(typeof arg !== 'object')
                    arg = {url: arg};
                arg = jQuery.extend({
                    complete: function(jqXHR, textStatus) {
                        pending--;
                        Target.trigger("navigation-complete", [jqXHR.responseText, jqXHR.statusText, jqXHR]);
                    },
                    success: function(data, textStatus, jqXHR) {
                        Target.trigger("log", [jqXHR.statusText]);

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Target.trigger("log", [new Error(errorThrown)]);

                    }
                }, arg || {});

                jQuery.ajax(arg);
                e.stopPropagation();
                e.preventDefault();
                return;
                break;

            case 'navigation-complete':
                allowCache = true;
                var HTML = jQuery(arg);
                //Body.append(Container);
                allowCache = false;

                var NewHeaders = HTML.filter('script, link');
                var NewBody = HTML.not(NewHeaders);

                var OldHeaders = jQuery('head script, head link');

                updateDOM(OldHeaders, NewHeaders);

                updateDOM(Body.children(), NewBody);
                HTML.remove();
                Body.trigger('ready');
                break;

//                var found = false;
//                var NewSections = Container.find('section');
//                var timeout = 300;
//                NewSections.each(function(i, newSection) {
//                    var NewSection = jQuery(newSection);
//                    var query = newSection.nodeName.toLowerCase() + '.' + NewSection.attr('class').replace(/\s+/, '.');
//                    var OldSection = jQuery(query);
//                    if(OldSection.length === 0)
//                        return;
//
//                    var oldDOMPath = getDOMPath(OldSection[0]);
//                    OldSection.empty();
//                    NewSection = OldSection.append(NewSection.children());
//                    var newDOMPath = getDOMPath(NewSection[0]);
//                    if(newDOMPath !== oldDOMPath)
//                        console.error("DOM Path mismatch: " + newDOMPath + " != " + oldDOMPath);
//
//                    NewSection
//                        .children()
//                        .hide()
//                        .fadeIn(timeout);
//
//                    timeout += 300;
//
//                    found = true;
//                });
//
//                if(!found) {
//                    console.error("No page sections found. Replacing entire body");
//                    Body.empty();
//                    Body.append(Container
//                        .children()
//                        .not(Headers)
//                    );
//                }
//
//                var OldHeaders = jQuery('head script, head link');
//                Headers.each(function(i, header) {
//                    var Header = jQuery(header);
//                    var found = false;
//                    if(Header.is('script')) {
//                        var src = Header.attr('src');
//                        src = src.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');
//                        var OldScript = OldHeaders.filter('script[src=' + src + ']');
//                        found = OldScript.length > 0;
//
//                    } else if (Header.is('link')) {
//                        var href = Header.attr('href');
//                        href = href.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');
//                        var OldStyleSheet = OldHeaders.filter('link[href=' + href + ']');
//                        found = OldStyleSheet.length > 0;
//
//                    }
//
//                    if(found)
//                        return;
//
//                    OldHeaders.append(header);
//                });

            case 'log':
                break;
        }
    };

    jQuery(document).ready(function() {
        jQuery(window).resize(onResize);
        onResize();

        Body = jQuery('body');
        Body.on(EVENTS, eventHandler);

//            Form.find('input[type=submit]').click(
//                function(e) {
//                    var input = jQuery(this);
//                    formValues[input.attr('name')] = input.val();
//                }
//            );
    });

})();

