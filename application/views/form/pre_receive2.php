<script>

    var master_product_status = {};
    var master_product_sub_status = "";
    var master_product_unit = "";
    var master_container_size = "";
    var global_code_sub_status_return = '<?php echo $sub_status_return; ?>';
    var global_code_sub_status_repackage = '<?php echo $sub_status_repackage; ?>';
    var global_ci_prod_status_label = 3; // เปลี่ยนให้เป็นตาม index ของ dataTable ใน ช่องที่เป็น Product Status
    var global_ci_prod_sub_status_label = 4; // เปลี่ยนให้เป็นตาม index ของ dataTable ใน ช่องที่เป็น Product Sub Status

    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Pre-Receive'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#showProductTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/flow/flowPreReceiveList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ

    //ADD BY POR 2014-07-10 CONFIG
    var conf_inv = '<?php echo $conf_inv; ?>';
    var statusprice = '<?php echo $statusprice; ?>';
    var conf_cont = '<?php echo $conf_cont; ?>';
    //END ADD CONFIG


    var flagSubmit = false;
    var oTable = null;
    var allVals = new Array();
    var separator = "<?php echo SEPARATOR; ?>";
    var form_name = "form_receive";
    var ci_no = 0;
    var ci_prod_code = 1;
    var ci_prod_name = 2;
    var ci_prod_status = 3;
    var ci_prod_sub_status = 4;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_invoice = 9;
    var ci_reserv_qty = 10;
    var ci_unit = 11;
    var ci_price_per_unit = 12;
    var ci_unit_price = 13;
    var ci_all_price = 14;
    var ci_remark = 15;
    //Define Hidden Field Datatable
    var ci_prod_id = 17;
    var ci_prod_status = 18;
    var ci_prod_sub_status = 19;
    var ci_unit_id = 20;
    var ci_item_id = 21;
    var ci_supplier_id = 22; // Add by Akkarapol, 27/08/2013, เพิ่ม supplier_id เข้าไปจะได้เช็คได้ว่าข้อมูล product ที่เลือกลงมาเป็นของ supplier ที่เลือกหรือไม่
    var ci_unit_price_id = 23; //Edit by por 2014-01-17 เพิ่มคีย์ รหัส unit price

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
        {name: 'ci_supplier_id', value: ci_supplier_id},
        {name: 'ci_price_per_unit', value: ci_price_per_unit},
        {name: 'ci_unit_price', value: ci_unit_price},
        {name: 'ci_all_price', value: ci_all_price},
        {name: 'ci_unit_price_id', value: ci_unit_price_id},
        {name: 'ci_invoice', value: ci_invoice},
    ]

    function pending_filter(product_status, is_pending) {
        var tmp = "";
        $.each(product_master, function (i, val) {
            if (true == is_pending) {
                tmp += i + ":" + val;
            } else {
                tmp += i + ":" + val;
            }
        });
        return "{" + tmp + "}";
    }

    function exportBarcodePrint(document_no) {
        var url = '<?php echo site_url("/report/printBarcode") ?>?d=' + document_no;
        var new_Tab = window.open(url, '_blank');
        redirect("<?php echo site_url(); ?>/flow/flowPreReceiveList");
    }

    var flag = false;
    $(document).ready(function () {

        $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', {"is_pending": "Y"}, function (data) {
            //master_product_status = JSON.parse(data);
            $.extend(master_product_status, JSON.parse(data));
        });
        $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', function (data) {
            //master_product_status = JSON.parse(data);
            $.extend(master_product_status, JSON.parse(data));
        });
        $.post('<?php echo site_url() . "/pre_receive/getSubStatus"; ?>', function (data) {
            master_product_sub_status = data;
        });
        $.post('<?php echo site_url() . "/pre_receive/getProductUnit"; ?>', function (data) {
            master_product_unit = data;
        });
        if (conf_cont) {
            $.post('<?php echo site_url() . "/pre_receive/getContainerSize"; ?>', function (data) {
                master_container_size = data;
            });
        }
        $.fn.callInvoiceModal = function () {
            $('#dynamic_modal').modal('show');
        };
        $("#add_container").click(function () {
            //console.log($("#container_list").val());
            $('#dynamic_modal').on('show.bs.modal', function (e) {

                var dynamic_modal_body = $("#dynamic_modal_body");
                var container_list = $("#container_list");
                var FieldCount = 1;
                var container_data = $("#doc_refer_container").val();
                var container_size = $("#doc_refer_con_size").val();
                var container_data_confirm = '<?php echo $container_list; ?>'; //ค่านี้จะได้จากตอน confirm and approve


                if (container_list.val() == '{') {
                    container_list.val(container_data_confirm);
                }
                console.log(container_list.val());
                $('#save_container_data').show();
                $("#dynamic_modal_label").html("Container List");
                if (container_list.val() != "" && container_list.val() != '{}') {   //behide save
                    var get_list_data = $.parseJSON(container_list.val());
                    $.each(get_list_data, function (idx2, val2) {
                        var objDiv = $("<div>");
                        var objConID2 = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]").val(val2.id);
                        var objInput2 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").val(val2.name).css({"textTransform": "uppercase", "width": "350px"});
                        var objSelect2 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width": "120px"});
                        var _master_container_size2 = $.parseJSON(master_container_size);
                        $.each(_master_container_size2, function (idx, val) {
                            var objOption2 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect2);
                            if (val2.size == val.Id) {
                                objOption2.prop("selected", true);
                            }
                        });
                        if (idx2 == 0) {
                            var objImage2 = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
                            objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
                            dynamic_modal_body.empty().append(objDiv);
                        } else {
                            var objImage2 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width": "24px", "height": "24px", "marginBotton": "3px", "marginLeft": "3px", "cursor": "pointer"});
                            objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
                            dynamic_modal_body.append(objDiv);
                        }
                    });
                } else {  //case open
                    var objConID = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]");
                    var objInput = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").val(container_data).css({"textTransform": "uppercase", "width": "350px"});
                    var objSelect = $("<select>").prop("class", "con_size").prop("class", "con_size").css({"width": "120px"}).prop("name", "con_size[]").val(container_size);
                    var _master_container_size = $.parseJSON(master_container_size);
                    $.each(_master_container_size, function (idx, val) {
                        var objOption = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect);
                        if (container_size == val.Id) {
                            objOption.prop("selected", true);
                        }
                    });
                    var objImage = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
                    dynamic_modal_body.empty().append(objConID).append(objInput).append(objSelect).append(objImage);
                }
                $(".container_list").keypress(function (e) {
                    var char = e.which || e.keyCode;
                    if (char == "39" || char == "34") {
                        return false;
                    }
                });
                $(".add_more_btn").click(function (e)  //on add input button click
                {
                    FieldCount++; //text box added increment
                    var objDiv = $("<div>");
                    var objConID3 = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]").val('NEW');
                    var objInput3 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").css({"textTransform": "uppercase", "width": "350px"});
                    var objSelect3 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width": "120px"});
                    var _master_container_size3 = $.parseJSON(master_container_size);
                    $.each(_master_container_size3, function (idx, val) {
                        var objOption3 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect3);
                    });
                    var objImage3 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width": "24px", "height": "24px", "height":"24px", "marginBottom": "3px", "marginLeft": "3px", "cursor": "pointer"});
                    objDiv.append(objConID3).append(objInput3).append(objSelect3).append(objImage3);
                    dynamic_modal_body.append(objDiv);
                    $(".container_list").keypress(function (e) {
                        var char = e.which || e.keyCode;
                        if (char == "39" || char == "34") {
                            return false;
                        }
                    });
                    return false;
                });
                dynamic_modal_body.on("click", ".removeclass", function (e) {
                    $(this).parent('div').remove(); //remove text box
                    return false;
                });
            });
            $('#dynamic_modal').modal({
                keyboard: false
                , backdrop: "static"
            });
            $('#save_container_data').click(function (e) {
                var temp = {};
                var data = "";
                var container_list = $(".container_list");
                var container_list_size = $(".con_size");
                var container_list_id = $(".container_id");
                var container_size_name = '';
                var i = 0; //ADD BY POR 2014-10-01 : for check input container name
                $.each(container_list, function (idx, val) {
                    var container_size_id = $(container_list_size[idx]).val();
                    var container_id = $(container_list_id[idx]).val();
                    var container_name = $(val).val();
                    if (container_name == "") { //for check input container name : ADD BY POR 2014-10-01
                        i++;
                    } else {
                        var objTemp = {};
                        objTemp['id'] = container_id;
                        objTemp['name'] = container_name.toUpperCase();
                        objTemp['size'] = container_size_id;
                        temp[idx] = objTemp;
                        //data += ($(val).val() != "" ? $(val).val() + "," : "");
                    }

                });
                //if i > 0 => have some container name is null : ADD BY POR 2014-10-01
                if (i > 0) {
                    alert("Please input value container");
                    return false;
                }
                //end if

                var newData = temp;
                var $el = $("#doc_refer_container");
                $el.empty(); // remove old options
                $.each(newData, function (value, obj) {
                    var label = obj.name + "  " + $(".con_size:first option[value='" + obj.size + "']").text();
                    $el.append($("<option></option>")
                            .attr("value", value).text(label));
                });
                $("#container_list").val(JSON.stringify(temp));
//                    $("#doc_refer_container").val($(".container_list").val() + ' ' + $(".con_size:first option:selected").text());
                $("#doc_refer_con_size").val($("#doc_refer_container_size").val());
                $('#dynamic_modal').modal("hide");
            });
        });
        // ==

        // Mask Input
        $("#est_receive_date").inputmask("99/99/9999");
        /**
         * Search Product Code By AutoComplete
         */
        $("#productCode").autocomplete({
            minLength: 0,
            search: function (event, ui) {
                $('#highlight_productCode').attr("placeholder", '');
            },
            source: function (request, response) {
                var shipper_id = '';
                var spl = $("[name='doc_refer_ext']").val().toUpperCase();
                if (!spl.match(<?= $noChkSup ?>)) {
                    shipper_id = $("[name='shipper_id']").val();
                }

                $.ajax({
                    url: "<?php echo site_url(); ?>/pre_receive/ajax_show_product_list",
                    dataType: "json",
                    type: 'post',
                    data: {
                        text_search: $('#productCode').val(),
                        supplier_id: shipper_id
                    },
                    success: function (val, data) {
                        if (val != null) {
                            response($.map(val, function (item) {
                                return {
                                    label: item.product_code + ' ' + item.product_name,
                                    value: item.product_code
                                }
                            }));
                        }
                    },
                });
            },
            open: function (event, ui) {
                var auto_h = $(window).innerHeight() - $('#table_of_productCode').position().top - 50;
                $('.ui-autocomplete').css('max-height', auto_h);
            },
            focus: function (event, ui) {
                $('#highlight_productCode').attr("placeholder", ui.item.label);
                return false;
            },
            select: function (event, ui) {
                $('#highlight_productCode').attr("placeholder", '');
                $('#productCode').val(ui.item.value);
                $('#formProductCode').submit();
                $('#productCode').val('');
                return false;
            }
        });
        // Edit Code by Ton! 20131001
        $('#receive_type').change(function () {
            var receiveType = $(this).val();
            var rowData = $('#showProductTable').dataTable().fnGetData();
            if (receiveType == 'RCV002') {//Return
                $('#showProductTable tbody td.obj_status').html('Normal');
                var status = "NORMAL";
                for (i in rowData) {
                    rowData[i][ci_prod_status] = status;
//                    rowData[i][ci_prod_sub_status] = 'SS001';// Add by Ton! 20131008
                    rowData[i][ci_prod_sub_status] = '<?php echo $sub_status_return; ?>'; // Add by Akkarapol, 06/11/2013, เปลี่ยนจากการใช้แบบกำหนดค่าตายตัวใน code ไปใช้การส่งค่าจาก controller แทนข้อมูลจะได้ไม่ผิดอีก!!!!!
                }

                $('#showProductTable tbody td.obj_sub_status').text('Return');
            } else {
                $('#showProductTable tbody td.obj_sub_status').text('No Specified');
                var status_no_specefied = "<?php echo $sub_status_no_specefied; ?>"; // Add by Akkarapol, 06/11/2013, เปลี่ยนจากการใช้แบบกำหนดค่าตายตัวใน code ไปใช้การส่งค่าจาก controller แทนข้อมูลจะได้ไม่ผิดอีก!!!!!
                for (i in rowData) {
                    rowData[i][ci_prod_sub_status] = status_no_specefied;
                }
                if (receiveType == 'RCV003') {//Adjust
                    $("[id='is_pending']").prop('checked', false);
                    $("[id='is_pending']").attr("disabled", true);
                    $('#showProductTable tbody .obj_status').html('Normal');
                    var statusNormal = "NORMAL"; // Edit By Akkarapol, 06/11/2013, เปลี่ยนจากคำว่า statusPending เป็น statusNormal เพราะต้องการให้ชื่อตัวแปรมันสื่อความหมายถึงหน้าที่ที่แท้จริง
                    for (i in rowData) {
                        rowData[i][ci_prod_status] = statusNormal; // Edit By Akkarapol, 06/11/2013, เปลี่ยนจากคำว่า statusPending เป็น statusNormal เพราะต้องการให้ชื่อตัวแปรมันสื่อความหมายถึงหน้าที่ที่แท้จริง
                    }

                    $("[id='is_repackage']").prop('checked', false);
                    $("[id='is_repackage']").attr("disabled", true);
//                    $('#showProductTable tbody .obj_sub_status').html('Normal');
//                    var statusRepackage = "SS000";

                }
            }
        });
        // End Edit Code by Ton! 20131001

        // Add By Ball
        // Prevent unwant redirect pages
        window.onbeforeunload = function () {
            return "You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work?";
        };
        // If found key F5 remove unload event!!
        $(document).bind('keydown keyup', function (e) {
            if (e.which === 116) {
                window.onbeforeunload = null;
            }
            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }
        });
        // Add By Ball

        // Add By Akkarapol, 20/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function () {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        $('.required').on("change keyup", function () {
