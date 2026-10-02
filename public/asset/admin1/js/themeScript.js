'use strict';
;(function ( $ ) {
    // Collaspe Aside
    $('.navToggleBtn').on('click',function(){
        var getlc=localStorage.getItem('cookContent');
        if(getlc ==null){
            localStorage.cookContent ='yes';
            $('body').toggleClass('aside-minimize');
            $(this).toggleClass('active');
        }else{
            localStorage.removeItem("cookContent"); 
            $('body').toggleClass('aside-minimize');
            $(this).toggleClass('active'); 
        }
    });

    $( document ).ready(function() {
        var getlc=localStorage.getItem('cookContent');
        if(getlc=='yes'){
            $('body').addClass('aside-minimize');
            $('.navToggleBtn').addClass('active');
        }else{
            $('.aside-minimize .collapse').collapse('hide');
        }
    });
    $(window).resize(function() {
        if ($(window).outerWidth() >= 1200 && $(window).outerWidth() <= 1440) {
            $('body').addClass('aside-minimize');
            $('.navToggleBtn').addClass('active'); 
        } 
    });
    
    // Page loader

    $(window).on('load', function () {
        $("#pageLoad").hide();
    });
    // Outside Click hide collaspe
    $(document).click(function(e) {
        if (!$(e.target).is('.subnav')) {
            $('.aside-minimize .collapse').collapse('hide');	    
        }
    });

    // Div show hide usign checkbox
    $("#compprice").click(function() {
        if($(this).is(":checked")) {
            $(".comprativePriceField").show();
        } else {
            $(".comprativePriceField").hide();
        }
    });

    // Clone Section
    $('#appendField').click(function(){
        var cloneItem = "<div class='cstRangFields mab-15'><div class='input-group binputGroup'><input type='text' class='form-control noField' placeholder='' /><span class='input-group-text btn-outline-secondary'>USD</span></div><div class='sign'><span>-</span></div><div class='input-group binputGroup'><input type='text' class='form-control noField' placeholder='' /><span class='input-group-text btn-outline-secondary'>USD</span></div><div class='sign'><span>X</span></div><div class='input-group binputGroup'><input type='text' class='form-control noField' placeholder='' /><span class='input-group-text btn-outline-secondary'>multiply</span></div><div class='input-group binputGroup comprativePriceField'><input type='text' class='form-control noField' placeholder='' /><span class='input-group-text btn-outline-secondary'>multiply</span></div><span class='removeBtn'></span></div>";
        $(cloneItem).appendTo('#appendCustomRangeField');
        //$('.removeBtn').parents('cstRangFields').remove();
        $("body").on("click", ".removeBtn", function () {
            $(this).closest(".cstRangFields").remove();
        });
    });
    // Perfect Scrollbar
    $('[data-scroll="true"]').each(function(){
        const ps = new PerfectScrollbar($(this)[0]);
    });
    // Form Control Model
    // Textarea text counter
    var textmax = $('[data-counter="true"]').attr('data-value');

    //var text_max = 100;
    $('#count_message').html('0 / ' + textmax );

    $('[data-counter="true"] textarea').keyup(function() {
        var text_length = $('[data-counter="true"] textarea').val().length;
        var text_remaining = textmax - text_length;
        
        $('#count_message').html(text_length + ' / ' + textmax);
    });

    // Sign In disabled
    $(".sign-panel").click(function(){
        $(".sign-text").show();
      });
      
      
    $('.sign-panel').on('click',function(){
        $('.sign-panel').attr('disabled', 'disabled');
    });
    
    // Plus and Minus
    var buttonPlus  = $(".plus");
    var buttonMinus = $(".minus");
    var inputValue = $('.quantity').val();
    if(inputValue == 1) {
        $(buttonMinus).prop("disabled", true);
    } else if (inputValue > 1) {
        $(buttonMinus).prop("disabled", false);
    }
    var incrementPlus = buttonPlus.click(function() {
        var $n = $(this)
        .parent(".qty-container").find(".quantity");
        $n.val(Number($n.val())+1 );
        var inpamount = Number($n.val());
        if (inpamount >1) {
            $(this).parent(".qty-container").find(".minus").prop("disabled", false);;
        }
    });

    var incrementMinus = buttonMinus.click(function() {
        var $n = $(this)
        .parent(".qty-container")
        .find(".quantity");
        var amount = Number($n.val());
        if (amount > 1) {
            $n.val(amount-1);
        } else if (amount = 1) {
            $(this).parent(".qty-container").find(".minus").prop("disabled", true);;
        }
    });

    // History Show and Hide
    $("#commissions-tab").click(function(){
        $(".history-hide").hide();
    });
    $("#referrals-tab, #promotion-tab").click(function(){
        $(".history-hide").show();
    });

    // History Show and Hide
    $("#remark-view").click(function(){
        $(".editeTextarea").toggle();
    });
    // Sidenav Tooltip

    // Dropdown Data close outside
    $('[data-auto-close="outside"]').on('hide.bs.dropdown', function (e) {
        e.stopPropagation();
    });
    // Dropdown Clickable
    $('.dropdownActive li').click(function(){
        $(this).toggleClass('active');
    });
    
    // Modal Expend
    $("[data-expend='true']").click(function(){
        $(this).parents('.modal-dialog').toggleClass('modal-fullscreen');
        $(this).toggleClass('active');
    });
    
    // Settings Edit option show hide
    $('.editSec').click(function(){
        //alert('1');
        $(this).parent('.editSecField').next().show();
        $(this).parent('.editSecField').hide();
    });
    $('.closeBtn, .saveBtn').click(function(){
        $(this).parents('.EditFormSection').prev().show();
        $(this).parents('.EditFormSection').hide();
    });

    $('.togglePassword').click(function(){
        $(this).toggleClass("active");
        var input = $(this).parent().find("input");
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });

    // Table Check box

    $(document).ready(function () {
        $('.selectAllCheck, .selectAllCheck + label').click(function () {
            //$(this).find('table')
            $('.singleCheck').prop('checked', this.checked);
            $('.FixedOder .selectAllCheck').prop('checked', this.checked);
            $('.selectAllCheck').removeClass('indeterminate');
        });
        $('.FixedOder .selectAllCheck').click(function () {
            $('thead .selectAllCheck').prop('checked', this.checked);
            $('.selectAllCheck').removeClass('indeterminate');
        });
        $('.singleCheck').change(function () {
            var check = ($('.singleCheck').filter(":checked").length == $('.singleCheck').length);
            var check1 = $('.singleCheck').filter(":checked").length;

            if(check == false && check1 != 0){
                console.log('1-');
                $('.selectAllCheck').addClass('indeterminate');
            }else if(check1 == 0 && check == false){
                console.log('2-');
                $('.selectAllCheck').removeClass('indeterminate');
            } else {
                console.log('3-');
                $('.selectAllCheck').removeClass('indeterminate');
            }
            //console.log(check);
            //console.log(check1);
            $('.selectAllCheck').prop("checked", check);
            
            $('.FixedOder .selectAllCheck').prop("checked", check);
        });
    });
    // Collaspe Button

    $("#orInsInfoBtn").click(function(){
        $("#collaspeInfo").collapse('toggle'); // toggle collapse
    });
    $(".hideAlert .btn-close").click(function(){
        $("#collaspeInfo").collapse('toggle'); 
    });

    // Copy text
    $('.copyId').click(function(){
        $('#popupInfo').show();
        var textToCopy = $('.copyId').attr('data-order-id');
        //alert(textToCopy);
        //tempTextarea.val(textToCopy).select();
        setTimeout(function() {
            $("#popupInfo").fadeOut('fast')
        }, 5000);
        //$(this).
    });

    // Search advance
    var $outField = $('.multipleSecrchField');
    var $inputField = $('.ajaxSearch');
    $('.multipleSecrchField').find('[type="search"]').click(function(e){
        e.stopPropagation();
        $(this).parent().addClass('active');
        $(this).next('.ajaxSearch').attr('autofocus', 'true');
    });
    $('.searchResultBox').click(function(e){
        e.stopPropagation();
    });
    $(document).on('click',function(e){
        e.stopPropagation();
        $('.multipleSecrchField').removeClass('active');
    });

    // Singleline content resize

    $(window).resize(function() {
        var tsize = $('.tableResizeSize').width();
        $('.customWidth').css("width", tsize - 86);
    });
    $(window).trigger('resize');

    //Sidebar Tooltip
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))


    // Select 2
    $(".modalCurrencySelect").select2({
        closeOnSelect : true,
        //placeholder : "Merchant",
        allowHtml: true,
        allowClear: false,
        dropdownParent: $('#currencyModal')
    });

    // Multiselect
    $(".multiselect").select2({
        closeOnSelect : true,
        //placeholder : "Merchant",
        allowHtml: true,
        allowClear: false
    });
    // Single select
    $(".singleSelect").select2({
        closeOnSelect : true,
        placeholder : "Please select",
        minimumResultsForSearch: -1
    });

    // Select 2 With flag icon

    function selectIconState (state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "../scaleorder3.0/images/flag";
        var $state = $(
            '<span><img src="' + baseUrl + '/' + state.element.value.toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
        );
        // Use .text() instead of HTML string concatenation to avoid script injection issues
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + state.element.value.toLowerCase() + ".png");
        return $state;
    };
    
    $("#flagWithicon").select2({
        templateResult: selectIconState,
        templateSelection: selectIconState,
        minimumResultsForSearch: -1
    });

    // Custom Multi Accordian
    $('.havChild').click(function () {
        var label = $(this);
        var parent = label.parent('.has-children');
        var list = label.siblings('.collasped');
        label.attr('aria-expanded',
            label.attr('aria-expanded')=='true' ? 'false' : 'true'
        );
        if ( parent.hasClass('active') ) {
            list.slideUp('fast');
            parent.removeClass('active');
        }
        else {
            list.slideDown('fast');
            parent.addClass('active');
        }
    });
}( jQuery ));

