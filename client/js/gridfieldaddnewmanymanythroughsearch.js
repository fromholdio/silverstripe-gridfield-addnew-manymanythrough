(function($) {
    $.entwine("ss", function($) {
        /**
         * GridFieldAddManyManyThroughSearchButton
         */

        $(".add-new-manymanythrough-search-dialog").entwine({
            loadDialog: function(deferred) {
                var dialog = this.addClass("loading").children(".ui-dialog-content").empty();

                deferred.done(function(data) {
                    dialog.html(data).parent().removeClass("loading");
                });
            }
        });

        $(".ss-gridfield .add-new-manymanythrough-search").entwine({
            onclick: function() {
                var dialog = $("<div></div>").appendTo("body").dialog({
                    modal: true,
                    resizable: false,
                    width: 500,
                    height: 600,
                    close: function() {
                        $(this).dialog("destroy").remove();
                    }
                });

                dialog.parent().addClass("add-new-manymanythrough-search-dialog").loadDialog(
                    $.get(this.prop("href"))
                );
                dialog.data("grid", this.closest(".ss-gridfield"));

                return false;
            }
        });

        $(".add-new-manymanythrough-search-dialog .add-new-manymanythrough-search-form").entwine({
            onsubmit: function() {
                this.closest(".add-new-manymanythrough-search-dialog").loadDialog($.get(
                    this.prop("action"), this.serialize()
                ));
                return false;
            }
        });

        // Allow the list item to be clickable as well as the anchor
        $('.add-new-manymanythrough-search-dialog .add-new-manymanythrough-search-items .list-group-item-action').entwine({
            onclick: function() {
                if (this.children('a').length > 0) {
                    this.children('a').first().trigger('click');
                }
            }
        });

        $(".add-new-manymanythrough-search-dialog .add-new-manymanythrough-search-items a").entwine({
            onclick: function() {
                var link = this.closest(".add-new-manymanythrough-search-items").data("add-link");
                var id   = this.data("id");

                var dialog = this.closest(".add-new-manymanythrough-search-dialog")
                    .addClass("loading")
                    .children(".ui-dialog-content")
                    .empty();

                $.post(link, { id: id }, function()
                {
                    dialog.data("grid").reload();

                    var gridsToReload = dialog.data('gridsToReload');
                    if (typeof gridsToReload !== 'undefined' && gridsToReload !== null) {
                        $.each(gridsToReload, function(i, gridName) {
                            $('.ss-gridfield[data-name="' + gridName + '"]').reload();
                        });
                    }

                    dialog.dialog("close");
                });

                return false;
            }
        });

        $(".add-new-manymanythrough-search-dialog .add-new-manymanythrough-search-pagination a").entwine({
            onclick: function() {
                this.closest(".add-new-manymanythrough-search-dialog").loadDialog($.get(
                    this.prop("href")
                ));
                return false;
            }
        });
    });
})(jQuery);
