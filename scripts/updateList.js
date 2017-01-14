function get() {
				if (filter) {
					request.open("POST","/query.php",true);
					request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					request.send(filterData);
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
			filter=false;
			var loop = window.setInterval(get(), 5000);