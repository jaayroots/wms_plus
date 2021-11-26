<script>

    // Get value Form
        var site_url = '<?php echo site_url(); ?>';
        var curent_flow_action = ''; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
        var data_table_id_class = '#showProductTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
        var redirect_after_save = site_url + "/flow/flowPutawayList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
        var global_module = '';
        var global_sub_module = '';
        var global_action_value = '';
        var global_next_state = '';
        var global_data_form = '';
        var from_State = "<?php echo $from_State; ?>"; //BY POR 2013-10-03 บอกให้รู้ว่าอยู่ในขั้นตอน Confirm (=5) หรือ Approve
        var allVals = new Array();
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
        var allVals = new Array();
        var separator = "<?php echo SEPARATOR; ?>";
        var form_name = "form_receive";
    // end of get value form


    // Read config from controller
        var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';
        var conf_inv = '<?php echo ($conf_inv)?true:false ?>';
        var conf_cont = '<?php echo ($conf_cont)?true:false ?>';
        var statusprice = '<?php echo ($statusprice)?true:false;?>';

//        alert(conf_inv);
//        alert(conf_cont);
//        alert(statusprice);
//        exit();
//


    // endof read config from controller


    // Assign index of DataTable
        var ci_prod_code = 1;
        var ci_lot = 5;
        var ci_serial = 6;
        var ci_mfd = 7;
        var ci_exp = 8;
        var ci_invoice = 9;
        var ci_container = 10;
        var ci_reserv_qty = 11;
        var ci_confirm_qty = 12;

        //Add By por 2014-01-14 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
        var ci_price_per_unit = 14;
        var ci_unit_price = 15;
        var ci_all_price = 16;
        // end of price per unit

        var ci_pallet_id = 20;
        var ci_remark = 21;
