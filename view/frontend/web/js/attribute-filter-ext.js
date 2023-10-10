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
        });
    };
});
