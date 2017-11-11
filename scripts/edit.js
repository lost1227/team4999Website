$(document).ready(function() {
  $("button.accordionbutton").click(function(e) {
    $(e.target).closest("div.accordion").find("div.accordioncontent").slideToggle();
    return false;
  });
});
