var filters = {
	robot: {},
	match: {},
	team: ""
}

$(document).ready(function() {
	$(".filtercontainer > input, .filtercontainer > select").on('change input', function(e) {
		var target = $(e.target);
		if(target.siblings(".filterbox").is(":checked")) {
			target.siblings(".filterbox").click();
		}
	});

	$("input.filterbox").click(function(e) {
		var target = $(e.target);
		var container = target.closest("div.filtercontainer");
		var key = container.data("key");
		var type = container.data("type");
		var place = container.data("place");
		var value = "";
		switch(type){
			case "string":
				value = target.siblings("input[type=text]").val();
				break;
			case "select":
				value = target.siblings("select").find(":selected").text();
				break;
			case "boolean":
				value = (target.siblings("select").find(":selected").text() == "Yes")?"true":"false";
				break;
			case "number":
				value = target.siblings("input[type=number]").val();
				break;
		}
		if(target.is(":checked")) {
			if(value === "") {
				target.prop('checked', false);
			} else {
				if(place == "robot") {
					filters.robot[key] = value;
				} else {
					filters.match[key] = value;
				}
			}
		} else {
			if(place == "robot") {
				delete filters.robot[key];
			} else {
				delete filters.match[key];
			}
		}
		get();
	});
});

$("#filterli").click(function() {
	$("#hamburgermenu").slideUp(100);
	$("#Filters").show();
	if($('#TeamSearch').is(':visible')) {
		$("#Filters").css('top','130px');
		$("#container").css('top','130px');
	} else {
		$("#Filters").css('top','80px');
		$("#container").css('top','80px');
	}
});