//            console.log($(this).val());
        });
        // END Add By Akkarapol, 20/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 20/09/2013, เพิ่ม onKeyup ของช่อง External Document ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="doc_refer_ext"]').keyup(function () {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
        });
        // END Add By Akkarapol, 20/09/2013, เพิ่ม onKeyup ของช่อง External Document ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        // Clear require field
        $('.text_datepicker').blur(function (e) {
//            console.log('xxxx');
        });
        // Add By Akkarapol, 26/09/2013, เพิ่ม action แบบเดียวกับที่ K.Krip ทำกับ pending คือ ถ้า click ที่ checkbox Re-Package แล้วจะให้ sub status เปลี่ยนเป็น Repackage และถ้าเช็คออก ก็คือจะคืนค่าเป็น Normal
        // Pending ckeckbox
        $('#is_pending').click(function () {
            var status = "";
            if ($(this).prop('checked')) {
                $('#showProductTable tbody .obj_status').html('Pending');
                status = "PENDING";
            } else {
                $('#showProductTable tbody .obj_status').html('Normal');
                status = "NORMAL";
            }
            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                flagSubmit = false;
                return false;
            }
            for (i in rowData) {
                rowData[i][ci_prod_status] = status;
            }
        });
        // END Add By Akkarapol, 26/09/2013, เพิ่ม action แบบเดียวกับที่ K.Krip ทำกับ pending คือ ถ้า click ที่ checkbox Re-Package แล้วจะให้ sub status เปลี่ยนเป็น Repackage และถ้าเช็คออก ก็คือจะคืนค่าเป็น Normal

        // Re-Package ckeckbox
        $('#is_repackage').click(function () {
            var status = "";
//            No Specified SS000, Return SS001, Repackage SS002
            if ($(this).prop('checked')) {
                $('#showProductTable tbody .obj_sub_status').html('Repackage');
//                status = "SS002";
                status = '<?php echo $sub_status_repackage; ?>'; // Add by Akkarapol, 06/11/2013, เปลี่ยนจากการใช้แบบกำหนดค่าตายตัวใน code ไปใช้การส่งค่าจาก controller แทนข้อมูลจะได้ไม่ผิดอีก!!!!!
            } else {
                $('#showProductTable tbody .obj_sub_status').html('Return');
//                status = "SS000";// Comment Out by Ton! 20131016
//                status = "SS001";// Edit by Ton! 20131016
                status = '<?php echo $sub_status_return; ?>'; // Add by Akkarapol, 06/11/2013, เปลี่ยนจากการใช้แบบกำหนดค่าตายตัวใน code ไปใช้การส่งค่าจาก controller แทนข้อมูลจะได้ไม่ผิดอีก!!!!!
            }
            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                flagSubmit = false;
                return false;
            }
            for (i in rowData) {
                rowData[i][ci_prod_sub_status] = status;
            }
        });
        // End

        // Add timeout for wait initial data,
        // BALL 2014-06-21
        setTimeout(function () {
            initProductTable();
        }, 2000);
        if ("RCV002" == $("[name='receive_type']").val()) {
            $("[name='is_pending']").attr("disabled", true);
            $("[name='is_repackage']").attr("disabled", false);
        } else {
            $("[name='is_repackage']").attr("disabled", true);
            $("[name='is_pending']").attr("disabled", false);
        }

        $("#est_receive_date").datepicker({}).keypress(function (event) {
            event.preventDefault();
        }).on('changeDate', function (ev) {
            //$('#est_receive_date').datepicker('hide');
        }).bind("cut copy paste", function (e) {
            e.preventDefault();
        });
        $.validator.addMethod("document", function (value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._/\\#,-]+$/i.test(value);
        }, "Document Format is invalid.");
        $('#getBtn').click(function () {

//            allVals = new Array();
//            var product_code = $('#productCode').val();
//            var spl = $("[name='doc_refer_ext']").val().toUpperCase();
//            if (spl.match(<?= $noChkSup ?>)) {
//
//            } else {
//                var dataSet = {
//                    post_val: product_code,
//                    supplier_id: $("[name='shipper_id']").val()
//                }
//            }

            showModalTable();
            // Edit By Akkarapol, 21/01/2014,set dataTable bind search when enter key
            $('#modal_data_table_filter label input')
                    .unbind('keypress keyup')
                    .bind('keypress keyup', function (e) {
                        if (e.keyCode == 13) {
                            oTable.fnFilter($(this).val());
                        }
                    });
            // Edit By Akkarapol, 21/01/2014,set dataTable bind search when enter key

        });
        function showModalTable() {
            allVals = new Array();
            var product_code = $('#productCode').val();
            var productStatus_select = $('#productStatus_select').val();
            var productSubStatus_select = $('#productSubStatus_select').val();
            var productLot = $('#productLot').val();
            var productSerial = $('#productSerial').val();
            var productMfd = $('#productMfd').val();
            var productExp = $('#productExp').val();
            var dataSet = {productCode_val: product_code
                , productStatus_val: productStatus_select
                , productSubStatus_val: productSubStatus_select
                , productLot_val: productLot
                , productSerial_val: productSerial
                , productMfd_val: productMfd
                , productExp_val: productExp
            }

            $('#prdModalval').val(product_code);
            initProductsDatatables();
        }

