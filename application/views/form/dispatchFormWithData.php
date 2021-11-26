<script>
    var master_container_size = "";
    var master_container_dropdown_list = '" "';
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Dispatch'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table = '<?php echo ($dispatch_group_item_by_product_code)?"show_ShowDataTableForInsert":"ShowDataTableForInsert"?>';

    var data_table_id_class = '#'+data_table; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
//    alert(data_table_id_class);
    var redirect_after_save = site_url + "/flow/flowDispatchList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';

    // Read config from controller
    var built_pallet = '<?php echo ($conf_pallet) ? true : false; ?>';
    var conf_inv = '<?php echo ($conf_inv) ? true : false ?>';
    var conf_invoice_require = '<?php echo $conf_invoice_require;?>';
    var conf_cont = '<?php echo ($conf_cont) ? true : false ?>';
    var statusprice = '<?php echo ($statusprice) ? true : false; ?>';
    var conf_change_dp_date = '<?php echo ($conf_change_dp_date) ? true : false; ?>';//add real dispatch date for ISSUE 5265 : kik : 20141020


    var form_name = "frmDispatch";
    var separator = "<?php echo SEPARATOR; ?>";

    var arr_index = new Array(
    'no'
    ,'product_code'
    ,'product_name'
    ,'product_status_label'
    ,'product_sub_status_label'
    ,'lot'
    ,'serial'
    ,'product_mfd'
    ,'product_exp'
    ,'invoice_out'
    ,'container_out'
    ,'dispatch_qty'
    ,'confirm_qty'
    ,'unit'
    ,'price_per_unit'
    ,'unit_price'
    ,'all_price'
    ,'suggest_location'
    ,'actual_location'
    ,'picking_by'
    ,'pallet_code_out'
    ,'remark'
    ,'product_id_h'
    ,'product_status_h'
    ,'product_sub_h'
    ,'unit_id_h'
    ,'item_id_h'
    ,'inbound_id_h'
    ,'suggest_id_h'
    ,'actual_id_h'
    ,'price_id_h'
    ,'cont_id_h'
    );

    var ci_prod_code = arr_index.indexOf("product_code");
    var ci_lot = arr_index.indexOf("lot");
    var ci_serial = arr_index.indexOf("serial");
    var ci_mfd = arr_index.indexOf("product_mfd");
    var ci_exp = arr_index.indexOf("product_exp");
    var ci_invoice = arr_index.indexOf("invoice_out");
    var ci_container = arr_index.indexOf("container_out");
    var ci_reserv_qty = arr_index.indexOf("dispatch_qty");
    var ci_confirm_qty = arr_index.indexOf("confirm_qty");
    var ci_pallet_id = arr_index.indexOf("pallet_code_out");
    var ci_remark = arr_index.indexOf("remark");
    //Define Hidden Field Datatable
    var ci_prod_id = arr_index.indexOf("product_id_h");
    var ci_prod_status = arr_index.indexOf("product_status_h");
    var ci_prod_sub_status = arr_index.indexOf("product_sub_h");
    var ci_unit_id = arr_index.indexOf("unit_id_h");
    var ci_item_id = arr_index.indexOf("item_id_h");
    var ci_inbound_id = arr_index.indexOf("inbound_id_h");
    var ci_suggest_loc = arr_index.indexOf("suggest_id_h");
    var ci_actual_loc = arr_index.indexOf("actual_id_h");

     // add by kik : 2014-01-14
    var ci_price_per_unit = arr_index.indexOf("price_per_unit");
    var ci_unit_price = arr_index.indexOf("unit_price");
    var ci_all_price = arr_index.indexOf("all_price");
    var ci_unit_price_id = arr_index.indexOf("price_id_h");
    //end add by kik : 2014-01-14

    var ci_cont_id = arr_index.indexOf("cont_id_h");

    var ci_list = [
            {name: 'ci_prod_code', value: ci_prod_code},
            {name: 'ci_lot', value: ci_lot},
            {name: 'ci_serial', value: ci_serial},
            {name: 'ci_mfd', value: ci_mfd},
            {name: 'ci_exp', value: ci_exp},
            {name: 'ci_confirm_qty', value: ci_confirm_qty},
            {name: 'ci_reserv_qty', value: ci_reserv_qty},
            {name: 'ci_price_per_unit', value: ci_price_per_unit},      // add by kik : 2014-01-14
            {name: 'ci_unit_price', value: ci_unit_price},              // add by kik : 2014-01-14
            {name: 'ci_all_price', value: ci_all_price},                // add by kik : 2014-01-14
            {name: 'ci_remark', value: ci_remark},
            {name: 'ci_prod_id', value: ci_prod_id},
            {name: 'ci_prod_status', value: ci_prod_status},
            {name: 'ci_unit_id', value: ci_unit_id},
            {name: 'ci_item_id', value: ci_item_id},
            {name: 'ci_inbound_id', value: ci_inbound_id},
            {name: 'ci_suggest_loc', value: ci_suggest_loc},
            {name: 'ci_actual_loc', value: ci_actual_loc},
            {name: 'ci_prod_sub_status', value: ci_prod_sub_status},
            {name: 'ci_unit_price_id', value: ci_unit_price_id},       // add by kik : 2014-01-14
            {name: 'ci_cont_id', value: ci_cont_id},
            {name: 'ci_invoice', value: ci_invoice},
            {name: 'ci_container', value: ci_container}
    ]


    $(document).ready(function() {

        $.validator.addMethod("australianDate",function(value, element) {
                return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
            },
            "Please enter a date in the format dd/mm/yyyy."
        );

        // Add By Akkarapol, 19/12/2013, เพิ่ม $('#ShowDataTableForInsert tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
        $('#ShowDataTableForInsert tbody tr td[title]').hover(function() {

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
        // END Add By Akkarapol, 19/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย


        // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 21/09/2013, เพิ่ม onKeyup ของช่อง Document External ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="doc_refer_ext"]').keyup(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onKeyup ของช่อง Document External ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $.post('<?php echo site_url("/receive/getContainerDropdownListOutbound"); ?>', {'order_id' : '<?php echo $order_id ?>'}, function(data) {
            master_container_dropdown_list = data;
            console.log(master_container_dropdown_list);
        });

        $.post('<?php echo site_url() . "/pre_receive/getContainerSize"; ?>', function(data) {
            master_container_size = data;
        });

        $.fn.callInvoiceModal = function () {
            $('#dynamic_modal').modal('show');
        };
        $("#add_container").click(function(){
            console.log($("#container_list").val());
            $('#dynamic_modal').on('show.bs.modal', function (e) {

                var dynamic_modal_body = $("#dynamic_modal_body");
                var container_list = $("#container_list");

                var FieldCount = 1;
                var container_data = $("#doc_refer_container").val();
                var container_size = $("#doc_refer_con_size").val();
                var container_data_confirm = '<?php echo $container_list; ?>'; //ค่านี้จะได้จากตอน confirm and approve

                if (container_list.val() == '{'){
                    container_list.val(container_data_confirm);
                    console.log(container_list.val());
                }

                $('#save_container_data').show();
                $("#dynamic_modal_label").html("Container List");
                if (container_list.val() != "" && container_list.val() != '{}') {   //behide save
                    console.log('xxxxx');
                    var get_list_data = $.parseJSON(container_list.val());
                    console.log(get_list_data);
                    $.each(get_list_data, function(idx2, val2){
                        var objDiv = $("<div>");
                        var objConID2 = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]").val(val2.id);
                        var objInput2 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").val(val2.name).css({"textTransform":"uppercase", "width":"350px"});
                        var objSelect2 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width":"120px"});
                        var _master_container_size2 = $.parseJSON(master_container_size);
                        $.each(_master_container_size2, function(idx, val){
                            var objOption2 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect2);
                                if (val2.size == val.Id){
                                    objOption2.prop("selected", true);
                                }
                        });
                        if (idx2 == 0){
                            //var objImage2 = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
                            objDiv.append(objConID2).append(objInput2).append(objSelect2);
                            dynamic_modal_body.empty().append(objDiv);
                        }else{
                            //var objImage2 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width":"24px", "height":"24px", "marginBotton":"3px", "marginLeft":"3px", "cursor":"pointer"});
                            objDiv.append(objConID2).append(objInput2).append(objSelect2);
                            dynamic_modal_body.append(objDiv);
                        }
                    });
                }else{  //case open
                    var objConID = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]");
                    var objInput = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").val(container_data).css({"textTransform" : "uppercase", "width": "350px"});
                    var objSelect = $("<select>").prop("class", "con_size").prop("class", "con_size").css({"width" : "120px"}).prop("name", "con_size[]").val(container_size);
                    var _master_container_size = $.parseJSON(master_container_size);
                    $.each(_master_container_size, function(idx, val){
                        var objOption = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect);
                        if (container_size == val.Id){
                            objOption.prop("selected", true);
                        }
                    });
                    //var objImage = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
                    dynamic_modal_body.empty().append(objConID).append(objInput).append(objSelect);
                }

                $(".add_more_btn").click(function (e)  //on add input button click
                {
                    FieldCount++; //text box added increment
                    var objDiv = $("<div>");
                    var objConID3 = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]").val('NEW');
                    var objInput3 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").css({"textTransform":"uppercase", "width":"350px"});
                    var objSelect3 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width":"120px"});
                    var _master_container_size3 = $.parseJSON(master_container_size);
                    $.each(_master_container_size3, function(idx, val){
                        var objOption3 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect3);
                    });
                    var objImage3 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width":"24px", "height":"24px", "height":"24px", "marginBottom":"3px", "marginLeft":"3px", "cursor":"pointer"});
                    objDiv.append(objConID3).append(objInput3).append(objSelect3).append(objImage3);
                    dynamic_modal_body.append(objDiv);
                    return false;
                });

                dynamic_modal_body.on("click", ".removeclass", function(e) {
                $(this).parent('div').remove(); //remove text box
                        return false;
                });
            });
            $('#dynamic_modal').modal({
                keyboard: false
                , backdrop: "static"
            });
        });

        $('#save_container_data').click(function (e) {
            if(confirm("Editing container will had been affect to another documents")){
                var temp = {};
                var container_list = $(".container_list");
                var container_list_size = $(".con_size");
                var container_list_id = $(".container_id");
                var container_size_name = '';
                $.each(container_list, function(idx, val){
                    var container_size_id = $(container_list_size[idx]).val();
                    var container_id = $(container_list_id[idx]).val();
                    var container_name = $(val).val();
                    var objTemp = {};
                    objTemp['id'] = container_id;
                    objTemp['name'] = container_name;
                    objTemp['size'] = container_size_id;
                    temp[idx] = objTemp;
                });

                $("#container_list").val(JSON.stringify(temp));
                //SENT VALUE FOR EDIT
                console.log('<?php echo $flow_id?>');
                console.log($("#frmDispatch"));
                $.post('<?php echo site_url() . "/receive/updateContainer" ?>', {order_id:'<?php echo $order_id;?>',flow_id:'<?php echo $flow_id;?>',container_list:$('#container_list').val(),type:'OUTBOUND'}, function(data) {
                    //console.log(data.val);
                    if (data.val =='NO') {
                        alert('Can not save changes');
                    } else {
                        $('#showProductTable').dataTable().makeEditable({
                            data: data,
                            event: 'click',
                            type: 'select',
                            onblur: 'submit',
                            sUpdateURL: function(value, settings) {
                                var oTable = $('#showProductTable').dataTable();
                                var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                                oTable.fnUpdate(value, rowIndex, ci_cont_id);
                                return value;
                            }
                        });
    //                        console.log('POR BA');

                        var newData = jQuery.parseJSON(data);
                        var $el = $("#doc_refer_container");
                        $el.empty(); // remove old options
                        $.each(newData, function(value, label) {
                          $el.append($("<option></option>")
                             .attr("value", value).text(label));
                        });

                        //initProductTable();

                        master_container_dropdown_list = data;

                        alert('Save changes success');

                        $('#dynamic_modal').modal('hide');
                    }
                });
            }else{
                return false;
            }

         });

        initProductTable();
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        $("#estDispatchDate").datepicker().keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#estDispatchDate').datepicker('hide');
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        //add real dispatch date for ISSUE 5265 : kik : 20141020
        $("#realDispatchDate").datepicker().keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._/\\,-]+$/i.test(value);
        }, "Document Format is invalid.");

        $("#" + form_name + " :input").not("[name=realDispatchDate],[name=doc_refer_int],[name=doc_refer_ce],[name=doc_refer_bl],[name=doc_refer_inv],[name=showProductTable_length] ,[name=vendor_id] ,[name=driver_name] ,[name=car_no] ,[name=remark] ").attr("disabled", true);
        $("[name='is_urgent']").attr("disabled", false);
    
         $('#div_for_modal_message').on('hidden.bs.modal', function (e) {
                $('#renter_id_select').attr("disabled", true);
                $('#frm_warehouse_select').attr("disabled", true);
                $('#to_warehouse_select').attr("disabled", true);
                $('#doc_refer_ext').attr("disabled", true);
                $('#dispatch_type_select').attr("disabled", true);
                $('#estDispatchDate').attr("disabled", true);
                $('#dispatch_type_select').attr("disabled", true);
                $('#doc_refer_container').attr("disabled", true);
                $('#dispatchDate').attr("disabled", true);
            
        });

    });


    function postRequestAction(module, sub_module, action_value, next_state, elm) {

	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

        //validate Engine called here.//
        if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction'){
            var statusisValidateForm = validateForm();
        }else{
            var statusisValidateForm = true;
        }

        //alert(statusisValidateForm);
        if (statusisValidateForm === true) {
            var rowData = $('#ShowDataTableForInsert').dataTable().fnGetData();
            if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction'){
                var num_row = rowData.length;
                if (num_row <= 0) {
                    alert("Please Select Product Order Detail");
                    return false;
                }

                //check container head : ADD BY POR 2014-09-10
                /* cancel check input container : BY POR 2014-10-06
                if(conf_cont){
                    if($("#doc_refer_container option").length <= 0){
                        alert("Please input container");
                        return false;
                    }
                }
                */
                //end check container head

                //check invoice by config : Comment by por 2014-10-14
                //check key invoice in datatable:ADD BY POR 2014-09-10
                if(conf_inv){
                    if(conf_invoice_require){
                        for (i in rowData) {
                            invoice = rowData[i][ci_invoice];
                            if(invoice == ""){
                                alert('Please Invoice No.');
                                return false;
                            }
                        }
                    }
                }
                //END ADD

                /*dont check require : Comment by por 2014-10-14
                //check key container in datatable:ADD BY POR 2014-09-10
                if(conf_cont){
                    for (i in rowData) {
                        container = rowData[i][ci_container];
                        if (container == "") {
                            alert('Please input container in line');
                            return false;
                        }
                    }
                }
                //END ADD
                */

                if(global_sub_module != 'quickApproveAction'){

                    for (i in rowData) {

                        reserve_qty = parseFloat(rowData[i][ci_reserv_qty]);
                        confirm_qty = parseFloat(rowData[i][ci_confirm_qty]);
                        if(confirm_qty == ""){
                            alert('Please fill all Reserve Qty');
                            return false;
                        }else if(reserve_qty != confirm_qty) {
                            alert('Please Confirm Qty Must be Equal Dispatch Qty');
                            return false;
                        }

                        if(statusprice==true){
                            price = rowData[i][ci_price_per_unit];
                            if (price == "" || price == 0) {
                                alert('Please fill all Price/Unit');
                                flagSubmit = false;
                                return false;
                            }

                            price = parseFloat(price);
                            if (price < 0) {
                                alert('Negative Price/Unit is not allow');
                                flagSubmit = false;
                                return false;
                            }
                        }

                    } //end of loop check data

                } //end of check state is quickApproveAction


            }// end if check Sub_Module : by kik : 2013-12-11
