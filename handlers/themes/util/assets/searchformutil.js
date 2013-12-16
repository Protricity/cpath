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
        jQuery('.search-form-util').each(function(i, frag) {
            frag = jQuery(frag);
            var form = frag.find('form.apiview-form');
            var table = frag.find('table.search-content');
            var tbody = frag.find('table.search-content tbody');

            var onPageClick = function() {
                var url = jQuery(this).attr('href');
                //var params = CPath.parseQueryString(url.split('?')[1]);
                form.trigger("cpath-submit", [url]);
                return false;
            };

            frag
                .find('a.search-form-page')
                .click(onPageClick);

            form.on("cpath-response", function(event, response, content) {
                console.debug(arguments, form, table);
                tbody
                    .children()
                    .remove();
                response.getSearchResults(function(row) {
                    var html = '<tr>';
                    jQuery.each(row, function(key, value) {
                        if(value)
                            value = value.replace( /<.*?>/g, ' ' );
                        html += '<td>' + value + '</td>'
                    });
                    html += '</tr>';
                    tbody.append(jQuery(html).fadeIn());
                });

                var stats = response.getStats();

                var pages = frag.find('.search-form-pages');
                var label = {};
                label.prev = frag.find('.search-form-page-previous');
                label.next = frag.find('.search-form-page-next');
                //label.last = container.find('.search-form-page-last');
                //label.next = container.find('.search-form-page-next');

                var tmpl = pages.attr('data-template-url');
                var getURL = function(id) {
                    return tmpl
                        .replace(encodeURIComponent('%PAGE%'), id)
                        .replace(encodeURIComponent('%LIMIT%'), stats.limit);
                };

                pages.children().remove();
                var ids = response.getPageIDs();
                jQuery.each(ids, function(i, id) {
                    pages.append(" <a href='" + getURL(id) + "' class='search-form-page'>" + id + '</a>');
                });

                label.prev.attr('href', getURL(stats.previousPage));
                label.next.attr('href', getURL(stats.nextPage));

                frag
                    .find('.search-form-pages a')
                    .click(onPageClick);
            });
        });
    });

    window.CPath.Themes.Utils.SearchFormUtil = THIS = {
        MODE_MOBILE: 'mobile',
        MODE_NARROW: 'narrow',
        MODE_WIDE: 'wide',

        minWidths: {
            mobile: 320,
            narrow: 640,
            wide: 1024
        }
    };
})();

