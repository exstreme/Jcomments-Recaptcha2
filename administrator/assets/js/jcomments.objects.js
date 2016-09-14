/**
 * JComments - Joomla Comment System
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

(function ($) {
    var JCommentsObjects = {
        progress: null,
        url: null,

        onSuccess: function () {
        },
        onFailure: function () {
        },

        setup: function (url) {
            this.url = url;
            return this;
        },

        run: function (hash, step, object_group, language, language_sef) {
            if (JCommentsObjects.progress == null) {
                JCommentsObjects.progress = new JCommentsProgressbar('#jcomments-progress-container');
            }
            $.ajax({
                type: "POST",
                url: JCommentsObjects.url + (language_sef != null ? '&lang=' + language_sef : ''),
                data: {hash: hash, step: step, object_group: object_group, lang: language},
                dataType: 'json'
            }).done(function (data) {
                    if (data) {
                        var count = data['count'];
                        var total = data['total'];

                        var hash = data['hash'];
                        var step = data['step'];
                        var object_group = data['object_group'];
                        var language = data['lang'];
                        var language_sef = data['lang_sef'];

                        if (data['percent']) {
                            JCommentsObjects.progress.set(data['percent']);
                        }

                        if (count < total) {
                            JCommentsObjects.run(hash, step, object_group, language, language_sef);
                        } else {
                            if (data['message']) {
                                $('#jcomments-modal-message').html(data['message']).show();
                                JCommentsObjects.progress.hide();
                            }

                            if (typeof JCommentsObjects.onSuccess == 'function') {
                                JCommentsObjects.onSuccess();
                            }
                        }
                    } else {
                        if (typeof JCommentsObjects.onFailure == 'function') {
                            JCommentsObjects.onFailure();
                        }
                    }
                });
        }
    };

    window.JCommentsObjects = JCommentsObjects;
})(jQuery);