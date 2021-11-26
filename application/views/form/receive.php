<script>
    // Read config from controller
    var conf_pallet = '<?php echo ($conf_pallet) ? true : false; ?>';
    var conf_inv = '<?php echo ($conf_inv) ? true : false ?>';
    var conf_cont = '<?php echo ($conf_cont) ? true : false ?>';
    
    var statusprice = '<?php echo ($statusprice) ? true : false; ?>';
    var master_product_status = {};
    var master_product_sub_status = "";
    var master_product_unit = "";
    var master_container_size = "";
    var master_container_dropdown_list = '';
    var global_code_sub_status_return = '<?php echo $sub_status_return; ?>';
    var global_code_sub_status_repackage = '<?php echo $sub_status_repackage; ?>';
    var global_ci_prod_status_label = 3; // เปลี่ยนให้เป็นตาม index ของ dataTable ใน ช่องที่เป็น Product Status
    var global_ci_prod_sub_status_label = 4; // เปลี่ยนให้เป็นตาม index ของ dataTable ใน ช่องที่เป็น Product Sub Status

    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = '';
    var data_table_id_class = '#showProductTable';
    var redirect_after_save = site_url + "/flow/flowReceiveList";
    var state = '<?php echo $present_state; ?>';
    var allVals = new Array();
    var nowTemp = new Date();
    var separator = "<?php echo SEPARATOR; ?>";
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    var allVals = new Array();
    var form_name = "form_receive";
    var sub_module = '<?php echo $Sub_Module; ?>';
    // Set index of DataTable
    var ci_prod_code = 1;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_invoice = 9;
    var ci_container = 10;
    var ci_reserv_qty = 11;
    var ci_confirm_qty = 12;
    //Add By por 2014-01-09 เน€เธเธดเนเธกเธ•เธฑเธงเนเธเธฃ เธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข, เธซเธเนเธงเธข, เธฃเธฒเธเธฒเธฃเธงเธก
    var ci_price_per_unit = 14;
    var ci_unit_price = 15;
    var ci_all_price = 16;
    //END Add

    var ci_pallet = 17;
    var ci_remark = 18; //Edit by por 2014-01-13 เนเธเนเนเธเธเธฒเธเน€เธฅเธ 12 เน€เธเนเธ 15 เน€เธเธทเนเธญเธเธเธฒเธเธกเธตเธเธฒเธฃเน€เธเธดเนเธก column เน€เธฅเธขเธ—เธณเนเธซเนเธเธตเธขเนเน€เธเธฅเธตเนเธขเธ


    //Define Hidden Field Datatable
    var ci_prod_id = 19; //Edit by por 2014-01-13 เนเธเนเนเธเธเธฒเธเน€เธฅเธ 13 เน€เธเนเธ 16 เน€เธเธทเนเธญเธเธเธฒเธเธกเธตเธเธฒเธฃเน€เธเธดเนเธก column เน€เธฅเธขเธ—เธณเนเธซเนเธเธตเธขเนเน€เธเธฅเธตเนเธขเธ
    var ci_prod_status = 20; //Edit by por 2014-01-13 เนเธเนเนเธเธเธฒเธเน€เธฅเธ 14 เน€เธเนเธ 17 เน€เธเธทเนเธญเธเธเธฒเธเธกเธตเธเธฒเธฃเน€เธเธดเนเธก column เน€เธฅเธขเธ—เธณเนเธซเนเธเธตเธขเนเน€เธเธฅเธตเนเธขเธ
    var ci_prod_sub_status = 21; //Edit by por 2014-01-13 เนเธเนเนเธเธเธฒเธเน€เธฅเธ 15 เน€เธเนเธ 18 เน€เธเธทเนเธญเธเธเธฒเธเธกเธตเธเธฒเธฃเน€เธเธดเนเธก column เน€เธฅเธขเธ—เธณเนเธซเนเธเธตเธขเนเน€เธเธฅเธตเนเธขเธ
    var ci_unit_id = 22; //Edit by por 2014-01-13 เนเธเนเนเธเธเธฒเธเน€เธฅเธ 16 เน€เธเนเธ 19 เน€เธเธทเนเธญเธเธเธฒเธเธกเธตเธเธฒเธฃเน€เธเธดเนเธก column เน€เธฅเธขเธ—เธณเนเธซเนเธเธตเธขเนเน€เธเธฅเธตเนเธขเธ
    var ci_item_id = 23; //Edit by por 2014-01-13 เนเธเนเนเธเธเธฒเธเน€เธฅเธ 17 เน€เธเนเธ 20 เน€เธเธทเนเธญเธเธเธฒเธเธกเธตเธเธฒเธฃเน€เธเธดเนเธก column เน€เธฅเธขเธ—เธณเนเธซเนเธเธตเธขเนเน€เธเธฅเธตเนเธขเธ
    var ci_putaway_rule = 24; //Edit by por 2014-01-13 เนเธเนเนเธเธเธฒเธเน€เธฅเธ 18 เน€เธเนเธ 21 เน€เธเธทเนเธญเธเธเธฒเธเธกเธตเธเธฒเธฃเน€เธเธดเนเธก column เน€เธฅเธขเธ—เธณเนเธซเนเธเธตเธขเนเน€เธเธฅเธตเนเธขเธ


    if (sub_module == 'updateInfo') {
    var ci_unit_price_id = 26; //Edit by por 2014-01-17 เน€เธเธดเนเธกเธเธตเธขเน เธฃเธซเธฑเธช unit price
    var ci_cont_id = 27;
    } else {
    var ci_unit_price_id = 25;
    var ci_cont_id = 26;
    }


    var ci_list = [
    {name: 'ci_prod_code', value: ci_prod_code},
    {name: 'ci_lot', value: ci_lot},
    {name: 'ci_serial', value: ci_serial},
    {name: 'ci_mfd', value: ci_mfd},
    {name: 'ci_exp', value: ci_exp},
    {name: 'ci_invoice', value: ci_invoice}, // add by kik for show invoice : 20140709
    {name: 'ci_container', value: ci_container}, // add by kik for show invoice : 20140709
    {name: 'ci_pallet', value: ci_pallet}, // add by kik for show invoice : 20140709
    {name: 'ci_reserv_qty', value: ci_reserv_qty},
    {name: 'ci_remark', value: ci_remark},
    {name: 'ci_prod_id', value: ci_prod_id},
    {name: 'ci_prod_status', value: ci_prod_status},
    {name: 'ci_unit_id', value: ci_unit_id},
    {name: 'ci_item_id', value: ci_item_id},
    {name: 'ci_prod_sub_status', value: ci_prod_sub_status},
    {name: 'ci_confirm_qty', value: ci_confirm_qty},
            //Add By por 2014-01-13 เน€เธเธดเนเธกเธ•เธฑเธงเนเธเธฃ เธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข, เธซเธเนเธงเธข, เธฃเธฒเธเธฒเธฃเธงเธก
            {name: 'ci_price_per_unit', value: ci_price_per_unit},
    {name: 'ci_unit_price', value: ci_unit_price},
    {name: 'ci_all_price', value: ci_all_price},
    {name: 'ci_unit_price_id', value: ci_unit_price_id},
            //END Add
            {name: 'ci_cont_id', value: ci_cont_id}

    ]


            $(document).ready(function() {

    // Mask Input
//        $("#receive_date").inputmask("99/99/9999");//comment by kik : 20141103 : because when mouse over on textbox, data set default today every time
    $("#receive_date").datepicker({

    }).keypress(function(event) {
    event.preventDefault();
    }).on('changeDate', function(ev) {
    var selected_date = $("#receive_date").val();
    var today = get_today_dd_mm_yyyy();
    var diff_date = date_diff_of_dd_mm_yyyy(today, selected_date);
    if (diff_date > 7){
    alert("You can choose backward a Receive Date only a Day is less than 7 days.");
    $("#receive_date").val(today);
    } else if (diff_date < 0){
    alert("Can't choose forward a Receive Date.");
    $("#receive_date").val(today);
    }

    //$('#receive_date').datepicker('hide');
    }).bind("cut copy paste", function(e) {
    e.preventDefault();
    });
    // Preload master data for datatables
    $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', {"is_pending" : "Y"}, function(data) {
    $.extend(master_product_status, JSON.parse(data));
    });
    $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', function(data) {
    $.extend(master_product_status, JSON.parse(data));
    });
    $.post('<?php echo site_url() . "/pre_receive/getSubStatus"; ?>', function(data) {
    master_product_sub_status = data;
    });
    $.post('<?php echo site_url() . "/pre_receive/getProductUnit"; ?>', function(data) {
    master_product_unit = data;
    });
    //check config container before load data : by kik : 20141004
    if (conf_cont){
    $.post('<?php echo site_url("/receive/getContainerDropdownList"); ?>', {'order_id' : '<?php echo $order_id ?>'}, function(data) {
//            console.log("POR BA BA");
//            console.log(data);
    master_container_dropdown_list = data;
    });
    $.post('<?php echo site_url() . "/pre_receive/getContainerSize"; ?>', function(data) {
    master_container_size = data;
    });
    }





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
    var objImage2 = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
    objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
    dynamic_modal_body.empty().append(objDiv);
    } else{
    var objImage2 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width":"24px", "height":"24px", "marginBotton":"3px", "marginLeft":"3px", "cursor":"pointer"});
    objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
    dynamic_modal_body.append(objDiv);
    }
    });
    } else{  //case open
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
    var objInput3 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").css({"textTransform":"uppercase", "width":"350px"});
    var objSelect3 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width":"120px"});
    var _master_container_size3 = $.parseJSON(master_container_size);
    $.each(_master_container_size3, function(idx, val){
    var objOption3 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect3);
    });
    var objImage3 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width":"24px", "height":"24px", "height":"24px", "marginBottom":"3px", "marginLeft":"3px", "cursor":"pointer"});
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
    var temp = {};
    var container_list = $(".container_list");
    var container_list_size = $(".con_size");
    var container_list_id = $(".container_id");
    var container_size_name = '';
    var i = 0; //ADD BY POR 2014-10-01 : for check input container name
    $.each(container_list, function(idx, val){
    var container_size_id = $(container_list_size[idx]).val();
    var container_id = $(container_list_id[idx]).val();
    var container_name = $(val).val();
    if (container_name == ""){ //for check input container name : ADD BY POR 2014-10-01
    i++;
    } else{
    var objTemp = {};
    objTemp['id'] = container_id;
    objTemp['name'] = container_name.toUpperCase();
    objTemp['size'] = container_size_id;
    temp[idx] = objTemp;
    }
    });
    //if i > 0 => have some container name is null : ADD BY POR 2014-10-01
    if (i > 0){
    alert("Please input value container");
    return false;
    }
    //end if

    $("#container_list").val(JSON.stringify(temp));
    //SENT VALUE FOR EDIT
    $.post('<?php echo site_url() . "/receive/updateContainer" ?>', $("#form_receive").serialize(), function(data) {
    if (data.val == 'NO') {
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
    //$("#showProductTable").dataTable().fnDestroy();
    initProductTable();
    //var rowData = $('#showProductTable').dataTable().fnGetData();
    //console.log(get_dropdown_data);

//                        $('#showProductTable').row( obj_container ).remove().draw(data);
//                        setTimeout(function(){
    master_container_dropdown_list = data;
//                            initProductTable();
//                        }, 500);
    //$('#showProductTable tbody td.obj_container').html(data);
//                        for (i in rowData) {
//                            rowData[i][ci_container] = data;
//                        }




    alert('Save changes success');
    //console.log(master_container_dropdown_list);
    //ติดไว้ก่อนเรื่อง refresh
    $('#dynamic_modal').modal('hide');
    }
    });
    });
