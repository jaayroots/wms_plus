<script>
    var statusprice = '<?php echo ($price_per_unit) ? true : false; ?>';
//    console.log(statusprice);
    
    var separator = "<?php echo SEPARATOR; ?>";
    
    var arr_index = new Array(
    'no'
    ,'product_code'
    ,'product_name'
    ,'product_status'
    ,'product_sub_status'
    ,'lot'
    ,'serial'
    ,'product_mfd'
    ,'product_exp'
    ,'invoice_no'
    ,'container'
    ,'reserve_qty'
    ,'confirm_qty'
    ,'unit'
    ,'price_per_unit'
    ,'unit_price'
    ,'all_price'
    ,'suggest_location'
    ,'actual_location'
    ,'picking_by'
    ,'pallet_code'
    ,'remark'
    ,'h_product_id'
    ,'h_product_status'
    ,'h_product_sub_status'
    ,'unit_Id'
    ,'item_Id'
    ,'inbound_id'
    ,'suggest_location_id'
    ,'actual_Location_id'
    ,'price_per_unit_id'
    );
    
    var ci_prod_code = arr_index.indexOf("product_code");
    var ci_lot = arr_index.indexOf("lot"); //5
    var ci_serial = arr_index.indexOf("serial"); //6
    var ci_mfd = arr_index.indexOf("product_mfd"); //7
    var ci_exp = arr_index.indexOf("product_exp"); //8
    var ci_invoice = arr_index.indexOf("invoice_no");
    var ci_container = arr_index.indexOf("container");
    var ci_reserv_qty = arr_index.indexOf("reserve_qty"); //9
    var ci_confirm_qty = arr_index.indexOf("confirm_qty"); //10
    
    var ci_pallet_id = arr_index.indexOf("pallet_code"); //18
    var ci_remark = arr_index.indexOf("remark"); //19
    //Define Hidden Field Datatable
    var ci_prod_id = arr_index.indexOf("h_product_id"); //20
    var ci_prod_status = arr_index.indexOf("h_product_status"); //21
    var ci_prod_sub_status = arr_index.indexOf("h_product_sub_status"); //22
    var ci_unit_id = arr_index.indexOf("unit_Id"); //23
    var ci_item_id = arr_index.indexOf("item_Id"); //24
    var ci_inbound_id = arr_index.indexOf("inbound_id"); //25
    var ci_suggest_loc = arr_index.indexOf("suggest_location_id"); //26
    var ci_actual_loc = arr_index.indexOf("actual_Location_id"); //27
    
    // add by kik : 2014-01-14
    var ci_price_per_unit = arr_index.indexOf("price_per_unit"); //12
    var ci_unit_price = arr_index.indexOf("unit_price"); //13
    var ci_all_price = arr_index.indexOf("all_price"); //14
    var ci_unit_price_id = arr_index.indexOf("price_per_unit_id"); //28
    //end add by kik : 2014-01-14
    
    var ci_list = [
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_lot', value: ci_lot},
        {name: 'ci_serial', value: ci_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_invoice', value: ci_invoice}, // add by por for show invoice : 20140813
        {name: 'ci_container', value: ci_container}, // add by por for show invoice : 20140813
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
        {name: 'ci_unit_price_id', value: ci_unit_price_id}         // add by kik : 2014-01-14
    ]

    var set_order_by = '<?php echo $set_order_by; ?>';
    var index_of_group = 0;
    if(set_order_by == 'location'){
        index_of_group = arr_index.indexOf("suggest_location");
    }else if(set_order_by == 'pallet'){
        index_of_group = arr_index.indexOf("pallet_code");
    }
    
    var count_row_all_data = <?php echo count($order_detail_data); ?>;
    var count_row_set_data = 0;
    var count_set_data = 1;
    function initProductTable() {
        $('#ShowDataTableForInsert').dataTable({
            "fnDrawCallback": function(oSettings) {
                if(set_order_by != 'item'){
                    if (oSettings.aiDisplay.length == 0)
                    {
                        return;
                    }

                    var nTrs = $('#ShowDataTableForInsert tbody tr');
                    var iColspan = nTrs[0].getElementsByTagName('td').length;
                    var sLastGroup = "";
                    for (var i = 0; i < nTrs.length; i++)
                    {
                        var iDisplayIndex = oSettings._iDisplayStart + i;
                        var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[index_of_group];

                        if (sGroup != sLastGroup)
                        {
                            var nGroup = document.createElement('tr');
                            var nCell = document.createElement('td');
                            nCell.colSpan = iColspan;
                            nCell.className = "group left_text";
                            nCell.innerHTML = sGroup;
                            nGroup.appendChild(nCell);
                            nTrs[i].parentNode.insertBefore(nGroup, nTrs[i]);
                            sLastGroup = sGroup;
                        }
                    }
                    count_row_set_data = count_set_data;  
                }else{
                    count_row_set_data = count_set_data+1;          
//                    count_row_set_data = count_set_data;          
                }
                
//                alert('complete');
//                console.log(count_set_data + " >> " + count_row_set_data + " || " + count_row_all_data);
//                if(count_row_set_data == count_row_all_data){
                    $("#report").show();
                    $('#show_loader').html('');
//                }
                
                count_set_data += 1;
            },
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "bAutoWidth": false,
            "iDisplayLength" : 250,
			//"sScrollX": "100%",
			"sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center pkj_no", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center pkj_product_code", "aTargets": [1]},
                {"sWidth": "12%", "sClass": "left_text pkj_product_name", "aTargets": [2]},
                {"sWidth": "6%", "sClass": "left_text obj_status pkj_product_status", "aTargets": [3]}, // Edit by Ton! 20131001
                {"sWidth": "6%", "sClass": "left_text obj_sub_status pkj_product_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
                {"sWidth": "6%", "sClass": "left_text pkj_lot", "aTargets": [5]},
                {"sWidth": "6%", "sClass": "left_text pkj_serial", "aTargets": [6]},
                {"sWidth": "6%", "sClass": "center obj_mfg pkj_mfd", "aTargets": [7]},
                {"sWidth": "6%", "sClass": "center obj_exp pkj_exp", "aTargets": [8]},
                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_reserve_qty", "aTargets": [9]},
                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_confirm_qty", "aTargets": [10]},
                {"sWidth": "5%", "sClass": "center pkj_unit", "aTargets": [11]},
                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_price_per_unit", "aTargets": [12]},
                {"sWidth": "5%", "sClass": "center pkj_unit_price", "aTargets": [13]},
                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_all_price", "aTargets": [14]},
                {"sWidth": "7%", "sClass": "center pkj_suggest_location", "aTargets": [15]},
                {"sWidth": "7%", "sClass": "center pkj_actual_location", "aTargets": [16]},
                {"sWidth": "7%", "sClass": "center pkj_pick_by", "aTargets": [17]},
                {"sWidth": "5%", "sClass": "center pkj_pallet_code", "aTargets": [18]},
                {"sWidth": "5%", "sClass": "center pkj_remark", "aTargets": [19]},
            ]
        }).makeEditable({
            "aoColumns": [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null   //add by kik : 2014-01-14
//                comment by kik : 2014-01-14
//                {
//                    sSortDataType: "dom-text",
//                    sType: "numeric",
//                    type: 'text',
//                    onblur: "submit",
//                    event: 'click',
//                    cssclass: "required number",
//                    sUpdateURL: "<?php // echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
//                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
//                        calculate_qty();
//                    }
//                }
//               end comment by kik : 2014-01-14  

                , null
                //                comment by kik : 2014-01-14
//                , {
//                    sSortDataType: "dom-text",
//                    sType: "numeric",
//                    type: 'text',
//                    onblur: "submit",
//                    event: 'click',
//                    cssclass: "required number",
//                    sUpdateURL: "<?php // echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
//                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
//                        calculate_qty();
//                    }
//                }
                , null
                , null
//               end comment by kik : 2014-01-14
                //ADD BY POR 2014-06-11 แสดงราคาต่อหน่วย, หน่วย, ราคารวม
//                {price}           //price 
<?php echo $price; ?>
//                {unitofprice}     //unitprice 
<?php echo $unitofprice; ?>
                ,null           //allprice
                //END ADD
                , null
                , null
                , null
                , null
//                , {
//                    onblur: 'submit',
//                    sUpdateURL: "<?php // echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
//                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว 
//                }
                , null
                , null
                , null
                , null
                , null
                , null
                , null
            ]
        });
        
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_id, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_item_id, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_inbound_id, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_actual_loc, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_price_id, false);
        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_container, false);   //dont show container in picking : ADD BY POR 2014-10-14
        
        if(set_order_by == 'location'){
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(arr_index.indexOf("suggest_location"), false);
        }else if(set_order_by == 'pallet'){
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(arr_index.indexOf("pallet_code"), false);
        }
        
        if(!built_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_pallet_id, false);
        }
        
        //add by kik : 20140114
        if(statusprice!=true){
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_all_price, false);
        }
        //end add by kik : 20140114
        
        if(!conf_inv){
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_invoice, false);
        }
        
        if(!conf_cont){
            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_container, false);
        }

