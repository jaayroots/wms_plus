/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var global_module = '';
var global_sub_module = '';
var global_action_value = '';
var global_next_state = '';
var global_data_form = '';
var message_array = {
    'pre_receive': {
        'openPreReceive': 'Open pre-receive document',
        'rejectAction': 'Reject document',
        'confirmPreReceive': 'Confirm pre-receive document',
        'approvePreReceive': 'Approve pre-receive document',
        'rejectAndReturnAction': 'Reject and return document'
    },
    'receive': {
        'rejectAction': 'Reject document',
        'rejectAndReturnAction': 'Reject and return document',
        'updateInfo': 'Update information document',
        'approveAction': 'Approve document',
    },
    'putaway': {
        'approveAction':
                {
                    'Approve': 'Approve putaway document'
                }
        , 'rejectAndReturnAction':
                {
                    'Reject': 'Reject document',
                    'Reject and Return': 'Reject and return document'
                }
    },
    'pre_dispatch': {
        'openPreDispatch': 'Open pre-dispatch document',
        'rejectAction': 'Reject document',
        'rejectAndReturnAction': 'Reject and return document',
        'confirmPreDispatch': 'Confirm pre-dispatch document',
        'approvePreDispatch': 'Approve pre-dispatch document'
    },
    'picking': {
        'rejectAction': 'Reject document',
        'rejectAndReturnAction': 'Reject and return document',
        'confirmPreDispatch': 'Confirm pre-dispatch document',
        'approveAction': 'Approve picking document'
    },
};





/**
 * Set Count Down Zone
 *
 */
var set_start_count = 6,
        start_count = set_start_count,
        timer;

function countdown() {
    if (--start_count <= 0) {
        $('#div_for_modal_message').modal('hide');
    }
    else {
        $('#span_text_modal_alert').text(start_count);
        timer = window.setTimeout(countdown, 1000);
    }
}


function confirm_save_data() {

    $('#btn_confirm_alert_message').hide();

    flagSubmit = false;
    clearTimeout(timer);
    $('#span_text_modal_alert').text('');

    $.ajaxSetup({async: false});
    $.post(site_url + "/" + global_module + "/" + global_sub_module, global_data_form, function(data) {

        var mess = '';
        var document_no = '';

        switch (data.status) {
            //ADD BY POR 2014-03-10 กรณี Approve receive  จะแสดง popup pdf ขึ้นมา ใช้แยกจาก dispatch เผื่ออนาคต process แตกต่างกัน
            case 'X002':
                var can_confirm = true;
                var document_no = '';
                $.each(data.return_val, function(type_of_alert, item) {
                    var message_alert = new Array();
                    $.each(item, function(j, in_item) {
                        message_alert.push(in_item.message);
                        document_no = in_item.document_no;
                    });
                    if (type_of_alert == "critical") {
                        mess += '<div id="div_unsuccess">';
                        can_confirm = false;
                    } else if (type_of_alert == "warning") {
                        mess += '<div id="div_warning">';
                    } else if (type_of_alert == "success") {
                        mess += '<div id="div_success">';
                    }

                    mess += '<h4>' + type_of_alert + '</h4>- ' + message_alert.join('<BR>- ') + '</div>';

                });
                $('#div_for_alert_message').html(mess);
                $('#div_for_modal_message').modal('show').css({
                    'margin-left': function() {
                        return ($(window).width() - $(this).width()) / 2;
                    }
                });

                // Auto Hide
                setTimeout(function() {
                    $("#div_for_modal_message").modal('hide');
                }, config_auto_hide);
                // End

                $('#btn_confirm_alert_message').hide();
                if (can_confirm) {
                    window.onbeforeunload = null;
                    flag_redirect = redirect_after_save;
                    window.open('export_to_pdf?document_no=' + document_no, '_blank');
                }
                return false;

                break;
                //END ADD

            case 'X003':
                var can_confirm = true;
                var document_no = '';
                $.each(data.return_val, function(type_of_alert, item) {
                    var message_alert = new Array();
                    $.each(item, function(j, in_item) {
                        message_alert.push(in_item.message);
                        document_no = in_item.document_no;
                    });
                    if (type_of_alert == "critical") {
                        mess += '<div id="div_unsuccess">';
                        can_confirm = false;
                    } else if (type_of_alert == "warning") {
                        mess += '<div id="div_warning">';
                    } else if (type_of_alert == "success") {
                        mess += '<div id="div_success">';
                    }

                    mess += '<h4>' + type_of_alert + '</h4>- ' + message_alert.join('<BR>- ') + '</div>';

                });
                $('#div_for_alert_message').html(mess);
                $('#div_for_modal_message').modal('show').css({
                    'margin-left': function() {
                        return ($(window).width() - $(this).width()) / 2;
                    }
                });
                $('#btn_confirm_alert_message').hide();

                // Auto Hide
                setTimeout(function() {
                    $("#div_for_modal_message").modal('hide');
                }, config_auto_hide);
                // End

                if (can_confirm) {
                    window.onbeforeunload = null;
                    flag_redirect = redirect_after_save;
                    window.open('export_dispatch_pdf?showfooter=show&document_no=' + document_no, '_blank');
                }
                return false;

                break;

            case 'save':
                var can_confirm = true;
                $.each(data.return_val, function(type_of_alert, item) {
                    var message_alert = new Array();
                    $.each(item, function(j, in_item) {
                        message_alert.push(in_item.message);
                        if (in_item.document_no != undefined) {
                            document_no = in_item.document_no;
                        }
                    });
                    if (type_of_alert == "critical") {
                        mess += '<div id="div_unsuccess">';
                        can_confirm = false;
                    } else if (type_of_alert == "warning") {
                        mess += '<div id="div_warning">';
                    } else if (type_of_alert == "success") {
                        mess += '<div id="div_success">';
                    }

                    mess += '<h4>' + type_of_alert + '</h4>- ' + message_alert.join('<BR>- ') + '</div>';

                });
                $('#div_for_alert_message').html(mess);
                $('#div_for_modal_message').modal('show').css({
                    'margin-left': function() {
                        return ($(window).width() - $(this).width()) / 2;
                    }
                });
                $('#btn_confirm_alert_message').hide(); 

                console.log(document_no);

                if (can_confirm) {
                    window.onbeforeunload = null;
                    flag_redirect = redirect_after_save;
                    if ( document_no != "") {
                        window.open('printBarcode?d=' + document_no, '_blank');
                    }
                    // Auto Hide
                    setTimeout(function() {
                        $("#div_for_modal_message").modal('hide');
                    }, config_auto_hide);
                    // End
                }
                return false;
                break;
            case 'validation':
                $.each(data.return_val, function(type_of_alert, item) {
                    var message_alert = new Array();
                    $.each(item, function(j, in_item) {
                        message_alert.push(in_item.message);
                    });

                    if (type_of_alert == "critical") {
                        mess += '<div id="div_unsuccess">';
                    } else if (type_of_alert == "warning") {
                        mess += '<div id="div_warning">';
                    } else if (type_of_alert == "success") {
                        mess += '<div id="div_success">';
                    }
                    mess += '<h4>' + type_of_alert + '</h4>- ' + message_alert.join('<BR>- ') + '</div>';

                });
                $('#div_for_alert_message').html(mess);
                $('#div_for_modal_message').modal('show').css({
                    'margin-left': function() {
                        return ($(window).width() - $(this).width()) / 2;
                    }
                });

                // Auto Hide
                setTimeout(function() {
                    $("#div_for_modal_message").modal('hide');
                }, config_auto_hide);
                // End

                return false;
                break;
        }
    }, "json");
}