//        var ci_split_info = 22;

        //Define Hidden Field Datatable
        var ci_prod_id = 22;
        var ci_prod_status = 23;
        var ci_prod_sub_status = 24;
        var ci_unit_id = 25;
        var ci_item_id = 26;

        var ci_suggest_loc = 27;
        var ci_actual_loc = 28;
        var ci_putaway_by = 29;
        var ci_putaway_date = 30;
        var ci_unit_price_id = 31;

        var ci_list = [
            {name: 'ci_prod_code', value: ci_prod_code},
            {name: 'ci_lot', value: ci_lot},
            {name: 'ci_serial', value: ci_serial},
            {name: 'ci_mfd', value: ci_mfd},
            {name: 'ci_exp', value: ci_exp},
            {name: 'ci_reserv_qty', value: ci_reserv_qty},
            {name: 'ci_remark', value: ci_remark},
            {name: 'ci_prod_id', value: ci_prod_id},
            {name: 'ci_prod_status', value: ci_prod_status},
            {name: 'ci_unit_id', value: ci_unit_id},
            {name: 'ci_item_id', value: ci_item_id},
            {name: 'ci_prod_sub_status', value: ci_prod_sub_status},
            {name: 'ci_confirm_qty', value: ci_confirm_qty},
            {name: 'ci_suggest_loc', value: ci_suggest_loc},
            {name: 'ci_actual_loc', value: ci_actual_loc},
//            {name: 'ci_split_info', value: ci_split_info}, // Add By Akkarapol, 04/09/2013, เพิ่มเพื่อให้ช่อง Split Info ไม่แสดงในขั้นตอนของการ Putatway
            {name: 'ci_putaway_by', value: ci_putaway_by}, //ADD BY POR 2013-11-08 เพิ่มให้ส่งค่า Activity_By ไปด้วย
            {name: 'ci_putaway_date', value: ci_putaway_date}, //ADD BY POR 2013-11-08 เพิ่มให้ส่งค่า Activity_Date ไปด้วย
            //Add By por 2014-01-09 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
            {name: 'ci_price_per_unit', value: ci_price_per_unit},
            {name: 'ci_unit_price', value: ci_unit_price},
            {name: 'ci_all_price', value: ci_all_price},
            {name: 'ci_unit_price_id', value: ci_unit_price_id},
            // end of price per unit
            {name: 'ci_invoice', value: ci_invoice},
            {name: 'ci_container', value: ci_container}
        ]
    // end of assign index of DataTable

    $(document).ready(function() {

        // Add By Ball
        // Prevent unwant redirect pages
        window.onbeforeunload = function() {
            return "You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work?";
        };

        // If found key F5 remove unload event!!
        $(document).bind('keydown keyup', function(e) {
            if (e.which === 116) {
                window.onbeforeunload = null;
            }
            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }
        });
        // Add By Ball

        // Add By Akkarapol, 20/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 20/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        //BY POR 2013-10-03 ถ้าอยู่ใน step confirm ให้เรียก initProductTableCon(); ถ้าไม่ใช่ให้เรียก initProductTableApp(); ต่างกันที่การแสดงผล
//        if (from_State == 5) {
//            initProductTableCon();
//        } else {
            initProductTable();
//        }


        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._-]+$/i.test(value);
        }, "Document Format is invalid.");

        if ("RCV002" == $("[name='receive_type']").val()) {
            $("[name='is_pending']").attr("disabled", true);
            $("[name='is_repackage']").attr("disabled", false);
        } else {
            $("[name='is_repackage']").attr("disabled", true);
            $("[name='is_pending']").attr("disabled", false);
        }

        $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", true);

        $("[name='is_urgent']").attr("disabled", false);
        
         $('#div_for_modal_message').on('hidden.bs.modal', function (e) {
            $("[name='renter_id']").attr("disabled", true); 
            $("[name='shipper_id']").attr("disabled", true);
            $("[name='consignee_id']").attr("disabled", true);
            $("[name='document_no']").attr("disabled", true); 
            $("[name='doc_refer_ext']").attr("disabled", true);
            $("[name='doc_refer_int']").attr("disabled", true);
            $("[name='receive_type']").attr("disabled", true);
            $("[name='doc_refer_inv']").attr("disabled", true);
            $("[name='doc_refer_ce']").attr("disabled", true);
            $("[name='doc_refer_bl']").attr("disabled", true);
            $('#est_receive_date').attr("disabled", true);
            $("[name='receive_date']").attr("disabled", true);
            $("[name='vendor_id']").attr("disabled", true);
            $("[name='driver_name']").attr("disabled", true);
            $("[name='car_no']").attr("disabled", true);
            $("[name='is_pending']").attr("disabled", true);    
            $("[name='is_repackage']").attr("disabled", true);   
            $('#remark').attr("disabled", true);
        });

    });


    function initProductTable() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "iDisplayLength"    : 250,
            "sPaginationType": "full_numbers",
            "bScrollCollapse": false,
            "bPaginate": false,
            "sDom": '<"H"lfr>t<"F"ip>',
//            "aoColumnDefs": [
//                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
//                {"sWidth": "12%", "sClass": "left_text", "aTargets": [2]},
//                {"sWidth": "5%", "sClass": "left_text obj_status", "aTargets": [3]}, // Edit by Ton! 20131001
//                {"sWidth": "5%", "sClass": "left_text obj_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [5]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [6]},
//                {"sWidth": "5%", "sClass": "center obj_mfg", "aTargets": [7]},
//                {"sWidth": "5%", "sClass": "center obj_exp", "aTargets": [8]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [9]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [10]},
//                {"sWidth": "3%", "sClass": "right_text set_number_format", "aTargets": [11]},
//                {"sWidth": "3%", "sClass": "right_text set_number_format", "aTargets": [12]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [13]},
//                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [14]},
//                {"sWidth": "7%", "sClass": "center", "aTargets": [15]},
//                {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [16]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [17]},
//                {"sWidth": "5%", "sClass": "center", "aTargets": [18]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [19]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [20]},
//                {"sWidth": "5%", "sClass": "left_text", "aTargets": [21]}
//            ]


        }).makeEditable({
            sUpdateURL: function(value, settings) {
                return value;
            }
            , "aoColumns": [
                        null    //No.
                        , null  //Product Code.
                        , null  //Product Name.
                        , null  //Status
                        , null  //Sub Status
                        , null  //Lot
                        , null  //Sel
                        , null  //Mfd
                        , null  //Exp
                        , null  //Invoice No.
                        , null  //Container No.
                        , null  //Receive Qty.
                        , null  //Confirm Qty.
                        , null  //Unit
//                        , null  //Price per unit
//                        , null  //Unit Price
                        {priceperunit}  //Price per unit
                        {unitofprice}   //Unit Price
                        ,null   //All Price
                        , null  //Suggest Loc
                        , null  //Actual Loc
                        , null  //Putaway By
                        , null  //Pallet Code
//                        , null  //Remark
                        , {       //Remark
                            onblur: 'submit',
                            event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
                        }
            ]
        });


        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_actual_loc, false);
