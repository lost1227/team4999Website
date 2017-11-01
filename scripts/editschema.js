$("document").ready(function() {
  $("button.addSelectOption").click(function(e) {
    var table = $(e.target).closest("table.selectoptions");
    var lastrow = table.find("input:last");
    //<input type="text" name="matchdata[anotherUniqueKey][values][3]" data-index="3" value="Option 4">
    var newrow = document.createElement("input");
    newrow.type = "text";
    newrow.name = table.data("name") + "[" + (lastrow.data("index") + 1) +"]";
    newrow.setAttribute("data-index", lastrow.data("index") + 1);
    newrow.className = "f_select";
    console.log(table.data("name"));
    console.log(newrow);
    var tr = document.createElement("tr");
    var td = document.createElement("td");
    td.appendChild(newrow);
    tr.appendChild(td);
    table.find("button").closest("tr").before(tr);
    return false;
  });
  $("#mainf").submit(function(e) {
    var selects = $("#mainf").find("input.f_select");
    var keys = $("#mainf").find("input.f_key");
    var names = $("#mainf").find("input.f_name");

    for(var i = 0; i < selects.length; i++) {
      if(selects[i].value == "") {
        selects[i].remove();
      }
    }

    var confirmdel = false;
    for(var i = 0; i < keys.length; i++) {
      if(keys[i].value == "") {
        if(confirmdel || window.confirm("Confirm deletion of key(s)?")){
          confirmdel = true;
          keys[i].closest("tr").remove();
        } else {
          return false;
        }
      }
    }

    for(var i = 0; i < names.length; i++) {
      if(names[i].value == "") {
        window.alert("No display name may be blank!");
        return false;
      }
    }
    return true;
  })
});