// Add By Akkarapol, 18/12/2013, เน€เธเธดเนเธก $('#showProductTable tbody tr td[title]').hover เน€เธเธฃเธฒเธฐ script เธ—เธตเนเน€เธฃเธตเธขเธเนเธเนเธเธฑเธเธเนเธเธฑเนเธ initProductTable เนเธกเนเธ—เธณเธเธฒเธ เธญเธฒเธเน€เธเธทเนเธญเธเธกเธฒเธเธฒเธเธงเนเธฒ เนเธกเนเนเธ”เนเธ–เธนเธ bind เธเธถเธเธเธณเธกเธฒเน€เธฃเธตเธขเธเนเธเนเนเธ $(document).ready เน€เธฅเธข
    $('#showProductTable tbody tr td[title]').hover(function() {

    // Add By Akkarapol, 23/12/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ เธ–เนเธฒ Product Name เธเธฑเธ Product Full Name เธเธฑเนเธ เนเธกเนเน€เธซเธกเธทเธญเธเธเธฑเธ เนเธซเนเนเธชเธ”เธ tooltip เธเธญเธ Product Full Name เธเธถเนเธเธกเธฒ
    var chk_title = $(this).attr('title');
    var chk_innerHTML = this.innerHTML;
    if (chk_title != chk_innerHTML) {
    $(this).show_tooltip();
    }
    // END Add By Akkarapol, 23/12/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ เธ–เนเธฒ Product Name เธเธฑเธ Product Full Name เธเธฑเนเธ เนเธกเนเน€เธซเธกเธทเธญเธเธเธฑเธ เนเธซเนเนเธชเธ”เธ tooltip เธเธญเธ Product Full Name เธเธถเนเธเธกเธฒ

    }, function() {
    $(this).hide_tooltip();
    });
    // END Add By Akkarapol, 18/12/2013, เน€เธเธดเนเธก $('#showProductTable tbody tr td[title]').hover เน€เธเธฃเธฒเธฐ script เธ—เธตเนเน€เธฃเธตเธขเธเนเธเนเธเธฑเธเธเนเธเธฑเนเธ initProductTable เนเธกเนเธ—เธณเธเธฒเธ เธญเธฒเธเน€เธเธทเนเธญเธเธกเธฒเธเธฒเธเธงเนเธฒ เนเธกเนเนเธ”เนเธ–เธนเธ bind เธเธถเธเธเธณเธกเธฒเน€เธฃเธตเธขเธเนเธเนเนเธ $(document).ready เน€เธฅเธข

    $('#bt-confirm').hide();
    // Edit Code by Ton! 20131001
    $('#receive_type').change(function() {
    var receiveType = $(this).val();
    var rowData = $('#showProductTable').dataTable().fnGetData();
    if (receiveType == 'RCV002') {//Return
    $('#showProductTable tbody td.obj_status').html('Normal');
    var status = "NORMAL";
    for (i in rowData) {
//                        rowData[i][3] = status;
    rowData[i][ci_prod_status] = status;
    rowData[i][ci_prod_sub_status] = '<?php echo $sub_status_return; ?>'; // Add by Akkarapol, 06/11/2013, เปลี่ยนจากการใช้แบบกำหนดค่าตายตัวใน code ไปใช้การส่งค่าจาก controller แทนข้อมูลจะได้ไม่ผิดอีก!!!!!
    }
    $('#showProductTable tbody td.obj_sub_status').text('Return');
    } else {
    $('#showProductTable tbody td.obj_sub_status').text('No Specified');
    for (i in rowData) {
    rowData[i][ci_prod_sub_status] = '<?php echo $sub_status_no_specefied; ?>';
    }
    if (receiveType == 'RCV003') {//Adjust
    $("[id='is_pending']").prop('checked', false);
    $("[id='is_pending']").attr("disabled", true);
    $('#showProductTable tbody .obj_status').html('Normal');
    var statusPending = "NORMAL";
    for (i in rowData) {
    rowData[i][ci_prod_status] = statusPending;
    rowData[i][3] = statusPending;
    }
    $("[id='is_repackage']").prop('checked', false);
    $("[id='is_repackage']").attr("disabled", true);
    var statusRepackage = "<?php echo $sub_status_repackage; ?>";
    for (i in rowData) {
    rowData[i][ci_prod_sub_status] = statusRepackage;
    rowData[i][4] = statusRepackage;
    }
    }
    }
    });
    // End Edit Code by Ton! 20131001

    // Pending ckeckbox
    $('#is_pending').click(function() {
    var status = "";
    if ($(this).prop('checked')) {
    $('#showProductTable tbody td.obj_status').html('Pending');
    status = "PENDING";
    } else {
    $('#showProductTable tbody td.obj_status').html('Normal');
    status = "NORMAL";
    }
    var rowData = $('#showProductTable').dataTable().fnGetData();
    var num_row = rowData.length;
    if (num_row > 0) {
    for (i in rowData) {
    rowData[i][ci_prod_status] = status;
    rowData[i][3] = status;
    }
    }
    });
    // Re-Package ckeckbox
    $('#is_repackage').click(function() {
    var status = "";
    if ($(this).prop('checked')) {
    $('#showProductTable tbody .obj_sub_status').html('Repackage');
//            status = "SS002";
    status = "<?php echo $sub_status_repackage; ?>";
    } else {
    $('#showProductTable tbody .obj_sub_status').html('Return');
//            status = "SS000";
    status = "<?php echo $sub_status_return; ?>";
    }
    var rowData = $('#showProductTable').dataTable().fnGetData();
    var num_row = rowData.length;
    if (num_row > 0) {
    for (i in rowData) {
    rowData[i][ci_prod_sub_status] = status;
    rowData[i][4] = status;
    }
    }
    });
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

    $('.required').each(function() {
    if ($(this).val() != '') {
    $(this).removeClass('required');
    }
    });
    $('[name="doc_refer_ext"]').keyup(function() {
    if ($(this).val() != '') {
    $(this).removeClass('required');
    } else {
    $(this).addClass('required');
    }
    });
    if (state == 3)   //ADD BY POR เธเธฃเธ“เธตเธ—เธตเนเธญเธขเธนเนเนเธเธฃเธฐเธซเธงเนเธฒเธเธฃเธญ confirm เธเธฒเธ HH เธเธฐเนเธกเนเธชเธฒเธกเธฒเธฃเธ–เนเธเนเนเธเธญเธฐเนเธฃเนเธ”เน
    {
    initProductTableComfirm();
    }
    else   //เธเธฃเธ“เธตเนเธกเนเนเธ”เนเธฃเธญเธ confirm เธเธฐเธชเธฒเธกเธฒเธฃเธ–เนเธเนเนเธเธเนเธญเธกเธนเธฅเนเธ datatable เนเธ”เน
    {
    setTimeout(function(){
    initProductTable();
    }, 1000);
    }


    $.validator.addMethod("document", function(value, element) {
    return this.optional(element) || /^[a-zA-Z0-9._/\\#,-]+$/i.test(value);
    }, "Document Format is invalid.");
    if ("RCV002" == $("[name='receive_type']").val()) {
    $("[name='is_pending']").attr("disabled", true);
    $("[name='is_repackage']").attr("disabled", false);
    } else {
    $("[name='is_repackage']").attr("disabled", true);
    $("[name='is_pending']").attr("disabled", false);
    }

    /* $("#est_receive_date").datepicker({onRender: function(date) {
     //return date.valueOf() < now.valueOf() ? 'disabled' : '';
     }}).keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
     //$('#sDate1').text($('#datepicker').data('date'));
     //$('#est_receive_date').datepicker('hide');
     }).bind("cut copy paste",function(e) {
     e.preventDefault();
     });
     
     $("#receive_date").keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
     //$('#receive_date').datepicker('hide');
     }).bind("cut copy paste",function(e) {
     e.preventDefault();
     });
     */

    });
    function initProductTable() {

    var oTable = $('#showProductTable').dataTable({
    "bAutowidth": false,
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "iDisplayLength": 250,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "bAutoWidth": false,
            //"sScrollY": "100%", // Remove By Ball, Cause it make main table longer
            //"sScrollX": "100%", // change to use 100%      // Remove By Ball, Cause it make main table longer
            // Add By Akkarapol, 14/10/2013, เน€เธเธดเนเธกเธเธฒเธฃเธเธฑเธ”เธเธฒเธฃ column เธเธญเธ datatable
            "aoColumnDefs": [
            {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
            {"sWidth": "15%", "sClass": "indent left_text", "aTargets": [2]},
            {"sWidth": "7%", "sClass": "left_text obj_status", "aTargets": [3]}, // Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text obj_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text lot", "aTargets": [5]},
            {"sWidth": "7%", "sClass": "left_text serial", "aTargets": [6]},
            {"sWidth": "7%", "sClass": "center obj_mfg", "aTargets": [7]},
            {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [8]},
            {"sWidth": "7%", "sClass": "center", "aTargets": [9]},
            {"sWidth": "7%", "sClass": "center obj_container", "aTargets": [10]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [11]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [12]},
            {"sWidth": "7%", "sClass": "center", "aTargets": [13]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [14]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [15]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [16]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [17]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [18]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [19]},
            ]
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
            },
    }
//                , null
//                        , null
    , {
    onblur: 'submit',
            type: 'text',
            event: 'click focusin',
            loadfirst: true,
    }// parser from controllers. Edit by Ton! 20130924
    , {
    onblur: 'submit',
            type: 'text',
            event: 'click focusin',
            loadfirst: true,
    }// parser from controllers. Edit by Ton! 20130924
    , {
            onblur: 'submit',
            type: 'datepicker',
            cssclass: 'date',
            event: 'click', // Add By Akkarapol, 14/10/2013, เน€เธเนเธ•เนเธซเน event เนเธเธเธฒเธฃเนเธเนเนเธเธเนเธญเธกเธนเธฅเธเนเธญเธ MFD เธเธตเน เนเธ”เธขเธเธฒเธฃ เธเธฅเธดเธเน€เธเธตเธขเธ เธเธฅเธดเธเน€เธ”เธตเธขเธง
            loadfirst: true,
            sUpdateURL: function(value, settings) {
                    var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    
                    var check_mfd = check_old_mfd(value,rowIndex,function(data){
                            if(data){
                                    alert("Found code '"+data.Product_Code+"' not match FEFO rule \n last MFD '"+data.last_mfd+"' , current MFD '"+value+"'");
                                    oTable.fnUpdate(null, rowIndex, ci_mfd);
                                    return false;
                            }
                    });
                    
                    // var getdate_exp = getdate(value,rowIndex,function(data){
                    //         oTable.fnUpdate(data, rowIndex, ci_exp);
                    // });
                    
                    return value;
            }
           
    } // MFD
    , {
    onblur: 'submit',
            type: 'datepicker',
            cssclass: 'date',
            dataType: 'json',
            event: 'click', // Add By Akkarapol, 14/10/2013, เน€เธเนเธ•เนเธซเน event เนเธเธเธฒเธฃเนเธเนเนเธเธเนเธญเธกเธนเธฅเธเนเธญเธ EXP เธเธตเน เนเธ”เธขเธเธฒเธฃ เธเธฅเธดเธเน€เธเธตเธขเธ เธเธฅเธดเธเน€เธ”เธตเธขเธง
            loadfirst: true,
             // Add By Akkarapol, 14/10/2013, เน€เธเธดเนเธก loadfirst เน€เธเธทเนเธญเนเธซเนเธเนเธญเธ EXP เธเธตเนเธกเธต textbox เธเธถเนเธเธกเธฒเธ•เธฑเนเธเนเธ•เนเนเธซเธฅเธ”เธซเธเนเธฒ
    } // EXP
//                , {//invoice
//                    onblur: 'submit',
//                    event: 'click',
//                } // end of invoice
    {init_invoice}
    {init_container}
//                , {
//                    data : master_container_dropdown_list,
//                    event: 'click',
//                    type: 'select',
//                    onblur: 'submit',
//                    is_container: true,
//                    sUpdateURL: function(value, settings) {
//                        var oTable = $('#showProductTable').dataTable();
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        oTable.fnUpdate(value, rowIndex, ci_cont_id);
//                        return value;
//                    }
//                }

    {receive_qty}// parser from controllers. Edit by Ton! 20130924
    {confirm_qty}// parser from controllers. Edit by Ton! 20130924
    , null

    , {
    data : master_product_unit,
            event: 'click',
            type: 'select',
            onblur: 'submit',
            sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
            var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
            oTable.fnUpdate(value, rowIndex, ci_unit_id);
            return value;
            }
    }
//                , null
//                        , null
    //ADD BY POR 2014-01-10 เนเธชเธ”เธเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข, เธซเธเนเธงเธข, เธฃเธฒเธเธฒเธฃเธงเธก
    {priceperunit} //เธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข
    {unitofprice} //เธซเธเนเธงเธขเธเธญเธเธฃเธฒเธเธฒ
    , null //เธฃเธฒเธเธฒเธฃเธงเธก
            //END ADD
            , null
            , {
            onblur: 'submit',
                    event: 'click', // Add By Akkarapol, 14/10/2013, เน€เธเนเธ•เนเธซเน event เนเธเธเธฒเธฃเนเธเนเนเธเธเนเธญเธกเธนเธฅเธเนเธญเธ Product Status เธเธตเน เนเธ”เธขเธเธฒเธฃ เธเธฅเธดเธเน€เธเธตเธขเธ เธเธฅเธดเธเน€เธ”เธตเธขเธง
            }

    , null
    ]
    });
    $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_putaway_rule, false); // Add By Akkarapol, 04/10/2013, เน€เธเธดเนเธกเน€เธเธทเนเธญเนเธเนเธชเธณเธซเธฃเธฑเธเน€เธเนเธ•เนเธซเนเธเนเธญเธ PutAway_Rule เธเธฑเนเธ hidden เนเธ
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_cont_id, false);
    // Set show/hide follow config xml

    if (!conf_inv){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_invoice, false);
    }



    if (!conf_cont){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_container, false);
    }


    if (!statusprice){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
    }



    if (!conf_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
    $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet, false);
    }

    // Floating menu in product detail.
    setTimeout(function(){
    $('#showProductTable').floatThead();
    }, 1000);
    }
    //ADD BY POR 2013-11-04 เน€เธเธดเนเธกเน€เธ•เธดเธก function เนเธเนเนเธเธเธฃเธ“เธตเธ—เธตเน เธฃเธญ confirm เธเธฒเธเธซเธเนเธฒ HH เธเธถเนเธเธเธฐเนเธกเนเธชเธฒเธกเธฒเธฃเธ–เนเธเนเนเธเธเนเธญเธกเธนเธฅเธญเธฐเนเธฃเนเธ”เน
    function initProductTableComfirm() {

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
            {"sWidth": "15%", "sClass": "indent left_text", "aTargets": [2]},
            {"sWidth": "7%", "sClass": "left_text obj_status", "aTargets": [3]}, // Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text obj_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text lot", "aTargets": [5]},
            {"sWidth": "7%", "sClass": "left_text serial", "aTargets": [6]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [7]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [8]},
            {"sWidth": "7%", "sClass": "center obj_mfg", "aTargets": [9]},
            {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [10]},
            {"sWidth": "5%", "sClass": "right_text", "aTargets": [11]},
            {"sWidth": "5%", "sClass": "right_text", "aTargets": [12]},
            {"sWidth": "7%", "sClass": "center", "aTargets": [13]},
            {"sWidth": "5%", "sClass": "right_text", "aTargets": [14]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [15]},
            {"sWidth": "5%", "sClass": "right_text", "aTargets": [16]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [17]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [18]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [19]},
            ]
    }).makeEditable({
    sUpdateURL: function(value, settings) {
    return value;
    }
    , "aoColumns": [
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
            , null
            , null
            , null
            , null
            , null
            , null
            , null
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
    $('#showProductTable').dataTable().fnSetColumnVis(ci_putaway_rule, false); // Add By Akkarapol, 04/10/2013, เน€เธเธดเนเธกเน€เธเธทเนเธญเนเธเนเธชเธณเธซเธฃเธฑเธเน€เธเนเธ•เนเธซเนเธเนเธญเธ PutAway_Rule เธเธฑเนเธ hidden เนเธ
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false); //ADD BY POR 2014-01-21 เนเธซเนเธเนเธญเธ unit price id เธ”เนเธงเธข
    $('#showProductTable').dataTable().fnSetColumnVis(ci_cont_id, false);
    // Set show/hide follow config xml
    if (!conf_inv){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_invoice, false);
    }



    if (!conf_cont){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_container, false);
    }



    if (!statusprice){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
    }



    if (!conf_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
    $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet, false);
    }

    // Floating menu in product detail.
    setTimeout(function(){
    $('#showProductTable').floatThead();
    }, 1000);
    }
    //END ADD BY POR

   
    function postRequestAction(module, sub_module, action_value, next_state, elm) {
    global_module = module;
    global_sub_module = sub_module;
    global_action_value = action_value;
    global_next_state = next_state;
    curent_flow_action = $(elm).data('dialog');
    var statusisValidateForm = validateForm();
    if (statusisValidateForm === true) {
    var rowData = $('#showProductTable').dataTable().fnGetData();
    var num_row = rowData.length;
    if (num_row <= 0) {
    alert("Please Select Product Order Detail");
    return false;
    }


    var is_pending = $("[name='is_pending']").prop('checked');
    //#ISSUE 3034 Reject Document
    //#เน€เธเธดเนเธกเนเธเธชเนเธงเธเธเธญเธ reject and (reject and return)
    //#start if check Sub_Module : by kik : 2013-12-11

    if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction') {

    var checkErr = $('.required').hasClass('error');
    var is_repackage = $("[name='is_repackage']").prop('checked');
    if (checkErr) { // Check text required fields.

    alert('Please Check Your Require Information (Red label).');
    return false;
    } else {

    global_rowData = rowData;
    check_receive_type();
    for (i in rowData) {
    confirm_qty = set_number_format(rowData[i][ci_confirm_qty].replace(',', ''));
    if (confirm_qty == 0){

    } else{

    //add variable reg_prod_mfd and reg_prod_exp for check format date product_mfd and product_exp (dd/mm/yyyy) only : by kik : 2013-11-26
    var reg_prod_mfd = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/g;
    var reg_prod_exp = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/g;
//            // Add By Akkarapol, 04/09/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒเธซเธฒเธเน€เธฅเธทเธญเธ Sub Status เน€เธเนเธ Return เธซเธฃเธทเธญ Re-Pack เธเธฐเธ•เนเธญเธเน€เธฅเธทเธญเธ Product Status = Normal เน€เธ—เนเธฒเธเธฑเนเธ
//            prod_status = rowData[i][ci_prod_status];
//            prod_sub_status = rowData[i][ci_prod_sub_status];
//            if (prod_sub_status == '<?php echo $sub_status_return; ?>' || prod_sub_status == '<?php echo $sub_status_repackage; ?>') { // Edit By Akkarapol,05/09/2013, เน€เธเธฅเธตเนเธขเธเธเธฒเธ SS004 เน€เธเนเธ SS002 เน€เธเธทเนเธญเนเธซเนเธ•เธฃเธเธเธฑเธเธเนเธญเธกเธนเธฅเธเธฃเธดเธ
//                if (prod_status != 'NORMAL') {
//                alert("This Sub Status Use On Product Status Must be 'Normal' Only!!");
//                        return false;
//                }
//            }
//            // END Add By Akkarapol, 04/09/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒเธซเธฒเธเน€เธฅเธทเธญเธ Sub Status เน€เธเนเธ Return เธซเธฃเธทเธญ Re-Pack เธเธฐเธ•เนเธญเธเน€เธฅเธทเธญเธ Product Status = Normal เน€เธ—เนเธฒเธเธฑเนเธ

    lot = rowData[i][ci_lot];
    serial = rowData[i][ci_serial];
    mfd = rowData[i][ci_mfd];
    exp = rowData[i][ci_exp];
    invoice = rowData[i][ci_invoice];
    // Edit By Akkarapol, 28/01/2014, change from parseInt to set_number_format because reserv_qty and confirm_qty is type float
//            reserv_qty = parseInt(rowData[i][ci_reserv_qty]);
//            confirm_qty = parseInt(rowData[i][ci_confirm_qty]);
    reserv_qty = set_number_format(rowData[i][ci_reserv_qty].replace(',', ''));
    // END Edit By Akkarapol, 28/01/2014, change from parseInt to set_number_format because reserv_qty and confirm_qty is type float

    if (lot == "" && serial == "") {
    //alert('Please fill Lot or Serial');
    //return false;
    //} else if (mfd == "") {
    //alert('Please fill Product Mfd.');
    //return false;
    //add code for check validate product_mfd text format dd/mm/yyyy only : by kik : 2013-11-26
    }
 
     if (mfd == "") {
        if (rowData[i][ci_putaway_rule] != 'FIFO') {
        alert('Please fill Product Mfd.');
        return false;
        }
      }
    
    if (mfd != "" && !(reg_prod_mfd.test(mfd))) {
    alert('Please fill Product Mfd format dd/mm/yyyy only. example 31/01/2000');
    return false;
    }
    if (exp == "") {
    // Add By Akkarapol, 04/10/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒเธ–เนเธฒ PutawayRule เธเธญเธ Product เนเธกเนเนเธเน 'FIFO' เนเธฅเนเธงเธ–เธถเธเธเธฐเนเธซเน alert เน€เธ•เธทเธญเธ
    if (rowData[i][ci_putaway_rule] != 'FIFO') {
    alert('Please fill Product Exp.');
    return false;
    }
    // END Add By Akkarapol, 04/10/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒเธ–เนเธฒ PutawayRule เธเธญเธ Product เนเธกเนเนเธเน 'FIFO' เนเธฅเนเธงเธ–เธถเธเธเธฐเนเธซเน alert เน€เธ•เธทเธญเธ

    //add code for check validate product_exp text format dd/mm/yyyy only : by kik : 2013-11-26
    }
    if (exp != "" && !(reg_prod_exp.test(exp))) {
    alert('Please fill Product Exp format dd/mm/yyyy only. example 31/01/2000');
    return false;
    }

    if (conf_inv){
    if (invoice == ""){
    alert('Please Invoice No.');
    return false;
    }
    }
    if (reserv_qty == "") {
    alert('Please fill all Receive Qty');
    return false;
    }
    if (confirm_qty == "" || confirm_qty == NaN) {
    alert('Please fill all Confirm Qty');
    return false;
    } else {

    if (reserv_qty < 0) {
    alert('Negative Receive Qty is not allow');
    return false;
    } else if (confirm_qty < 0) {
    alert('Negative Confirm Qty is not allow');
    return false;
    }
    }

    if (statusprice) {
    price = rowData[i][ci_price_per_unit];
    if (price == "" || price == 0) {
    alert('Please fill all Price/Unit');
    return false;
    }

    price = parseFloat(price);
    if (price < 0) {
    alert('Negative Price/Unit is not allow');
    return false;
    }
    }

            /**
             * check product_status empty
             */
        prod_status = rowData[i][ci_prod_status];
                if (prod_status == "" ) {
        alert('Please fill  Material Status');
                flagSubmit = false;
                return false;
        }

        prod_sub_status = rowData[i][ci_prod_sub_status];
                if (prod_sub_status == "" ) {
        alert('Please fill  Material Sub Status');
                flagSubmit = false;
                return false;
        }

            /**
             * end check product_status empty
            */

//
//    if (!is_pending) {
//    prod_status = rowData[i][ci_prod_status];
//            if ("PENDING" == prod_status) {
//    alert("Product Status 'Pending' not Allow !!");
//            return false;
//    }
//    } else {
//    prod_status = rowData[i][ci_prod_status];
//            if ("PENDING" != prod_status) {
//    alert("Product Status Must be 'Pending' Only!!");
//            return false;
//    }
//    }

    // Add By Akkarapol, 04/09/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ เธ–เนเธฒเธซเธฒเธเน€เธเนเธ Re-Package เนเธฅเนเธง Sub Status เธเธญเธ Product เนเธซเนเน€เธเนเธ Re-Package เน€เธ—เนเธฒเธเธฑเนเธ
//    if (is_repackage) {
//    prod_status = rowData[i][ci_prod_status];
//            if ("NORMAL" != prod_status) {
//    alert("Product Status Must be 'NORMAL' Only!!");
//            return false;
//    }
//
//    prod_sub_status = rowData[i][ci_prod_sub_status];
//            if ("<?php echo $sub_status_repackage; ?>" != prod_sub_status) { // Edit By Akkarapol,05/09/2013, เน€เธเธฅเธตเนเธขเธเธเธฒเธ SS001 เน€เธเนเธ SS002 เน€เธเธทเนเธญเนเธซเนเธ•เธฃเธเธเธฑเธเธเนเธญเธกเธนเธฅเธเธฃเธดเธ
//    alert("Product Sub Status Must be 'Repackage' Only!!"); // Edit By Akkarapol, 05/09/2013, เน€เธเธฅเธตเนเธขเธเธเธฒเธ Re-Pack เน€เธเนเธ Repackage เน€เธเธทเนเธญเนเธซเนเธ•เธฃเธเธเธฑเธเธเนเธญเธกเธนเธฅเธเธฃเธดเธ
//            return false;
//    }
//    // END Add By Akkarapol, 04/09/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ เธ–เนเธฒเธซเธฒเธเน€เธเนเธ Re-Package เนเธฅเนเธง Sub Status เธเธญเธ Product เนเธซเนเน€เธเนเธ Re-Package เน€เธ—เนเธฒเธเธฑเนเธ
//
//    // Add By Akkarapol, 04/09/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ เธ–เนเธฒเธซเธฒเธเน€เธเนเธ Return เธเธถเนเธเนเธกเนเนเธ”เนเน€เธฅเธทเธญเธเน€เธเนเธ Re-Package เนเธฅเนเธง Sub Status เธเธญเธ Product เนเธซเนเน€เธเนเธ Return เน€เธ—เนเธฒเธเธฑเนเธ
//    } else if ("RCV002" == $("[name='receive_type']").val()) {
//    prod_sub_status = rowData[i][ci_prod_sub_status];
//            if ("<?php echo $sub_status_return; ?>" != prod_sub_status) { // Edit By Akkarapol,05/09/2013, เน€เธเธฅเธตเนเธขเธเธเธฒเธ SS004 เน€เธเนเธ SS001 เน€เธเธทเนเธญเนเธซเนเธ•เธฃเธเธเธฑเธเธเนเธญเธกเธนเธฅเธเธฃเธดเธ
//    alert("Product Status Must be 'Return' Only!!");
//            return false;
//    }
//    }
    // END Add By Akkarapol, 04/09/2013, เน€เธเธดเนเธกเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ เธ–เนเธฒเธซเธฒเธเน€เธเนเธ Return เธเธถเนเธเนเธกเนเนเธ”เนเน€เธฅเธทเธญเธเน€เธเนเธ Re-Package เนเธฅเนเธง Sub Status เธเธญเธ Product เนเธซเนเน€เธเนเธ Return เน€เธ—เนเธฒเธเธฑเนเธ


// Add By Akkarapol, 18/09/2013, เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product MFD. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต ShelfLife เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน
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
    return false;
    } else {
    isMFDDate = 'Y';
    }
    }, "html");
    if (isMFDDate == 'Y') {

    } else {
    return false;
    }
    }
// END Add By Akkarapol, 18/09/2013, เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product MFD. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต ShelfLife เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน


/// ย้อน lot //
prod_mfd = rowData[i][ci_mfd];
        prod_id = rowData[i][ci_prod_id];
        prod_code = rowData[i][ci_prod_code];
                    if (prod_mfd != "") {
                        console.log(prod_mfd);
                        console.log(prod_id);
                $.post('<?php echo site_url() . "/receive/check_last_mfd" ?>', { prod_mfd : prod_mfd, prod_id : prod_id}, function(data) {
                
                    if(data){
                        alert("Found code '"+data.Product_Code+"' not match FEFO rule \n last MFD '"+data.last_mfd+"' , current MFD '"+prod_mfd+"'");
                    }
                   
    
        }, "JSON");

            }
       ///// end////




// Add By Akkarapol, 18/09/2013, เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product Exp. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต Aging เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน
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
    return false;
    } else {
    isExpDate = 'Y';
    }
    }, "html");
    if (isExpDate == 'Y') {

    } else {
    return false;
    }
    }