//Define button Search onClick
        $('#search_submit').click(function () {
            var dataSet = {
                post_val: allVals,
                receive_type: $('#receive_type').val()
            }
            var oTable = $('#showProductTable').dataTable();
            var recordsTotal = oTable.fnSettings().fnRecordsTotal() + 1;
            $.post('<?php echo site_url() . "/pre_receive/showProduct" ?>', dataSet, function (data) {
                var qty = "";
                var lot = "";
                var serial = "";
                var remark = "";
                var mfd = "";
                var exp = "";
                var id = "new";
                var price = "";
                var allprice = "";
                var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                var invoice = "";
                $.each(data.product, function (i, item) {
                    oTable.dataTable().fnAddData([
                        recordsTotal + i
                                , item.Product_Code
                                , item.Product_NameEN
                                , ($("#is_pending").prop("checked") ? "Pending" : item.Prod_Status_Value)
                                , item.Prod_Sub_Status_Value
                                , lot
                                , serial
                                , mfd
                                , exp
                                , invoice
                                , qty
                                , item.public_name
                                , price
                                , item.unitprice
                                , allprice
                                , remark
                                , del
                                , item.Product_Id
                                , item.Prod_Status
                                , item.Prod_Sub_Status
                                , item.Standard_Unit_Id
                                , id
                                , item.Supplier_Id
                                , item.unitprice_id
                    ]);
                });
                //initProductTable();

                reInitProductTable();
            }, "json");
            $('.modal.in').modal('hide');
            allVals = new Array();
            $(this).backtobottom();
        });
