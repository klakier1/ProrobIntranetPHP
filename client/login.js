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
    jquery
}

function jQlogin() {
    event.preventDefault();
    $('.error').html('');
    let requestData = $('#loginForm').serializeArray();

    $.ajax({
        url: "../public/login", //wymagane, gdzie się łączymy
        method: "post", //typ połączenia, domyślnie get
        contentType: 'application/x-www-form-urlencoded', //gdy wysyłamy dane czasami chcemy ustawić ich typ
        dataType: 'json', //typ danych jakich oczekujemy w odpowiedzi
        data: requestData
    }
    ).done(function (arg) {
        alert(arg);
    }
    ).fail(function (arg) {
        if (typeof arg.responseJSON !== "undefined")
            $('.error').html(arg.responseJSON.message);
        else
            $('.error').html("Brak odpowiedzi serwera");
    }
    );
    return false;
}