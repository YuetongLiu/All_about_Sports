$(document).ready(function() {
    $("#header").hide();
    $("#container").hide();

    setTimeout(() => {
        $("#introduction").fadeIn();
        setTimeout(() => {
            $("#introduction").fadeOut();
            $("#header").fadeIn();
            $("#container").fadeIn();
        }, 2000);
    }, 100);
})