//$('#ShowDataTableForInsert').dataTable().rowGrouping({
//            							iGroupingColumnIndex: 1,
//            							sGroupingColumnSortDirection: "asc",
//            							iGroupingOrderByColumnIndex: 0
//								});

    }
    
    $(document).ready(function(){
        initProductTable();
    });
    
</script>
<style>
    td.group {
        background-color: #BEBEBE;
    }
</style>
<table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
    <tr>
        <td>
            <div id="defDataTable_wrapper" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                <div style="width:100%;overflow-x: auto;" id="showDataTable"> 
                    <table cellpadding="2" cellspacing="0" border="0" class="display" id="ShowDataTableForInsert" style="max-width: none">
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
                                _lang('reserve_qty'),
                                _lang('confirm_qty'),
                                _lang('unit'),
                                _lang('price_per_unit'),
                                _lang('unit_price'),
                                _lang('all_price'),
                                _lang('suggest_location'),
                                _lang('actual_location'),
                                _lang('picking_by'),
                                _lang('pallet_code'),
                                _lang('remark'),
                                "Product_Id",
                                "Product_Status",
                                "Product_Sub_Status",
                                "Unit_Id",
                                "Item_Id",
                                "Inbound Id",
                                "Suggest_Location_Id",
                                "Actual_Location_Id",
                                "Price/Unit ID"     //add by kik : 20140113
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
                            if (isset($order_detail_data)) :
                                $str_body = "";
                                $j = 1;

                                $last_product_and_status = '';
                                $set_data_for_show_total_by_item = array();
                            
                                foreach ($order_detail_data as $order_column) :
                                    
                                    $product_and_status = $order_column->Product_Code."|".$order_column->Status_Value;
                                    	if($process_id==2):
                                            $confirm_qty =!empty($order_column->Confirm_Qty)?set_number_format($order_column->Confirm_Qty):'';
                                        else:
                                            $confirm_qty = set_number_format($order_column->Confirm_Qty);
                                        endif;
                                    if($last_product_and_status != $product_and_status):

