<script>
    $(document).ready(function() {
        var area_width = $('#report_customs').width() - 20;
	$('#table-wrapper').width(area_width);
        
        $("#scroll_div").scroll(function(){
            $('#header_title').scrollLeft($(this).scrollLeft());
	});
        
    });
      
    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#frmRemainReport").attr('action', "<?php echo site_url(); ?>" + "/report_customs/exportRemainToExcel");
        } else {
            $("#frmRemainReport").attr('action', "<?php echo site_url(); ?>" + "/report_customs/exportRemainToPDF");
        }
        $("#frmRemainReport").submit();
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
    .Tables_wrapper{
        clear: both;
        height: auto;
        position: relative;
        width: 100%;
    }
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
        background: -webkit-gradient(linear, center top, center bottom, from(#A64B00), to(#FF7400)) !important;
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
    .Tables_wrapper .ui-toolbar {
        padding: 5px 5px 0;
        overflow: hidden;
    }
    
</style>
<form  method="post" target="_blank" id="frmRemainReport">
    <input type="hidden" name="fdate" value="<?php echo $search['fdate']; ?>" />
    <input type="hidden" name="customs_entry" value="<?php echo $search['customs_entry']; ?>" />
    <input type="hidden" name="ior" value="<?php echo $search['ior']; ?>" />
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
        <div id="table-wrapper" style="width: <?php echo ($search['w'] - 30)?>px;">
            <div style="width: <?php echo ($search['w'] - 30)?>px; overflow-x: hidden;">
                <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" style="max-width: none">
                    <thead>
                        <tr>
                            <th style="width:60px;"><?php echo _lang("no"); ?></th><!--ลำดับที่-->
                            <th style="width:100px;"><?php echo _lang("customs_entry"); ?></th><!--เลขที่ใบขน/ใบขน-->
                            <th style="width:100px;"><?php echo _lang("product_code"); ?></th><!--รหัสสินค้า/วัตถุดิบ-->
                            <th><?php echo _lang("product_name"); ?></th><!--รายละเอียดสินค้า-->
                            <th style="width:80px;"><?php echo _lang("balance"); ?></th><!--ปริมาณ-->
                            <th style="width:100px;"><?php echo _lang("unit"); ?></th>   <!--หน่วยนับ-->
                            <th style="width:120px;"><?php echo _lang('all_price'); ?></th> <!--มูลค่า-->
                        </tr>
                    </thead>
                
                    <tbody>
                        <?php
                        if(empty($data)):
                            echo "<tr><td align=center colspan=7><b>No Data Available.</b></td></tr>";
                        else:
                            $all_sum_balance = 0;
                            $sum_all_price = 0;
                            foreach ($data as $keyProduct => $datas) {
                                $i = 0;
                                ?>
                                <tr style="font-weight:bold;"><td style="background-color:#BBBBBB;">#</td><td style="background-color:#BBBBBB;text-align:left;" colspan=6>CE No.<?php echo $datas[0]['Doc_Refer_CE']; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IOR: <?php echo $datas[0]['Vendor_Name']; ?></td></tr>
                                <?php
                                $sum_balance = 0;
                                $sum_price = 0;
                                foreach ($datas as $key => $value) {
                                    $i++;
                                    ?>
                                    <tr>
                                        <td><?php echo $i ?></td>
                                        <td>&nbsp;</td>
                                        <td><?php echo $value["Product_Code"]; ?></td>
                                        <td align="left"><?php echo $value["Product_NameEN"]; ?></td>
                                        <td align="right"><?php echo set_number_format($value["Balance_Qty"]); ?></td>
                                        <td><?php echo $value["unit"]; ?></td>
                                        <td align="right"><?php echo set_number_format($value["Price"]) . " " . $value["unit_price"] ?></td>
                                    </tr>
                                    <?php
                                    $sum_balance+=$value["Balance_Qty"]; //รวม receive qty ของแต่ละ group          
                                    $sum_price+=$value["Price"];
                                    $all_sum_balance+=$value["Balance_Qty"]; //รวม receive qty ทั้งหมด
                                    $sum_all_price+=$value["Price"];
                                 }
                                 
                                 echo "<tr><td colspan=3>&nbsp;</td><td>Total</td><td align=right>".set_number_format($sum_balance)."</td><td>&nbsp;</td><td align='right'>".set_number_format($sum_price) . " " . $value["unit_price"] . "</td></tr>";
                             }
                             echo "<tr><td colspan=3>&nbsp;</td><td><b>All Total</b></td><td align=right><b>".set_number_format($all_sum_balance)."</b></td><td>&nbsp;</td><td>".set_number_format($sum_all_price)."</td></tr>";
                         endif;
                         ?>
                    </tbody>

                    <tfoot>
                        
                    </tfoot>

                </table>
            </div>  
         </div>  
    </div>
    <div id="pagination" class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
        <div class="dataTables_info" id="defDataTable2_info">Showing <?php echo $low+1 ?> to <?php echo $show_to ?> of <?php echo $items_total;?> entries</div>
        <div style="padding:3px;"class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers" id="defDataTable2_paginate">
            <?php echo $pagination?>
        </div>
    </div>
</form>