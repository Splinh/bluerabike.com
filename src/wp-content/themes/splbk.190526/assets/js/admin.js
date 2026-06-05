jQuery(function($) {
  $("table.fixed").removeClass("fixed");
  const create_user = $("#createuser");
  create_user.find("#send_user_notification").removeAttr("checked").attr("disabled", true);
  let selectedTemplate = $("#page_template");
  let $editorWrapper = $("#postdivrich");
  function toggleEditor() {
    if (selectedTemplate.val() === "templates/template-page-home.php") {
      $editorWrapper.hide();
    } else {
      $editorWrapper.show();
      setTimeout(function() {
        $(window).trigger("resize");
      }, 10);
    }
  }
  toggleEditor();
  selectedTemplate.on("change", function() {
    toggleEditor();
  });
  $(document).on("click", ".notice-dismiss", function(e) {
    var _a;
    (_a = $(this).closest(".notice.is-dismissible")) == null ? void 0 : _a.fadeOutAndRemove(500);
  });
  $.fn.fadeOutAndRemove = function(speed) {
    return this.fadeOut(speed, function() {
      $(this).remove();
    });
  };
});
//# sourceMappingURL=admin.js.map