//                                        if($last_product_and_status != ''):
//                                            $str_body.="<tr><td colspan='31'>a</td></tr>";
//                                        endif;
//                                        if($last_product_and_status != ''):
//                                                $str_body.='<tr>';
//$count_colspan = 11;
//$count_colspan_after_sum = 5;
//                                                if($count_colspan != 0):
//                                                        $str_body.='<div><td align="center" colspan="'.$count_colspan.'"> </td></div>';
//                                                endif;
//
//                                        $str_body.='<div class="reserve_qty"><td class="reserve_qty" align="center"><b>' . set_number_format($set_data_for_show_total_by_item[$last_product_and_status]['reserv_qty']) . '</b></td></div>
//                                        <div class="confirm_qty"><td class="confirm_qty" align="center"><b>' . ($set_data_for_show_total_by_item[$last_product_and_status]['confirm_qty']==0?'':set_number_format($set_data_for_show_total_by_item[$last_product_and_status]['confirm_qty'])) . '</b></td></div>
//                                        <div class="unit"><td class="unit" align="center"> </td></div>';
//
//                                        //    #check if price_per_unit for show column Price / Unit,Unit Price,All Price
//                                        if($price_per_unit == TRUE):
//                                                $str_body.='<div class="price_per_unit"><td class="price_per_unit" align="center"> </td></div>
//                                                <div class="unit_price"><td class="unit_price" align="center"> </td></div>
//                                                <div class="all_price"><td class="all_price" align="center"> </td></div>';
//                                        endif;
//
//                                        if($count_colspan_after_sum > 0):
//                                                $str_body.='<div><td align="center" colspan="'.$count_colspan_after_sum.'"> </td></div>';
//                                        endif;
//
//                                                $str_body .= '</tr>';
//                                        endif;

                                        $set_data_for_show_total_by_item[$product_and_status]['reserv_qty'] = (float)str_replace(',', '', $order_column->Reserv_Qty);
                                        $set_data_for_show_total_by_item[$product_and_status]['confirm_qty'] = (float)str_replace(',', '', $confirm_qty);
                                    else:
                                        $set_data_for_show_total_by_item[$product_and_status]['reserv_qty'] += (float)str_replace(',', '', $order_column->Reserv_Qty);
                                        $set_data_for_show_total_by_item[$product_and_status]['confirm_qty'] += (float)str_replace(',', '', $confirm_qty);
                                    endif;
                                    
                                    $title_remark = ($order_column->Remark != " " && $order_column->Remark != NULL) ? " title='" . $order_column->Remark . "' " : "";
                                    $order_id = $order_column->Order_Id;
                                    $all_price = ($order_column->Confirm_Qty) * (@$order_column->Price_Per_Unit);
