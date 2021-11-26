<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var form_name = "action_form";
    var separator = "<?php echo SEPARATOR; ?>";
    var ci_prod_code = 1;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_balance_qty = 9;
    var ci_confirm_qty = 10;
    var ci_remark = 15;
    var ci_prod_id = 16;
    var ci_prod_status = 17;
    var ci_prod_sub_status = 18;
    var ci_unit_id = 19;
    var ci_item_id = 20;
    var ci_suggest_loc = 21;
    var ci_actual_loc = 22;
    var ci_old_loc = 23;
    var ci_list = [
        {name: 'ci_prod_id', value: ci_prod_id},
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_lot', value: ci_lot},
        {name: 'ci_serial', value: ci_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_prod_status', value: ci_prod_status},
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_suggest_loc', value: ci_suggest_loc},
        {name: 'ci_actual_loc', value: ci_actual_loc},
        {name: 'ci_unit_id', value: ci_unit_id},
        {name: 'ci_balance_qty', value: ci_balance_qty},
        {name: 'ci_confirm_qty', value: ci_confirm_qty},
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_old_loc', value: ci_old_loc}
    ]

    $(document).ready(function() {

        // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="assigned_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        initProductTable();
        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark],[name=assigned_id]").attr("disabled", true);
        //Define Dialog Model 

    });
    //Define Dialog Model 
    $('#myModal').modal('toggle').css({
        'width': function() {
            return ($(document).width() * .9) + 'px'; // make width 90% of screen
        },
        'margin-left': function() {
            return -($(this).width() / 2); // center model
        }
    });
    function initProductTable() {
        var myDataTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "bScrollCollapse": false,
            "bPaginate": false,
//            "sScrollX": "100%",
//            "sScrollY": "300px",
            "sScrollXInner": "120%",
            "sDom": '<"H"lfr>t<"F"ip>',
//            "aoColumnDefs": [
//                { "sClass": "actual_loc_class", "aTargets": [ 14 ] }
//            ]

            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "12%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "5%", "sClass": "left_text obj_status", "aTargets": [3]}, // Edit by Ton! 20131001
                {"sWidth": "5%", "sClass": "left_text obj_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "5%", "sClass": "center obj_mfg", "aTargets": [7]},
                {"sWidth": "5%", "sClass": "center obj_exp", "aTargets": [8]},
                {"sWidth": "3%", "sClass": "right_text", "aTargets": [9]},
                {"sWidth": "3%", "sClass": "right_text", "aTargets": [10]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [11]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [12]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [13]},
                {"sWidth": "7%", "sClass": "left_text actual_loc_class", "aTargets": [14]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [15]},
            ]
        }).makeEditable({
            sUpdateURL: function(value, settings) {
                return value;
            },
            "aoColumns": [
                null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , null
                        , {
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    sClass: "actual_loc_class",
                    cssclass: "required number",
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
                        calculate_qty();
                    }
                }
                , null
                        , null
                        , null
//	Add By Akkarapol, 03/09/2013, เปลี่ยน ActualLocation จาก DropdownList ไปเป็น Textbox
//								,{								
//									loadurl	 :  '<?php echo site_url() . "/repackage/getSuggestLocRule"; ?>',
//									loaddata : function(value, settings) {
//													// AJAX Post Data
//													var oTable = $('#showProductTable').dataTable( );
//													var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//													var aData = oTable.fnGetData( rowIndex );
//													var dataSet = { 
//														receive_type	: $("#receive_type").val(),
//														product_code	: aData[ci_prod_code], 
//														product_status	: aData[ci_prod_status],
//														lot				: aData[ci_lot],
//														serial			: aData[ci_serial],
//														prod_mfd		: "",
//														prod_exp		: ""
//													};
//													return dataSet;
//											   },
//									loadtype : "POST",
//									type     : 'select',
//									onblur   : 'submit',
//									sUpdateURL: function(value, settings){
//										console.log(settings);
//										var oTable = $('#showProductTable').dataTable( );
//										var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//										oTable.fnUpdate( value ,rowIndex ,ci_actual_loc );
//										sNewCellDisplayValue = sNewCellDisplayValue + '<a ONCLICK="showProdInLoc(\''+value+'\',\''+sNewCellDisplayValue+'\')"><?php echo img("css/images/icons/view.png"); ?></a>'; 
//										sNewCellValue = sNewCellDisplayValue;
//										return value;
//									}
//								}

                        , {
                    sSortDataType: "dom-text",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    loadfirst: true, // Add By Akkarapol, 14/10/2013, เพิ่ม loadfirst เพื่อให้ช่อง Actual Location นี้มี textbox ขึ้นมาตั้งแต่โหลดหน้า

                }
                // END Add By Akkarapol, 03/09/2013, เปลี่ยน ActualLocation จาก DropdownList ไปเป็น Textbox
                , {onblur: 'submit', type: 'text'}
                , null
            ]
        });
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_actual_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_old_loc, false);
//        $('#showProductTable tbody tr[title]').tooltip();
        // Add By Akkarapol, 23/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
        $('#showProductTable tbody tr td[title]').hover(function() {

            // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 
            var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;
            if (chk_title != chk_innerHTML) {
                $(this).show_tooltip();
            }
            // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 

        }, function() {
            $(this).hide_tooltip();
        });
        // END Add By Akkarapol, 23/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย  

        new FixedColumns(myDataTable, {
            "iLeftColumns": 0,
            "sLeftWidth": 'relative',
            "iLeftWidth": 0
        });
    }

    function postRequestAction(module, sub_module, action_value, next_state) {
        var statusisValidateForm = validateForm();
        if (statusisValidateForm === true) {
            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }

            for (i in rowData) {
                //console.log(rowData[i]);
                var balance_qty = parseInt(rowData[i][ci_balance_qty]);
                var confirm_qty = parseInt(rowData[i][ci_confirm_qty]);
                if (balance_qty != confirm_qty) {
                    alert('Receive Qty Must be Equal Confirm Qty'); // Edit By Akkarapol, 03/09/2013, เปลี่ยน alert จาก Balance Qty Must be Equal Confirm Qty เป็น Receive Qty Must be Equal Confirm Qty
                    return false;
                }

                var chk_suggest_loc = parseInt(rowData[i][ci_suggest_loc]);
                var chk_actual_loc = rowData[i][ci_actual_loc]; // Edit By Akkarapol, 03/09/2013, ใส่ค่า Index แบบ ตรงตัว เพราะค่าถูกเปลี่ยน
                chk_actual_loc = chk_actual_loc.toString();
                var chk_remark = rowData[i][ci_remark];
                if (chk_actual_loc != "") {

                    // Comment By Akkarapol, ไม่ต้องเช็คแล้ว เนื่องจากใช้การเรียกจากฐานข้อมูลมาเลยเป็นแบบ autocomplete
//
//// Add By Akkarapol, 03/09/2013, เพิ่มการเช็ค ActualLocattion เข้าไป จากการที่ User กรอก Location Code ผ่าน textbox และทำการ Get ค่า ID มาเพื่อเปรียบเทียบกับ Suggest
//                    var dataSet = {
//                        chk_actual_loc: chk_actual_loc
//                    }
//                    var IsLocation = 'N';
//                    $.ajaxSetup({async: false});
//                    $.post('<?php echo site_url() . "/repackage/chkActualLocattion" ?>', dataSet, function(data) {
//                        if (data == '') {
//                            alert('Location is Fail');
//                            return false;
//                        } else {
//                            chk_actual_loc = data;
//                            rowData[i][ci_actual_loc] = chk_actual_loc;
//                            IsLocation = 'Y';
//                        }
//                    }, "html");
//                    
//                    if (IsLocation == 'Y') {
//                        if (chk_suggest_loc != chk_actual_loc) {
//                            if (chk_remark == "") {
//                                alert('Please Check Your Information Remark');
//                                return false;
//                            }
//                        }
//                    }else{
//                        return false;
//                    }
//// END Add By Akkarapol, 03/09/2013, เพิ่มการเช็ค ActualLocattion เข้าไป จากการที่ User กรอก Location Code ผ่าน textbox และทำการ Get ค่า ID มาเพื่อเปรียบเทียบกับ Suggest
//  END Comment By Akkarapol, ไม่ต้องเช็คแล้ว เนื่องจากใช้การเรียกจากฐานข้อมูลมาเลยเป็นแบบ autocomplete

                } else {
                    alert('Please Check Actual Location');
                    return false;
                }

            }


            if (confirm("Are you sure to action " + action_value + "?")) {
                $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", false);
                var f = document.getElementById(form_name);
                var actionType = document.createElement("input");
                actionType.setAttribute('type', "hidden");
                actionType.setAttribute('name', "action_type");
                actionType.setAttribute('value', action_value);
                f.appendChild(actionType);
                var toStateNo = document.createElement("input");
                toStateNo.setAttribute('type', "hidden");
                toStateNo.setAttribute('name', "next_state");
                toStateNo.setAttribute('value', next_state);
                f.appendChild(toStateNo);
                var oTable = $('#showProductTable').dataTable().fnGetData();
                for (i in oTable) {
                    var prod_data = oTable[i].join(separator);
                    var prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
                    prodItem.setAttribute('value', prod_data);
                    f.appendChild(prodItem);
                }

                $.each(ci_list, function(i, obj) {
                    var ci_item = document.createElement("input");
                    ci_item.setAttribute('type', "hidden");
                    ci_item.setAttribute('name', obj.name);
                    ci_item.setAttribute('value', obj.value);
                    f.appendChild(ci_item);
                });
                var data_form = $("#" + form_name).serialize();
                var message = "";
                var step_name = "Relocate Product";
                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
                    switch (data.status) {
                        case 'C001':
                            message = "Save " + step_name + " Complete";
                            break;
                        case 'C002':
                            message = "Confirm " + step_name + " Complete";
                            break;
                        case 'C003':
                            message = "Approve " + step_name + " Complete";
                            break;
                        case 'E001':
                            message = "Save " + step_name + " Incomplete";
                            break;
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/flow/flowRepackageList";
                    redirect(url);
                }, "json");
            }
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowRepackageList";
            redirect(url);
        }
    }


    function validateForm() {
        $("form").validate({
            rules: {
                assigned_id: {required: true}
            }
        });
        return $("form").valid();
    }

    function showProdInLoc(location_id, location_code) {
        var dataSet = {location_id: location_id, location_code: location_code}
        $.post('<?php echo site_url() . "/reLocation/showProductInLocation" ?>', dataSet, function(data) {
            $(".modal-body").html(data);
            var oTable = $('#defDataTable').dataTable({
                "bJQueryUI": true,
                "bAutoWidth": false,
                "bSort": false,
                "bRetrieve": true,
                "bDestroy": true,
                "sPaginationType": "full_numbers",
                "aoColumnDefs": [
                    {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [1]},
                    {"sWidth": "5%", "sClass": "center", "aTargets": [2]},
                    {"sWidth": "20%", "sClass": "left_text", "aTargets": [3]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [6]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [7]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [8]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [9]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [10]},
                    {"sWidth": "5%", "sClass": "right_text", "aTargets": [11]}
                ]
            });
        }, "html");
        $('#myModal').modal('toggle');
    }

    $(document).ready(function() {


        function selectBind(elm) {

            $('.sel_actual_location').click(function() {
                var location_id = $(this).data('location_id');
                $(elm).text($(this).data('location_code'));
                var datatables = $('#showProductTable').dataTable();
                var aPos = datatables.fnGetPosition(elm);
                var rowData = datatables.fnGetData();
                if (typeof location_id !== "undefined")
                {
                    rowData[aPos[0]][ci_actual_loc] = location_id;
                }
                //datatables.fnUpdate( 'xxxxx', aPos['0'], 20 );
            });
        }

        $('.actual_loc_class').keyup(function() {
            var _this = this;
            var tr_data_no = $(this).parent('tr').children('td')[0].innerHTML;

            $(this).append('<div class="suggestionsBox" id="suggestions-' + tr_data_no + '" uu="' + tr_data_no + '" style="display:none;"> <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div> </div>');
            var value = $(this).find('form input').val();
            var minChar = 1;
            if (value.length >= minChar) {
                if (value != '') {
                    $.post("<?php echo site_url(); ?>/repackage/autoCompleteActualLocation", {tr_data_no: tr_data_no, text_search: value}, function(val, data) {
                        if (data.length > 0) {
                            var response = $.parseJSON(val);
                            $('#suggestions-' + tr_data_no).show();
                            //$('#autoSuggestionsList').html(val, data);
                            $.each(response, function(idx, value) {
                                var li = $('<li>').attr('class', 'sel_actual_location').data('location_id', value.location_id).data('location_code', value.location_code).html(value.location_code);
                                $('#autoSuggestionsList').append(li);
                            });
                            selectBind(_this);
                        }
                    });
                }
            }


        });
    });


    function hideSuggestions() {
        setTimeout("$('#suggestions').hide();", 100);
    }


    // Add function calculate_qty : by kik : 28-10-2013 
    function calculate_qty() {
        var rowData = $('#showProductTable').dataTable().fnGetData();
        var num_row = rowData.length;
        var sum_cf_qty = 0;
        for (i in rowData) {
            var tmp_qty = 0;
            tmp_qty = parseInt(rowData[i][ci_confirm_qty]);

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
            }

            sum_cf_qty = sum_cf_qty + tmp_qty;
        }

        $('#sum_cf_qty').html(number_format(sum_cf_qty));

    }
    // end function calculate_qty : by kik : 28-10-2013 
