function redirect(url) {
    $(location).attr('href', url);
}

function number_format(number, decimals, dec_point, thousands_sep) {
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function strip(html) {
    var tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
}
var def_flag = false;
$(document).ready(function () {
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100 && def_flag == false) {
            def_flag = true;
        } else if ($(this).scrollTop() > 271) {
            //$(".DTFC_LeftHeadWrapper").addClass("scroll_nav").width($(".DTFC_LeftHeadWrapper").parent().width()).css({
                //left: 'auto'
            //});
            //$(".dataTables_scrollHead").addClass("scroll_nav").width($(".dataTables_scrollHead").parent().width());
            $("#header_title").addClass("scroll_nav").width($(".header_title").parent().width());	
          
        } else {
            //$(".DTFC_LeftHeadWrapper").removeClass("scroll_nav");
            //$(".dataTables_scrollHead").removeClass("scroll_nav");
            $("#header_title").removeClass("scroll_nav");
        }
    });
    $.validator.addMethod("custom_date", function (value, element) {
        return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
    }, "Please enter a date in the format dd/mm/yyyy.");
    $.fn.show_tooltip = function () {
        var dv = $("<div>").addClass('tooltip_h').css({
            width: $(this).width(),
            height: 'auto',
            backgroundColor: '#000',
            opacity: '0.9',
            position: 'absolute',
            top: $(this).position().top - 55,
            left: $(this).position().left,
            color: '#FFFFFF'
        }).text($(this).prop('title'));
        $(this).append(dv);
    }
    $.fn.hide_tooltip = function () {
        $(this).find('div.tooltip_h').remove();
    }
    $('.toggleForm').click(function () {
        var _this = this;
        var target = $(this).data('target');
        $("#" + target).toggle("slow", function (flag) {
            var state = $(this).is(':visible');
            if (state) {
                $(_this).removeClass('icon-plus-sign');
                $(_this).addClass('icon-minus-sign');
            } else {
                $(_this).removeClass('icon-minus-sign');
                $(_this).addClass('icon-plus-sign');
            }
        });
    });
    jQuery.fn.dataTableExt.oSort['numeric-comma-asc'] = function (a, b) {
        var x = (a == "-") ? 0 : a.replace(/,/, ".");
        var y = (b == "-") ? 0 : b.replace(/,/, ".");
        x = parseFloat(x);
        y = parseFloat(y);
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    };
    jQuery.fn.dataTableExt.oSort['numeric-comma-desc'] = function (a, b) {
        var x = (a == "-") ? 0 : a.replace(/,/, ".");
        var y = (b == "-") ? 0 : b.replace(/,/, ".");
        x = parseFloat(x);
        y = parseFloat(y);
        return ((x < y) ? 1 : ((x > y) ? -1 : 0));
    };
    $.fn.reloadCheckPages = function () {
        var obj = $('tr td input[name="chkBoxVal[]"]');
        var total = obj.length;
        var flag = 0;
        $.each(obj, function (key, value) {
            if ($(value).attr('checked') == "checked") {
                flag += 1;
            }
        });
        if (flag == total) {
            $('#select_all_page').attr('checked', true);
        } else {
            $('#select_all_page').attr('checked', false);
        }
    }
    $.fn.bindSelectCurrentPage = function () {
        $('#select_current_page').click(function () {
            var checked = ($(this).attr('checked') == "checked" ? true : false);
            var checkBoxes = $('tr td input[name="chkBoxVal[]"]');
            checkBoxes.attr("checked", checked);
        });
    }
    $('.notAllowSpace').keypress(function (e) {
        var evt = e || window.event;
        var ev_key = (evt.keyCode ? evt.keyCode : evt.charCode);
        if (ev_key == 32) {
            return false;
        }
    });
});

function set_number_format(data) {
    $data = number_format(data, config_number_format);
    return $data;
}


/**
 * function for get today date style 'dd/mm/yyyy'
 * @returns {String}
 */
function get_today_dd_mm_yyyy(){
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!

    var yyyy = today.getFullYear();
    if(dd<10){
        dd='0'+dd
    } 
    if(mm<10){
        mm='0'+mm
    } 
    var today = dd+'/'+mm+'/'+yyyy
    return today;
}

/**
 * function for cal diff of date style 'dd/mm/yyyy'
 * @param {type} date1
 * @param {type} date2
 * @returns {String}
 */
function date_diff_of_dd_mm_yyyy(date1, date2){
    var tmp_start = date1.split("/");
    date1 = tmp_start[1]+"/"+tmp_start[0]+"/"+tmp_start[2];
    
    var tmp_end = date2.split("/");
    date2 = tmp_end[1]+"/"+tmp_end[0]+"/"+tmp_end[2];
    
    var date1 = new Date(date1);
    var date2 = new Date(date2);
    var timeDiff = date1.getTime() - date2.getTime();
    var timeDiffAbs = Math.abs(timeDiff);
    var diffDays = Math.ceil(timeDiffAbs / (1000 * 3600 * 24)); 
    
    if (timeDiff < 0) {
        diffDays = "-"+diffDays;
    }
    
    return diffDays;
}

function check_special_character(str) {
    var pattern = new RegExp(/[~`!#$%\^&*+=\-\[\]\\';,/{}|\\":<>\?]/); //unacceptable chars
    if (pattern.test(str)) {
        return false;
    }
    return true;
}