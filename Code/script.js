$(document).ready(function() {

    login_button = document.querySelector("#login-change-button");
    login_button.addEventListener("click", function() {
        $("#login").hide();
        $("#signup").show();
    });

    signup_button = document.querySelector("#signup-change-button");
    signup_button.addEventListener("click", function() {
        $("#signup").hide();
        $("#login").show();
    });

    subscribe_player_button = document.querySelector("#subscribe-player-button");
    subscribe_player_button.addEventListener("click", function() {
        $("#subscribe-player").hide();
        $("#subscribe-team").show();
    });

    subscribe_team_button = document.querySelector("#subscribe-team-button");
    subscribe_team_button.addEventListener("click", function() {
        $("#subscribe-team").hide();
        $("#subscribe-player").show();
    });
})