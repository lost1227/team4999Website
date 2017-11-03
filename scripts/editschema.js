$("document").ready(function() {
  $("button.addSelectOption").click(function(e) {
    var table = $(e.target).closest("table.selectoptions");
    //<input type="text" name="matchdata[anotherUniqueKey][values][3]" data-index="3" value="Option 4">
    var newrow = document.createElement("input");
    newrow.type = "text";
    newrow.name = table.data("name") + "[]";
    newrow.className = "f_select";
    var tr = document.createElement("tr");
    var td = document.createElement("td");
    td.appendChild(newrow);
    tr.appendChild(td);
    table.find("button").closest("tr").before(tr);
    return false;
  });
  $("#mainf").submit(function(e) {
    var valid = true;

    $("#mainf").find("table.hiddenselectoptions").remove();

    var cKeys = [];

    $("#mainf").find("input.f_select").each(function() {
      if(this.value == "") {
        $(this).remove();
      }
    });

    var confirmdel = false;
    $("#mainf").find("input.f_key").each(function() {
      if(this.value == "") {
        if(confirmdel || window.confirm("Confirm deletion of key(s)?")){
          confirmdel = true;
          $(this).closest("tr").remove();
        } else {
          valid = false;
          return false;
        }
      } else if (cKeys.includes(this.value)) {
        window.alert("All keys must be unique");
        valid = false;
        return false;
      } else {
        cKeys.push(this.value);
      }
    });
    if(!valid) { return false; }

    $("#mainf").find("input.f_name").each(function() {
      if(this.value == "") {
        window.alert("No display name may be blank!");
        valid = false;
        return false;
      }
    })

    $("select.datatselector").each(function() {
      console.log(this);
      console.log($(this).closest("tr"));
      if($(this).val() == "select" && $(this).closest("tr").find("input.f_select").length <= 0) {
        window.alert("All drop downs must have at least one option!");
        valid = false;
        return false;
      }
    })
    if(!valid) { return false; }
    return true;
  });
  $("select.datatselector").change(function(e) {
    var target = $(e.target);
    var t = target.closest("tr").find("table");
    if(target.val() == "select") {
      t.removeClass("hiddenselectoptions");
      t.addClass("selectoptions");
    } else {
      t.removeClass("selectoptions");
      t.addClass("hiddenselectoptions");
    }
  });
});
