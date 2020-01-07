var currentUser = null; //dane zalogowanego uzytkownika
var usersList = null; //dane wszystkich użytkowników - tylko dla admina

var debug = true;

const cookies = $.cookie();

$(document).ready(function () {

    $.ajax({
        url: '../public/api/user/', //pobierz wszystkich uzytkownikow
        method: "get", //typ połączenia, domyślnie get
        contentType: 'application/x-www-form-urlencoded', //gdy wysyłamy dane czasami chcemy ustawić ich typ
        dataType: "json"
    }).done(function (response) {
        if (response != null) {
            if (response.error == false) {
                if (response.data_length > 0) {
                    var usersWorkingTime = response.data;

                    //SET CURRENT USER
                    currentUser = response.data.find(p => p.id == cookies.id);
                    $("#userName").text(`${currentUser.first_name} ${currentUser.last_name}`);
                    console.log(response);

                    //INFLATE SPINNER
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

                        let item = $("<option></option>");
                        item.attr("value", `${currentUser.id}`);
                        item.text(`${currentUser.first_name} ${currentUser.last_name}`);
                        spinner.append(item);
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
    });

    $("#userGetWorkTime").click(function (event) {
        $('.error').empty();
        $("#tableContainer").empty();

        let selector = $("#userSelectorWorkTime");
        let id = selector.val();
        if (debug)
            console.log("selected id: " + id)
        $.ajax({
            url: '../public/api/timesheet/user_id/' + id, //pobierz wszystkich uzytkownikow
            method: "get", //typ połączenia, domyślnie get
            contentType: 'application/x-www-form-urlencoded' //gdy wysyłamy dane czasami chcemy ustawić ich typ
        }).done(function (response) {
            if (response != null) {
                if (response.error == false) {
                    if (response.data_length > 0) {

                        if (debug)
                            console.log(response.data);
                        $("#tableContainer").empty();
                        generateTableHeader();
                        response.data.forEach(tsr => {
                            generateTimesheetRowHtml(tsr)
                        });

                    } else {
                        $('.error').html("Odczytano zero rekordów"); //nie opcji żeby się zdarzyło..
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
        });
    })

});



function setHeader() {
    //$("header").html(`Witaj!`)
}

function generateTimesheetRowHtml(tsr) {
    //     <tr>
    //     <th scope="row">1</th>
    //     <td>Mark</td>
    //     <td>Otto</td>
    //     <td>@mdo</td>
    //   </tr>

    // id: 80
    // user_id: 10
    // date: "2019-12-06"
    // from: "11:00:00"
    // to: "15:00:00"
    // customer_break: "00:15:00"
    // statutory_break: "00:19:00"
    // comments: "hjdijd"
    // project_id: 0
    // company_id: 0
    // status: false
    // created_at: "2019-12-06 19:42:30.941"
    // updated_at: "2019-12-06 19:42:30.941"
    // project: "Z19 Rüsselsheim"
    let row = $("<tr></tr>");
    row.append($("<td></td>").text(tsr.id));
    row.append($("<td></td>").text(tsr.date));
    row.append($("<td></td>").text(tsr.from.replace(':00', '')));
    row.append($("<td></td>").text(tsr.to.replace(':00', '')));
    row.append($("<td></td>").text(tsr.customer_break.replace(':00', '')));
    row.append($("<td></td>").text(tsr.statutory_break.replace(':00', '')));
    row.append($("<td></td>").text(tsr.project));
    row.append($("<td></td>").text(tsr.comments));

    $("#timesheet").append(row);
}

function generateTableHeader() {
    //     <table class="table table-responsive table-dark my-3" style="font-size: 14px; white-space: nowrap;">
    //     <thead>
    //         <tr>
    //             <th scope="col" style="width: 40px">#</th>
    //             <th scope="col" style="width: 140px">Data</th>
    //             <th scope="col" style="width: 40px">Od</th>
    //             <th scope="col" style="width: 40px">Do</th>
    //             <th scope="col" style="width: 40px">Przer. klienta</th>
    //             <th scope="col" style="width: 40px">Przer. ustawowa</th>
    //             <th scope="col" style="width: 200px">Projekt</th>
    //             <th scope="col" style="width: 100%">Komentarz</th>
    //         </tr>
    //     </thead>
    //     <tbody id="timesheet">

    //     </tbody>
    // </table>

    let table = $("<table></table>")
    table.addClass("table table-responsive table-dark my-3");
    table.css("font-size", "14px");
    table.css("white-space", "nowrap");

    let tHead = $("<thead></thead>");

    let colWidth = ['40px', '140px', '40px', '40px', '40px', '40px', '200px', '100%'];
    let colName = ['#', 'Data', 'Od', 'Do', 'Przer. klienta', 'Przer. ustawowa', 'Projekt', 'Komentarz']

    let tr = $("<tr></tr>");
    let colHead = Array.apply(null, Array(8))
    colHead.forEach((element, index) => {
        element = $("<th></th>");
        element.attr("scope", "col");
        element.css("width", colWidth[index]);
        element.text(colName[index]);
        tr.append(element);
    });

    let tBody = $("<tbody></tbody>");
    tBody.attr("id", "timesheet");

    tHead.append(tr);
    table.append(tHead);
    table.append(tBody);

    $("#tableContainer").append(table);

}