// END Add By Akkarapol, 18/09/2013, เธ•เธฃเธงเธเธชเธญเธเธงเนเธฒ Product Exp. เธ—เธตเนเน€เธฅเธทเธญเธเธกเธฒเธเธฑเนเธ เธกเธต Aging เธ•เธฒเธกเธ—เธตเนเน€เธเนเธ•เธเนเธฒเนเธ Master เธซเธฃเธทเธญเนเธกเน


    }
    }

    global_rowData = rowData;
    check_receive_type();
    } // end if check text required fields.

    //          -- END #Defect #323 --
    }// end if check Sub_Module : by kik : 2013-12-11

    //if (confirm("Are you sure to action " + action_value + "?")) {
    var f = document.getElementById("form_receive");
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
    global_data_form = $("#form_receive").serialize();
    var message = "";
    if (global_sub_module != 'rejectAction' && global_sub_module != 'rejectAndReturnAction') {
    validation_data();
    } else {

    var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
    $('#div_for_alert_message').html(mess);
    $('#div_for_modal_message').modal('show').css({
    'margin-left': function() {
    return ($(window).width() - $(this).width()) / 2;
    }
    });
    }
    } else {
    alert("Please Check Your Require Information (Red label).");
    return false;
    }
    }

    function cancel() {
    if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
    url = "<?php echo site_url(); ?>/flow/flowReceiveList";
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
    , receive_type: {required: true}
    //, vendor_id: {required: true}
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


//    comment by kik : 08-11-2013
//            var url = "<?php // echo site_url();              ?>/product_info/splitProd/" + item_id;
//    function viewSplitInfo(item_id) {
//            window.open(url, 'TheWindow');
//    }


    //	#defect 444 Modal : Split QTY
    //	#DATE:2013-11-08
    //  #BY:KIK
    //	#เน€เธเธฅเธตเนเธขเธเธเธฒเธฃเนเธชเธ”เธเธเธฅ split qty เนเธเธซเธเนเธฒ receive เธเธฒเธเน€เธ”เธดเธก เธ—เธตเนเธ•เนเธญเธเนเธเธ—เธณเธเธฒเธเนเธ window เนเธซเธกเน เธเธถเธเธ—เธณเน€เธเนเธ modal popup เธเธถเนเธเธกเธฒเนเธ—เธ

    //  #START New Comment Code #defect 444
    function viewSplitInfo(item_id) {

    var url = "<?php echo site_url(); ?>/product_info/splitProd/" + item_id;
    $.post(url, item_id, function(data) {
    $('#estDetail #myModalLabel').html('Product Information & Split');
    $("#estDetail .modal-body").html(data);
    $('#estDetail').modal('show');
    $(".dateMFD").datepicker();
    }, "html");
    }
    //  #End New Comment Code #defect 444



    // Add function calculate_qty : by kik : 28-10-2013
    function calculate_qty() {
    var rowData = $('#showProductTable').dataTable().fnGetData();
    var rowData2 = $('#showProductTable').dataTable();
    var num_row = rowData.length;
    var sum_reserv_qty = 0;
    var sum_confirm_qty = 0;
    var sum_price = 0; //ADD BY POR 2014-01-09 เธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเนเธงเธข
    var sum_all_price = 0; //ADD BY POR 2014-01-09 เธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ”

    for (i in rowData) {
    var tmp_reserv_qty = 0;
    var tmp_confirm_qty = 0;
    //+++++ADD BY POR 2013-11-28 เนเธเธฅเธเธ•เธฑเธงเน€เธฅเธเนเธซเนเธญเธขเธนเนเนเธเธฃเธนเธเนเธเธเธเธณเธเธงเธ“เนเธ”เน
    var str = rowData[i][ci_reserv_qty];
    var str2 = rowData[i][ci_confirm_qty];
    rowData[i][ci_reserv_qty] = str.replace(/\,/g, '');
    rowData[i][ci_confirm_qty] = str2.replace(/\,/g, '');
//                tmp_reserv_qty = parseInt(rowData[i][ci_reserv_qty]); //-----COMMENT BY POR 2013-11-28 เนเธเนเนเธเนเธซเนเน€เธเนเธ parseFloat เน€เธเธทเนเธญเธเธเธฒเธ qty เน€เธเธฅเธตเนเธขเธเน€เธเนเธ float
//                tmp_confirm_qty = parseInt(rowData[i][ci_confirm_qty]); //-----COMMENT BY POR 2013-11-28 เนเธเนเนเธเนเธซเนเน€เธเนเธ parseFloat เน€เธเธทเนเธญเธเธเธฒเธ qty เน€เธเธฅเธตเนเธขเธเน€เธเนเธ float


    tmp_reserv_qty = parseFloat(rowData[i][ci_reserv_qty]); //+++++ADD BY POR 2013-11-28 เนเธเนเนเธเนเธซเนเน€เธเนเธ parseFloat เน€เธเธทเนเธญเธเธเธฒเธ qty เน€เธเธฅเธตเนเธขเธเน€เธเนเธ float
    tmp_confirm_qty = parseFloat(rowData[i][ci_confirm_qty]); //+++++ADD BY POR 2013-11-28 เนเธเนเนเธเนเธซเนเน€เธเนเธ parseFloat เน€เธเธทเนเธญเธเธเธฒเธ qty เน€เธเธฅเธตเนเธขเธเน€เธเนเธ float
    if (!($.isNumeric(tmp_reserv_qty))) {
    tmp_reserv_qty = 0;
    }

    if (!($.isNumeric(tmp_confirm_qty))) {
    tmp_confirm_qty = 0;
    }

    //+++++ADD BY POR 2014-01-09 เน€เธเธดเนเธกเธเธฒเธฃเธเธณเธเธงเธ“เธฃเธฒเธเธฒ
    var tmp_price = 0; //เธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข
    var all_price = 0; //เธฃเธฒเธเนเธฒเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเธถเนเธเธฃเธฒเธขเธเธฒเธฃ

    var str2 = rowData[i][ci_price_per_unit]; //เธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข
    rowData[i][ci_price_per_unit] = str2.replace(/\,/g, '');
    tmp_price = parseFloat(rowData[i][ci_price_per_unit]);
    if (!($.isNumeric(tmp_price))) {
    tmp_price = 0;
    }

    //เธเธณ qty เธกเธฒเธเธนเธ“เธเธฑเธเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธข เน€เธเธทเนเธญเธซเธฒเธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเธถเนเธเธฃเธฒเธขเธเธฒเธฃ
    all_price = tmp_price * tmp_confirm_qty;
    sum_price = sum_price + tmp_price; //เธฃเธงเธกเธฃเธฒเธเธฒเธ—เธธเธเธฃเธฒเธขเธเธฒเธฃเธ•เนเธญเธซเธเนเธงเธข
    sum_all_price = sum_all_price + all_price; //เธฃเธงเธกเธฃเธฒเธเธฒเธ—เธฑเนเธเธซเธกเธ”

    rowData2.fnUpdate(set_number_format(all_price), parseInt(i), ci_all_price); //update เธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”เนเธ datatable
    //END ADD

    sum_reserv_qty = sum_reserv_qty + tmp_reserv_qty;
    sum_confirm_qty = sum_confirm_qty + tmp_confirm_qty;
    }

    $('#sum_recieve_qty').html(set_number_format(sum_reserv_qty));
    $('#sum_cf_qty').html(set_number_format(sum_confirm_qty));
    $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-09 เน€เธเธดเนเธกเนเธซเนเนเธชเธ”เธเธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเนเธงเธข
    $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-09 เน€เธเธดเนเธกเนเธซเนเนเธชเธ”เธเธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”
    }
    // end function calculate_qty : by kik : 28-10-2013


//    function hide_cont(){
//
//    }

function check_old_mfd(mfd_data,rowIndex,callback) {
    var rowData2 = $('#showProductTable').dataTable().fnGetData();
    $.post('<?php echo site_url() . "/receive/check_last_mfd" ?>', { prod_mfd : mfd_data, prod_id : rowData2[rowIndex][19]}, function(result) {
        callback(result)
    }, "JSON");


}

function getdate(mfd_data,rowIndex,callback) {
    var rowData2 = $('#showProductTable').dataTable().fnGetData();
    $.post('<?php echo site_url() . "/receive/edit_mfd_to_exp" ?>', { data : mfd_data, prodid : rowData2[rowIndex][19]}, function(result) {
        callback(result)
    }, "JSON");


}



    function exportFile(type, document) {
    if (type == 'tally'){
    $("#form_receive").attr('action', "<?php echo site_url(); ?>" + "/receive/exportReceiveTallySheet?document_no=<?php echo $document_no; ?>");
    $("#form_receive").submit();
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
    .hide_cont{

    }
</style>
<div class="well">
    <form id="form_receive" method=post action="" target="_blank">
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
            $receive_date = date('d/m/Y');
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
            <table width="98%" border="0">
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
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate GRN" readonly class="required document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("document_external"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_ext', trim($doc_refer_ext), 'placeholder="' . DOCUMENT_EXT . '" class="required document " style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("document_internal"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_int', trim($doc_refer_int), 'placeholder="' . DOCUMENT_INT . '"  class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
<!--
                <tr>
                    <td align="right"><?php echo _lang("invoice_no"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_inv', trim($doc_refer_inv), 'placeholder="' . DOCUMENT_INV . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("customs_entry"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_ce', trim($doc_refer_ce), 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right"><?php echo _lang("bl_no"); ?></td>
                    <td align="left"><?php echo form_input('doc_refer_bl', trim($doc_refer_bl), 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
-->
                <tr>
                    <td align="right"><?php echo _lang("receive_type"); ?></td>
                    <td align="left"><?php echo form_dropdown('receive_type', $receive_list, $receive_type, "onChange='changeOption(this)' id='receive_type'"); ?></td>
                    <td align="right"><?php echo _lang("receive_date"); ?></td>
                    <?php $receive_date_read_only = ($can_change_receive_date == TRUE ? '' : 'disabled="disabled"'); ?>
                    <td align="left"><?php echo form_input('receive_date', $receive_date, 'id="receive_date" placeholder="Receive Date" ' . $receive_date_read_only); ?></td>
                    <td align="right"><?php echo _lang("asn"); ?></td>
                    <td align="left"><?php echo form_input('est_receive_date', $est_receive_date, 'id="est_receive_date" placeholder="Advance Shipment Notice" class="required" readonly="readyonly" '); ?></td>
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
                        <?php echo form_checkbox(array('name' => 'is_pending', 'id' => 'is_pending'), ACTIVE, $is_pending); ?>&nbsp;Pending&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox(array('name' => 'is_repackage', 'id' => 'is_repackage'), ACTIVE, $is_repackage); ?>&nbsp;Re-Package
                        <br>
                        <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                    </td>
                    <?php if ($conf_cont): ?>
                        <td align="right"><?php echo _lang("container"); ?></td>
                        <td align="left">
                            <?php echo form_multiselect('doc_refer_container', $doc_refer_container, NULL, 'disabled="disabled" id="doc_refer_container" placeholder="' . DOCUMENT_CONTAINER . '"  style="text-transform: uppercase"'); ?> <img id="add_container" src="<?php echo base_url("images/add.png") ?>" style="width: 22px; height: 22px; margin-bottom: 3px; cursor: pointer;" />
                            <input type="hidden" id="doc_refer_con_size" name="doc_refer_con_size">
                        </td>
                    <?php endif; ?>
                    <td align="right" valign="top"><?php echo _lang("remark"); ?></td>
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo trim($remark); ?></TEXTAREA>
                    </td>


                </tr>
            </table>
        </fieldset>
        <input type="hidden" name="token" value="<?php echo $token ?>" />
        <input type="hidden" id="container_list" name="container_list" value='<?php echo $container_list; ?>' />
        <input type="hidden" id="container_size_list" name="container_size_list" value='<?php echo $container_size_list; ?>' />
    </form>
    <fieldset class="well">
        <legend>&nbsp;&nbsp;<b>Product Detail</b>&nbsp;&nbsp;</legend>
        <table width="100%">
            <tr align="center" >
                <td align="center" colspan="6" id="showDataTable" >
                    <table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
                        <thead>
                            <?php
                            if ($Sub_Module == "updateInfo") {// Add by Ton! 20130829 For show split product button.
//                                Show split product button.
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
                                    _lang('pallet_code'),
                                    _lang('remark'),
                                    "Product_Id",
                                    "Product_Status",
                                    "Product_Sub_Status",
                                    "Unit_Id",
                                    "Item_Id",
                                    "PutAway_Rule",
                                    "Split Info", // Edit By Akkarapol, 30/03/2013, เธขเนเธฒเธขเธเธฒเธเธ—เธตเนเธญเธขเธนเนเธซเธฅเธฑเธ Remark เธกเธฒเนเธงเนเธซเธฅเธฑเธ Item_Id เน€เธเธทเนเธญเธเธเธฒเธ เธ–เนเธฒเนเธงเนเธ—เธตเนเธซเธฅเธฑเธ Remark เธเธฐเธ—เธณเนเธซเน Index เธเธญเธเธเนเธฒเธ•เนเธฒเธเนเธเธดเธ”เนเธ เนเธฅเธฐเธ—เธณเนเธซเนเน€เธเธดเธ” Error เนเธเธเธฒเธฃเนเธชเธ”เธเธเธฅเธซเธฅเธฒเธขเธญเธขเนเธฒเธ เนเธฅเธฐเธ—เธณเนเธซเนเธเธฒเธฃเน€เธฃเธตเธขเธเนเธเน Index เนเธเธเธฒเธฃเน€เธเธเธเนเธญเธกเธนเธฅ เธเธดเธ”เนเธ
                                    "Unit Price ID",
                                    "Container_id"
                                );
                            } else {
//                                Not show split product button.
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
                                    _lang('pallet_code'),
                                    _lang('remark'),
                                    "Product_Id",
                                    "Product_Status",
                                    "Product_Sub_Status",
                                    "Unit_Id",
                                    "Item_Id",
                                    "PutAway_Rule",
                                    "Unit Price ID",
                                    "Container_id"
                                );
                            }

                            $str_header = "";
                            foreach ($show_column as $index => $column) {
                                $str_header .= "<th>" . $column . "</th>";
                            }
                            ?>
                            <tr><?php echo $str_header; ?></tr>
                        </thead>
                        <tbody>
                            <?php
                            $sum_Receive_Qty = 0;    //add sumQty variable for calculate total qty : by kik : 24-10-2013
                            $sum_Cf_Qty = 0;
                            $sumPriceUnit = 0; //ADD BY POR 2014-01-13 เธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเนเธงเธข
                            $sumPrice = 0; //ADD BY POR 2014-01-13 เธฃเธฒเธเธฒเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”
                            $allprice = 0; //ADD BY POR 2014-01-13 เธเธณเธซเธเธ”เนเธซเนเธเธฅเธฃเธงเธกเธ—เธฑเนเธเธซเธกเธ”เธ•เนเธญเธซเธเนเธงเธข เน€เธเนเธ 0 เน€เธเธทเนเธญเธเธเธฒเธเธขเธฑเธเนเธกเนเธกเธตเธเธฒเธฃ confirm qty เธ”เธฑเธเธเธฑเนเธ qty เธเธถเธเน€เธเนเธ 0 เธญเธขเธนเน
                            if (isset($order_deatil)) {//p($order_deatil);exit();
                                $str_body = "";
                                $j = 1;
                                     
                                if ($Sub_Module == "updateInfo") {// Add by Ton! 20130829 For show split product button.
//                                Show split product button.
                                    foreach ($order_deatil as $order_column) {
                                        $disable_status = '';
                                        if ($order_column->Pallet_Id != NULL && $order_column->Pallet_Id != ''):
                                            $disable_status = $this->disable_row_datatable;
                                        endif;
//                                        echo $disable_status.'<br>';

                                        $allprice = 0;
                                        //ADD BY POR 2014-01-13 เธ–เนเธฒ confirm qty เธกเธตเธเนเธฒเนเธฅเนเธง เนเธซเนเนเธชเธ”เธ allprice เธ•เธฒเธกเธเนเธฒเธ—เธตเนเธชเนเธเธกเธฒ
                                        if ($order_column->Confirm_Qty > 0) {
                                            $allprice = $order_column->Confirm_Qty * @$order_column->Price_Per_Unit;
                                        }
                                        //echo $disable_row_datatable;
                                        //case have split can not edit container : BY POR 2014-07-29
//                                        $disable_row = "";
//                                        if(!empty($order_column->Split_From_Item_Id) && !empty($order_column->Pallet_Code)):
//                                            $disable_row = $this->disable_row_datatable;
////                                            $disable_row = $disable_row_datatable;
//                                            //$disable_row = "disable_row_datatable";
//                                        endif;
                                        //if have pallet can not edit container :ADD BY POR 2014-11-05
                                        if (!empty($order_column->Pallet_Code)):
                                            $disable_row = $this->disable_row_datatable;
                                        else :
                                            $disable_row = "";
                                        endif;

                                        //END ADD
                                        $str_body .= "<tr>";
                                        $str_body .= "<td>" . $j . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Code . "</td>";
//                                        $str_body .= "<td title='" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "' >" . htmlspecialchars($order_column->Product_Name, ENT_QUOTES) . "</td>"; // Edit By Akkarapol, 23/12/2013, เนเธชเนเธเธฑเธเธเนเธเธฑเนเธ htmlspecialchars เธเธฃเธญเธเธเธฒเธฃเนเธชเธ”เธเธเธฅเน€เธเนเธฒเนเธเน€เธเธทเนเธญเนเธซเนเนเธชเธ”เธเธเธฅเนเธ”เนเธญเธขเนเธฒเธเธ–เธนเธเธ•เนเธญเธเนเธเธเธฃเธ“เธตเธ—เธตเนเธกเธตเธ•เธฑเธงเธญเธฑเธเธฉเธฃเธเธดเน€เธจเธฉเธญเธขเธนเนเนเธ string
                                        $str_body .= "<td title=\"" . str_replace('"', '&quot;', $order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                        $str_body .= "<td class='obj_status {$disable_status}'>" . $order_column->Status_Value . "</td>";
                                        $str_body .= "<td class='obj_sub_status {$disable_status}'>" . $order_column->Sub_Status_Value . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                        $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                        $str_body .= "<td class='{$disable_row}'>" . @$order_column->Cont_No . ' ' . @$order_column->Cont_Size_No . @$order_column->Cont_Size_Unit_Code . "</td>";
                                        $str_body .= "<td>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                        $str_body .= "<td>" . set_number_format($order_column->Confirm_Qty) . "</td>";
                                        $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                        //ADD BY POR 2014-01-13 เน€เธเธดเนเธก column เน€เธเธตเนเธขเธงเธเธฑเธ price
                                        $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";
                                        $str_body .= "<td>" . @$order_column->unitprice_name . "</td>";
                                        $str_body .= "<td>" . set_number_format(@$allprice) . "</td>";
                                        //END ADD
                                        $str_body .= "<td>" . @$order_column->Pallet_Code . "</td>";
                                        $str_body .= "<td>" . $order_column->Remark . "</td>";
                                        $str_body .= "<td style-\'display:none\'>" . $order_column->Product_Id . "</td>";
                                        $str_body .= "<td class='obj_status' style-\'display:none\'>" . $order_column->Status_Code . "</td>";
                                        $str_body .= "<td class='obj_sub_status' style-\'display:none\'>" . $order_column->Sub_Status_Code . "</td>";
                                        $str_body .= "<td style=\"display:none\">" . $order_column->Unit_Id . "</td>";
                                        $str_body .= "<td style=\"display:none\">" . $order_column->Item_Id . "</td>";
                                        $str_body .= "<td style=\"display:none\">" . $order_column->PutAway_Rule . "</td>"; // Add By Akkarapol, 04/10/2013, เน€เธเธดเนเธก column 'PutAway_Rule' เน€เธเนเธฒเนเธเน€เธเธทเนเธญเนเธเนเน€เธเนเธ alert EXP
                                        #add code for do not show split when order return form putaway module : by kik : 2013-11-29
                                        if ($is_orderReturn || $is_partial || !empty($order_column->Split_From_Item_Id)) {
                                            $str_body .= "<td >Do not split</td>";
                                            #add code for show split when order not return form putaway module : by kik : 2013-11-29
                                        } else {
                                            $str_body .= "<td align='center'><a ONCLICK=\"viewSplitInfo('" . $order_column->Item_Id . "')\" >" . img("css/images/icons/edit.png") . "</a></td>"; // Edit By Akkarapol, 30/03/2013, เธขเนเธฒเธขเธเธฒเธเธ—เธตเนเธญเธขเธนเนเธซเธฅเธฑเธ Remark เธกเธฒเนเธงเนเธซเธฅเธฑเธ Item_Id เน€เธเธทเนเธญเธเธเธฒเธ เธ–เนเธฒเนเธงเนเธ—เธตเนเธซเธฅเธฑเธ Remark เธเธฐเธ—เธณเนเธซเน Index เธเธญเธเธเนเธฒเธ•เนเธฒเธเนเธเธดเธ”เนเธ เนเธฅเธฐเธ—เธณเนเธซเนเน€เธเธดเธ” Error เนเธเธเธฒเธฃเนเธชเธ”เธเธเธฅเธซเธฅเธฒเธขเธญเธขเนเธฒเธ เนเธฅเธฐเธ—เธณเนเธซเนเธเธฒเธฃเน€เธฃเธตเธขเธเนเธเน Index เนเธเธเธฒเธฃเน€เธเธเธเนเธญเธกเธนเธฅ เธเธดเธ”เนเธ
                                        }
                                        $str_body .= "<td style=\"display:none\">" . @$order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-17 เน€เธเธดเนเธก ID เธเธญเธ unit price
                                        $str_body .= "<td style=\"display:none\">" . @$order_column->Cont_Id . "</td>";
                                        $str_body .= "</tr>";
                                        $j++;
                                        $sum_Receive_Qty+=$order_column->Reserv_Qty;        // add by kik:25-10-2013
                                        $sum_Cf_Qty+=$order_column->Confirm_Qty;
                                        // add by kik:25-10-2013
                                        //ADD BY POR 2014-01-13 เธฃเธงเธกเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธขเนเธฅเธฐเธฃเธฒเธเธฒเธฃเธงเธก
                                        $sumPriceUnit+=@$order_column->Price_Per_Unit;
                                        $sumPrice+=$allprice;
                                        //END ADD
                                    }
                                } else {

//                                Not show split product button.
                                    foreach ($order_deatil as $order_column) {

                                        $disable_status = '';
                                        if ($order_column->Pallet_Id != NULL && $order_column->Pallet_Id != ''):
                                            $disable_status = $this->disable_row_datatable;
                                        endif;

                                        $allprice = 0;
                                        //ADD BY POR 2014-01-13 เธ–เนเธฒ confirm qty เธกเธตเธเนเธฒเนเธฅเนเธง เนเธซเนเนเธชเธ”เธ allprice เธ•เธฒเธกเธเนเธฒเธ—เธตเนเธชเนเธเธกเธฒ
                                        if ($order_column->Confirm_Qty > 0) {
                                            $allprice = $order_column->Confirm_Qty * @$order_column->Price_Per_Unit;
                                        }
                                        $str_body .= "<tr>";
                                        $str_body .= "<td>" . $j . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Code . "</td>";
//                                    $str_body .= "<td title=\"" . $order_column->Full_Product_Name . "\" >" . $order_column->Product_Name . "</td>";
//                                        $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . htmlspecialchars($order_column->Product_Name, ENT_QUOTES) . "</td>"; // Edit by Ton! 20131127
                                        $str_body .= "<td title=\"" . str_replace('"', '&quot;', $order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                        $str_body .= "<td class='obj_status {$disable_status}'>" . $order_column->Status_Value . "</td>";
                                        $str_body .= "<td class='obj_sub_status {$disable_status}'>" . $order_column->Sub_Status_Value . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                        $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                        $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                        $str_body .= "<td>" . @$order_column->Cont_No . ' ' . @$order_column->Cont_Size_No . @$order_column->Cont_Size_Unit_Code . "</td>";
                                        $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                        $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Confirm_Qty) . "</td>";
                                        $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                        //ADD BY POR 2014-01-13 เน€เธเธดเนเธก column เน€เธเธตเนเธขเธงเธเธฑเธ price
                                        $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";
                                        $str_body .= "<td>" . @$order_column->unitprice_name . "</td>";
                                        $str_body .= "<td>" . set_number_format(@$allprice) . "</td>";
                                        //END ADD
                                        $str_body .= "<td>" . @$order_column->Pallet_Code . "</td>";
                                        $str_body .= "<td>" . $order_column->Remark . "</td>";
                                        $str_body .= "<td style=\"display:none\">" . $order_column->Product_Id . "</td>";
                                        $str_body .= "<td class='obj_status' style-\'display:none\'>" . $order_column->Status_Code . "</td>";
                                        $str_body .= "<td class='obj_sub_status' style-\'display:none\'>" . $order_column->Sub_Status_Code . "</td>";
                                        $str_body .= "<td style=\"display:none\">" . $order_column->Unit_Id . "</td>";
                                        $str_body .= "<td style=\"display:none\">" . $order_column->Item_Id . "</td>";
                                        $str_body .= "<td style=\"display:none\">" . $order_column->PutAway_Rule . "</td>"; // Add By Akkarapol, 04/10/2013, เน€เธเธดเนเธก column 'PutAway_Rule' เน€เธเนเธฒเนเธเน€เธเธทเนเธญเนเธเนเน€เธเนเธ alert EXP
                                        $str_body .= "<td style=\"display:none\">" . @$order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-17 เน€เธเธดเนเธก ID เธเธญเธ unit price
                                        $str_body .= "<td style=\"display:none\">" . @$order_column->Cont_Id . "</td>";
                                        $str_body .= "</tr>";
                                        $j++;
                                        $sum_Receive_Qty+=$order_column->Reserv_Qty;        // add by kik:25-10-2013
                                        $sum_Cf_Qty+=$order_column->Confirm_Qty;
                                        // add by kik:25-10-2013
                                        //ADD BY POR 2014-01-13 เธฃเธงเธกเธฃเธฒเธเธฒเธ•เนเธญเธซเธเนเธงเธขเนเธฅเธฐเธฃเธฒเธเธฒเธฃเธงเธก
                                        $sumPriceUnit+=@$order_column->Price_Per_Unit;
                                        $sumPrice+=$allprice;
                                        //END ADD
                                    }
                                }
                                echo $str_body;
                            }
                            ?>

                        </tbody>

                        <!-- show total qty : by kik : 25-10-2013-->
                            <tfoot>
                            <? if ($Sub_Module == "updateInfo") { ?>
                                                 <tr>
                                                     <th colspan="11" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                                                     <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Receive_Qty); ?></span></th>
                                                     <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty); ?></span></th>
                                                     <th></th>
                                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                     <th></th>
                                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                                     <th colspan="11" class ='ui-state-default indent' ></th>
                                                 </tr>
                            <? } else { ?>
                                                 <tr>
                                                     <th colspan="11" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                                                     <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Receive_Qty); ?></span></th>
                                                     <th class ='ui-state-default indent'  style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty); ?></span></th>
                                                     <th></th>
                                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                    <th></th>
                                                     <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                                     <th colspan="10" class ='ui-state-default indent' ></th>
                                               </tr>

                            <? } ?>
                            </tfoot>
                        <!-- end show total qty : by kik : 25-10-2013-->


                    </table>

                </td>
            </tr>
        </table>
    </fieldset>
</div>

<!--	#defect 444 Modal : Split QTY
	#DATE:2013-11-08
        #BY:KIK
	#เน€เธเธฅเธตเนเธขเธเธเธฒเธฃเนเธชเธ”เธเธเธฅ split qty เนเธเธซเธเนเธฒ receive เธเธฒเธเน€เธ”เธดเธก เธ—เธตเนเธ•เนเธญเธเนเธเธ—เธณเธเธฒเธเนเธ window เนเธซเธกเน เธเธถเธเธ—เธณเน€เธเนเธ modal popup เธเธถเนเธเธกเธฒเนเธ—เธ

        #START New Comment Code #defect 444-->

        <style>
    #estDetail{
        /*        width: 1170px;	 SET THE WIDTH OF THE MODAL
                margin: -250px 0 0 -600px; */

        /*<!--add by kik : for set size modal %-->*/
        width: 90%!important;	/* SET THE WIDTH OF THE MODAL */
        top:42%!important;
        margin-left: -45%!important;
    }

        </style>

        <div style="padding:5px 10px;" id="estDetail" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                <h3 id="myModalLabel"></h3>
            </div>
            <div class="modal-body" ></div>
            <div class="modal-footer">
                <div style="float:left;">
                </div>
                <div style="float:right;">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </div>
        </div>
<!--    #End New Comment Code #defect 444-->
<?php $this->load->view('element_modal'); ?>
