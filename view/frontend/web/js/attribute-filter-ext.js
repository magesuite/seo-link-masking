/**
 * Provide alternative template path for attribute filters
 */

define([], function () {
    "use strict";

    return function(Filters) {
        return Filters.extend({
            defaults: {
                template: "MageSuite_SeoLinkMasking/attribute-filter"
            },

            /**
             * Items loaded via ajax carry plain urls, the template expects post data
             */
            addItemId: function (item) {
                if (this.isLinkMaskingEnabled) {
                    item.url = this.getMaskedUrl(item.url);
                }

                return this._super(item);
            },

            getMaskedUrl: function (url) {
                if (typeof url === 'string' && url.charAt(0) === '{') {
                    this.postDataTemplate = this.postDataTemplate || url;

                    return url;
                }

                if (!this.postDataTemplate) {
                    return url;
                }

                var postData = JSON.parse(this.postDataTemplate);

                postData.data.url = url;

                return JSON.stringify(postData);
            }
        });
    };
});