//        set data when enter key
        $('#formProductCode').submit(function () {
            if (flag == false) {
                flag = true;
                var oTable = $('#showProductTable').dataTable();
                var recordsTotal = oTable.fnSettings().fnRecordsTotal() + 1;
                // Add By Akkarapol, 17/09/2013, Set DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg
                var spl = $("[name='doc_refer_ext']").val().toUpperCase();
                if (spl.match(<?= $noChkSup ?>)) {
                    var dataSet = {
                        post_val: $('#productCode').val(),
                        supplier_id: ''
                    }
                } else {
                    // Add By Akkarapol, 17/09/2013, ในกรณีที่ Product ที่คีย์เข้าไป เป็น Product แรกของใบ Pre-Receive ไม่จำเป็นต้องเช็คว่า เป็นของ Supplier ไหน เพื่อความรวดเร็วในการนำเข้าข้อมูล
                    if ($('#showProductTable').dataTable().fnGetData() == '') {
                        var dataSet = {
                            post_val: $('#productCode').val(),
                            supplier_id: ''
                        }
                    } else {
                        var dataSet = {
                            post_val: $('#productCode').val(),
                            supplier_id: $("[name='shipper_id']").val()
                        }
                    }
                    // END  Add By Akkarapol, 17/09/2013, ในกรณีที่ Product ที่คีย์เข้าไป เป็น Product แรกของใบ Pre-Receive ไม่จำเป็นต้องเช็คว่า เป็นของ Supplier ไหน เพื่อความรวดเร็วในการนำเข้าข้อมูล
                }
                // END Add By Akkarapol, 17/09/2013, Set DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg

                $.post('<?php echo site_url() . "/pre_receive/showProductWhenEnterKey" ?>', dataSet, function (data) {

                    if (data['error_msg'] != '') {
                        alert(data['error_msg']);
                    }
                    var qty = "";
                    var lot = "";
                    var serial = "";
                    var remark = "";
                    var mfd = "";
                    var exp = "";
                    //ADD BY POR 2014-01-09 กำหนดให้ ราคาต่อหน่วย,หน่วยของราคา และราคารวม เป็นค่าว่างในเบื้องต้น
                    var price = "";
                    var allprice = "";
                    //END ADD
                    var id = "new";
                    var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                    var invoice = "";
                    $.each(data.product, function (i, item) {
                        oTable.fnAddData([
                            recordsTotal + i
                                    , item.Product_Code
                                    , item.Product_NameEN
                                    , item.Prod_Status_Value
                                    , item.Prod_Sub_Status_Value
                                    , lot
                                    , serial
                                    , mfd
                                    , exp
                                    , invoice
                                    , qty
                                    , item.public_name /*Product Unit*/
                                    //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคาต่อหน่วย หน่วยของราคา และจำนวนเงินรวม
                                    , price
                                    , item.unitprice
                                    , allprice
                                    //END ADD
                                    , remark
                                    , del
                                    , item.Product_Id
                                    , item.Prod_Status
                                    , item.Prod_Sub_Status
                                    , item.Standard_Unit_Id
                                    , id
                                    , item.Supplier_Id
                                    , item.unitprice_id
                        ]
                                );
                        $('[name="shipper_id"] option[value=' + item.Supplier_Id + ']').prop('selected', true); // Add By Akkarapol, 17/09/2013, ในกรณีที่เป็น Product แรกของ ใบ Pre-Receive จะให้ Shipper ถูกเลือกโดยอัตโนมัติตาม Product นั้น

                    });
                    //initProductTable();
                    reInitProductTable();
                    flag = false;
                    $(".lot form input").keyup(function () {
                        alert("AAA");
                    });
                }, "json");
            }
            $('.modal.in').modal('hide');
            allVals = new Array();
            return false;
        });
        $('#select_all').click(function () {
            var cdata = $('#modal_data_table').dataTable();
//            allVals = new Array(); // Comment By Akkarapol, 29/11/2013, คอมเม้นต์ทิ้งเพราะถ้าเกิดเซ็ตให้ allVals มันเป็น new Array() แล้ว จะทำให้ ค่าที่เคยเลือกไว้หายไปด้วย แล้วจะได้ค่าที่ไม่ตรงตามความต้องการ จึงจำเป็นต้องปิดส่วนนี้ไป
            $(cdata.fnGetNodes()).find(':checkbox').each(function () {
                $this = $(this);
                $this.attr('checked', 'checked');
                allVals.push($this.val());
            });
        });
        $('#deselect_all').click(function () {
            var selected = new Array();
            var cdata = $('#modal_data_table').dataTable();
            $(cdata.fnGetNodes()).find(':checkbox').each(function () {
                $this = $(this);
                $this.attr('checked', false);
                selected.push($this.val());
                allVals.pop($this.val());
            });
            allVals = new Array();
        });
    });
    //Define Dialog Model
    $('#myModal').modal('toggle').css({
        'width': function () {
            return ($(document).width() * .9) + 'px'; // make width 90% of screen
        },
        'margin-left': function () {
            return -($(this).width() / 2); // center model
        }
    });
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
            var tmp_price = 0; //ราคาต่อหน่วย
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

    function getCheckValue(obj) {
        var isChecked = $(obj).attr("checked");
        if (isChecked) {
            allVals.push($(obj).val());
        } else {
//            allVals.pop($(obj).val()); // Comment By Akkarapol, 27/11/2013, คอมเม้นต์ allVals.pop($(obj).val()) ทิ้งเพราะการใช้ pop มันไม่ได้เอาค่าของ $(obj).val() ออกไปจริงๆ แต่มันเอาอันสุดท้ายที่ถูก puch เข้าไป ออก ซึ่ง ผิดมหันต์เลยล่ะ เพราะค่าที่เหลือ จะไม่ใช่ค่าที่ต้องการจริงๆ

// Add BY Akkarapol, 27/11/2013, เพิ่มการใช้ฟังก์ชั่น grep เพื่อ return ค่าที่เหลือหลังจากการตัด ค่าที่ไม่ได้เลือกออกไป จะเหลือเฉพาะค่าที่ เลือกแล้วเท่านั้น จะได้ค่าที่ตรงตามต้องการจริงๆ
            allVals = jQuery.grep(allVals, function (value) {
                return value != $(obj).val();
            });
// END Add BY Akkarapol, 27/11/2013, เพิ่มการใช้ฟังก์ชั่น grep เพื่อ return ค่าที่เหลือหลังจากการตัด ค่าที่ไม่ได้เลือกออกไป จะเหลือเฉพาะค่าที่ เลือกแล้วเท่านั้น จะได้ค่าที่ตรงตามต้องการจริงๆ

        }
//        console.log(allVals);
    }

    $.fn.dataTableExt.afnSortData['dom-text'] = function (oSettings, iColumn)
    {
        var aData = [];
        $('td:eq(' + iColumn + ') input', oSettings.oApi._fnGetTrNodes(oSettings)).each(function () {
            aData.push(this.value);
        });
        return aData;
    }

    function initProductTable() {
    $('#showProductTable').dataTable({
    "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "iDisplayLength"    : 250,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
            {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
            {"sWidth": "30%", "sClass": "left_text", "aTargets": [2]},
            {"sWidth": "7%", "sClass": "left_text obj_status", "aTargets": [3]},
            {"sWidth": "7%", "sClass": "left_text obj_sub_status", "aTargets": [4]},
            {"sWidth": "7%", "sClass": "left_text lot", "aTargets": [5]},
            {"sWidth": "7%", "sClass": "left_text serial", "aTargets": [6]},
            {"sWidth": "7%", "sClass": "center obj_mfg", "aTargets": [7]},
            {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [8]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [9]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [10]},
            {"sWidth": "7%", "sClass": "center", "aTargets": [11]},
            {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [12]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [13]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [14]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [15]},
            {"sWidth": "3%", "sClass": "center", "aTargets": [16]}
            ]

    }).makeEditable({
    sUpdateURL: function(value, settings) {
    return value;
    }
    , "aoColumns": [
            null
            , null
            , null
            , {
            data: JSON.stringify(master_product_status),
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    select_filter_active : true,
                    select_filter_by : "PENDING",
                    sUpdateURL: function(value, settings) {
                    var oTable = $('#showProductTable').dataTable();
                            var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                            oTable.fnUpdate(value, rowIndex, ci_prod_status);
                            return value;
                    },
            }
    , {
    data : master_product_sub_status,
            event: 'click',
            type: 'select',
            onblur: 'submit',
            sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    oTable.fnUpdate(value, rowIndex, ci_prod_sub_status);
                    return value;
            }
    }
    , {
    // Lot
    sSortDataType: "dom-text",
            type: 'text',
            onblur: "submit",
            event: 'click focusin',
            loadfirst: true, // ต้องปิด ไว้เพื่อแก้ปัญหาเฉพาะหน้าไปก่อน แผนคือการเคลียร์ค่า html ใน data array ทั้งหมด : comment by kik , 05-11-2013
            width: '75%',
            cssclass: 'input_lot',
    }
    , {
    // Serial
    sSortDataType: "dom-text",
            type: 'text',
            onblur: "submit",
            event: 'click focusin',
            loadfirst: true, //ต้องปิด ไว้เพื่อแก้ปัญหาเฉพาะหน้าไปก่อน แผนคือการเคลียร์ค่า html ใน data array ทั้งหมด : comment by kik , 05-11-2013
            width: '75%',
    }
    , {
    onblur: 'submit',
            type: 'datepicker',
            cssclass: 'date',
            event: 'click focusin',
            is_required: false, //EDIT BY BALL 2014-04-23 เพิ่ม require
            loadfirst: true, // Comment By Akkarapol, 14/10/2013, คอมเม้นต์ loadfirst ทิ้งเนื่องจาก ช่อง MFD นี้ยังไม่จำเป็นต้องกรอกในขั้นตอนนี้ก็ได้
    }	// MFD
    , {
    onblur: 'submit',
            type: 'datepicker',
            cssclass: 'date',
            event: 'click focusin', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง EXP นี้ โดยการ คลิกเพียง คลิกเดียว
            loadfirst: true,
    }	// EXP
    {init_invoice}
    /* comment by por change sent from controller: 2014-10-04
     , {
     // invoice
     sSortDataType: "dom-text",
     type: 'text',
     onblur: "submit",
     event: 'click focusin',
     loadfirst: true,
     width: '75%',
     }
     */
    , {//qty
    sSortDataType: "dom-text",
            sType: "numeric",
            type: 'text',
            onblur: "submit",
            event: 'click keyup',
            is_required: true, //EDIT BY BALL 2014-04-23 เพิ่ม require
            loadfirst: true,
            cssclass: "required number",
            fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 24-10-2013
            calculate_qty();
            }
    }//end qty
    , {
    //loadurl: '<?php //echo site_url() . "/pre_receive/getProductUnit";                                                                                                                                                                                                                                                       ?>',
    data : master_product_unit,
            loadtype: 'POST',
            type: 'select',
            event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Unit นี้ โดยการ คลิกเพียง คลิกเดียว
            onblur: 'submit',
            sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    oTable.fnUpdate(value, rowIndex, ci_unit_id);
                    return value;
            }
    }
    {priceperunit} //ราคาต่อหน่วย
    {unitofprice} //หน่วยของราคา
    , null //ราคารวม
            , { //remark
            onblur: 'submit',
                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
            }
    , null
            , null
            , null
    ]
    });
            $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_supplier_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
            //CONFIG XML
            if (statusprice != true){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
    }

    if (!conf_inv){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_invoice, false);
    }
    //END CONFIG XML

    $('#showProductTable tbody tr td[title]').hover(function() {

    // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา
    var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;
            if (chk_title != chk_innerHTML){
    $(this).show_tooltip();
    }
    // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา

    }, function() {
    $(this).hide_tooltip();
    });
            // Floating menu in product detail.
            setTimeout(function(){
            $('#showProductTable').floatThead();
            }, 1000);