function validation_data() {
    $('.highlight_table_err').each(function() {
        $(this).removeClass('highlight_table_err');

    });

    $('.highlight_err').each(function() {
        $(this).removeClass('highlight_err');

    });


    var flag_pass_validation = true;
    var mess = '';
    $.ajaxSetup({async: false});
    $.post(site_url + "/" + global_module + "/validation_" + global_sub_module, global_data_form, function(data) {

        // Zone Set Default CountDown
        $('#span_text_modal_alert').text('');
        start_count = set_start_count;
        // END Zone Set Default CountDown



        // Zone merge data from validate form and validate data
        if (global_return.return_val == undefined) {
            var tmp_return = new Object();
            tmp_return.critical = new Array();
            tmp_return.warning = new Array();
            tmp_return.success = new Array();
            global_return.return_val = tmp_return;
        }

        var tmp_data = new Object();
        var return_val = new Object();

        return_val.critical = new Array();
        return_val.warning = new Array();
        return_val.success = new Array();

        if (data.status == 'validation' || global_return.status == 'validation') {
            tmp_data.status = 'validation';
        }

        if (data.return_val['critical'] != undefined) {
            if (global_return.return_val['critical'] != undefined) {
                return_val.critical = $.merge(data.return_val['critical'], global_return.return_val['critical']);
            } else {
                return_val.critical = data.return_val['critical'];
            }
        } else if (global_return.return_val['critical'] != undefined) {
            return_val.critical = global_return.return_val['critical'];
        }

        if (data.return_val['warning'] != undefined) {
            if (global_return.return_val['warning'] != undefined) {
                return_val.warning = $.merge(data.return_val['warning'], global_return.return_val['warning']);
            } else {
                return_val.warning = data.return_val['warning'];
            }
        } else if (global_return.return_val['warning'] != undefined) {
            return_val.warning = global_return.return_val['warning'];
        }

        if (data.return_val['success'] != undefined) {
            if (global_return.return_val['success'] != undefined) {
                return_val.success = $.merge(data.return_val['success'], global_return.return_val['success']);
            } else {
                return_val.success = data.return_val['success'];
            }
        } else if (global_return.return_val['success'] != undefined) {
            return_val.success = global_return.return_val['success'];
        }

        tmp_data.return_val = return_val;

        data = tmp_data;
        // End Zone merge data from validate form and validate data


        switch (data.status) {
            case 'validation':

                if (typeof data_table_id_class !== 'undefined') {
                var cTable = $(data_table_id_class).dataTable().fnGetNodes();
                }

                var can_confirm = true;

                flag_pass_validation = false;

                $.each(data.return_val, function(type_of_alert, item) {

                    var message_alert = new Array();

                    $.each(item, function(j, in_item) {
                        message_alert.push(in_item.message);

                        if (type_of_alert == 'critical') {
                            if (in_item.row instanceof Array) {
                                for (i in in_item.row) {
                                    if (typeof hide_arr_index !== 'undefined') {
                                        var unique_hide_arr_index = hide_arr_index.filter(function(hide_key, hide_item) {
                                            return hide_arr_index.indexOf(hide_key) == hide_item;
                                        });
                                        var minus_col_index = 0;
                                        $.each(unique_hide_arr_index, function(unique_key, unique_item) {
                                            if (unique_item < in_item.col[i]) {
                                                minus_col_index++;
                                            }
                                        });
                                        in_item.col[i] -= minus_col_index;
                                    }
                                    $(cTable[in_item.row[i]]).find("td:eq(" + in_item.col[i] + ")").addClass("highlight_table_err");
                                }
                            } else if ((in_item.row != "" && in_item.row !== undefined) && (in_item.col != "" && in_item.col !== undefined)) {
                                if (typeof hide_arr_index !== 'undefined') {
                                    var unique_hide_arr_index = hide_arr_index.filter(function(hide_key, hide_item) {
                                        return hide_arr_index.indexOf(hide_key) == hide_item;
                                    });
                                    var minus_col_index = 0;
                                    $.each(unique_hide_arr_index, function(unique_key, unique_item) {
                                        if (unique_item < in_item.col) {
                                            minus_col_index++;
                                        }
                                    });
                                    in_item.col -= minus_col_index;
                                }
                                $(cTable[in_item.row]).find("td:eq(" + in_item.col + ")").addClass("highlight_table_err");
                            } else if (in_item.id != "" && in_item.id !== undefined) {
                                $("#" + in_item.id).addClass("highlight_err");
                            }
                        }

                    });

                    if (message_alert.length > 0) {
                        if (type_of_alert == "critical") {
                            mess += '<div id="div_unsuccess">';
                            can_confirm = false;
                        } else if (type_of_alert == "warning") {
                            mess += '<div id="div_warning">';
                        } else if (type_of_alert == "success") {
                            mess += '<div id="div_success">';
                        }
                        mess += '<h4>' + type_of_alert + '</h4>- ' + message_alert.join('<BR>- ') + '</div>';
                    }

                });

                $('#div_for_alert_message').html(mess);
                $('#div_for_modal_message').modal('show').css({
                    'margin-left': function() {
                        return ($(window).width() - $(this).width()) / 2;
                    }
                });

                $('#btn_confirm_alert_message').show();

                if (can_confirm) {

                    mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>' + mess;

                    $('#div_for_alert_message').html(mess);
                    $('#div_for_modal_message').modal('show').css({
                        'margin-left': function() {
                            return ($(window).width() - $(this).width()) / 2;
                        }
                    });

                    countdown();

                } else {
                    $('#btn_confirm_alert_message').hide();
                }

                return false;
                break;
            default:

                mess += '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';

                $('#div_for_alert_message').html(mess);
                $('#div_for_modal_message').modal('show').css({
                    'margin-left': function() {
                        return ($(window).width() - $(this).width()) / 2;
                    }
                });
                $('#btn_confirm_alert_message').show();

                countdown();

                return false;
                break;

        }

        return false;

    }, "json");

    flagSubmit = false;

    if (!flag_pass_validation) {
        return false;
    }
}




function call_modal_alert(data, redirect_after_save){

    var can_confirm = true;
    var mess = '';

    $.each(data.return_val, function(type_of_alert, item) {
        var message_alert = new Array();
        $.each(item, function(j, in_item) {
            message_alert.push(in_item.message);
        });
        if (type_of_alert == "critical") {
            mess += '<div id="div_unsuccess">';
            can_confirm = false;
        } else if (type_of_alert == "warning") {
            mess += '<div id="div_warning">';
        } else if (type_of_alert == "success") {
            mess += '<div id="div_success">';
        }

        mess += '<h4>' + type_of_alert + '</h4>- ' + message_alert.join('<BR>- ') + '</div>';

    });
    $('#div_for_alert_message').html(mess);
    $('#div_for_modal_message').modal('show').css({
        'margin-left': function() {
            return ($(window).width() - $(this).width()) / 2;
        }
    });
    $('#btn_confirm_alert_message').hide();

    if (can_confirm) {
        window.onbeforeunload = null;
        flag_redirect = redirect_after_save;
        // Auto Hide
        setTimeout(function() {
            $("#div_for_modal_message").modal('hide');
        }, config_auto_hide);
        // End
    }
}