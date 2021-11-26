<?php 
echo doctype('xhtml1-strict'); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php echo meta("Content-type", "text/html; charset=" . $this->config->item('charset'), "equiv"); ?>
        <title><?php echo WEBTITLE; ?></title>

        <!-- CSS Style -->
        <?php
        echo link_tag(base_url("css/themes/smoothness/jquery-ui-1.10.2.custom.min.css"));
        echo link_tag(base_url("css/jquery.dataTables_themeroller.css"));
        echo link_tag(base_url("css/menu.css"));
        echo link_tag(base_url("css/layout.css"));
        echo link_tag(base_url("css/buttons.css"));
        echo link_tag(base_url("css/error_message.css"));
        echo link_tag(base_url("css/bootstrap.min.css"));
        echo link_tag(base_url("css/datepicker.css"));
        echo link_tag(base_url("css/dataTable.bootstrap.css"));
        echo link_tag(base_url("css/multi-select.css"));
        echo link_tag(base_url("css/debug_kit.css"));
        echo link_tag(base_url("css/jquery.checkboxtree.min.css"));
        echo link_tag(base_url("css/sweetalert2.min.css"));
        ?>

        <![if IE]>
        <style>
            table#layout { height: expression(document.body.clientHeight-100); } /*the full height size of document minus 50px of top cell and minus 50px of bottom cell.*/
        </style>
        <![endif]>

        <!-- Javascript -->
        <!--ADD BY POR 2013-11-27 CREDIT BY P'BALL กำหนดค่าไว้สำหรับเรียก config ไปใช้ใน javascript-->
        <script language="JavaScript" type="text/javascript">
            var config_number_format = 2;
            var config_auto_hide = 2;
        </script>
        <!--END ADD-->
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery-1.9.1.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery-ui-1.10.2.custom.min.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jsmin.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery.maskedinput.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery.floatThead.min.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/sweetalert2.min.js") ?>"></script>
        <script>
            var flag = false;
            var curent_flow_action = '';
            $(document).ready(function () {
                $(".quick_dispatch_approve").click(function () {

                    if (!flag) {

                        flag = true;
                        var flow_id = [];
                        $.each($(".check_list"), function (idx, val) {
                            var obj = $(val);
                            if (obj.prop("checked")) {
                                flow_id.push(obj.val());
                            }
                        });

                        $.post("<?php echo site_url("/dispatch/mQuickApproveDispatch") ?>", {"flow_id": JSON.stringify(flow_id)}, function (data) {
                            if (data.status != "save") {
                                alert("FAILED, Please contact administrator.");
                            } else {
                                //                            alert("Approve Dispatch Complete.");
                                var can_confirm = true;
                                var mess = '';
                                var site_url = '<?php echo site_url(); ?>';
                                curent_flow_action = 'Dispatch';
                                $.each(data.return_val, function (type_of_alert, item) {
                                    var message_alert = new Array();
                                    $.each(item, function (j, in_item) {
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
                                    'margin-left': function () {
                                        return ($(window).width() - $(this).width()) / 2;
                                    }
                                });
                                $('#btn_confirm_alert_message').hide();


                                if (can_confirm) {
                                    window.onbeforeunload = null;
                                    flag_redirect = site_url + "/flow/flowDispatchList";
                                    // Auto Hide
                                    setTimeout(function () {
                                        $("#div_for_modal_message").modal('hide');
                                    }, config_auto_hide);
                                    // End
                                }
                                return false;
                            }
                            // {"status":"X003","return_val":{"success":[{"document_no":"DDR140910-001","message":"Approve Dispatch Complete."}]}}
                        }, "JSON");

                    } else {
                        alert('Please wait, system try to processing.');
                    }

                });

                /**
                 *
                 * Print Picking
                 *
                 */
                $(".print_group_picking").click(function () {

                    var flow_id = [];
                    $.each($(".check_list"), function (idx, val) {
                        var obj = $(val);
                        if (obj.prop("checked")) {
                            flow_id.push(obj.val());
                        }
                    });

                    if (flow_id === undefined || flow_id.length == 0) {
                        alert('Please select document for "Consolidate Picking"');
                        return false;
                    } else {
                        $(this).prop("href", "<?php echo site_url("/picking/consolidate_picking") ?>?flow_id=" + JSON.stringify(flow_id));
                    }

                });


                 /**
                 *
                 * Print  DN Picking
                 *
                 */
                $(".print_dn_picking").click(function () {

                var flow_id = [];
                $.each($(".check_list"), function (idx, val) {
                    var obj = $(val);
                    if (obj.prop("checked")) {
                        flow_id.push(obj.val());
                    }
                });

                if (flow_id === undefined || flow_id.length == 0) {
                    alert('Please select document for "Delivery Note"');
                    return false;
                } else {
                    $(this).prop("href", "<?php echo site_url("/picking/delivery_note") ?>?flow_id=" + JSON.stringify(flow_id));
                }

                });

                                // ====================================

                // hide #back-top first
                $("#back-top").hide().css({top: window.innerHeight - 85, left: window.innerWidth - 95});

                // fade in #back-top
                $(function () {
                    $(window).scroll(function () {
                        if ($(this).scrollTop() > 100) {
                            $('#back-top').fadeIn();
                        } else {
                            $('#back-top').fadeOut();
                        }
                    });

                    // scroll body to top
                    $('#back-top').click(function () {
                        if ($(this).hasClass('noclick')) {
                            $(this).removeClass('noclick');
                        } else {
                            $('body,html').animate({
                                scrollTop: 0
                            }, 500);
                            return false;
                        }
                    });

                    // scroll body to bottom
                    $.fn.backtobottom = function () {
                        $('body,html').animate({
                            scrollTop: $(document).height()
                        }, 500);
                        return false;
                    }

                    $('#back-top').draggable({
                        start: function (event, ui) {
                            $(this).addClass('noclick');
                        },
                        /*start: function(event, ui) {
                         $(this).removeClass('noclick');
                         }  */
                    });

                });

                $(".quick_picking_approve").click(function () {

                    if (!flag) {

                        flag = true;
                        var flow_id = [];
                        $.each($(".check_list"), function (idx, val) {
                            var obj = $(val);
                            if (obj.prop("checked")) {
                                flow_id.push(obj.val());
                            }
                        });

                        if (flow_id === undefined || flow_id.length == 0) {
                            alert('Please select document for "Quick Approve Picking"');
                            flag = false;
                            return false;
                        }

                        if (!confirm('Are you want to process Quick Approve Picking?')) {
                            flag = false;
                            return false;
                        }
                        ;

                        $.post("<?php echo site_url("/picking/mQuickApproveAction") ?>", {"flow_id": JSON.stringify(flow_id)}, function (data) {
                            if (data.status != "save") {
                                alert("FAILED, Please contact administrator.");
                            } else {
                                var can_confirm = true;
                                var mess = '';
                                var site_url = '<?php echo site_url(); ?>';
                                curent_flow_action = 'Dispatch';
                                $.each(data.return_val, function (type_of_alert, item) {
                                    var message_alert = new Array();
                                    $.each(item, function (j, in_item) {
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
                                    'margin-left': function () {
                                        return ($(window).width() - $(this).width()) / 2;
                                    }
                                });
                                $('#btn_confirm_alert_message').hide();

                                if (can_confirm) {
                                    window.onbeforeunload = null;
                                    flag_redirect = site_url + "/flow/flowPickingList";
                                    setTimeout(function () {
                                        $("#div_for_modal_message").modal('hide');
                                    }, config_auto_hide);
                                }
                                return false;
                            }
                        }, "JSON");

                    } else {
                        alert('Please wait, system try to processing.');
                    }

                });


                $('ul#menu li.head_menu').tooltip({
                    delay: {show: 1, hide: 1},
                    "placement": 'top'
                });

                $('ul#menu .simple li').tooltip({
                    delay: {show: 1, hide: 1},
                    "placement": 'right'
                });

                // BALL
                $('.list_row_click').mouseover(function () {
                    $(this).addClass('hover_row');
                }).mouseout(function () {
                    $(this).removeClass('hover_row');
                });

                list_row_click();
                // END BALL

                $('.tree').checkboxTree({
                    collapseImage: '<?php echo base_url("images/downArrow.gif") ?>',
                    expandImage: '<?php echo base_url("images/rightArrow.gif") ?>'
                });
                // Add By Akkarapol, 26/12/2013, เพิ่มการเซ็ตค่าให้กับ DebugKit ว่าให้ย่อขยายตามการกดปุ่ม DEBUG(#debug_kit_text_title) ที่มุมขวาของจอ
                $('#div_debug_kit').hide();
                $('#debug_kit_text_title').click(function () {
                    $('#div_debug_kit').fadeToggle(300);
                });
                // END Add By Akkarapol, 26/12/2013, เพิ่มการเซ็ตค่าให้กับ DebugKit ว่าให้ย่อขยายตามการกดปุ่ม DEBUG(#debug_kit_text_title) ที่มุมขวาของจอ

                $('#defDataTable').dataTable({
                    "bJQueryUI": true,
                    "bSort": false,
                    "sPaginationType": "full_numbers"
                });

                $("#li_logout").click(function () {
                    socket.emit('logout', db_config, uniqid, username);
                    var url_redirect = '<?php echo base_url('index.php/authen/logout') ?>';
                    window.location = url_redirect;
                });

                $('.string_Eng-f').live('keypress', function (evt) {
                    var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
                    if ((iKeyCode >= 65 && iKeyCode <= 90) || (iKeyCode >= 97 && iKeyCode <= 122) || !(iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57) && iKeyCode != 37 && iKeyCode != 39) || (iKeyCode == 32) || (iKeyCode == 40) || (iKeyCode == 41) || (iKeyCode == 44) || (iKeyCode == 45) || (iKeyCode == 46))
                        return true;
                    return false;
                });

                $('.string_Thai-f').live('keypress', function (evt) {
                    var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
                    if ((iKeyCode >= 161 && iKeyCode <= 255) || (iKeyCode >= 3585 && iKeyCode <= 3675) || !(iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57) && iKeyCode != 37 && iKeyCode != 39) || (iKeyCode == 32) || (iKeyCode == 40) || (iKeyCode == 41) || (iKeyCode == 44) || (iKeyCode == 45) || (iKeyCode == 46)) {
                        return true;
                    }
                    return false;
                });

                $('.string_special_characters-f').live('keypress', function (evt) {
                    var pattern = new RegExp(/[~`!#$%\^&*+=\-\[\]\\';,/{}|\\":<>\?]/); //unacceptable chars
                    if (pattern.test(String.fromCharCode(evt.keyCode))) {
                        return false;
                    }
                    return true;
                });

                $(".isEmail").blur(function () {
                    var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

                    if ($(this).val() !== '' && filter.test($(this).val()) === false) {
                        $(this).addClass('required');
                        $(this).focus();
                        alert("Please check your e-mail format.");
                    }
                });

                $('.integer-f').live('keypress', function (evt) { // 0-9 . Add by Ton! 20150626
                    var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
                    if ((iKeyCode >= 48 && iKeyCode <= 57) || iKeyCode === 37 || iKeyCode === 9 || iKeyCode === 8) {
                        return true;
                    }
                    return false;
                });

                $('.numeric-f').live('keypress', function (evt) { // 0-9 . Add by Ton! 20150626
                    var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
                    if ((iKeyCode >= 48 && iKeyCode <= 57 || iKeyCode === 9 || iKeyCode === 8 || iKeyCode === 46 || iKeyCode === 37)) {
                        return true;
                    }
                    return false;
                });

                $('.numericT-f').live('keypress', function (evt) { // 0-9 - . Add by Ton! 20150626
                    var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
                    if ((iKeyCode >= 48 && iKeyCode <= 57 || iKeyCode === 9 || iKeyCode === 8 || iKeyCode === 46 || iKeyCode === 37 || iKeyCode === 45)) {
                        return true;
                    }
                    return false;
                });

                $('.not_zero103-f').live('blur', function () { // format string decimal(10, 3)
                    var strValue = this.value;
                    var strId = this.id;

                    $('#' + strId).removeClass('required');
                    if (strValue.length > 0) {
                        if (strValue.length === 1 && (strValue === "0" || strValue === ".")) {
                            alert("Please enter a number that is not equal to zero(0).");
                            $('#' + strId).val('');
                            $('#' + strId).focus();
                            return false;
                        }

                        if (parseFloat(strValue).toFixed(3) <= 0) {
                            alert("Please enter a number that is not equal to zero(0).");
                            $('#' + strId).val('');
                            $('#' + strId).focus();
                            return false;
                        } else {
                            if (parseFloat(strValue).toFixed(3) === "NaN") {
                                alert("System can't save numeric format this. ( This format must be xxxxxxx.xxx )");
                                $('#' + strId).addClass('required');
                                $('#' + strId).select();
                            } else {
                                if (strValue !== "") {
                                    $('#' + strId).val(parseFloat(strValue).toFixed(3));

                                    var countStrConvert = parseFloat(strValue).toFixed(3).toString().length;
                                    var subStrConvert = $('#' + strId).val().substring(0, countStrConvert - 4);
                                    if (subStrConvert.length > 7) {
                                        alert("System can't save numeric format this. ( This format must be xxxxxx.xxx )");
                                        $('#' + strId).addClass('required');
                                        $('#' + strId).select();
                                    }
                                }
                            }
                        }
                    }
                });

                $('.not_zero156-f').live('blur', function () { // format string decimal(15, 6)
                    var strValue = this.value;
                    var strId = this.id;

                    $('#' + strId).removeClass('required');
                    if (strValue.length > 0) {
                        if (strValue.length === 1 && (strValue === "0" || strValue === ".")) {
                            alert("Please enter a number that is not equal to zero(0).");
                            $('#' + strId).val('');
                            $('#' + strId).focus();
                            return false;
                        }

                        if (parseFloat(strValue).toFixed(6) <= 0) {
                            alert("Please enter a number that is not equal to zero(0).");
                            $('#' + strId).val('');
                            $('#' + strId).focus();
                            return false;
                        } else {
                            if (parseFloat(strValue).toFixed(6) === "NaN") {
                                alert("System can't save numeric format this. ( This format must be xxxxxxxx.xxxxxx )");
                                $('#' + strId).addClass('required');
                                $('#' + strId).select();
                            } else {
                                if (strValue !== "") {
                                    $('#' + strId).val(parseFloat(strValue).toFixed(6));

                                    var countStrConvert = parseFloat(strValue).toFixed(6).toString().length;
                                    var subStrConvert = $('#' + strId).val().substring(0, countStrConvert - 7);
                                    if (subStrConvert.length > 8) {
                                        alert("System can't save numeric format this. ( This format must be xxxxxxxx.xxxxxx )");
                                        $('#' + strId).addClass('required');
                                        $('#' + strId).select();
                                    }
                                }
                            }
                        }
                    }
                });

                $('.not_zero102-f').live('blur', function () { // format string decimal(10, 2)
                    var strValue = this.value;
                    var strId = this.id;

                    $('#' + strId).removeClass('required');
                    if (strValue.length > 0) {
                        if (strValue.length === 1 && (strValue === "0" || strValue === ".")) {
                            alert("Please enter a number that is not equal to zero(0).");
                            $('#' + strId).val('');
                            $('#' + strId).focus();
                            return false;
                        }
                    }

                    if (parseFloat(strValue).toFixed(2) <= 0) {
                        alert("Please enter a number that is not equal to zero(0).");
                        $('#' + strId).val('');
                        $('#' + strId).focus();
                        return false;
                    } else {
                        if (parseFloat(strValue).toFixed(2) === "NaN") {
                            alert("System can't save numeric format this. ( This format must be xxxxxxx.xx )");
                            $('#' + strId).addClass('required');
                            $('#' + strId).select();
                        } else {
                            if (strValue !== "") {
                                $('#' + strId).val(parseFloat(strValue).toFixed(2));

                                var countStrConvert = parseFloat(strValue).toFixed(2).toString().length;
                                var subStrConvert = $('#' + strId).val().substring(0, countStrConvert - 3);
                                if (subStrConvert.length > 7) {
                                    alert("System can't save numeric format this. ( This format must be xxxxxx.xx )");
                                    $('#' + strId).addClass('required');
                                    $('#' + strId).select();
                                }
                            }
                        }
                    }
                });

                $('.not_zero-f').live('blur', function () {
                    var strValue = this.value;
                    var strId = this.id;

                    $('#' + strId).removeClass('required');
                    if (strValue.length > 0) {
                        if (strValue.length === 1 && (strValue === "0" || strValue === ".")) {
                            alert("Please enter a number that is not equal to zero(0).");
                            $('#' + strId).val('');
                            $('#' + strId).focus();
                            return false;
                        }
                    }
                });

                $(".float-f").live("blur", function () {
                    if ($(this).val().length > 0) {
                        if (parseFloat($(this).val()).toFixed(2) === "NaN") {
                            alert("Please input float format ( This format must be xx.xx )");
                        } else {
                            $(this).val(parseFloat($(this).val()).toFixed(2));
                        }
//                    var isValid = /^\d{1,2}(?:\.\d{1,2})?$|^\.\d{1,2}$/.test(parseFloat($(this).val()).toFixed(2));
//                    console.log(isValid);
                    }
                });


                $(".check_strenght").live("keyup", function () {
                    var result = checkStrenght($(this).val()); // check password strength
                    if (result < 1) {
                        $(this).css('border-color', 'red');

                        $(this).tooltip('destroy');
                        $(this).attr('title', 'Too short');
                        $(this).tooltip();
                    } else if (result === 1) {
                        $(this).css('border-color', 'red');

                        $(this).tooltip('destroy');
                        $(this).attr('title', 'Weak');
                        $(this).tooltip();
                    } else if (result === 2) {
                        $(this).css('border-color', 'orange');

                        $(this).tooltip('destroy');
                        $(this).attr('title', 'Weak');
                        $(this).tooltip();
                    } else if (result === 3) {
                        $(this).css('border-color', 'yellow');

                        $(this).tooltip('destroy');
                        $(this).attr('title', 'Fair');
                        $(this).tooltip();
                    } else if (result === 4) {
                        $(this).css('border-color', 'blue');

                        $(this).tooltip('destroy');
                        $(this).attr('title', 'Good');
                        $(this).tooltip();
                    } else {
                        $(this).css('border-color', 'green');

                        $(this).tooltip('destroy');
                        $(this).attr('title', 'Strong');
                        $(this).tooltip();
                    }
                });

                $(".export_excel_uom_all").click(function () {
                    window.open('<?php echo site_url('product_master/export_excel_product_uom_all'); ?>', '_blank');
                });
            });

            //Datatables trigger click and hilight
            function processRow(nRow, aData, iDisplayIndex, iDisplayIndexFull) {

                $(nRow).mouseover(function () {
                    $(this).addClass('hover_row');
                }).mouseout(function () {
                    $(this).removeClass('hover_row');
                });

                $(nRow).click(function (elm) {
                    if (elm.target.type !== 'checkbox') {
                        $(':checkbox', this).trigger('click'); // trigger click first
                        $(':checkbox', this)[0].onclick(); // trigger html onclick
                    }
                });

            }

            function list_row_click() {
                $('.list_row_click').click(function (event)
                {
                    if (event.target.type == "checkbox") {
                        event.stopPropagation();
                    } else {
                        var _this = this;

                        if ($(".view_item").length > 0) {
                            $(_this).find('.view_item')[0].onclick();
                        }

                        if ($("input[name='chkBoxVal[]']").length > 0) {
                            if (event.target.type !== 'checkbox') {
                                $(':checkbox', this).trigger('click');
                            }
                        }
                    }

                });
            }

            function openForm(form_name, module, mode, obj_id, token) {
                $('.list_row_click').unbind('click');
                if ("" != module) {
                    var f = document.createElement("form");
                    f.setAttribute('method', "post");
                    f.setAttribute('action', "<?php echo site_url(); ?>" + "/" + module);
                    f.setAttribute('id', form_name);

                    var strMode = document.createElement("input");
                    strMode.setAttribute('type', "hidden");
                    strMode.setAttribute('name', "mode");
                    strMode.setAttribute('value', mode);
                    f.appendChild(strMode);

                    var objToken = document.createElement("input");
                    objToken.setAttribute('type', "hidden");
                    objToken.setAttribute('name', "token");
                    objToken.setAttribute('value', token);
                    f.appendChild(objToken);

                    if ("" != obj_id) {
                        var strObjId = document.createElement("input");
                        strObjId.setAttribute('type', "hidden");
                        strObjId.setAttribute('name', "id");
                        strObjId.setAttribute('value', obj_id);
                        f.appendChild(strObjId);
                    }

                    if (mode == "D") {
                        if (confirm("Confirm delete this data?") == true) {
                            document.getElementsByTagName('body')[0].appendChild(f);
                            $("#" + form_name).submit();
                        }

                    } else {
                        document.getElementsByTagName('body')[0].appendChild(f);
                        $("#" + form_name).submit();
                    }
                }
                $('.list_row_click').bind('click', list_row_click);
            }

            function postAction(module, sub_module) {
                var f = document.createElement("form");
                f.setAttribute('method', "post");
                f.setAttribute('action', "<?php echo site_url(); ?>" + "/" + module + "/" + sub_module);
                f.setAttribute('id', "workflow_form");

                document.getElementsByTagName('body')[0].appendChild(f);
                $("#workflow_form").submit();
            }

        </script>
        <style>
            .tooltip {
                font-size: 1.3em !important;
                z-index: 9999 !important;
                min-width: 200px;
            }
            #myModalPassword {
                width: 450px;
                margin: -250px 0 0 -250px;
            }
        </style>

        <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url() . 'css/images/WMSP_ICO16.ico'; ?>">

            <?php include "element_nodejs_main.php"; ?>

    </head>
    <body>

        <div id="preload" style="display: none; position: fixed; left: 0; top: 0; z-index: 999; width: 100%; height: 100%; overflow: visible; opacity: 0.5; background: #333 url('<?php echo base_url('images/ajax-loader.gif') ?>') no-repeat center center; "></div>

        <?php include "element_modal_message_alert.php"; ?>
        <TABLE id="layout" border=0 align="center" style='height:95%' >
            <TR class="header">
                <TD>
                    <TABLE width="100%" border=0  cellpadding="1" cellspacing="1"  >
                        <TR>
                            <TD width="40%" align="left">
                                <a href="<?php echo base_url(); ?>">
                                <image src="<?php echo base_url(); ?>css/images/JWDinfo.png" style="width:auto;height:40px;">
                                    <!-- <image src="<?php echo base_url() . $this->config->item('logo_patch'); ?>" alt="<?php echo $this->config->item('logo_alt'); ?>" style="width:auto;height:40px;"> -->
                                </a>
                            </TD>
                            <TD width="45%" align="right"><font style='font-size:16px;color:red;'>
                                    <?php echo $this->session->userdata['renter_name'] ?> : <?php echo $this->session->userdata['dept_name'] ?>
                                    <?php
                                    $status = @shell_exec('svnversion ' . str_replace('application/views/header.php', '', realpath(__FILE__)));
                                    @list($master, $merge) = explode(":", $status);
                                    echo '(Revision: ' . (isset($merge) ? $merge : $master) . ')';
                                    ?>
                                </font>
                            </TD>
                            <TD width="11%" align="right">
                                <div class="dropdown">
                                    <a data-toggle="dropdown" href="#"><h5><i class='icon-white icon-user'></i><?php echo $this->session->userdata('username'); ?></h5></a>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                        <li><a data-target='#myModalPassword' data-toggle='modal' id='EditPass'> Change Password </a></li>
                                        <li><a href='<?php echo base_url("index.php/authen/logout") ?>'> Logout </a></li>
                                    </ul>
                                </div>

                            </TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR class="header_menu">
                <TD>{menu}</TD>
            </TR>