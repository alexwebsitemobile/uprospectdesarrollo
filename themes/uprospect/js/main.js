$(function () {
    $('#toggle-menu').click(function (evt) {
        evt.stopPropagation();
        $('#card-menu').toggleClass("open-menu");
    });


    $(document).click(function () {
        $('#card-menu').removeClass('open-menu');
    });
});