//            $('.lot form input').on('keyup', function () {
//                console.log("TEST");
//            //var disc = $(this).closest('td').next().find('input').val();
//            //console.log(disc);
//    });
    }

    function removeItem(obj) {
    var index = $(obj).closest("table tr").index();
            var oTable = $('#showProductTable').dataTable();
            var data = oTable.fnGetData(index);
            // Refresh index number after delete rec.
            // BALL
            oTable.fnDeleteRow(index, function() {
            var rows = oTable.fnGetNodes();
                    for (var i = 0; i < rows.length; i++)
            {
            $(rows[i]).find("td:eq(0)").html(i + 1);
            }
            });
            // End Refresh
            calculate_qty(); // add by kik : 25-10-2013
    }

    function deleteItem(obj) {
    var index = $(obj).closest("table tr").index();
            var oTable = $('#showProductTable').dataTable();
            var data = oTable.fnGetData(index);
            data = data.join(separator); // Add By Akkarapol, 28/01/2014, set delimiter from ',' to separator in variable data

            // Refresh index number after delete rec.
            // BALL
            oTable.fnDeleteRow(index, function() {
            var rows = oTable.fnGetNodes();
                    for (var i = 0; i < rows.length; i++)
            {
            $(rows[i]).find("td:eq(0)").html(i + 1);
            }
            });
            // End Refresh
            var f = document.getElementById(form_name);
            var prodDelItem = document.createElement("input");
            prodDelItem.setAttribute('type', "hidden");
            prodDelItem.setAttribute('name', "prod_del_list[]");
            prodDelItem.setAttribute('value', data);
            f.appendChild(prodDelItem);
            calculate_qty(); // add by kik : 25-10-2013
    }

    function  validate_format_input(value){
    var pattern = new RegExp(/^([a-zA-Z]+\s)*[a-zA-Z]+$/);
            return pattern.test(value);
    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
    global_module = module;
            global_sub_module = sub_module;
            global_action_value = action_value;
            global_next_state = next_state;
            curent_flow_action = $(elm).data('dialog');
            $("input[name='prod_list[]']").remove(); //ADD BY POR clear ค่าใน datatable ก่อนเก็บค่าใหม่
            //#ISSUE 3034 Reject Document
            //#เพิ่มในส่วนของ reject and (reject and return)
            //#start if check Sub_Module : by kik : 2013-12-11

            // Add flag for prevent duplicate data.
            // BALL
            if (flagSubmit) {
    alert('Please wait, System processing your request.');
            return false;
    }

    flagSubmit = true;
            if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction') {
    var statusisValidateForm = validateForm();
    } else {
    statusisValidateForm = true;
    }

    if (statusisValidateForm === true) {
    // Add By Akkarapol, 03/12/2013, add script for check duplicate External Document when submit form
    var doc_refer_ext = $("[name='doc_refer_ext']").val().toUpperCase();
            var flow_id = $("[name='flow_id']").val();
//                                  if ($("[name='flow_id']").val() == "") {
            if (doc_refer_ext != "") {
    var dataSet = {
    doc_refer_ext: doc_refer_ext,
            Process_Type: 'INBOUND',
            flow_id: flow_id
    }
    var chk_doc_ext = false;
            $.ajaxSetup({async: false});
            $.post('<?php echo site_url() . "/pre_receive/chk_doc_ext_duplicate" ?>', dataSet, function(data) {
            if (data) {
            chk_doc_ext = true;
            } else {
            flagSubmit = false;
            }
            }, "html");
            if (!chk_doc_ext) {
    alert('External Document is Duplicate');
            flagSubmit = false;
            return false;
    }
    }
    // END Add By Akkarapol, 03/12/2013, add script for check duplicate External Document when submit form

    //check input container
    //if($("#doc_refer_container").val()==""){
    if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction') {
    if (conf_cont){
    if ($("#doc_refer_container option").length <= 0){
    alert("Please Input Container");
            flagSubmit = false;
            return false;
    }
    }
    }


    var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
    alert("Please Select Product Order Detail");
            flagSubmit = false;
            return false;
    }

    var is_pending = $("[name='is_pending']").prop('checked');
            var is_repackage = $("[name='is_repackage']").prop('checked');
            //#ISSUE 3034 Reject Document
            //#เพิ่มในส่วนของ reject and (reject and return)
            //#start if check Sub_Module : by kik : 2013-12-11
            if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction') {

    global_rowData = rowData;
            for (i in rowData) {
    //add variable reg_prod_mfd and reg_prod_exp for check format date product_mfd and product_exp (dd/mm/yyyy) only : by kik : 2013-11-26
    var reg_prod_mfd = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/g;
            var reg_prod_exp = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/g;
            //            #2064,
            //            DATE:2013 - 08 - 27
            //            #BY:Akkarapol
            //            START
            // Add By Akkarapol, 17/09/2013, Set DomCode ที่ไม่ต้องเช็ค product,suplier เพื่อเอาไปใช้กับฟังก์ชั่น ereg
            var spl = $("[name='doc_refer_ext']").val().toUpperCase();
            if (spl.match(<?= $noChkSup ?>)) {

    } else {
    supplier_id = $("[name='shipper_id']").val();
            item_supplier_id = rowData[i][ci_supplier_id];
            if (supplier_id != item_supplier_id) {
//    alert('Please Check Your Supplier');
//            flagSubmit = false;
//            return false;
    }
    }
    //END #2064,

    //Start add code for check validate date text format dd/mm/yyyy only
    //by kik : 2013-11-26
    prod_mfd = rowData[i][ci_mfd];
            if (prod_mfd != "" && !(reg_prod_mfd.test(prod_mfd))) {
    alert('Please fill Product Mfd format dd/mm/yyyy only. example 31/01/2000');
            flagSubmit = false;
            return false;
    }

    prod_exp = rowData[i][ci_exp];
            if (prod_exp != "" && !(reg_prod_exp.test(prod_exp))) {
    alert('Please fill Product Exp format dd/mm/yyyy only. example 31/01/2000');
            flagSubmit = false;
            return false;
    }
    //end add code for check validate date text format dd/mm/yyyy only : by kik : 2013-11-26


    qty = rowData[i][ci_reserv_qty];
            if (qty == "" || qty == 0) {
    alert('Please fill all Receive Qty');
            flagSubmit = false;
            return false;
    }

    qty = parseFloat(qty);
            if (qty < 0) {
    alert('Negative Receive Qty is not allow');
            flagSubmit = false;
            return false;
    }

    if (statusprice == true){
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




    // Add By Akkarapol, 18/09/2013, ตรวจสอบว่า Product MFD. ที่เลือกมานั้น มี ShelfLife ตามที่เซ็ตค่าใน Master หรือไม่
    var chk_prod_code = rowData[i][ci_prod_code];
            var chk_mfd = rowData[i][ci_mfd];
            if (chk_mfd != "") {

    var dataSet = {
    chk_prod_code: chk_prod_code,
            chk_mfd: chk_mfd
    }
    var isMFDDate = 'N';
            $.ajaxSetup({async: false});
            $.post('<?php echo site_url() . "/pre_receive/chkMFDDateOfProduct" ?>', dataSet, function(data) {
            if (data != 'Ok') {
            alert(data);
                    flagSubmit = false;
                    return false;
            } else {
            isMFDDate = 'Y';
            }
            }, "html");
            if (isMFDDate == 'Y') {

    } else {
    flagSubmit = false;
            return false;
    }
    }
    // END Add By Akkarapol, 18/09/2013, ตรวจสอบว่า Product MFD. ที่เลือกมานั้น มี ShelfLife ตามที่เซ็ตค่าใน Master หรือไม่



    // Add By Akkarapol, 16/09/2013, ตรวจสอบว่า Product Exp. ที่เลือกมานั้น มี Aging ตามที่เซ็ตค่าใน Master หรือไม่
    var chk_prod_code = rowData[i][ci_prod_code];
            var chk_exp = rowData[i][ci_exp];
	    var chk_mfd = rowData[i][ci_mfd];
            if (chk_exp != "") {

    var dataSet = {
    chk_prod_code: chk_prod_code,
            chk_exp: chk_exp,
	    chk_mfd: chk_mfd
    }
    var isExpDate = 'N';
            $.ajaxSetup({async: false});
            $.post('<?php echo site_url() . "/pre_receive/chkExpDateOfProduct" ?>', dataSet, function(data) {
            if (data != 'Ok') {
            alert(data);
                    flagSubmit = false;
                    return false;
            } else {
            isExpDate = 'Y';
            }
            }, "html");
            if (isExpDate == 'Y') {

    } else {
    flagSubmit = false;
            return false;
    }
    }
    // END Add By Akkarapol, 16/09/2013, ตรวจสอบว่า Product Exp. ที่เลือกมานั้น มี Aging ตามที่เซ็ตค่าใน Master หรือไม่

    }


    }// end if check Sub_Module : by kik : 2013-12-11

    check_receive_type();
            //if (confirm("Are you sure to action " + action_value + "?")) {
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
            for (i in oTable)
    {
    var prod_data = "";
            $.each(oTable[i], function(idx, elm_val){
            prod_data += strip(elm_val) + separator;
            });
            prod_data = prod_data.substring(0, prod_data.length - 3);
            var prodItem = document.createElement("input");
            prodItem.setAttribute('type', "hidden");
            prodItem.setAttribute('name', "prod_list[]");
            prodItem.setAttribute('value', prod_data);
            f.appendChild(prodItem);
    }

    $.each(ci_list, function(i, obj){
    var ci_item = document.createElement("input");
            ci_item.setAttribute('type', "hidden");
            ci_item.setAttribute('name', obj.name);
            ci_item.setAttribute('value', obj.value);
            f.appendChild(ci_item);
    });
            //return false;
            // START DEBUG

            //var data_form = $("#" + form_name).serialize(); //comment by por 2014-033-07
            global_data_form = $("#" + form_name).serialize();
            var message = "";
            if (global_sub_module != 'rejectAction' && global_sub_module != 'rejectAndReturnAction'){
    validation_data();
    } else{
    var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
            $('#div_for_alert_message').html(mess);
            $('#div_for_modal_message').modal('show').css({
    'margin-left': function() {
    return ($(window).width() - $(this).width()) / 2;
    }
    });
    }

    //            } else {
    //            	flagSubmit = false;
    //            	return false;
    //            }
    // close confirm action
    } else {
    alert("Please Check Your Require Information (Red label).");
            flagSubmit = false;
            return false;
    }

    // Add confirm box after pass 30 seconds, ask user want to re-submit?
    var t = setTimeout(function(){
    //console.log(flagSubmit);
    if (confirm('Oop server lost signal, Are you want to try submit again?') && flagSubmit == true) {
    flagSubmit = false;
            clearTimeout(t);
            postRequestAction(module, sub_module, action_value, next_state);
    } else {
    clearTimeout(t);
            flagSubmit = false;
    }
    }, 30000);
            // End check
    }

    function cancel() {
    if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
    url = "<?php echo site_url(); ?>/flow/flowPreReceiveList";
            redirect(url)
    }
    }

    function validateForm() {
    $("form").validate({
    rules: {
    renter_id: {required: true}
    , shipper_id: {required: true}
    , consignee_id: {required: true}
    , doc_refer_ext: {required: true}
    , est_receive_date: {required: true}
    , receive_type: {required: true}
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






    function lookup() {
//        var minChar = 1;
//        if ($('#productCode').val().length >= minChar) {
//            if ($('#productCode').val() != '') {
//                var shipper_id = '';
//                var spl = $("[name='doc_refer_ext']").val().toUpperCase();
//                if (!spl.match(<?= $noChkSup ?>)) {
//                    shipper_id = $("[name='shipper_id']").val();
//                }
//
//                $.post("<?php echo site_url(); ?>/pre_receive/showProductList", {text_search: $('#productCode').val(), supplier_id: shipper_id}, function(val, data) {
//                    if (data.length > 0) {
//                        $('#suggestions').show();
//                        $('#autoSuggestionsList').html(val, data);
//                    }
//                });
//            }
//        }
    }

    function fill(id, code) {
    $('#productCode').val(code);
            $('#formProductCode').submit();
            $('#productCode').val('');
            setTimeout("$('#suggestions').hide();", 1);
    }

    function hideSuggestions() {
    setTimeout("$('#suggestions').hide();", 100);
    }

    function initProductsDatatables(results)
    {
    if (oTable != null) {
    oTable.fnDestroy();
    }

    oTable = $('#modal_data_table').dataTable({
    "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "iDisplayLength": 100,
            "sPaginationType": "full_numbers",
            "bProcessing": true,
            "bServerSide": true,
            "fnRowCallback" : processRow, // Add for trigger row event
            "sAjaxSource": "<?php echo site_url(); ?>/pre_receive/productList",
            "fnServerData": function(sSource, aoData, fnCallback) {
            var spl = $("[name='doc_refer_ext']").val().toUpperCase();
                    // Add By Akkarapol, 27/11/2013, เพิ่มการตรวจสอบว่า ถ้า doc_refer_ext เป็นไปตามที่เซ็ทไว้ ก็ไม่ต้องให้ตรวจสอบด้วย supplier_id ในการ query Product ออกมา
                    if (spl.match(<?= $noChkSup ?>)) {
            aoData.push(
            {"name": "post_val", "value": $('#productCode').val()}
            );
            } else {
            aoData.push(
            {"name": "post_val", "value": $('#productCode').val()},
            {"name": "supplier_id", "value": $("[name='shipper_id']").val()}
            );
            }
            // END Add By Akkarapol, 27/11/2013, เพิ่มการตรวจสอบว่า ถ้า doc_refer_ext เป็นไปตามที่เซ็ทไว้ ก็ไม่ต้องให้ตรวจสอบด้วย supplier_id ในการ query Product ออกมา

            $.getJSON(sSource, aoData, function(json) {
            fnCallback(json);
                    //                              $('.fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-tl.ui-corner-tr.ui-helper-clearfix').hide(); // Add BY Akkarapol, 27/11/2013, เซ็ตให้ แถบด้านบนของ Modal ที่มี "Show 100 entries" กับ แถบ Search ซ่อนเอาไว้ไม่ต้องแสดง // COmment By Akkarapol, 29/11/2013, คอมเม้นต์ทิ้งเพราะตอนนี้อยากแสดงช่อง search แล้ว

                    // Add By Akkarapol, 27/11/2013, เพิ่มสำหรับวน loop เซ็ตให้ checkbox มัน checked ตามที่ได้ถูก check ไว้ในกรณีที่เปลี่ยนหน้าไปหน้าอื่น แล้วกลับมายังหน้านั้น จะทำให้ checkbox จะยังถูก checked อยู่
                    for (i in allVals) {
            $('#chkBoxVal' + allVals[i]).prop('checked', true);
            }
            // END Add By Akkarapol, 27/11/2013, เพิ่มสำหรับวน loop เซ็ตให้ checkbox มัน checked ตามที่ได้ถูก check ไว้ในกรณีที่เปลี่ยนหน้าไปหน้าอื่น แล้วกลับมายังหน้านั้น จะทำให้ checkbox จะยังถูก checked อยู่

            });
            },
    });
    }


//////////////////////////////////////////////////////////////////

    function reInitProductTable() {
    $('#showProductTable').dataTable({
    "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "iDisplayLength": 250,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [{
            "sWidth": "3%",
                    "sClass": "center",
                    "aTargets": [0]
            }, {
            "sWidth": "5%",
                    "sClass": "center",
                    "aTargets": [1]
            }, {
            "sWidth": "30%",
                    "sClass": "left_text",
                    "aTargets": [2]
            }, {
            "sWidth": "7%",
                    "sClass": "left_text obj_status",
                    "aTargets": [3]
            }, {
            "sWidth": "7%",
                    "sClass": "left_text obj_sub_status",
                    "aTargets": [4]
            }, {
            "sWidth": "7%",
                    "sClass": "left_text lot",
                    "aTargets": [5]
            }, {
            "sWidth": "7%",
                    "sClass": "left_text serial",
                    "aTargets": [6]
            }, {
            "sWidth": "7%",
                    "sClass": "center obj_mfg",
                    "aTargets": [7]
            }, {
            "sWidth": "7%",
                    "sClass": "center obj_exp",
                    "aTargets": [8]
            }, {
            "sWidth": "7%",
                    "sClass": "left_text",
                    "aTargets": [9]
            }, {
            "sWidth": "5%",
                    "sClass": "right_text set_number_format",
                    "aTargets": [10]
            }, {
            "sWidth": "7%",
                    "sClass": "center",
                    "aTargets": [11]
            }, {
            "sWidth": "7%",
                    "sClass": "right_text set_number_format",
                    "aTargets": [12]
            }, {
            "sWidth": "5%",
                    "sClass": "center",
                    "aTargets": [13]
            }, {
            "sWidth": "5%",
                    "sClass": "right_text set_number_format",
                    "aTargets": [14]
            }, {
            "sWidth": "5%",
                    "sClass": "center",
                    "aTargets": [15]
            }, {
            "sWidth": "3%",
                    "sClass": "center",
                    "aTargets": [16]
            }]

    }).makeEditable({
    sUpdateURL: function(value, settings) {
    return value;
    }
    , "aoColumns": [
            null
            , null
            , null
            , {data: JSON.stringify(master_product_status),
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    select_filter_active : true,
                    select_filter_by : "PENDING",
                    sUpdateURL: function(value, settings) {
                    var oTable = $('#showProductTable').dataTable();
                            var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                            oTable.fnUpdate(value, rowIndex, ci_prod_status);
                            return value;
                    },
            }
    , {data : master_product_sub_status,
            event: 'click',
            type: 'select',
            onblur: 'submit',
            sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    oTable.fnUpdate(value, rowIndex, ci_prod_sub_status);
                    return value;
            }
    }
    , {sSortDataType: "dom-text",
            type: 'text',
            onblur: "submit",
            event: 'click focusin',
            loadfirst: true, // ต้องปิด ไว้เพื่อแก้ปัญหาเฉพาะหน้าไปก่อน แผนคือการเคลียร์ค่า html ใน data array ทั้งหมด : comment by kik , 05-11-2013
            width: '75%',          
    }
    , {sSortDataType: "dom-text",
            type: 'text',
            onblur: "submit",
            event: 'click focusin',
            loadfirst: true, //ต้องปิด ไว้เพื่อแก้ปัญหาเฉพาะหน้าไปก่อน แผนคือการเคลียร์ค่า html ใน data array ทั้งหมด : comment by kik , 05-11-2013
            width: '75%',         
    }
    , {
    onblur: 'submit',
            type: 'datepicker',
            cssclass: 'date',
            event: 'click focusin',
            is_required: false, //EDIT BY BALL 2014-04-23 เพิ่ม require
            loadfirst: true, // Comment By Akkarapol, 14/10/2013, คอมเม้นต์ loadfirst ทิ้งเนื่องจาก ช่อง MFD นี้ยังไม่จำเป็นต้องกรอกในขั้นตอนนี้ก็ได้
    }   // MFD
    , {
    onblur: 'submit',
            type: 'datepicker',
            cssclass: 'date',
            event: 'click focusin', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง EXP นี้ โดยการ คลิกเพียง คลิกเดียว
            loadfirst: true,
    }   // EXP
    {reinit_invoice}
    , {//qty
    sSortDataType: "dom-text",
            sType: "numeric",
            type: 'text',
            onblur: "submit",
            event: 'click keyup',
            is_required: true, //EDIT BY BALL 2014-04-23 เพิ่ม require
            loadfirst: true,
            cssclass: "required number",
            fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 24-10-2013
            calculate_qty();
            }
    }//end qty
    , {
    data : master_product_unit,
            loadtype: 'POST',
            type: 'select',
            event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Unit นี้ โดยการ คลิกเพียง คลิกเดียว
            onblur: 'submit',
            sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    oTable.fnUpdate(value, rowIndex, ci_unit_id);
                    return value;
            }
    }
    {priceperunit} //ราคาต่อหน่วย
    {unitofprice} //หน่วยของราคา
    , null //ราคารวม
            , { //remark
            onblur: 'submit',
                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
            }
    , null
            , null
            , null
    ]
    });

            $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_supplier_id, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
            //CONFIG XML
            if (statusprice != true){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
    }
    if (!conf_inv) {
    $('#showProductTable').dataTable().fnSetColumnVis(ci_invoice, false);
    }
    //END CONFIG XML

    $('#showProductTable tbody tr td[title]').hover(function() {

    // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา
    var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;
            if (chk_title != chk_innerHTML){
    $(this).show_tooltip();
    }
    // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา

    // Floating menu in product detail.
    setTimeout(function(){
    $('#showProductTable').floatThead();
    }, 1000);
    }, function() {
    $(this).hide_tooltip();
    });
    }