</script>
<style>
    /*    #myModal {
            width: 900px;	 SET THE WIDTH OF THE MODAL 
            margin: -250px 0 0 -450px;  CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) 
        }*/

    #myModal {
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px; 
    } 


    /*Add by Akkarapol, 27/08/2013, เธ—เธณ autocomplete เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� Product Code เน�เธฅเธฐเน€เธกเธทเน�เธญเธ�เธฅเธดเธ�เธ—เธตเน� list product เธ”เธฑเธ�เธ�เธฅเน�เธฒเธง เธ�เน�เธ�เธฐเน�เธซเน� auto input to datatable*/

    .suggestionsBox {
        position: absolute;
        width: 200px;
        background-color: #f2f2f2;
        -moz-border-radius: 2px;
        -webkit-border-radius: 2px;
        border: 1px solid #333;
        color:#333;
        /*margin-top: 10px;*/
        margin-top:1px;
        margin-right: 0px;
        margin-bottom: 0px;
        margin-left: 0px;
        padding-right: 2px;
        padding-left: 2px;
        font-size:11px;	
        z-index:100;
    }

    .suggestionList {
        margin: 0px;
        padding-top: 0px;
        padding-right: 2px;
        padding-bottom: 2px;
        padding-left: 2px;
        height:200px;
        overflow:scroll;
    }

    .suggestionList ul {
        list-style:none;
    }

    .suggestionList li {
        margin: 0px 0px 3px 0px;
        padding: 3px;
        cursor: pointer;
        list-style-type: none;
        /*background-color: #E8E8E8;*/
        color:#000000;
    }

    .suggestionList li:hover {
        background-color: #659CD8;
    }
    /* END Add by Akkarapol, 27/08/2013, เธ—เธณ autocomplete เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� Product Code เน�เธฅเธฐเน€เธกเธทเน�เธญเธ�เธฅเธดเธ�เธ—เธตเน� list product เธ”เธฑเธ�เธ�เธฅเน�เธฒเธง เธ�เน�เธ�เธฐเน�เธซเน� auto input to datatable*/


