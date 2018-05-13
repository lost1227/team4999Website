function addAccordionListener(e) {
  e.find("button.accordionbutton").click(function(e) {
    $(e.target).closest("div.accordion").find("div.accordioncontent").slideToggle();
    return false;
  });
  e.find("button.deletebutton").click(function(e) {
    var text = $(e.target).siblings("button.accordionbutton").text()
    if(text == "") {
      if(!window.confirm("Delete element?")) {
        return false;
      }
    } else {
      if(!window.confirm("Delete "+text+"?")) {
        return false;
      }
    }
    $(e.target).closest("div.accordion").remove();
    return false;
  })
}
$(document).ready(function() {

  addAccordionListener($("div.accordion"));

  $("#addRobot").click(function(e) {
    addRobotRow();
    return false;
  });

  $("#addMatch").click(function(e) {
    addMatchRow();
    return false;
  });

  $("#mainf").submit(function(e) {
    $(e.target).find("input.optionboolean").each(function() {
      var me = $(this);
      if(me.prop("checked")) {
        me.val("true");
      } else {
        me.val("false");
        me.prop("checked", "true");
      }
    })
  });

  $("img.gallery").click(function(e) {
    var pic = $(e.target);
    var check = pic.siblings("input.deletePix");
    if(check.prop("checked")) {
      check.prop("checked", false);
      pic.removeClass("delete");
    } else {
      check.prop("checked", true);
      pic.addClass("delete");
    }
    return false;
  });

  $("#delbut").click(function(e) {
    $("#delf").submit();
    return false;
  });
});
function getId(prefix, callback) {
  var request = new XMLHttpRequest();
  var id;
  request.onreadystatechange = function() {
      if(this.readyState == 4 && this.status == 200) {
        callback(this.responseText);
      }
  }
  request.open("GET","newId.php?prefix="+prefix, true);
  request.send();
}

function addRobotRow() {
  getId("rb_",function(id){
    var row = $($.parseHTML(getRobotRow(id)));
    addAccordionListener(row);
    $("#addRobot").before(row);
    row.find("button.accordionbutton").click();
  });
}

function addMatchRow() {
  getId("mt_",function(id){
    var row = ($($.parseHTML(getMatchRow(id))));
    addAccordionListener(row);
    $("#addMatch").before(row);
    row.find("button.accordionbutton").click();
  })
}
