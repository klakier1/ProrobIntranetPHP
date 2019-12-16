/// jquery-3.4.1.min.js
function login() {
    event.preventDefault();
    var login = loginForm.elements["email"].value;
    var password = loginForm.elements["password"].value;
    var params = "email=" + login + "&password=" + password;
    var httpRequest = new XMLHttpRequest();
    httpRequest.open("POST", "../public/login")
    httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    httpRequest.onreadystatechange = function () {
        if (this.readyState == 4)
            alert(this.responseText);
    }
    httpRequest.send(params);
}

function jQlogin() {
    
    event.preventDefault();
    var login = loginForm.elements["email"].value;
    var password = loginForm.elements["password"].value;
    var params = "email=" + login + "&password=" + password;
    var httpRequest = new XMLHttpRequest();
    httpRequest.open("POST", "../public/login")
    httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    httpRequest.onreadystatechange = function () {
        if (this.readyState == 4)
            alert(this.responseText);
    }
    httpRequest.send(params);

}