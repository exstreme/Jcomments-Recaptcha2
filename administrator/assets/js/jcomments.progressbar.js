/**
 * JComments - Joomla Comment System
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

!function ($) {
    var JCommentsProgressbar = function (element) {
        this.container = $(element);
        if (this.container) {
            this.bar = $(document.createElement('div')).addClass('jcomments-progressbar-value').width(0);
            this.label = $(document.createElement('div')).addClass('jcomments-progressbar-label');
            this.container.html('').append($(this.bar).append(this.label)).addClass('jcomments-progressbar');
            this.set(0);
        }
    };

    JCommentsProgressbar.prototype = {
        constructor: JCommentsProgressbar,
        set: function (value) {
            if (this.container) {
                if (value > 100) {
                    value = 100;
                }
                $(this.bar).width(value + '%');
                $(this.label).html(value + '%');
                $(this.container).show();
            }
        },
        hide: function () {
            if (this.container) {
                this.container.hide();
            }
        }
    };

    this.JCommentsProgressbar = JCommentsProgressbar;
}(window.jQuery);