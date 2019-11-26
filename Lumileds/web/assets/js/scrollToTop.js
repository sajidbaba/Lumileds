$(document).ready(function(){
    let scrollToTopBtn = $('#scroll-to-top');

    $(window).scroll(function () {
        if ($(this).scrollTop() > 50) {
            scrollToTopBtn.fadeIn();
        } else {
            scrollToTopBtn.fadeOut();
        }
    });

    scrollToTopBtn.click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        return false;
    });
});
