var request = new XMLHttpRequest();
			request.onreadystatechange = function() {
				if (this.readyState == this.DONE && this.status == 200){
					document.getElementById("container").innerHTML = this.responseText;
				}
			};
			request.open("GET","/query.php",true);
			request.send();
			var loop = window.setInterval(function() {
				request.open("GET","/query.php",true);
				request.send();
			}, 5000);