/*    .tooltip {
        left: 180px !important;
        width: 200px !important;
    }*/
</style>
<div class="well">
    <form id="action_form" method=post action="" >
        <fieldset class="well">
            <legend>&nbsp;Order&nbsp;</legend>
            <?php
            if (isset($flow_id)) {
                echo form_hidden('flow_id', $flow_id);
            }
            if (isset($order_id)) {
                echo form_hidden('order_id', $order_id);
            }
            if (!isset($owner_id)) {
                $owner_id = "";
            }
            if (!isset($renter_id)) {
                $renter_id = "";
            }
            if (!isset($shipper_id)) {
                $shipper_id = "";
            }
            if (!isset($consignee_id)) {
                $consignee_id = "";
            }
            if (!isset($receive_type)) {
                $receive_type = "";
            }
            if (!isset($est_receive_date)) {
                $est_receive_date = "";
            }
            if (!isset($remark)) {
                $remark = "";
            }
            if (!isset($process_type)) {
                $process_type = $data_form['process_type'];
            }
            if (!isset($document_no)) {
                $document_no = "";
            }
            if (!isset($doc_refer_int)) {
                $doc_refer_int = "";
            }
            if (!isset($doc_refer_ext)) {
                $doc_refer_ext = "";
            }
            if (!isset($doc_refer_inv)) {
                $doc_refer_inv = "";
            }
            if (!isset($doc_refer_ce)) {
                $doc_refer_ce = "";
            }
            if (!isset($doc_refer_bl)) {
                $doc_refer_bl = "";
            }
            if (!isset($receive_date)) {
                $receive_date = "";
            }

            if (!isset($vendor_id)) {
                $vendor_id = "";
            }
            if (!isset($driver_name)) {
                $driver_name = "";
            }
            if (!isset($car_no)) {
                $car_no = "";
            }

            if ((!isset($is_pending)) || ($is_pending != 'Y')) {
                $is_pending = FALSE;
            } else {
                $is_pending = TRUE;
            }
            ?>
            <?php echo form_hidden('process_id', $process_id); ?>
            <?php echo form_hidden('present_state', $present_state); ?>
            <?php echo form_hidden('user_id', $user_id); ?>
            <?php echo form_hidden('process_type', $process_type); ?>
            <?php echo form_hidden('owner_id', $owner_id); ?>
            <table width="98%">
                <tr>
                    <td align="right">Renter</td>
                    <td align="left"><?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'class="required"'); ?></td>
                    <td align="right">Shipper</td>
                    <td align="left"><?php echo form_dropdown('shipper_id', $shipper_list, $shipper_id, 'class="required" '); ?></td>
                    <td align="right">Consignee</td>
                    <td align="left"><?php echo form_dropdown('consignee_id', $consignee_list, $consignee_id, 'class="required"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Document No.</td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate GRN" disabled class="required document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Document External</td>
                    <td align="left"><?php echo form_input('doc_refer_ext', trim($doc_refer_ext), 'placeholder="' . DOCUMENT_EXT . '" class="required document " style="text-transform: uppercase"'); ?></td>
                    <td align="right">Document Internal</td>
                    <td align="left"><?php echo form_input('doc_refer_int', trim($doc_refer_int), 'placeholder="' . DOCUMENT_INT . '"  class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Invoice No.</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', trim($doc_refer_inv), 'placeholder="' . DOCUMENT_INV . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry	</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', trim($doc_refer_ce), 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No.</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', trim($doc_refer_bl), 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Receive Type</td>
                    <td align="left"><?php echo form_dropdown('receive_type', $receive_list, $receive_type, "onChange=changeOption(this)"); ?></td>
                    <td align="right">Receive Date.</td>
                    <td align="left"><?php echo form_input('receive_date', date("d/m/Y"), 'id="receive_date" placeholder="Receive Date" readonly="readyonly"'); ?></td>
                    <td align="right">Worker Name</td>
                    <td align="left"><?php echo form_dropdown('assigned_id', $assign_list, $assigned_id, " class='required' "); ?></td>

                </tr>
                <tr style='display:none'>
                    <td align="right">Vendor</td>
                    <td align="left"><?php echo form_dropdown('vendor_id', $vendor_list, $vendor_id, ''); ?></td>
                    <td align="right">Driver Name</td>
                    <td align="left"><?php echo form_input('driver_name', trim($driver_name), 'placeholder="' . DRIVER_NAME . '" '); ?></td>
                    <td align="right">Car No.</td>
                    <td align="left"><?php echo form_input('car_no', trim($car_no), 'placeholder="' . CAR_NO . '" '); ?></td>
                </tr>
                <tr valign="center">
                    <td align="right">Remark</td>
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo trim($remark); ?></TEXTAREA>
			</td>
			<td align="right"></td>
			<td align="left" style='display:none'>
                        <?php echo form_checkbox('is_pending', ACTIVE, false); ?>&nbsp;Pending
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox('is_repackage', ACTIVE, false); ?>&nbsp;Re-Package</td>
		  </tr>
	  </table>
	</fieldset>
	</form>
	<fieldset class="well">
	<legend>&nbsp;Order Detail&nbsp;</legend>
	  <table width="100%">
		  <tr align="center" >
			<td align="center" colspan="6" id="showDataTable" >
				<table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
					<thead>
                            <?php
                            $show_column = array(
                                _lang('no'),
                                _lang('product_code'),
                                _lang('product_name'),
                                _lang('product_status'),
                                _lang('product_sub_status'),
                                _lang('lot'),
                                _lang('serial'),
                                _lang('product_mfd'),
                                _lang('product_exp'),
                                _lang('receive_qty'),
                                _lang('confirm_qty'),
                                _lang('unit'),
                                _lang('from_location'),
                                _lang('suggest_location'),
                                _lang('actual_location'),
                                _lang('remark'),
                                "Product_Id",
                                "Product_Status",
                                "Product_Sub_Status",
                                "Unit_Id",
                                "Item_Id",
                                "Suggest_Location_Id",
                                "Actual_Location_Id",
                                "Old_Location_Id"
                            );
                            $str_header = "";
                            foreach ($show_column as $index => $column) {
                                $str_header .= "<th>" . $column . "</th>";
                            }
                            ?>
						<tr><?php echo $str_header; ?></tr>
					</thead>
                        <?php
                        if (isset($pending_detail)) {
                            $str_body = "";
                            $count_rows = 1;
                            $sum_Receive_Qty = 0;   //add by kik : 25-10-2013   
                            $sum_Cf_Qty = 0;        //add by kik : 25-10-2013
                            foreach ($pending_detail as $order_column) {
//                                $str_body .= '<tr title="' . $order_column->Full_Product_Name . '" >';
//                                $str_body .= "<tr title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">"; // Edit by Ton! 20131127

                                $str_body .= "<tr>";
                                $str_body .= "<td>" . $count_rows . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Code . "</td>";
                                // comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์
                                // $str_body .= "<td title='" . $order_column->Full_Product_Name . "' >" . $order_column->Product_Name . "</td>";
                                // END comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์                              
                                // Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
//                                $str_body .= '<td >' . $order_column->Product_Name . '</td>';
//                                $str_body .= "<td title='" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "' >" . $order_column->Product_Name . "</td>";
                                $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                // END Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
                                $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>"; // Edit By Akkarapol, 03/09/2013, แก้ไขจาก Product_Sub_Status เป็น Sub_Status_Value เนื่องจากตัวของ Product_Sub_Status นั้นแสดงผลผิดพลาด
                                $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                $str_body .= "<td style='text-align: right;'>" . $order_column->Reserv_Qty . "</td>";
                                $str_body .= "<td style='text-align: right;'>" . $order_column->Confirm_Qty . "</td>";
                                $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                if ($order_column->Old_Location == "") {
                                    $order_column->Old_Location = $order_column->Actual_Location;
                                }
                                $icon_old_loc = '<a ONCLICK="showProdInLoc(\'' . $order_column->Old_Location_Id . '\',\'' . $order_column->Old_Location . '\')">' . img("css/images/icons/view.png") . '</a>';
                                $icon_suggest_loc = '<a ONCLICK="showProdInLoc(\'' . $order_column->Suggest_Location_Id . '\',\'' . $order_column->Suggest_Location . '\')">' . img("css/images/icons/view.png") . '</a>';

                                $str_body .= "<td>" . $order_column->Old_Location . " " . $icon_old_loc . "</td>";
                                $str_body .= "<td>" . $order_column->Suggest_Location . " " . $icon_suggest_loc . "</td>";
                                $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                                $str_body .= "<td>" . $order_column->Remark . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                                $str_body .= "<td>" . $order_column->Status_Code . "</td>";
                                $str_body .= "<td>" . $order_column->Sub_Status_Code . "</td>";
                                $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                                $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                                $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                                $str_body .= "<td>" . $order_column->Actual_Location_Id . "</td>";
                                $str_body .= "<td>" . $order_column->Old_Location_Id . "</td>";
                                $str_body .= "</tr>";
                                $count_rows++;
                                $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                $sum_Cf_Qty+=$order_column->Confirm_Qty;            //add by kik    :   25-10-2013
                            }
                            echo $str_body;
                        }
                        ?>
					<tbody></tbody>
                                         <!-- show total qty : by kik : 28-10-2013-->
                                        <tfoot>
                                           <tr>
                                                 <th colspan='9' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo number_format(@$sum_Receive_Qty); ?></span></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo number_format(@$sum_Cf_Qty); ?></span></th>
                                                 <th colspan='11' class ='ui-state-default indent' ></th>
                                            </tr> 
                                        </tfoot>
                                        <!-- end show total qty : by kik : 28-10-2013-->       
				</table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">List Product In Location</h3>
        <input  value="" type="hidden" name="prdModalval" id="prdModalval" >
    </div>
    <div class="modal-body"><!-- working area--></div>
    <div class="modal-footer"></div>
</div>