//            if (confirm("Are you sure to do following action : " + action_value + "?")) {
                $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", false);
                var f = document.getElementById("frmDispatch");
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

                var oTable = $('#ShowDataTableForInsert').dataTable().fnGetData();
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

                global_data_form = $("#frmDispatch").serialize();
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
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function initProductTable() {

        //    var oTable = $('#tbreport').dataTable({
        //		"bJQueryUI": true,
        //		"sScrollY": "300px",
        //		"sScrollX": "100%",
        //		//"sScrollXInner": "200%",
        //		"bScrollCollapse": false,
        //		"bPaginate": false
        //	});
        //
        //});
        //



        //        --#Comment 2013-08-26 #Defect 371
        //        --# Start --

        //	var oTable = $('#ShowDataTableForInsert').dataTable({
        //
        //            "bJQueryUI": true,
        //            "bStateSave" :true,
        //            "bRetrieve": true,
        //            "bDestroy": true,
        //            "sPaginationType": "full_numbers",
        //            "bScrollCollapse": false,
        //	    "bPaginate": false,
        //            "sScrollX": "100%",
        //            "sDom": '<"H"lfr>t<"F"ip>'
        //           }).makeEditable({
        //            "aoColumns": [
        //                null,
        //                null,
        //                null,
        //                null,
        //                null,
        //                null,
        //                null,
        //                null,
        //                null,
        //				null,
        //                {
        //                  sSortDataType: "dom-text",
        //                  sType: "numeric",
        //                  type: 'text',
        //                  onblur:"submit",
        //                  event: 'click',
        //                  cssclass: "required number",
        //                   sUpdateURL: "<? // echo site_url() . '/pre_dispatch/saveEditedRecord';    ?>"
        //                  }
        //				 , null
        //                 , null
        //				 , null
        //				 , null
        //				 ,{onblur: 'submit',sUpdateURL: "<? // echo site_url() . '/pre_dispatch/saveEditedRecord';    ?>"}
        //				 , null
        //				 , null
        //				 , null
        //				 , null
        //                 ]
        //           });
        //           --# End Comment 2013-08-26 #ISSUE NO/#BUG NO --


        //           --#Defect 371
        //           --#DATE:2012-08-26
        //           --#BY:KIK
        //           -- START--
        //           -- ปัญหา  : filde Unit ขึ้นเป็น text box ให้แก้ไข ทั้งๆ ที่ในขั้นตอนนี้ไม่สามารถแก้ไขได้
        //           -- แก้โดย : โค้ดเก่า ใส่ class required number ไว้ comment เอาออกแล้ว
//        alert(data_table_id_class);
        var oTable = $('#'+data_table).dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "bSort": false,
            "iDisplayLength"    : 250,
            "sPaginationType": "full_numbers",
            "bScrollCollapse": false,
            "bPaginate": false,
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [arr_index.indexOf("no")]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("product_code")]},
                {"sWidth": "12%", "sClass": "left_text", "aTargets": [arr_index.indexOf("product_name")]},
                {"sWidth": "6%", "sClass": "left_text obj_status", "aTargets": [arr_index.indexOf("product_status_label")]}, // Edit by Ton! 20131001
                {"sWidth": "6%", "sClass": "left_text obj_sub_status", "aTargets": [arr_index.indexOf("product_sub_status_label")]}, //Edit by Ton! 20131001
                {"sWidth": "6%", "sClass": "left_text", "aTargets": [arr_index.indexOf("lot")]},
                {"sWidth": "6%", "sClass": "left_text", "aTargets": [arr_index.indexOf("serial")]},
                {"sWidth": "6%", "sClass": "center obj_mfg", "aTargets": [arr_index.indexOf("product_mfd")]},
                {"sWidth": "6%", "sClass": "center obj_exp", "aTargets": [arr_index.indexOf("product_exp")]},
                {"sWidth": "5%", "sClass": "right_text", "aTargets": [arr_index.indexOf("invoice_out")]},  //invoice
                {"sWidth": "5%", "sClass": "right_text", "aTargets": [arr_index.indexOf("container_out")]}, //container
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("dispatch_qty")]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("confirm_qty")]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("unit")]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("price_per_unit")]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("unit_price")]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("all_price")]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [arr_index.indexOf("suggest_location")]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [arr_index.indexOf("actual_location")]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [arr_index.indexOf("picking_by")]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("product_code")]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("product_code")]},

            ]
        }).makeEditable({
            "aoColumns": [
                null
                ,null
                ,null
                ,null
                ,null
                ,null
                ,null
                ,null
                ,null
                <?php if($dispatch_group_item_by_product_code): ?>
                    ,null
                <?php else: ?>
                    ,{//invoice
                        onblur: 'submit',
                        sUpdateURL: "<?php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
                        event: 'click'
                    } // end of invoice
                <?php endif; ?>
//                , {
//                    data : master_container_dropdown_list,
//                    event: 'click',
//                    type: 'select',
//                    onblur: 'submit',
//                    is_container: true,
//                    sUpdateURL: function(value, settings) {
//                        var oTable = $('#ShowDataTableForInsert').dataTable();
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        oTable.fnUpdate(value, rowIndex, ci_cont_id);
//                        return value;
//                    }
//                }
                ,null
                ,null
                , null
                , null
                 //ADD BY POR 2014-06-11 แสดงราคาต่อหน่วย, หน่วย, ราคารวม

                <?php if($dispatch_group_item_by_product_code): ?>
                    ,null
                    ,null
                <?php else: ?>
                    {price}           //price
                    {unitofprice}     //unitprice
                <?php endif; ?>
                ,null             //allprice
                //END ADD
                , null
                , null
                , null
                , null //pallet code
//                , {onblur: 'submit', sUpdateURL: "<?//php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>"}
                , null
                , null
                , null
                , null
                , null
            ]
        });

        //        -- END Defect 371--

        $('#'+data_table).dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_unit_id, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_item_id, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_inbound_id, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_actual_loc, false);
        $('#'+data_table).dataTable().fnSetColumnVis(ci_unit_price_id, false);
         $('#'+data_table).dataTable().fnSetColumnVis(ci_cont_id, false);

        if(!built_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
            $('#'+data_table).dataTable().fnSetColumnVis(ci_pallet_id, false);
        }

        if(!conf_inv){
            $('#'+data_table).dataTable().fnSetColumnVis(ci_invoice, false);
        }

        if(!conf_cont){
            $('#'+data_table).dataTable().fnSetColumnVis(ci_container, false);
        }

        if(!statusprice){
            $('#'+data_table).dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#'+data_table).dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#'+data_table).dataTable().fnSetColumnVis(ci_all_price, false);
        }


        new FixedColumns(oTable, {
            "iLeftColumns": 1,
            "sLeftWidth": 'relative',
            "iLeftWidth": 5
        });
    }

    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
    }

    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#ShowDataTableForInsert').dataTable().fnGetData(index);
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
        var f = document.getElementById("frmDispatch");
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowDispatchList";
            redirect(url)
        }
    }

    function validateForm() {
        //validate engine
        var status;
        var index = 0;
        var flag = 0;
        $("form").each(function(i,v) {//focusCleanup: true,
            console.log($(this).valid());
            $(this).validate();
            $(this).valid();
            flag += ($(this).valid() == true ? 1 : 0);
            index++;
        });
        status = (index == flag ? true : false);
        return status;
    }

    function validateDateRange(dateFrom, dateTo, interval) {
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        var checkin = $(dateFrom).datepicker({
            onRender: function(date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function(ev) {
            if (ev.date.valueOf() > checkout.date.valueOf()) {
                var newDate = new Date(ev.date);
                newDate.setDate(newDate.getDate() + 1);
                checkout.setValue(newDate);
            }
            checkin.hide();
            $(dateTo)[0].focus();
        }).data('datepicker');
        var checkout = $(dateTo).datepicker({
            onRender: function(date) {
                return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function(ev) {
            checkout.hide();
        }).data('datepicker');
    }

    //ADD BY POR 2014-06-11 เพิ่มให้มีการคำนวณราคาต่อหน่วย
    function calculate_qty() {
        var rowData = $('#ShowDataTableForInsert').dataTable().fnGetData();
        var rowData2 = $('#ShowDataTableForInsert').dataTable();        //ADD BY POR 2014-06-11

        var num_row = rowData.length;
        var sum_cf_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมด
        for (i in rowData) {
            var tmp_qty = 0;
            var tmp_price = 0;//ราคาต่อหน่วย
            var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ

            //+++++ADD BY POR 2013-11-29 แปลงตัวเลขให้อยู่ในรูปแบบคำนวณได้
            var str = rowData[i][ci_confirm_qty];
            rowData[i][ci_confirm_qty] = str.replace(/\,/g, '');

            tmp_qty = parseFloat(rowData[i][ci_confirm_qty]); //+++++ADD BY POR 2013-11-29 แก้ไขให้เป็น parseFloat เนื่องจาก qty เปลี่ยนเป็น float

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
            }

            //+++++ADD BY POR 2014-06-11 เพิ่มการคำนวณราคา
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
            rowData2.fnUpdate(set_number_format(all_price), parseFloat(i), ci_all_price); //update ราคารวมทั้งหมดใน datatable
            //END ADD

            sum_cf_qty = sum_cf_qty + tmp_qty;
        }

        $('#sum_cf_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-06-11 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-06-11 เพิ่มให้แสดงราคารวมทั้งหมด

    }
    // end function calculate_qty : by kik : 28-10-2013

	function exportFile(type, document) {
		if(type=='tally'){
			$("#frmDispatch").attr('action', "<?php echo site_url(); ?>" + "/receive/exportReceiveTallySheet?document_no=<?php echo $DocNo;?>");
			$("#frmDispatch").submit();
		}
	}
</script>
<style>
    /* START Custom position */
/*    .tooltip {
        left: 100px !important;
        width: 200px !important;
    }*/
    /* END Custom position*/
</style>
<?php
if (!isset($DocNo)) {
    $DocNo = "";
}
if (!isset($est_action_date)) {
    $est_action_date = date("d/m/Y");
}
if (!isset($dispatch_date)) {
    $dispatch_date = date("d/m/Y");
}
//add real dispatch date for ISSUE 5265 : kik : 20141020
if (!isset($Real_Action_Date)) {
    $Real_Action_Date = date("d/m/Y");
}else if($Real_Action_Date == ''){
    $Real_Action_Date = date("d/m/Y");
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
if (!isset($remark)) {
    $remark = "";
}
if (!isset($process_type)) {
    $process_type = $data_form['process_type'];
}

if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
    $is_urgent = false;
} else {
    $is_urgent = true;
}
?>
<div class="content well" style='height:100%' >
    <form class="" method="POST" action="" id="frmDispatch" name="frmDispatch" target="_blank">
        <fieldset style="margin:0px auto;">
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
            <?php echo form_hidden('process_type', $process_type); ?>
            <?php echo form_hidden('owner_id', $owner_id); ?>
            <legend>Dispatch Order</legend>
            <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                <tr>
                    <td align="right">
                        Renter
                    </td>
                    <td align="left">
                        {renter_id_select}
                    </td>
                    <td align="right">
                        Shipper
                    </td>
                    <td align="left">
                        {frm_warehouse_select}
                    </td>
                    <td align="right">
                        Consignee
                    </td>
                    <td>
                        {to_warehouse_select}
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        Document No.
                    </td>
                    <td align="left">
                        <input type="text" placeholder="AUTO GENERATE DDR" id="document_no" name="document_no" readonly value="<?php echo $DocNo; ?>" />
                    </td>
                    <td align="right">
                        Document External
                    </td>
                    <td align="left">
                        <input type="text" placeholder="<?php echo DOCUMENT_EXT; ?>" id="doc_refer_ext" name="doc_refer_ext"  value="<?php echo $doc_refer_ext; ?>"class="required document" style="text-transform: uppercase"/>
                    </td>
                    <td align="right">
                        Document Internal
                    </td>
                    <td align="left">
                        <input type="text" placeholder="<?php echo DOCUMENT_INT; ?>" class="document" id="doc_refer_int" name="doc_refer_int" value="<?php echo $doc_refer_int; ?>" style="text-transform: uppercase"/>
                    </td>
                </tr>
                <tr>
                    <td align="right">Invoice No.</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', $doc_refer_inv, 'placeholder="' . DOCUMENT_INV . '"  class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry	</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', $doc_refer_ce, 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No.</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', $doc_refer_bl, 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">
                        Dispatch Type
                    </td>
                    <td>
                        {dispatch_type_select}
                    </td>
                    <td align="right">
                        Est. Dispatch Date
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Date Format" id="estDispatchDate" name="estDispatchDate" class="australianDate" value="<?php echo $est_action_date; ?>" disabled />
                    </td>
                    <td align="right" >
                        Dispatch Date
                    </td>
                    <td>
                        <input type="text" placeholder="Date Format" id="dispatchDate" name="dispatchDate" class="australianDate" value="<?php echo $dispatch_date; ?>" disabled/>
                    </td>
                </tr>
                <tr>
                    <td align="right">Vendor</td>
                    <td align="left"><?php echo form_dropdown('vendor_id', $vendor_list, $vendor_id, ''); ?></td>
                    <td align="right">Driver Name</td>
                    <td align="left"><?php echo form_input('driver_name', trim($driver_name), 'placeholder="' . DRIVER_NAME . '" '); ?></td>
                    <td align="right">Car No.</td>
                    <td align="left"><?php echo form_input('car_no', trim($car_no), 'placeholder="' . CAR_NO . '" '); ?></td>
                </tr>
                <tr>
                    <?php if($conf_cont):
                        ?>
                        <td align="right">Container</td>
                        <td align="left">
                            <?php echo form_multiselect('doc_refer_container', $doc_refer_container, NULL, 'disabled="disabled" id="doc_refer_container" placeholder="' . DOCUMENT_CONTAINER . '"  style="text-transform: uppercase"'); ?> <?php if($process_step!="confirm" && !empty($check_container)): ?><img id="add_container" src="<?php echo base_url("images/edit.png") ?>" style="width: 24px; height: 24px; margin-bottom: 3px; cursor: pointer;" /><?php endif; ?>
                            <input type="hidden" id="doc_refer_con_size" name="doc_refer_con_size">
                        </td>
                    <?php endif; ?>
                    <td align="right">Remark</td>
                    <td align="left">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
                    </td>

                    <!--add real dispatch date for ISSUE 5265 : kik : 20141020-->
                    <?php if($conf_change_dp_date):?>
                           <td align="right" valign="top">Real Dispatch Date</td>
                    <?php endif; ?>

                   <td align="left" valign="top">
                         <?php if($conf_change_dp_date):?>
                           <input type="text" placeholder="Date Format" id="realDispatchDate" name="realDispatchDate" class="australianDate" value="<?php echo $Real_Action_Date; ?>" />
                         <?php endif; ?>

                        <br/>
                        <div style='margin-left:30px;'>
                            <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>

                        </div>

                   </td>

                    <td align="left">
                        <input type="hidden" id="container_list" name="container_list" value='<?php echo $container_list; ?>' />
                        <input type="hidden" id="container_size_list" name="container_size_list" value='<?php echo $container_size_list; ?>' />
                    </td>
                </tr>
            </table>
        </fieldset>
        <input type="hidden" name="token" value="<?php echo $token ?>" />
    </form>
    <fieldset>
        <legend>Product Detail</legend>
        <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
            <tr>
                <td>
                    <div id="defDataTable_wrapper" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">

                        <!--show container-->
                        <?php if (!empty($group_tableList)) : ?>
                            <div style="width:100%;overflow-x: auto;" id="show_showDataTable">
                                <table cellpadding="2" cellspacing="0" border="0" class="display" id="show_ShowDataTableForInsert">
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
                                            _lang('invoice_out'),
                                            _lang('container_out'),
                                            _lang('dispatch_qty'),
                                            _lang('confirm_qty'),
                                            _lang('unit'),
                                            _lang('price_per_unit'),
                                            _lang('unit_price'),
                                            _lang('all_price'),
                                            _lang('suggest_location'),
                                            _lang('actual_location'),
                                            _lang('picking_by'),
                                            _lang('pallet_code_out'),
                                            _lang('remark'),
                                            "Product_Id",
                                            "Product_Status",
                                            "Product_Sub_Status",
                                            "Unit_Id",
                                            "Item_Id",
                                            "Inbound Id",
                                            "Suggest_Location_Id",
                                            "Actual_Location_Id",
                                            "Price/Unit ID",     //add by kik : 20140113
                                            "Cont_Id"     //add by kik : 20140113
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
                                        $sumPriceUnit = 0;      //add by kik : 14-01-2014 ราคารวมทั้งหมดต่อหน่วย
                                        $sumPrice = 0;          //add by kik : 14-01-2014 ราคารวมทั้งหมด

                                        if (isset($order_detail_data)) {
                                            $str_body = "";
                                            $j = 1;
                                            //print_r($order_detail_data);
                                            foreach ($group_tableList as $order_column) {
                                                $order_column = (object)$order_column;
//                                                p($order_column);
                                                $title_remark = ($order_column->Reason_Remark != " " && $order_column->Reason_Remark != NULL)?" title='".$order_column->Reason_Remark."' ":"";
                                                $order_id = $order_column->Order_Id;
                                                $all_price = ($order_column->DP_Confirm_Qty)*(@$order_column->Price_Per_Unit);
    //                                            $str_body .= "<tr title=\"" . $order_column->Full_Product_Name . "\" id=\"" . $order_column->Item_Id . "\">";
    //                                            $str_body .= "<tr title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\" id=\"" . $order_column->Item_Id . "\">"; // Edit by Ton! 20131127

                                                $str_body .= "<tr>";

                                                $str_body .= "<td>" . $j . "</td>";
                                                //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
                                                $str_body .= "<td class='td_click' align='center' title='Click to display the running process is not finished.'  ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'>" . $order_column->Product_Code . "</td>";

                                                //  Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้
                                                //  $str_body .= '<td>' . $order_column->Product_Name . '</td>';
    //                                            $str_body .= "<td title='" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "' >" . $order_column->Product_Name . "</td>";
                                                $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                                // END Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้

                                                $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                                $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                                $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                                $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                                $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                                $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                                $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                                $str_body .= "<td>" . $order_column->Cont_Data . "</td>";
                                                $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
    //                                            $str_body .= "<td style='text-align: right;'>" . $order_column->DP_Confirm_Qty . "</td>"; // Edit by Ak , change DP_Confirm_Qty to Confirm_Qty for show data confirm qty // Edit By Akkarapol, 14/10/2013, แก้มาใช้ตัว Confirm_Qty เนื่องจาก HH บันทึกเข้าที่ ฟิลด์นี้ // Edit By Akkarapol, 08/11/2013, HH ไม่ได้เซฟเข้า Confirm_Qty แล้ว แต่เซฟเข้า DP_Confirm_Qty แทน
                                                $str_body .= "<td>" . set_number_format($order_column->DP_Confirm_Qty) . "</td>";// Edit by Ton! 20131002
                                                $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                                $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";       //add by kik : 20140113
                                                $str_body .= "<td>" . @$order_column->Unit_Price_value . "</td>";                        //add by kik : 20140113
                                                $str_body .= "<td>" . set_number_format($all_price) . "</td>";                          //add by kik : 20140113
                                                $str_body .= "<td>" . $order_column->Suggest_Location . "</td>";
                                                $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                                                if ("ATV002" != $order_column->Activity_Code) {

                                                }
                                                $str_body .= "<td>" . @$order_column->Activity_By_Name . "</td>";
                                                $str_body .= "<td>" . @$order_column->Pallet_Code . "</td>";
                                                $str_body .= "<td .$title_remark.>" . $order_column->Remark . "</td>";
                                                $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                                                $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                                $str_body .= "<td>" . $order_column->Product_Sub_Status . "</td>";
                                                $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                                                $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                                                $str_body .= "<td>" . $order_column->Inbound_Item_Id . "</td>";
                                                $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                                                $str_body .= "<td>" . $order_column->Actual_Location_Id . "</td>";
                                                $str_body .= "<td>" . @$order_column->Unit_Price_Id . "</td>";                           //add by kik : 20140113
                                                $str_body .= "<td>" . @$order_column->Cont_Id . "</td>";                           //add by kik : 20140113
                                                $str_body .= "</tr>";
                                                $j++;
                                                $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                                $sum_Cf_Qty+=$order_column->DP_Confirm_Qty;            //add by kik    :   25-10-2013
                                                $sumPriceUnit+=@$order_column->Price_Per_Unit;       //add by kik    :   20140114
                                                $sumPrice+=$all_price;                              //add by kik    :   20140114
                                            }
                                            echo $str_body;
                                        }
                                        ?>
                                    </tbody>

                                     <!-- show total qty : by kik : 28-10-2013-->
                                            <tfoot>
                                               <tr>
                                                     <th colspan='11' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                     <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Receive_Qty);?></span></th>
                                                     <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty);?></span></th>
                                                     <th></th>
                                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                     <th></th>
                                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                                     <th colspan='12' class ='ui-state-default indent' ></th>
                                                </tr>
                                            </tfoot>
                                            <!-- end show total qty : by kik : 28-10-2013-->
                                </table>
                            </div>
                        <?php endif; ?>


                        <div style="width:100%;overflow-x: auto; <?php echo ($dispatch_group_item_by_product_code)?'display:none;':''; ?>" id="showDataTable">
                            <table cellpadding="2" cellspacing="0" border="0" class="display" id="ShowDataTableForInsert">
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
                                        _lang('invoice_out'),
                                        _lang('container_out'),
                                        _lang('dispatch_qty'),
                                        _lang('confirm_qty'),
                                        _lang('unit'),
                                        _lang('price_per_unit'),
                                        _lang('unit_price'),
                                        _lang('all_price'),
                                        _lang('suggest_location'),
                                        _lang('actual_location'),
                                        _lang('picking_by'),
                                        _lang('pallet_code_out'),
                                        _lang('remark'),
                                        "Product_Id",
                                        "Product_Status",
                                        "Product_Sub_Status",
                                        "Unit_Id",
                                        "Item_Id",
                                        "Inbound Id",
                                        "Suggest_Location_Id",
                                        "Actual_Location_Id",
                                        "Price/Unit ID",     //add by kik : 20140113
                                        "Cont_Id"     //add by kik : 20140113
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
                                    $sumPriceUnit = 0;      //add by kik : 14-01-2014 ราคารวมทั้งหมดต่อหน่วย
                                    $sumPrice = 0;          //add by kik : 14-01-2014 ราคารวมทั้งหมด

                                    if (isset($order_detail_data)) {
                                        $str_body = "";
                                        $j = 1;
                                        //print_r($order_detail_data);
                                        foreach ($order_detail_data as $order_column) {
                                            $title_remark = ($order_column->Reason_Remark != " " && $order_column->Reason_Remark != NULL)?" title='".$order_column->Reason_Remark."' ":"";
                                            $order_id = $order_column->Order_Id;
                                            $all_price = ($order_column->DP_Confirm_Qty)*(@$order_column->Price_Per_Unit);
//                                            $str_body .= "<tr title=\"" . $order_column->Full_Product_Name . "\" id=\"" . $order_column->Item_Id . "\">";
//                                            $str_body .= "<tr title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\" id=\"" . $order_column->Item_Id . "\">"; // Edit by Ton! 20131127

                                            $str_body .= "<tr>";

                                            $str_body .= "<td>" . $j . "</td>";
                                            //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
                                            $str_body .= "<td class='td_click' align='center' title='Click to display the running process is not finished.'  ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'>" . $order_column->Product_Code . "</td>";

                                            //  Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้
                                            //  $str_body .= '<td>' . $order_column->Product_Name . '</td>';
//                                            $str_body .= "<td title='" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "' >" . $order_column->Product_Name . "</td>";
                                            $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                            // END Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้

                                            $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                            $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                            $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                            $str_body .= "<td>" . @$order_column->Cont_No.' '.@$order_column->Cont_Size_No.@$order_column->Cont_Size_Unit_Code. "</td>";
                                            $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
//                                            $str_body .= "<td style='text-align: right;'>" . $order_column->DP_Confirm_Qty . "</td>"; // Edit by Ak , change DP_Confirm_Qty to Confirm_Qty for show data confirm qty // Edit By Akkarapol, 14/10/2013, แก้มาใช้ตัว Confirm_Qty เนื่องจาก HH บันทึกเข้าที่ ฟิลด์นี้ // Edit By Akkarapol, 08/11/2013, HH ไม่ได้เซฟเข้า Confirm_Qty แล้ว แต่เซฟเข้า DP_Confirm_Qty แทน
                                            $str_body .= "<td>" . set_number_format($order_column->DP_Confirm_Qty) . "</td>";// Edit by Ton! 20131002
                                            $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                            $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";       //add by kik : 20140113
                                            $str_body .= "<td>" . @$order_column->Unit_Price_value . "</td>";                        //add by kik : 20140113
                                            $str_body .= "<td>" . set_number_format($all_price) . "</td>";                          //add by kik : 20140113
                                            $str_body .= "<td>" . $order_column->Suggest_Location . "</td>";
                                            $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                                            if ("ATV002" != $order_column->Activity_Code) {

                                            }
                                            $str_body .= "<td>" . @$order_column->Activity_By_Name . "</td>";
                                            $str_body .= "<td>" . @$order_column->Pallet_Code . "</td>";
                                            $str_body .= "<td .$title_remark.>" . $order_column->Remark . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Sub_Status . "</td>";
                                            $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Inbound_Item_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Actual_Location_Id . "</td>";
                                            $str_body .= "<td>" . @$order_column->Unit_Price_Id . "</td>";                           //add by kik : 20140113
                                            $str_body .= "<td>" . @$order_column->Cont_Id . "</td>";                           //add by kik : 20140113
                                            $str_body .= "</tr>";
                                            $j++;
                                            $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                            $sum_Cf_Qty+=$order_column->DP_Confirm_Qty;            //add by kik    :   25-10-2013
                                            $sumPriceUnit+=@$order_column->Price_Per_Unit;       //add by kik    :   20140114
                                            $sumPrice+=$all_price;                              //add by kik    :   20140114
                                        }
                                        echo $str_body;
                                    }
                                    ?>
                                </tbody>

                                 <!-- show total qty : by kik : 28-10-2013-->
                                        <tfoot>
                                           <tr>
                                                 <th colspan='11' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Receive_Qty);?></span></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty);?></span></th>
                                                 <th></th>
                                                 <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                 <th></th>
                                                 <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                                 <th colspan='12' class ='ui-state-default indent' ></th>
                                            </tr>
                                        </tfoot>
                                        <!-- end show total qty : by kik : 28-10-2013-->
                            </table>
                        </div>

                    </div>
                </td>
            </tr>
        </table>
    </fieldset>
</div>

<!--call element Product Est. balance Detail modal : add by kik : 06-11-2013-->
<?php $this->load->view('element_showEstBalance'); ?>

<?php $this->load->view('element_modal_message_alert'); ?>

<?php $this->load->view('element_modal'); ?>