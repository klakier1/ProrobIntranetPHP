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
        window.location.href = "index.php"
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