//                                            $str_body .= "<tr title=\"" . $order_column->Full_Product_Name . "\"  id=\"" . $order_column->Item_Id . "\">";
//                                            $str_body .= "<tr title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\"  id=\"" . $order_column->Item_Id . "\">"; // Edit by Ton! 20131127
                                    $str_body .= "<tr>";
                                    $str_body .= "<td>" . $j . "</td>";
                                    //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
                                    $str_body .= "<td class='td_click' align='center' title='Click to display the running process is not finished.'  ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'>" . $order_column->Product_Code . "</td>";

                                    //  Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้
                                    //  $str_body .= '<td>' . $order_column->Product_Name . '</td>';
//                                            $str_body .= "<td title='" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "' >" . $order_column->Product_Name . "</td>";
                                    $str_body .= "<td title=\"" . str_replace('"', '&quot;', $order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                    // END Edit By Akkarapol, 19/12/2013, เปลี่ยนกลับไปใส่ title ใน td อีกครั้ง เนื่องจากใส่ใน tr แล้วเวลาที่ tooltip แสดง มันจะบังส่วนอื่น ทำให้ทำงานต่อไม่ได้

                                    $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                    $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
//                                    $str_body .= "<td colspan='5'>" . $order_column->Product_Exp . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                    $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                    $str_body .= "<td>" . @$order_column->Cont_No . ' ' . @$order_column->Cont_Size_No . @$order_column->Cont_Size_Unit_Code . "</td>";
                                    $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                    $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Confirm_Qty) . "</td>";
                                    $str_body .= "<td>" . @$order_column->Unit_Value . "</td>";
                                    $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";       //add by kik : 20140113
                                    $str_body .= "<td>" . @$order_column->Unit_Price_value . "</td>";                        //add by kik : 20140113
                                    $str_body .= "<td>" . set_number_format($all_price) . "</td>";            //add by kik : 20140113
//                                            $str_body .= "<td>" . set_number_format($order_column->All_Price) . "</td>";            //add by kik : 20140113
                                    $str_body .= "<td>" . $order_column->Suggest_Location . "</td>";
                                    $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                                    if ("ATV002" != $order_column->Activity_Code) {
                                        
                                    }
                                    $str_body .= "<td>" . @$order_column->Activity_By_Name . "</td>";
                                    $str_body .= "<td>" . @$order_column->Pallet_Code . "</td>";
                                    $str_body .= "<td .$title_remark.>" . $order_column->Reason_Remark . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                    $str_body .= "<td>" . $order_column->Product_Sub_Status . "</td>";
                                    $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Inbound_Item_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                                    $str_body .= "<td>" . $order_column->Actual_Location_Id . "</td>";
                                    $str_body .= "<td>" . @$order_column->Unit_Price_Id . "</td>";                           //add by kik : 20140113
                                    $str_body .= "</tr>";
                                    $j++;
                                    $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                    $sum_Cf_Qty+=$order_column->Confirm_Qty;            //add by kik    :   25-10-2013
                                    $sumPriceUnit+=@$order_column->Price_Per_Unit;       //add by kik    :   20140114
                                    $sumPrice+=$all_price;                              //add by kik    :   20140114  
                                
                                    
	$last_product_and_status = $product_and_status;
                                    
                                    endforeach;
                                echo $str_body;
                            endif;
                            ?>
<!--                            <tr>
                                <td colspan='11' class ='ui-state-default indent' id="colspan_before_reserve_qty" style='text-align: center;'><b>Total</b></td>
                                <td  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Receive_Qty); ?></span></td>
                                <td  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty); ?></span></td>
                                <td></td>
                                <td class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></td>
                                <td></td>
                                <td class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></td>
                                <td  id="colspan_before_suggest_location"  colspan='5' class ='ui-state-default indent' ></td>
                            </tr> -->
                        </tbody>

                        <!-- show total qty : by kik : 28-10-2013-->
                        <tfoot>
                            <tr>
                                <th colspan='11' class ='ui-state-default indent' id="colspan_before_reserve_qty" style='text-align: center;'><b>Total</b></th>
                                <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Receive_Qty); ?></span></th>
                                <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo set_number_format($sum_Cf_Qty); ?></span></th>
                                <th></th>
                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                <th></th>
                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                <th  id="colspan_before_suggest_location"  colspan='5' class ='ui-state-default indent' ></th>
                            </tr> 
                        </tfoot>
                        <!-- end show total qty : by kik : 28-10-2013-->   

                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>