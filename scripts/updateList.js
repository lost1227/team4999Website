function clearFilters(){
	filter.enabled = false;
	get();
}
function createFilter() {
	this.enabled = false,
	this.filterData = function() {
		var data;
		for (value in this) {
			if (value) {
				data += this[value] + '=' + value;
			} else {
				data += this[value] + '=%';
			}
			data += '&';
		}
		return data.substring(0, data.length-1);
	}
}
filter = new createFilter();

function get() {
				if (filter.enabled) {
					request.open("POST","/query.php",true);
					request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					request.send(filter.filterData());
				} else {
					request.open("GET","/query.php",true);
					request.send();
				}
}
var request = new XMLHttpRequest();
			request.onreadystatechange = function() {
				if (this.readyState == this.DONE && this.status == 200){
					document.getElementById("container").innerHTML = this.responseText;
				}
			};
			request.open("GET","/query.php",true);
			request.send();
			var loop = window.setInterval(get(), 5000);