//const moment = require("moment");

const months = ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec",
    "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"
];
var today = new Date();
var range = {
    start: moment().startOf('month').format('YYYY-MM-DD'),
    end: moment().endOf('month').format('YYYY-MM-DD')
}
var currentUser = null; //dane zalogowanego uzytkownika
var usersList = null; //dane wszystkich użytkowników - tylko dla admina

var debug = true;

const cookies = $.cookie();

//initial values
$("#inputPeriodFrom").val(moment().startOf('month').format('YYYY-MM-DD'));
$("#inputPeriodTo").val(moment().endOf('month').format('YYYY-MM-DD'));
$("#inputMonth").val(moment().format('YYYY-MM'));
$("#inputWeek").val(moment().format('YYYY-[W]WW'))

$(document).ready(function() {

    $.ajax({
        url: '../public/api/user/', //pobierz wszystkich uzytkownikow
        method: "get", //typ połączenia, domyślnie get
        contentType: 'application/x-www-form-urlencoded', //gdy wysyłamy dane czasami chcemy ustawić ich typ
        dataType: "json"
    }).done(function(response) {
        if (response != null) {
            if (response.error == false) {
                if (response.data_length > 0) {
                    var usersWorkingTime = response.data;

                    //SET CURRENT USER
                    currentUser = response.data.find(p => p.id == cookies.id);
                    $("#userName").text(`${currentUser.first_name} ${currentUser.last_name}`);
                    console.log(response);

                    //POPULATE SPINNER
                    let spinner = $("#userSelectWorkTime");
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

    }).fail(function(arg) {
        if (typeof arg.responseJSON !== "undefined")
            $('.error').html(arg.responseJSON.message);
        else
            $('.error').html("Brak odpowiedzi serwera");
    });

    $("#userGetWorkTime").click(function(event) {
        $('.error').empty();
        $("#tableContainer").empty();

        let selectorUser = $("#userSelectWorkTime");
        let id = selectorUser.val();

        getPeriodOfTime(range);

        if (debug) {
            console.log("selected id: " + id)
            console.log(`../public/api/timesheet/user_id/${id}/${range.start}/${range.end}`)
        }
        $.ajax({
            url: `../public/api/timesheet/user_id/${id}/${range.start}/${range.end}`, //pobierz wszystkich uzytkownikow
            method: "get", //typ połączenia, domyślnie get
            contentType: 'application/x-www-form-urlencoded' //gdy wysyłamy dane czasami chcemy ustawić ich typ
        }).done(function(response) {
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
        }).fail(function(arg) {
            if (typeof arg.responseJSON !== "undefined")
                $('.error').html(arg.responseJSON.message);
            else
                $('.error').html("Brak odpowiedzi serwera");
        });
    })

    $("input[name='timeRange']").change(function(event) {
        if ($("#timeRangeWeek").prop('checked') == true) {
            $("#inputWeek").prop('disabled', false);
        } else {
            $("#inputWeek").prop('disabled', true);
        }

        if ($("#timeRangeMonth").prop('checked') == true) {
            $("#inputMonth").prop('disabled', false);
        } else {
            $("#inputMonth").prop('disabled', true);
        }

        if ($("#timeRangePeriod").prop('checked') == true) {
            $("#inputPeriodFrom").prop('disabled', false);
            $("#inputPeriodTo").prop('disabled', false);
        } else {
            $("#inputPeriodFrom").prop('disabled', true);
            $("#inputPeriodTo").prop('disabled', true);
        }
    })

});

function getPeriodOfTime(range) {
    var selected = $("input[type='radio'][name='timeRange']:checked").val();
    switch (selected) {
        case "week":
            range.start = moment($('#inputWeek').val()).startOf('week').format('YYYY-MM-DD');
            range.end = moment($('#inputWeek').val()).endOf('week').format('YYYY-MM-DD');
            break;
        case "month":
            range.start = moment($('#inputMonth').val()).startOf('month').format('YYYY-MM-DD');
            range.end = moment($('#inputMonth').val()).endOf('month').format('YYYY-MM-DD');
            break;
        case "period":
            range.start = $('#inputPeriodFrom').val();
            range.end = $('#inputPeriodTo').val();
        default:
            break;
    }
}

function formatDate(date) {
    var year = date.getFullYear();
    var month = (date.getMonth() + 1).toString();
    var day = date.getDate().toString();

    if (month.length < 2)
        month = '0' + month;
    if (day.length < 2)
        day = '0' + day;

    return [year, month, day].join('-');
}

// function populateYearSelector(id) {
//     let selectYear = $(id);
//     selectYear.empty();
//     for (let year = today.getFullYear() - 5; year < today.getFullYear() + 5; year++) {
//         let optionYear = $("<option></option>");
//         optionYear.attr("value", year);
//         optionYear.text(year);
//         if (year == today.getFullYear())
//             optionYear.prop('selected', true);
//         selectYear.append(optionYear);
//     }
// }

// function populateMonthSelector(id) {
//     let selectMonth = $(id);
//     selectMonth.empty();
//     for (let index = 0; index < months.length; index++) {
//         let optionMonth = $("<option></option>");
//         optionMonth.attr("value", index);
//         optionMonth.text(months[index]);
//         if (today.getMonth() == index)
//             optionMonth.prop('selected', true);
//         selectMonth.append(optionMonth);
//     }
// }

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

function generateMonthPicker() {
    //     <div class="col-md-3 offset-md-1">
    //     <span class="w-100 h-100 align-middle">Wybierz miesiąc</span>
    // </div>
    // let divLabel = $("<div></div>").addClass("col-md-3 offset-md-1");
    // let spanLabel = $("<span></span>").addClass("w-100 h-100 align-middle");
    // spanLabel.text("Wybierz miesiąc");
    // divLabel.append(spanLabel);


    // <div class="col-md-2 w-100 pr-md-1"> <!-- paddingRight-medium-1 ; dzieki temu jest mniejszy odstep miedzy kolumnami -->
    //     <select id="timesheetMonthSelect" class="w-100 h-100">
    //         <option value="01">Styczeń</option>
    //         <option value="02">Luty</option>
    //         <option value="03">Marzec</option>
    //         <option value="04">Kwiecień</option>
    //         <option value="05">Maj</option>
    //         <option value="06">Czerwiec</option>
    //         <option value="07">Lipiec</option>
    //         <option value="08">Sierpień</option>
    //         <option value="09">Wrzesień</option>
    //         <option value="10">Październik</option>
    //         <option value="11">Listopad</option>
    //         <option value="12">Grudzień</option>
    //     </select>
    // </div>
    let divMonth = $("<div></div>").addClass("col-md-2 w-100 pr-md-1");
    let selectMonth = $("<select></select>").addClass("w-100 h-100");
    selectMonth.attr('id', "timesheetMonthSelect");
    for (let index = 0; index < months.length; index++) {
        let optionMonth = $("<option></option>");
        optionMonth.attr("value", index + 1);
        optionMonth.text(months[index]);
        if (today.getMonth() == index)
            optionMonth.prop('selected', true);
        selectMonth.append(optionMonth);
    }
    divMonth.append(selectMonth);


    //  <div class="col-md-2 w-100 pl-md-1"> <!-- paddingLeft-medium-1 -->
    //     <select id="timesheetYear" class="w-100 h-100">
    //         <option value="2016">2016</option>
    //         <option value="2017">2017</option>
    //         <option value="2018">2018</option>
    //         <option value="2019">2019</option>
    //         <option value="2020">2020</option>
    //         <option value="2021">2021</option>
    //         <option value="2022">2022</option>
    //         <option value="2023">2023</option>
    //         <option value="2024">2024</option>
    //     </select>
    // </div>
    let divYear = $("<div></div>").addClass("col-md-2 w-100 pl-md-1");
    let selectYear = $("<select></select>").addClass("w-100 h-100");
    selectYear.attr('id', "timesheetYearSelect");
    for (let year = today.getFullYear() - 5; year < today.getFullYear() + 5; year++) {
        let optionYear = $("<option></option>");
        optionYear.attr("value", year);
        optionYear.text(year);
        if (year == today.getFullYear())
            optionYear.prop('selected', true);
        selectYear.append(optionYear);
    }
    divYear.append(selectYear);


    // <div class="col-md-3">
    //     <button class="w-100 h-100" id="userGetWorkTimeChangeMonth">Zmień</button>
    // </div>
    // let divButtom = $("<div></div>").addClass("col-md-3");
    // let buttom = $("<button></button>").addClass("w-100 h-100");
    // buttom.attr('id', "userGetWorkTimeChangeMonth");
    // buttom.text("Zmień");
    // divButtom.append(buttom);

    // append to container
    //$("#monthPickerContainer").append([divLabel, divMonth, divYear, divButtom]);
    $("#divUserSelectorWorkTime").after([divMonth, divYear]);
}