</script>
<style>
    #myModal {
        width: 1170px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -600px;
    }

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

</style>
<div class="well">
    <form id="form_receive" method="post" action="">
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
    $receive_date = "";
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
            <legend>&nbsp;Add Order Pre-Receive&nbsp;</legend>

            <table width="98%">
                <tr>
                    <td align="right"><?php echo _lang("renter"); ?> :</td>
                    <td align="left"><?php echo form_dropdown('renter_id', $renter_list, $renter_id, ' class="required" '); ?></td>
                    <td align="right"><?php echo _lang("shipper"); ?> :</td>
                    <td align="left"><?php echo form_dropdown('shipper_id', $shipper_list, $shipper_id, 'class="required" '); ?></td>
                    <td align="right"><?php echo _lang("consignee"); ?> :</td>
                    <td align="left"><?php echo form_dropdown('consignee_id', $consignee_list, $consignee_id, 'class="required"'); ?></td>
                </tr>
                <tr>
                    <td align="right"><?php echo _lang("document_no"); ?> :</td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate GRN" disabled="disabled"  style="text-transform: uppercase"'); ?></td> <!-- เปลี่ยน disabled เป็น disabled="disabled" เพื่อให้รองรับกับ php5 และเอา class required ออก เพราะถึงอย่างไรก็เป็นช่องที่ disable ไว้อยู่แล้ว -->
                    <td align="right"><?php echo _lang("document_external"); ?> :</td>
                    <td align="left"><?php echo form_input('doc_refer_ext', $doc_refer_ext, 'placeholder="' . DOCUMENT_EXT . '" class="required document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("document_internal"); ?> :</td>
                    <td align="left"><?php echo form_input('doc_refer_int', $doc_refer_int, 'placeholder="' . DOCUMENT_INT . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
<!--                <tr>
                    <td align="right"><?php echo _lang("invoice_no"); ?> :</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', $doc_refer_inv, 'id="doc_refer_inv" placeholder="' . DOCUMENT_INV . '" class="document" style="text-transform: uppercase"'); ?> </td>
                    <td align="right"><?php echo _lang("customs_entry"); ?> :</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', $doc_refer_ce, 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("bl_no"); ?> :</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', $doc_refer_bl, 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>-->
                <tr>
                    <td align="right"><?php echo _lang("receive_type"); ?> :</td>
                    <td align="left"><?php echo form_dropdown('receive_type', $receive_list, $receive_type, "onChange='changeOption(this)' id='receive_type'"); ?></td>
                    <td align="right"><?php echo _lang("receive_date"); ?> :</td>
                    <td align="left"><?php echo form_input('receive_date', $receive_date, 'id="receive_date" placeholder="Receive Date" disabled '); ?></td>
                    <td align="right"><?php echo _lang("asn"); ?> :</td>
                    <td align="left"><?php echo form_input('est_receive_date', $est_receive_date, 'id="est_receive_date" placeholder="Advanced Shipment Notices" '); ?></td>                   
                </tr>
                <tr valign="center"> <!-- Edit and Add By Joke 21/06/2016  -->     
<?php if ($conf_cont): ?>
                        <td align="right"><?php echo _lang("container"); ?> : </td>
                        <td>
    <?php echo form_multiselect('doc_refer_container', (empty($doc_refer_container) ? array() : $doc_refer_container), NULL, 'disabled="disabled" size="3" id="doc_refer_container" placeholder="' . DOCUMENT_CONTAINER . '"  style="text-transform: uppercase"'); ?>
                            <img id="add_container" src="<?php echo base_url("images/add.png") ?>" style="width: 22px; height: 22px; margin-bottom: 3px; cursor: pointer;" />
                            <input type="hidden" id="doc_refer_con_size" name="doc_refer_con_size">
                        </td>
<?php endif; ?>
<!--                    <td align="right">
                        <label >Destination :</label>
                    </td>
                    <td>
                        <textarea id="DestinationDetail" name="DestinationDetail" rows="3" ><?php echo $DestinationDetail ?></textarea>
                    </td>  -->
                    <td align="right" ><?php echo _lang("remark"); ?> :</td>
                    <td align="left">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2"  placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
                    </td>                    
<!--                    <td align="right">
                        <label style="margin-top: -34px;">Delivery Time : </label>
                    </td>
                    <td align="left">
                        <input type="text" id="DeliveryTime" name="DeliveryTime" value="<?php echo $DeliveryTime ?>" style="margin-top: -47px;">
                    </td>  -->
                    <td></td>
                    <td align="left" >
                        <?php echo form_checkbox(array('name' => 'is_pending', 'id' => 'is_pending'), ACTIVE, $is_pending); ?>&nbsp;Pending&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox(array('name' => 'is_repackage', 'id' => 'is_repackage'), ACTIVE, $is_repackage); ?>&nbsp;Re-Package <br>
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                    </td>  
                </tr>
<!--                <tr> 
                    <td></td>
                    <td align="left" >
<?php echo form_checkbox(array('name' => 'is_pending', 'id' => 'is_pending'), ACTIVE, $is_pending); ?>&nbsp;Pending&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox(array('name' => 'is_repackage', 'id' => 'is_repackage'), ACTIVE, $is_repackage); ?>&nbsp;Re-Package <br>
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                    </td>  
                    <td align="right" ><?php echo _lang("remark"); ?> :</td>
                    <td align="left">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2"  placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
                    </td>
                </tr> Edit and Add By Joke 21/06/2016  

                <tr valign="center">

                </tr>-->
            </table>
        </fieldset>
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
        <input type="hidden" id="container_list" name="container_list" value="<?php echo $container_list; ?>" />
        <input type="hidden" id="container_size_list" name="container_size_list" value="<?php echo $container_size_list; ?>" />
    </form>
    <fieldset class="well">
        <legend>&nbsp;Product Detail&nbsp;</legend>
        <table width="100%">
            <tr>
                <td align="left" width="100"><?php echo _lang('product_code'); ?></td>
                <td align="left">
                    <form id="formProductCode" action="" method="post">
                        <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 890px;">
                            <div style="position: relative;">
                        <?php echo form_input("productCode", "", "id='productCode' placeholder='" . _lang('product_code') . "' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 850px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                            <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 850px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?>
                            </div>
                        </table>
                        <a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn" style="float:left;">Get Detail</a>

                        <div class="suggestionsBox" id="suggestions" style="display:none;">
                            <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
                        </div>
                        <input type="hidden" id="product_id" name="product_code" />
                        <input type="submit" style="display:none;">
                    </form>
                </td>
                <td align="right"></td>
                <td align="left">&nbsp;</td>
                <td align="right">&nbsp;</td>
                <td align="left">&nbsp;</td>
            </tr>
            <tr align="center" >
                <td align="center" colspan="6" id="showDataTable" >
                    <table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
                        <thead id="product_detail_head">
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
                            _lang('receive_qty'),
                            _lang('unit'),
                            _lang('price_per_unit'),
                            _lang('unit_price'),
                            _lang('all_price'),
                            _lang('remark'),
                            _lang('del'),
                            "Product_Id",
                            "Product_Status",
                            "Product_Sub_Status",
                            "Unit_Id",
                            "Item_Id",
                            "Supplier_Id",
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
                        $sumQty = 0;    //add sumQty variable for calculate total qty : by kik : 24-10-2013
                        $sumPriceUnit = 0; //ADD BY POR 2014-01-09 ราคารวมทั้งหมดต่อหน่วย
                        $sumPrice = 0; //ADD BY POR 2014-01-09 ราคารวมทั้งหมด

                        if (isset($order_deatil)) {
                            $str_body = "";
                            $j = 1;
                            foreach ($order_deatil as $order_column) {
//                                    P(htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES));
                                $str_body .= "<tr>";
                                $str_body .= "<td>" . $j . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Code . "</td>";
//                                    $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20131127
                                $str_body .= "<td title=\"" . str_replace('"', '&quot;', $order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";
                                $str_body .= "<td>" . @$order_column->unitprice_name . "</td>";
                                $str_body .= "<td>" . set_number_format(@$order_column->All_Price) . "</td>";
                                $str_body .= "<td>" . $order_column->Remark . "</td>";
                                $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Product_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Sub_Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Unit_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Item_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Supplier_Id . "</td>"; //Add by Akkarapol, 27/08/2013, เน€เธ�เธดเน�เธกเธ�เธฒเธฃเธ•เธฃเธงเธ�เธชเธญเธ�เธ•เธญเธ�เธ—เธตเน�เธ�เธฐ submit เธงเน�เธฒ product เธ—เธตเน�เธญเธขเธนเน�เน�เธ� datatable เธ�เธฐเธ•เน�เธญเธ�เน€เธ�เน�เธ�เธชเธดเธ�เธ�เน�เธฒเธ�เธญเธ� supplier เธ�เธ�เธ—เธตเน�เน€เธฅเธทเธญเธ�เธญเธขเธนเน�เน€เธ—เน�เธฒเธ�เธฑเน�เธ�
                                $str_body .= "<td style-\"display:none\">" . @$order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-17 เพิ่ม ID ของ unit price
                                $str_body .= "</tr>";
                                $j++;
                                $sumQty+=$order_column->Reserv_Qty;     // Add $sumQty for calculate total qty : by kik : 24-10-2013
                                $sumPriceUnit+=@$order_column->Price_Per_Unit;
                                $sumPrice+=@$order_column->All_Price;
                            }
                            echo $str_body;
                        }
                        ?>
                        </tbody>
                        <!-- show total qty : by kik : 24-10-2013-->
                        <tfoot>
                            <tr>
                                <th colspan="10" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                                <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_all_qty"><?php echo set_number_format($sumQty); ?></span></th>
                                <th></th>
                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                <th></th>
                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                <th colspan="9" class ='ui-state-default indent' ></th>
                            </tr>
                        </tfoot>
                        <!-- end show total qty : by kik : 24-10-2013-->
                    </table>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div id="show_product_full" style="position: absolute; width: 100px; height: 100px; display: none; background-color: #AEAEAE;"></div>
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Product Detail</h3>
        <input  value="" type="hidden" name="prdModalval" id="prdModalval" >
    </div>
    <div class="modal-body">
        <table id="modal_data_table" cellpadding="0" cellspacing="0" border="0" aria-describedby="modal_data_table_info" class="well" style="max-width: none;">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Product Code</th>
                    <th>Product Name English</th>
                    <th>Product Name Thai</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot></tfoot>
        </table>
    </div>
    <div class="modal-footer">
        <div style="float:left;">
            <input class="btn btn-sm red" value="Select All" type="button" id="select_all">
            <input class="btn btn-sm red" value="Deselect All" type="button" id="deselect_all">
        </div>
        <div style="float:right;">
            <input class="btn btn-primary" value="Select" type="submit" id="search_submit">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        </div>
    </div>
</div>
    <?php $this->load->view('element_modal_message_alert'); ?>
<?php $this->load->view('element_modal'); ?>
