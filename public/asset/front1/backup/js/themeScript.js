$('.togglePsw').click(function(){
    $(this).toggleClass('active');
    var input = $(this).parent('.pswtoggle').find('input');
    if (input.attr('type') == 'password') {
        input.attr('type', 'text');
    } else {
        input.attr('type', 'password');
    }
});