//        $('#showProductTable').dataTable().fnSetColumnVis(ci_split_info, false); // Add By Akkarapol, 04/09/2013, เพิ่มเพื่อให้ช่อง Split Info ไม่แสดงในขั้นตอนของการ Putatway
        $('#showProductTable').dataTable().fnSetColumnVis(ci_putaway_by, false); //ADD BY POR 2013-11-08 เพิ่มให้ส่งค่า Activity_By ไปด้วย
        $('#showProductTable').dataTable().fnSetColumnVis(ci_putaway_date, false); //ADD BY POR 2013-11-08 เพิ่มให้ส่งค่า Activity_Date ไปด้วย
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);


        // Set show/hide follow config xml
        if(!built_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
            $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_id, false);
        }

        if(!statusprice){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }

        if(!conf_inv){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_invoice, false);
        }

        if(!conf_cont){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_container, false);
        }

        // end of set show/hide follow config xml


        $('#showProductTable tbody tr td[title]').hover(function() {

            // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา
            var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;
            if(chk_title != chk_innerHTML){
                $(this).show_tooltip();
            }
            // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา

        }, function() {
            $(this).hide_tooltip();
        });

        new FixedColumns(oTable, {
            "iLeftColumns": 0,
            "sLeftWidth": 'relative',
            "iLeftWidth": 0
        });

    } // end of dataTable




    function postRequestAction(module, sub_module, action_value, next_state, elm) {
        global_module = module;
        global_sub_module = sub_module;
        global_action_value = action_value;
        global_next_state = next_state;
		curent_flow_action = $(elm).data('dialog');

        var statusisValidateForm = validateForm();
        var str;
        if (statusisValidateForm === true) {
            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }



            for (i in rowData) {

//                // Add By Akkarapol, 11/03/2014, เพิ่มการตรวจสอบว่าหากเลือก Sub Status เป็น Return หรือ Re-Pack จะต้องเลือก Product Status = Normal เท่านั้น
//                prod_status = rowData[i][ci_prod_status];
//                prod_sub_status = rowData[i][ci_prod_sub_status];
//                if (prod_sub_status == '<?php echo $sub_status_return; ?>' || prod_sub_status == '<?php echo $sub_status_repackage; ?>') {
//                    if (prod_status != 'NORMAL') {
//                        alert("This Sub Status Use On Product Status Must be 'Normal' Only!!");
//                        return false;
//                    }
//                }
//                // END Add By Akkarapol, 11/03/2014, เพิ่มการตรวจสอบว่าหากเลือก Sub Status เป็น Return หรือ Re-Pack จะต้องเลือก Product Status = Normal เท่านั้น


                str = rowData[i][ci_confirm_qty]; //EDIT BY POR 2013-11-29 แก้ไขตัวแปรจาก qty เป็น str
                qty=str.replace(/\,/g,'');

                if (qty == "") {
                    alert('Please fill all Receive Qty');
                    return false;
                }
                qty = parseInt(qty);

                if (qty < 0) {
                    alert('Negative Receive Qty is not allow');
                    return false;
                }

                // Add By Akkarapol, 03/09/2013, เพิ่มส่วนการตรวจสอบจำนวน Receive Qty ต้องเท่ากับ Confirm Qty ถ้าหากไม่เท่าจะให้ Alert เตือน
                var is_repackage = '<?= $is_repackage ?>';
                if (is_repackage == 'Y') { // Add By Akkarapol, 03/09/2013, เพิ่มการตรวจสอบว่า ถ้าเป็น Repackage ถึงจะเช็คว่าให้ Qty สองค่าเท่ากัน แต่หากเป็นแบบอื่น สามารถ Receive แบบ Partial ได้
                    reserv_qty = rowData[i][ci_reserv_qty];
                    confirm_qty = rowData[i][ci_confirm_qty];
                    if (parseInt(confirm_qty) != parseInt(reserv_qty)) {
                        alert("Please Check Infomation Receive Qty = '" + reserv_qty + "' But Confirm Qty = '" + confirm_qty + "'");
                        return false;
                    }
                }
                // END Add By Akkarapol, 03/09/2013, เพิ่มส่วนการตรวจสอบจำนวน Receive Qty ต้องเท่ากับ Confirm Qty ถ้าหากไม่เท่าจะให้ Alert เตือน
            }

            //if (confirm("Are you sure to action " + action_value + "?")) {
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
                    var prod_data = "";
                    $.each(oTable[i], function(idx, elm_val)
                    {
                        prod_data += strip(elm_val) + separator;
                    });
                    prod_data = prod_data.substring(0, prod_data.length - 3);
                    //var prod_data = oTable[i].join(separator);
                    //console.log(prod_data);
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

                global_data_form = $("#" + form_name).serialize();
                var message = "";

//                if(global_sub_module != 'rejectAction' && global_sub_module !='rejectAndReturnAction'){
//                    validation_data();
//                }else{
                    var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                    $('#div_for_alert_message').html(mess);
                    $('#div_for_modal_message').modal('show').css({
                        'margin-left': function() {
                            return ($(window).width() - $(this).width()) / 2;
                        }
                    });
//                }


//                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
//                    switch (data.status) {
//                        case 'C001':
//                            window.onbeforeunload = null;
//                            message = "Save Putaway Complete";
//                            break;
//                        case 'C002':
//                            window.onbeforeunload = null;
//                            message = "Confirm Putaway Complete";
//                            break;
//                        case 'C003':
//                            window.onbeforeunload = null;
//                            message = "Approve Putaway Complete";
//                            break;
//                        case 'C005':        // add by kik : 12-11-2013
//                            window.onbeforeunload = null;
//                            message = "Reject and Return Putaway Complete";
//                            break;
//                        case 'C006':        // add by kik : 27-11-2013
//                            window.onbeforeunload = null;
//                            message = "Reject Putaway Complete";
//                            break;
//                        case 'E001':
//                            message = "Save Putaway Incomplete";
//                            break;
//                    }
//                    alert(message);
//                    url = "<?php echo site_url(); ?>/flow/flowPutawayList";
//                    redirect(url);
//                }, "json");
            //}
        } else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPutawayList";
            redirect(url);
        }
    }

    function validateForm() {
        $("form").validate({
            rules: {
                renter_id: {required: true}
                , shipper_id: {required: true}
                , consignee_id: {required: true}
                , doc_refer_ext: {required: true}
                , receive_date: {required: true}
            }
        });
        return $("form").valid();
    }

    function changeOption(obj) {
        if ("RCV002" == obj.value) {
            $("[name='is_pending']").prop('checked', false);
            $("[name='is_pending']").attr("disabled", true);
            $("[name='is_repackage']").attr("disabled", false);
        } else {
            $("[name='is_repackage']").prop('checked', false);
            $("[name='is_repackage']").attr("disabled", true);
            $("[name='is_pending']").attr("disabled", false);
        }
    }

    function viewSplitInfo(item_id) {
        var url = "<?php echo site_url(); ?>/product_info/splitProd/" + item_id;
        window.open(url, 'TheWindow');
    }


    // Add function calculate_qty : by kik : 24-10-2013
    function calculate_qty() {
        var rowData = $('#showProductTable').dataTable().fnGetData();
        var rowData2 = $('#showProductTable').dataTable();

        var num_row = rowData.length;
        var sum_reserv_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมด

        for (i in rowData) {
            var tmp_qty = 0;
            var tmp_price = 0;//ราคาต่อหน่วย
            var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ


            //-----EDIT BY POR 2013-11-28 แปลงตัวเลขให้อยู่ในรูปแบบคำนวณได้
            var str = rowData[i][ci_reserv_qty];
            rowData[i][ci_reserv_qty] = str.replace(/\,/g, '');
            tmp_qty = parseFloat(rowData[i][ci_reserv_qty]);

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
            }

            //+++++ADD BY POR 2014-01-09 เพิ่มการคำนวณราคา
            var str2 = rowData[i][ci_price_per_unit]; //ราคาต่อหน่วย
            rowData[i][ci_price_per_unit] = str2.replace(/\,/g, '');
            tmp_price = parseFloat(rowData[i][ci_price_per_unit]);
            if (!($.isNumeric(tmp_price))) {
                tmp_price = 0;
            }

            //นำ qty มาคูณกับราคาต่อหน่วย เพื่อหาราคาทั้งหมดต่อหนึ่งรายการ
            all_price = tmp_price * tmp_qty;

            sum_price = sum_price + tmp_price; //รวมราคาทุกรายการต่อหน่วย
            sum_all_price = sum_all_price + all_price; //รวมราคาทั้งหมด

            rowData2.fnUpdate(set_number_format(all_price), parseInt(i), ci_all_price); //update ราคารวมทั้งหมดใน datatable
            //END ADD

            sum_reserv_qty = sum_reserv_qty + tmp_qty;
        }

        $('#sum_all_qty').html(set_number_format(sum_reserv_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมด

    }

    // end function calculate_qty : by kik : 24-10-2013






    // Add function calculate_qty : by kik : 28-10-2013
//    function calculate_qty() {
//        var rowData = $('#showProductTable').dataTable().fnGetData();
//        var num_row = rowData.length;
//        var sum_cf_qty = 0;
//        for (i in rowData) {
//            var tmp_qty = 0;
//
//            //+++++ADD BY POR 2013-11-28 แปลงตัวเลขให้อยู่ในรูปแบบคำนวณได้
//            var str=rowData[i][ci_confirm_qty];
//            rowData[i][ci_confirm_qty]=str.replace(/\,/g,'');
//
//            //tmp_qty = parseInt(rowData[i][ci_confirm_qty]);
//            tmp_qty = parseFloat(rowData[i][ci_confirm_qty]);
//
//            if (!($.isNumeric(tmp_qty))) {
//                tmp_qty = 0;
//            }
//
//            sum_cf_qty = sum_cf_qty + tmp_qty;
//            alert(sum_cf_qty);exit();
//        }
//
//        $('#sum_cf_qty').html(set_number_format(sum_cf_qty));
//
//    }
    // end function calculate_qty : by kik : 28-10-2013


    // Add By Akkarapol, 20/11/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Putaway Job
    function exportFile(file_type) {
        if (file_type == 'PDF') {
//            console.log($("#frmPicking"));
            $("#frmFlowId").attr('action', "<?php echo site_url(); ?>" + "/report/export_putaway_pdf")
        }
        $("#frmFlowId").submit();
    }
    // END Add By Akkarapol, 20/11/2013, เพิ่มฟังก์ชั่นสำหรับการ Print Putaway Job

    function exportPallet(document_no) {
        // console.log(document_no);
        // return
        
	if (confirm('Are you want to download all pallet tag?')) {
	// $.get( "<?php echo site_url(); ?>" + "/report/export_putaway_pallet?d=" + document_no, function( data ) {
		// $.each(data, function(i , file){
		// 	window.open('https://www.wmsoncloud.tk/WMS/WebAppForPC/index.php/putaway/auto_print?d=' + file);
		// });
	// }, "JSON");
    window.open('<?php echo site_url(); ?>/counting/product_tag_call?pallet_list=' + document_no);


	}
    }


</script>
<style>
    /* START Custom position */
/*    .tooltip {
        left: 180px !important;
        width: 200px !important;
    }*/
    /* END Custom position*/
</style>
<div class="well">

    <!--Add By Akkarapol, 20/11/2013, เพิ่ม form สำหรับใช้เก็บค่า flow_id เพื่อส่งค่าไป Generate PDF-->
    <form class="" method="POST" action="" id="frmFlowId" name="frmFlowId" target="_blank" >
        <?php echo form_hidden('flow_id', $flow_id); ?>
        <?php echo form_hidden('showfooter','show'); //ADD BY POR 2013-12-19 ใช้สำหรับบอกว่าให้แสดง footer ที่มีให้เซ็น ตอน พิมพ์ PDF หรือไม่ ?>
    </form>
    <!--END Add By Akkarapol, 20/11/2013, เพิ่ม form สำหรับใช้เก็บค่า flow_id เพื่อส่งค่าไป Generate PDF-->

    <form id="form_receive" method=post action="">
        <?php
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
            $est_receive_date = date("d/m/Y");
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
            $receive_date = date("d/m/Y");
        }

        if ((!isset($is_pending)) || ($is_pending != 'Y')) {
            $is_pending = false;
        } else {
            $is_pending = true;
        }

        if ((!isset($is_repackage)) || ($is_repackage != 'Y')) {
            $is_repackage = false;
        } else {
            $is_repackage = true;
        }
        if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
            $is_urgent = false;
        } else {
            $is_urgent = true;
        }
        ?>
        <?php
        if (isset($flow_id)) {
            echo form_hidden('flow_id', $flow_id);
        }
        ?>
        <?php
        if (isset($order_id)) {
            echo form_hidden('order_id', $order_id);
        }
        ?>
        <?php echo form_hidden('process_id', $process_id); ?>
        <?php echo form_hidden('present_state', $present_state); ?>
        <?php echo form_hidden('user_id', $user_id); ?>
        <?php echo form_hidden('process_type', $process_type); ?>
        <?php echo form_hidden('owner_id', $owner_id); ?>
        <fieldset class="well">
            <legend>&nbsp;&nbsp;<b>Order Receive</b>&nbsp;&nbsp;</legend>
            <table width="98%">
                <tr>
                    <td align="right"><?php echo _lang("renter"); ?></td>
                    <td align="left"><?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'class="required"'); ?></td>
                    <td align="right"><?php echo _lang("shipper"); ?></td>
                    <td align="left"><?php echo form_dropdown('shipper_id', $shipper_list, $shipper_id, 'class="required" '); ?></td>
                    <td align="right"><?php echo _lang("consignee"); ?></td>
                    <td align="left"><?php echo form_dropdown('consignee_id', $consignee_list, $consignee_id, 'class="required"'); ?></td>
                </tr>
                <tr>
                    <td align="right"><?php echo _lang("document_no"); ?></td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate GRN" disabled class="required document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("document_external"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_ext', trim($doc_refer_ext), 'placeholder="' . DOCUMENT_EXT . '" class="required document " style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("document_internal"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_int', trim($doc_refer_int), 'placeholder="' . DOCUMENT_INT . '"  class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right"><?php echo _lang("invoice_no"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_inv', trim($doc_refer_inv), 'placeholder="' . DOCUMENT_INV . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("customs_entry"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_ce', trim($doc_refer_ce), 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("bl_no"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_bl', trim($doc_refer_bl), 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right"><?php echo _lang("receive_type"); ?></td>
                    <td align="left"><?php echo form_dropdown('receive_type', $receive_list, $receive_type, "onChange=changeOption(this)"); ?></td>
                    <td align="right"><?php echo _lang("asn"); ?></td>
                    <td align="left"><?php echo form_input('est_receive_date', $est_receive_date, 'id="est_receive_date" placeholder="Advance Shipment Notice" class="required" readonly="readyonly" '); ?></td>
                    <td align="right"><?php echo _lang("receive_date"); ?></td>
                    <td align="left"><?php echo form_input('receive_date', $receive_date, 'id="receive_date" placeholder="Receive Date" readonly="readyonly"'); ?></td>
                </tr>
                <tr>
                    <td align="right"><?php echo _lang("vendor"); ?></td>
                    <td align="left"><?php echo form_dropdown('vendor_id', $vendor_list, $vendor_id, ''); ?></td>
                    <td align="right"><?php echo _lang("driver_name"); ?></td>
                    <td align="left"><?php echo form_input('driver_name', trim($driver_name), 'placeholder="' . DRIVER_NAME . '" '); ?></td>
                    <td align="right"><?php echo _lang("car_no"); ?></td>
                    <td align="left"><?php echo form_input('car_no', trim($car_no), 'placeholder="' . CAR_NO . '" '); ?></td>
                </tr>
                <tr valign="center">
                    <td align="right"></td>
                    <td align="left" valign="top">
                        <?php echo form_checkbox('is_pending', ACTIVE, $is_pending); ?>&nbsp;Pending
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox('is_repackage', ACTIVE, $is_repackage); ?>&nbsp;Re-Package
                        <br>
                        <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                    </td>
                    <td align="right" valign="top">Remark</td>
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo trim($remark); ?></TEXTAREA>
			</td>

		  </tr>
	  </table>
	</fieldset>
	</form>
	<fieldset class="well">
	<legend>&nbsp;&nbsp;<b>Product Detail</b>&nbsp;&nbsp;</legend>
	  <table width="100%">
		  <tr align="center" >
			<td align="center" colspan="6" id="showDataTable" >
				<table class="well display" style="max-width: none" align="center" cellpadding="0" cellspacing="0" border="0" id="showProductTable" >
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
                                _lang('invoice_no'),
                                _lang('container'),
                                _lang('receive_qty'),
                                _lang('confirm_qty'),
                                _lang('unit'),
                                _lang('price_per_unit'),
                                _lang('unit_price'),
                                _lang('all_price'),
                                _lang('suggest_location'),
                                _lang('actual_location'),
                                _lang('putaway_by'),
                                _lang('pallet_code'),
                                _lang('remark'),
//                                "Split Info",
                                "Product_Id",
                                "Product_Status",
                                "Product_Sub_Status",
                                "Unit_Id",
                                "Item_Id",
                                "Suggest_Location_Id",
                                "Actual_Location_Id",
                                "Activity_By",
                                "Activity_Date",
                                "Unit Price ID"
                            );
                            $str_header = "";
                            foreach ($show_column as $index => $column) {
                                $str_header .= "<th>" . $column . "</th>";
                            }
                            ?>
						<tr><?php echo $str_header; ?></tr>
					</thead>
                                        <tbody>

                            <?php
                            $sum_Receive_Qty = 0;   //add by kik : 25-10-2013
                            $sum_Cf_Qty = 0;        //add by kik : 25-10-2013
                            $sumPriceUnit = 0; //ADD BY POR 2014-01-14 ราคารวมทั้งหมดต่อหน่วย
                            $sumPrice = 0; //ADD BY POR 2014-01-14 ราคารวมทั้งหมด
                            if (isset($order_deatil)) {
                                $str_body = "";
                                $j = 1;

                                foreach ($order_deatil as $order_column) {//p($order_column);exit();
                                    $title_remark = ($order_column->Remark != " " && $order_column->Remark != NULL)?" title='".$order_column->Remark."' ":"";
                                    $str_body .= "<tr>";
                                    $str_body .= "<td>" . $j . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Code . "</td>";
//                                    $str_body .= "<td title=\"" . $order_column->Full_Product_Name . "\" >" . $order_column->Product_Name . "</td>";
//                                    $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20131127
                                    $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                    $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                    $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                    $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                    $str_body .= "<td>" . @$order_column->Cont_No." ".@$order_column->Cont_Size_No." ".@$order_column->Cont_Size_Unit_Code . "</td>";
                                    $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";  //add style='text-align: center : by kik : 25-10-2013
                                    $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Confirm_Qty) . "</td>"; //add style='text-align: center : by kik : 25-10-2013
                                    $str_body .= "<td>" . $order_column->Unit_Value . "</td>";

                                    //ADD BY POR 2014-01-14 เพิ่ม price per unit
                                    $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";
                                    $str_body .= "<td>" . @$order_column->unitprice_name . "</td>";
                                    $str_body .= "<td>" . set_number_format(@$order_column->All_Price) . "</td>";
                                    //END ADD

                                    $str_body .= "<td>" . $order_column->Suggest_Location . "</td>";
                                    $str_body .= "<td>" . $order_column->Actual_Location . "</td>";

                                    if ("ATV002" != $order_column->Activity_Code) {

                                    }

                                    $str_body .= "<td>" . ($order_column->Activity_Code != 'PUTAWAY' ? '' : @$order_column->Activity_By_Name) . "</td>"; // Edit By Akkarapol, 20/11/2013, เพิ่มการตรวจสอบว่า ถ้า Activity_Code ไม่ใช่ PUTAWAY ล่ะก็ ไม่ต้องมาแสดง Activity By
                                    $str_body .= "<td>" . @$order_column->Pallet_Code . "</td>";
                                    $str_body .= "<td>" . @$order_column->Reason_Remark . "</td>"; // Edit by Ak, 27/08/2013 , change 'Remark' to 'Reason_Remark' for show remark from HH
                                    //
//                                    $str_body .= "<td align='center'><a ONCLICK=\"viewSplitInfo('" . $order_column->Item_Id . "')\" >" . img("css/images/icons/edit.png") . "</a></td>";

                                    $str_body .= "<td style-\"display:none\">" . $order_column->Product_Id . "</td>";
                                    $str_body .= "<td style-\"display:none\">" . $order_column->Status_Code . "</td>";
                                    $str_body .= "<td style-\"display:none\">" . $order_column->Sub_Status_Code . "</td>";
                                    $str_body .= "<td style-\"display:none\">" . $order_column->Unit_Id . "</td>";
                                    $str_body .= "<td style-\"display:none\">" . $order_column->Item_Id . "</td>";
                                    $str_body .= "<td style-\"display:none\">" . $order_column->Suggest_Location_Id . "</td>";
                                    $str_body .= "<td style-\"display:none\">" . $order_column->Actual_Location_Id . "</td>";
                                    $str_body .= "<td style-\"display:none\">" . @$order_column->Activity_By . "</td>";  //ADD BY POR 2013-11-08 เพิ่มให้ส่ง id ผู้ putaway ไปด้วย
                                    $str_body .= "<td style-\"display:none\">" . $order_column->Activity_Date . "</td>"; //ADD BY POR 2013-11-08 เพิ่มให้ส่ง วันที่ putaway ไปด้วย
                                    $str_body .= "<td style-\"display:none\">" . @$order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-17 เพิ่ม ID ของ unit price

                                    $str_body .= "</tr>";
                                    $j++;
                                    $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                    $sum_Cf_Qty+=$order_column->Confirm_Qty;            //add by kik    :   25-10-2013
                                    $sumPriceUnit+=@$order_column->Price_Per_Unit;
                                    $sumPrice+=@$order_column->All_Price;

                                }
                                echo $str_body;
                            }
                            ?>

                           </tbody>

                            <!-- show total qty : by kik : 25-10-2013-->
                            <tfoot>
                                <tr>
                                     <th class ='ui-state-default indent'   colspan="11" style='text-align: center;'><b>Total</b></th>
                                     <th class ='ui-state-default indent'   style='text-align: right;'><span ><?php echo set_number_format($sum_Receive_Qty); ?></span></th>
                                     <th class ='ui-state-default indent'   style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty); ?></span></th>
                                     <th></th>
                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                     <th></th>
                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                     <th class ='ui-state-default indent'   colspan="13" ></th>
                                     <th class ='ui-state-default indent'   colspan="7" style='display:none'   ></th>
                                </tr>
                            </tfoot>
                            <!-- end show total qty : by kik : 25-10-2013-->

			 </table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>

 <?php $this->load->view('element_modal_message_alert'); ?>
