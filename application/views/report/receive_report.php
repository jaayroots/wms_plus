<script>
    var column = $.parseJSON('<?php echo $show_hide; ?>');
    $(document).ready(function() {
//        var area_width = $('#frmReceive').width() - 20;
//	$('#table-wrapper').width(area_width);
//        
//        $("#scroll_div").scroll(function(){
//            $('#header_title').scrollLeft($(this).scrollLeft());
//	});
        
        $.each(column, function(idx,val){
            if(!val){
                $("."+idx).hide();
            }
        });
    });
      
    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url("/report/exportReceiveToExcel"); ?>")
        } else {
            $("#form_report").attr('action', "<?php echo site_url("/report/exportReceiveToPDF"); ?>")
        }
        $("#form_report").submit();
    }
</script>
<style>
    td.group{
        background:#E6F1F6;
    }
    tr.reject_row{
        color:red;
    }
</style>
<style>
/*    .Tables_wrapper{
        clear: both;
        height: auto;
        position: relative;
        width: 100%;
    }*/
    .table_report{
        table-layout: fixed;
        margin-left: 0;
        max-width: none;
        width: 100%;
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-top-right-radius: 0 !important;
        margin-bottom: 0 !important;
    }

    .table_report tbody {
            width: 1000px;
            overflow: auto;
    }

    .table_report th {
        padding: 3px 5px;
        background: -moz-linear-gradient(center top , #013953, #002232) repeat scroll 0 0 transparent !important;
        background: -webkit-gradient(linear, center top, center bottom, from(#013953), to(#002232)) !important;
        border-left: 1px solid #D0D0D0 !important;
        border-radius: 0 0 0 0 !important;
        color : white !important;
    }

    table.table_report tr:nth-child(odd) td{
        background-color: #E2E4FF;
    }

    table.table_report tr:nth-child(even) td{
        background-color: #FFFFFF;
    }

    table.table_report td {
        border-right: 1px solid #D0D0D0;
        padding: 3px 5px;
    }

    table.table_report td {
        border-right: 1px solid #D0D0D0;
        padding: 3px 5px;
    }
/*    .Tables_wrapper .ui-toolbar {
        padding: 5px 5px 0;
        overflow: hidden;
    }*/

</style>
<form  method="post" target="_blank" id="form_report">
    <input type="hidden" name="fdate" value="<?php echo $search['fdate']; ?>" />
    <input type="hidden" name="tdate" value="<?php echo $search['tdate']; ?>" />
    <input type="hidden" name="doc_type" value="<?php echo $search['doc_type']; ?>" />
    <input type="hidden" name="doc_value" value="<?php echo $search['doc_value']; ?>" />
    <input type="hidden" name="active" value="<?php echo $search['active']; ?>" />
    <div class='Tables_wrapper'>
        <div class=" fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
            <div id="defDataTable2_length" class="dataTables_length">
                <?php echo $display_items_per_page?>
            </div>
            <div class="dataTables_filter" id="defDataTable2_filter">
                <label>
                    <!--Search: <input type="text" aria-controls="defDataTable2">-->
                </label>
            </div>
        </div>
        <!--<div id="table-wrapper" style="width: <?php echo ($search['w'] - 30)?>px;">-->
        <div id="table-wrapper" >
            <!--<div id='header_title' style="width: <?php echo ($search['w'] - 30)?>px; overflow-x: hidden;">-->
<!--            <div id='header_title'>
                <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" style="max-width: none">
                    <thead>
                        <tr>
                            <th class="receive_date" style="width:80px;"><?php echo _lang('receive_date'); ?></th>
                            <th class="document_no" style="width:100px;"><?php echo _lang('document_no'); ?></th>
                            <th class="document_ext" style="width:100px;"><?php echo _lang('document_ext'); ?></th>
                            <th class="document_bl" style="width:100px;"><?php echo _lang('document_bl'); ?></th>
                            <th class="product_code" style="width:100px;"><?php echo _lang('product_code'); ?></th>
                            <th class="product_name" style="width:200px;"><?php echo _lang('product_name'); ?></th>
                            <th class="product_status" style="width:80px;"><?php echo _lang('product_status'); ?></th>
                            <th class="lot" style="width:100px;"><?php echo _lang('lot'); ?></th>
                            <th class="serial" style="width:100px;"><?php echo _lang('serial'); ?></th>
                            <th class="product_mfd" style="width:80px;"><?php echo _lang('product_mfd'); ?></th>
                            <th class="product_exp" style="width:80px;"><?php echo _lang('product_exp'); ?></th>
                            <th class="invoice" style="width:80px;"><?php echo _lang('invoice_no'); ?></th>
                            <th class="container" style="width:80px;"><?php echo  _lang('container'); ?></th>
                            <th class="qty" style="width:80px;"><?php echo _lang('qty'); ?></th>
                            <th class="unit" style="width:50px;"><?php echo _lang('unit'); ?></th>

                            ADD BY POR 2014-06-18 กำหนดให้แสดง price per unit ด้วย
                            <th class="price_per_unit" style="width:80px;"><?php echo _lang('price_per_unit'); ?></th>
                            <th class="unit_price" style="width:50px;"><?php echo _lang('unit_price'); ?></th>
                            <th class="all_price" style="width:80px;"><?php echo _lang('all_price'); ?></th>
                            END ADD

                            <th class="from" style="width:80px;"><?php echo _lang('from'); ?></th>
                            <th class="to" style="width:80px;"><?php echo _lang('to'); ?></th>
                            <th class="pallet_code" style="width:80px;"><?php echo _lang('pallet_code'); ?></th>
                            <th class="remark" style="width:150px;"><?php echo _lang('remark'); ?></th>
                            
                            <th class="uom_qty" style="width: 100px;"><?php echo _lang('uom_qty') ?></th>
                            <th class="uom_unit_prod" style="width: 80px;"><?php echo _lang('uom_unit_prod') ?></th>
                        </tr>
                    </thead>
                </table>
            </div>-->
            <!--<div id="scroll_div" style="width: <?php // echo ($search['w'] - 30)?>px; height: 100%; overflow-y: hidden; overflow-x: scroll;">-->
            <!--<div id="scroll_div">-->
                <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info">
                    <thead>
                        <tr>
                            <th class="receive_date" style="width:80px;"><?php echo _lang('receive_date'); ?></th>
                            <th class="document_no" style="width:100px;"><?php echo _lang('document_no'); ?></th>
                            <th class="document_ext" style="width:100px;"><?php echo _lang('document_ext'); ?></th>
                            <!-- <th class="document_bl" style="width:100px;"><?php //echo _lang('document_bl'); ?></th> -->
                            <th class="document_refer_int" style="width:100px;"><?php echo _lang('doc_refer_int'); ?></th>
                            <th class="product_code" style="width:100px;"><?php echo _lang('product_code'); ?></th>
                            <th class="product_name" style="width:200px;"><?php echo _lang('product_name'); ?></th>
                            <th class="product_status" style="width:80px;"><?php echo _lang('product_status'); ?></th>
                            <th class="lot" style="width:100px;"><?php echo _lang('lot'); ?></th>
                            <th class="serial" style="width:100px;"><?php echo _lang('serial'); ?></th>
                            <th class="product_mfd" style="width:80px;"><?php echo _lang('product_mfd'); ?></th>
                            <th class="product_exp" style="width:80px;"><?php echo _lang('product_exp'); ?></th>
                            <th class="invoice" style="width:80px;"><?php echo _lang('invoice_no'); ?></th>
                            <th class="container" style="width:80px;"><?php echo  _lang('container'); ?></th>
                            <th class="qty" style="width:80px;"><?php echo _lang('qty'); ?></th>
                            <th class="unit" style="width:50px;"><?php echo _lang('unit'); ?></th>

                            <!--ADD BY POR 2014-06-18 กำหนดให้แสดง price per unit ด้วย-->
                            <th class="price_per_unit" style="width:80px;"><?php echo _lang('price_per_unit'); ?></th>
                            <th class="unit_price" style="width:50px;"><?php echo _lang('unit_price'); ?></th>
                            <th class="all_price" style="width:80px;"><?php echo _lang('all_price'); ?></th>
                            <!--END ADD-->

                            <th class="from" style="width:80px;"><?php echo _lang('from'); ?></th>
                            <th class="to" style="width:80px;"><?php echo _lang('to'); ?></th>
                            <th class="pallet_code" style="width:80px;"><?php echo _lang('pallet_code'); ?></th>
                            <th class="remark" style="width:150px;"><?php echo _lang('remark'); ?></th>
                            
                            <th class="uom_qty" style="width: 100px;"><?php echo _lang('CBM') ?></th>
                            <th class="uom_unit_prod" style="width: 80px;"><?php echo _lang('uom_unit_prod') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sum_not_reject = 0;
                        //ADD BY POR 2014-06-18 เพิ่มให้แสดง price_per_unit
                        $sum_not_reject_price = 0;
                        $sum_not_reject_all_price = 0;
                        $sum_not_reject_uom = 0;
                        //END ADD

                        $sum_reject = 0;
                        //ADD BY POR 2014-06-18 เพิ่มให้แสดง price_per_unit
                        $sum_reject_price = 0;
                        $sum_reject_all_price = 0;
                        $sum_reject_uom = 0;
                        //END ADD

                        $sum_all = 0;
                        //ADD BY POR 2014-06-18 เพิ่มให้แสดง price_per_unit
                        $sum_price = 0;
                        $sum_all_price = 0;
                        $sum_uom = 0;
                        //END ADD

                        $have_reject = FALSE;
                        if(empty($data)):
                            echo "<tr><td colspan=$all_column align=center><b>No Data Available.</b></td></tr>";
                        else:     
                            foreach ($data as $key => $value) : ?>
                            <!--order reject concept show in report -->
                                <!--1. รับเข้าตามจำนวนที่กรอกมา เพื่อสามารถ ตรวจสอบยอดที่มีรายการเคลื่อนไหวเข้ามา และยกเลิกภายหลังได้-->
                                <!--2. ตัดออกตามจำนวน receive qty เพื่อให้ยอด total ทั้งหมด เท่ากัน-->
                                
                                <?php if($value->Is_reject == 'Y'): 
//                                p($value);
                                    $have_reject = TRUE;
                                    ?>

                                        <!--บวกเข้า-->
                                        <tr>
                                            <td class="receive_date" style="text-align:left;width:80px;"><?php echo $value->Receive_Date; ?></td>
                                            <td class="document_no" style="text-align:left;width:100px;"><?php echo $value->Document_No; ?></td>
                                            <td class="document_ext" style="text-align:left;width:100px;"><?php echo $value->Doc_Refer_Ext; ?></td>
                                            <!-- <td class="document_bl" style="text-align:left;width:100px;"><?php //echo $value->Doc_Refer_BL; ?></td> -->
                                            <td class="document_refer_int" style="text-align:left;width:100px;"><?php echo $value->Doc_Refer_Int; ?></td>
                                            <td class="product_code" style="text-align:left;width:100px;"><?php echo $value->Product_Code; ?></td>
                                            <td class="product_name" style="text-align:left;width:200px;"><?php echo $value->Product_Name; ?></td>
                                            <td class="product_status" style="text-align:left;width:80px;"><?php echo $value->Dom_TH_Desc; ?></td>
                                            <td class="lot" style="text-align:center;width:100px;"><?php echo $value->Product_Lot; ?></td>
                                            <td class="serial" style="text-align:center;width:100px;"><?php echo $value->Product_Serial; ?></td>
                                            <td class="product_mfd" style="text-align:left;width:80px;"><?php echo $value->Product_Mfd; ?></td>
                                            <td class="product_exp" style="text-align:left;width:80px;"><?php echo $value->Product_Exp; ?></td>
                                            <td class="invoice" style="text-align:left;width:80px;"><?php echo @$value->Invoice_No; ?></td>
                                            <td class="container" style="text-align:left;width:80px;"><?php echo @$value->Cont_No." ".@$value->Cont_Size_No."".@$value->Cont_Size_Unit_Code; ?></td>
                                            <td class="qty" style="text-align:right;width:80px;" ><?php echo set_number_format($value->Receive_Qty); ?></td>
                                            <td class="unit" style="text-align:center;width:50px;"><?php echo $value->Unit_Value; ?></td>

                                            <!--ADD BY POR 2014-06-18 price per unit-->
                                            <td class="price_per_unit" style="text-align:right;width:80px;"><?php echo set_number_format($value->Price_Per_Unit); ?></td>
                                            <td class="unit_price" style="text-align:center;width:50px;"><?php echo $value->Unit_price; ?></td>
                                            <td class="all_price" style="text-align:right;width:80px;"><?php echo set_number_format($value->All_price); ?></td>
                                            <!--END ADD-->

                                            <td class="from" style="text-align:center;width:80px;"><?php echo $value->From_sup; ?></td>
                                            <td class="to" style="text-align:center;width:80px;"><?php echo $value->To_sup; ?></td>
                                            <td class="pallet_code" style="text-align:center;width:80px;"><?php echo $value->Pallet_Code; ?></td>
                                            <td class="remark" style="text-align:left;width:150px;"><?php echo $value->Uom_Qty;//$value->Remark; ?></td>
                                            
                                            <td class='uom_qty' style="text-align:right;width: 100px;"><b><?php echo set_number_format($value->CBM); ?></b></td>
                                            <td class='uom_unit_prod' style="text-align:right;width: 80px;"><?php echo $value->Uom_Unit_Val; ?></td>
                                        </tr>

                                        <!--ลบออก-->
                                        <tr class="reject_row">
                                            <td class="receive_date" style="text-align:left;width:80px;"><?php echo $value->Receive_Date; ?></td>
                                            <td class="document_no" style="text-align:left;width:100px;"><?php echo $value->Document_No; ?></td>
                                            <td class="document_ext" style="text-align:left;width:100px;"><?php echo $value->Doc_Refer_Ext; ?></td>
                                            <td class="document_ext" style="text-align:left;width:100px;"><?php echo $value->Doc_Refer_Int; ?></td>
                                            <!-- <td class="document_bl" style="text-align:left;width:100px;"><?php //echo $value->Doc_Refer_BL; ?></td> -->
                                            <td class="product_code" style="text-align:left;width:100px;"><?php echo $value->Product_Code; ?></td>
                                            <td class="product_name" style="text-align:left;width:200px;"><?php echo $value->Product_Name; ?></td>
                                            <td class="product_status" style="text-align:left;width:80px;"><?php echo $value->Dom_TH_Desc; ?></td>
                                            <td class="lot" style="text-align:center;width:100px;"><?php echo $value->Product_Lot; ?></td>
                                            <td class="serial" style="text-align:center;width:100px;"><?php echo $value->Product_Serial; ?></td>
                                            <td class="product_mfd" style="text-align:left;width:80px;"><?php echo $value->Product_Mfd; ?></td>
                                            <td class="product_exp" style="text-align:left;width:80px;"><?php echo $value->Product_Exp; ?></td>
                                            <td class="invoice" style="text-align:left;width:80px;"><?php echo @$value->Invoice_No; ?></td>
                                            <td class="container" style="text-align:left;width:80px;"><?php echo @$value->Cont_No." ".@$value->Cont_Size_No."".@$value->Cont_Size_Unit_Code; ?></td>
                                            <td class="qty" style="text-align:right;width:80px;" ><?php echo set_number_format(-$value->Receive_Qty); ?></td>
                                            <td class="unit" style="text-align:center;width:50px;"><?php echo $value->Unit_Value; ?></td>

                                            <!--ADD BY POR 2014-06-18 price per unit-->
                                            <td class="price_per_unit" style="text-align:right;width:80px;"><?php echo set_number_format(-$value->Price_Per_Unit); ?></td>
                                            <td class="unit_price" style="text-align:center;width:50px;"><?php echo $value->Unit_price; ?></td>
                                            <td class="all_price" style="text-align:right;width:80px;"><?php echo set_number_format(-$value->All_price); ?></td>
                                            <!--END ADD-->

                                            <td class="from" style="text-align:center;width:80px;"><?php echo $value->From_sup; ?></td>
                                            <td class="to" style="text-align:center;width:80px;"><?php echo $value->To_sup; ?></td>
                                            <td class="pallet_code" style="text-align:center;width:80px;"><?php echo $value->Pallet_Code; ?></td>
                                            <td class="remark" style="text-align:left;width:150px;"><?php echo ($value->Remark == " " || $value->Remark == "" || empty($value->Remark))?"Reject":$value->Remark; ?></td>
                                            
                                            <td class='uom_qty' style="text-align:right;width: 100px;"><b><?php echo set_number_format(-$value->CBM); ?></b></td>
                                            <td class='uom_unit_prod' style="text-align:right;width: 80px;"><?php echo $value->Uom_Unit_Val; ?></td>
                                       </tr>

                                        <?php 
                                        $sum_reject +=@$value->Receive_Qty ;

                                        //ADD BY POR 2014-06-18
                                        $sum_reject_price +=@$value->Price_Per_Unit ;
                                        $sum_reject_all_price +=@$value->All_price ;
                                        //END ADD
                                        
                                        $sum_reject_uom +=@$value->CBM;
                                        ?>

                                <?php else:?>
                                    <tr>
                                            <td class="receive_date" style="text-align:left;width:80px;"><?php echo $value->Receive_Date; ?></td>
                                            <td class="document_no" style="text-align:left;width:100px;"><?php echo $value->Document_No; ?></td>
                                            <td class="document_ext" style="text-align:left;width:100px;"><?php echo $value->Doc_Refer_Ext; ?></td>
                                            <td class="document_ext" style="text-align:left;width:100px;"><?php echo $value->Doc_Refer_Int; ?></td>
                                            <!-- <td class="document_bl" style="text-align:left;width:100px;"><?php //echo $value->Doc_Refer_BL; ?></td> -->
                                            <td class="product_code" style="text-align:left;width:100px;"><?php echo $value->Product_Code; ?></td>
                                            <td class="product_name" style="text-align:left;width:200px;"><?php echo $value->Product_Name; ?></td>
                                            <td class="product_status" style="text-align:left;width:80px;"><?php echo $value->Dom_TH_Desc; ?></td>
                                            <td class="lot" style="text-align:center;width:100px;"><?php echo $value->Product_Lot; ?></td>
                                            <td class="serial" style="text-align:center;width:100px;"><?php echo $value->Product_Serial; ?></td>
                                            <td class="product_mfd" style="text-align:left;width:80px;"><?php echo $value->Product_Mfd; ?></td>
                                            <td class="product_exp" style="text-align:left;width:80px;"><?php echo $value->Product_Exp; ?></td>
                                            <td class="invoice" style="text-align:left;width:80px;"><?php echo @$value->Invoice_No; ?></td>
                                            <td class="container" style="text-align:left;width:80px;"><?php echo @$value->Cont_No." ".@$value->Cont_Size_No."".@$value->Cont_Size_Unit_Code; ?></td>
                                            <td class="qty" style="text-align:right;width:80px;" ><?php echo set_number_format($value->Receive_Qty); ?></td>
                                            <td class="unit" style="text-align:center;width:50px;"><?php echo $value->Unit_Value; ?></td>

                                            <!--ADD BY POR 2014-06-18 price per unit-->
                                            <td class="price_per_unit" style="text-align:right;width:80px;"><?php echo set_number_format($value->Price_Per_Unit); ?></td>
                                            <td class="unit_price" style="text-align:center;width:50px;"><?php echo $value->Unit_price; ?></td>
                                            <td class="all_price" style="text-align:right;width:80px;"><?php echo set_number_format($value->All_price); ?></td>
                                            <!--END ADD-->

                                            <td class="from" style="text-align:center;width:80px;"><?php echo $value->From_sup; ?></td>
                                            <td class="to" style="text-align:center;width:80px;"><?php echo $value->To_sup; ?></td>
                                            <td class="pallet_code" style="text-align:center;width:80px;"><?php echo $value->Pallet_Code; ?></td>
                                            <td class="remark" style="text-align:left;width:150px;"><?php echo $value->Remark; ?></td>
                                            
                                            <td class='uom_qty' style="text-align:right;width: 100px;"><b><?php echo set_number_format($value->CBM); ?></b></td>
                                            <td class='uom_unit_prod' style="text-align:center;width: 80px;"><?php echo $value->Uom_Unit_Val; ?></td>   
                                        </tr>

                                        <?php 
                                        $sum_not_reject +=@$value->Receive_Qty ;

                                        $sum_not_reject_price +=@$value->Price_Per_Unit ;
                                        $sum_not_reject_all_price +=@$value->All_price ;
                                        $sum_not_reject_uom +=@$value->CBM;
                                        
                                        ?>

                                <?php endif; 

                                $sum_all+=@$value->Receive_Qty;
                                //ADD BY POR 2014-06-18
                                $sum_price +=@$value->Price_Per_Unit ;
                                $sum_all_price +=@$value->All_price ;
                                $sum_uom +=@$value->CBM;
                                //END ADD
                            endforeach;
                         endif;
                        ?>
                    </tbody>

                    <tfoot>
                        <tr >
                            <th style='text-align: left;' colspan="<?php echo @$colspan[1];?>"><b>Total</b> <?php if($have_reject): ?> <font style="font-size:10px;">(Exclude Reject)</font><?php endif;?></th>
                            <th style='text-align: right;'><?php echo set_number_format($sum_not_reject); ?></th>
                            <th class="unit"></th>
                            <th class="from"></th>
                            <th class="to"></th>
                            <th class="pallet_code" ></th>
                            <th class="remark" ></th>
                            <th style='text-align: right;' class="price_per_unit"><?php echo set_number_format($sum_not_reject_price); ?></th>
                            <th class="unit_price"></th>
                            <th style='text-align: right;' class="all_price"><?php echo set_number_format($sum_not_reject_all_price); ?></th>                            
                            <th style='text-align: right;' class="uom_qty"><?php echo set_number_format($sum_not_reject_uom); ?></th>
                            <th style='text-align: right;' class="uom_unit_prod"></th>
                        </tr>
                        <!--Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
                        <?php if($have_reject): ?>
                        <tr bgcolor="#EEEED1" >
                            <td style='text-align: left;' colspan="<?php echo $colspan[1];?>"><b>Reject</b></td>
                            <td style='text-align: right; border: 1px solid #D0D0D0;'><?php echo set_number_format($sum_reject); ?></td>
                            <td class="unit"></td>
                            <td class="from"></td>
                            <td class="to"></td>
                            <td class="pallet_code" ></td>
                            <td class="remark" ></td>                            
                            <td style='text-align: right; border: 1px solid #D0D0D0;' class="price_per_unit"><?php echo set_number_format($sum_reject_price); ?></td>
                            <td class="unit_price"></td>
                            <td style='text-align: right; border: 1px solid #D0D0D0;' class="all_price"><?php echo set_number_format($sum_reject_all_price); ?></td>
                            <td style='text-align: right;' class="uom_qty"><?php echo set_number_format($sum_reject_uom); ?></td>
                            <td style='text-align: right;' class="uom_unit_prod"></td>
                        </tr>
                        <tr bgcolor="#CDCDB4">
                            <td style='text-align: left;' colspan="<?php echo $colspan[1];?>"><b>All Total</b></td>
                            <td style='text-align: right; border: 1px solid #D0D0D0;'><?php echo set_number_format($sum_all); ?></td>
                            <td class="unit"></td>
                            <td class="from"></td>
                            <td class="to"></td>
                            <td class="pallet_code" ></td>
                            <td class="remark" ></td>
                            <td style='text-align: right; border: 1px solid #D0D0D0;' class="price_per_unit"><?php echo set_number_format($sum_price); ?></td>
                            <td class="unit_price"></td>
                            <td style='text-align: right; border: 1px solid #D0D0D0;' class="all_price"><?php echo set_number_format($sum_all_price); ?></td>
                            <td style='text-align: right;' class="uom_qty"><?php echo set_number_format($sum_uom); ?></td>
                            <td style='text-align: right;' class="uom_unit_prod"></td>
                        </tr>
                        <?php endif; ?>
                        <!-- end Reject Area show Total - ในส่วนนี้จะแสดงผลเฉพาะ SKU ที่มีรายการ reject เกิดขึ้นเท่านั้น-->
                    </tfoot>

                </table>
            <!--</div>-->  
         </div>  
    </div>
    <div id="pagination" class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
        <div class="dataTables_info" id="defDataTable2_info">Showing <?php echo $low+1 ?> to <?php echo $show_to ?> of <?php echo $items_total;?> entries</div>
        <div style="padding:3px;"class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers" id="defDataTable2_paginate">
            <?php echo $pagination?>
        </div>
    </div>
</form>
