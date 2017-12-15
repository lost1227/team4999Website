$(document).ready(function() {
  $("button.accordionbutton").click(function(e) {
    $(e.target).closest("div.accordion").find("div.accordioncontent").slideToggle();
    return false;
  });

  $("#mainf").submit(function(e) {
    $(e.target).find("input[type=checkbox]").each(function() {
      var me = $(this);
      if(me.prop("checked")) {
        me.val("true");
      } else {
        me.val("false");
        me.prop("checked", "true");
      }
    })
  });
});
