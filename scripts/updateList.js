function clearFilters(){
	filter.enabled = false;
	get();
}
var filter = {
	enabled : false,
	filterData : function () {
		var data = '';
		for (index in this) {
			if (!(index == 'enabled' || index == 'filterData')) {
				if (index) {
					data += index + '=' + this[index];
				} else {
					data += index + '=%';
				}
				data += '&';
			}
		}
		return data.substring(0, data.length-1);
	}
}

function get() {
				if (filter.enabled) {
					request.open("POST","query.php",true);
					request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					request.send(filter.filterData());
				} else {
					request.open("GET","query.php",true);
					request.send();
				}
}

var request = new XMLHttpRequest();
request.onreadystatechange = function() {
	if (this.readyState == this.DONE && this.status == 200){
		document.getElementById("container").innerHTML = this.responseText;
	}
};
request.open("GET","query.php",true);
request.send();
var loop = window.setInterval(function() {get();}, 5000);