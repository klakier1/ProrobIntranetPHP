var currentUser = null; //dane zalogowanego uzytkownika
var usersList = null; //dane wszystkich użytkowników - tylko dla admina
const cookies = $.cookie();

$(document).ready(function () {

    $.ajax({
        url: '../public/api/user/id/' + cookies.id, //wymagane, gdzie się łączymy
        method: "get", //typ połączenia, domyślnie get
        contentType: 'application/x-www-form-urlencoded', //gdy wysyłamy dane czasami chcemy ustawić ich typ
    }).done(function (response) {
        if (response != null) {
            if (response.error == false) {
                if (response.data_length > 0) {
                    currentUser = response.data[0];
                    $("#userName").text(`${currentUser.first_name} ${currentUser.last_name}`);
                    console.log(response);
                    //alert(`Witaj ${user.first_name} ${user.last_name}`);
                } else {
                    $('.error').html("Brak danych użytkownika"); //nie opcji żeby się zdarzyło..
                }
            } else {
                $('.error').html(response.message);
            }
        } else {
            $('.error').html("Brak odpowiedzi serwera");
        }

    }).fail(function (arg) {
        if (typeof arg.responseJSON !== "undefined")
            $('.error').html(arg.responseJSON.message);
        else
            $('.error').html("Brak odpowiedzi serwera");
    }
    );

    $.ajax({
        url: '../public/api/user/', //pobierz wszystkich uzytkownikow
        method: "get", //typ połączenia, domyślnie get
        contentType: 'application/x-www-form-urlencoded', //gdy wysyłamy dane czasami chcemy ustawić ich typ
    }).done(function (response) {
        if (response != null) {
            if (response.error == false) {
                if (response.data_length > 0) {
                    var usersWorkingTime = response.data;
                    let spinner = $("#userSelectorWorkTime");
                    spinner.empty();

                    if (cookies.role == "admin") {
                        for (let user of usersWorkingTime) {
                            let item = $("<option></option>");
                            item.attr("value", `${user.id}`);
                            item.text(`${user.first_name} ${user.last_name}`);
                            spinner.append(item);
                        }
                    } else if (cookies.role == "employee") {

                        // TODO
                        // usersWorkingTime.find()
                    }


                } else {
                    $('.error').html("Brak danych użytkowników"); //nie opcji żeby się zdarzyło..
                }
            } else {
                $('.error').html(response.message);
            }
        } else {
            $('.error').html("Brak odpowiedzi serwera");
        }

    }).fail(function (arg) {
        if (typeof arg.responseJSON !== "undefined")
            $('.error').html(arg.responseJSON.message);
        else
            $('.error').html("Brak odpowiedzi serwera");
    }
    );

});

function setHeader() {
    //$("header").html(`Witaj!`)
}