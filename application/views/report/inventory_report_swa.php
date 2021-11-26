<?php
if (empty($data)) {
    echo 'no result';
} else {
?>

<script>
      var column = $.parseJSON('<?php echo $show_hide; ?>');   //ADD BY POR 2014-06-25 à¸à¸³à¸«à¸™à¸” column à¸—à¸±à¹‰à¸‡à¸«à¸¡à¸”à¸—à¸µà¹ˆà¸¡à¸µà¹ƒà¸™à¸£à¸²à¸¢à¸‡à¸²à¸™à¸™à¸µà¹‰
//    var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';

    $(document).ready(function() {
       
//        if(!built_pallet){  // check config built_pallet if it is false then hide a column Pallet Code
//            $('.td_pallet_code').hide();
//        }

        var area_width = $('#frmPreDispatch').width() - 20;
		$('#table-wrapper').width(area_width);
                
        //ADD BY POR 2014-06-25 à¸ˆà¸±à¸”à¸à¸²à¸£à¹€à¸£à¸·à¹ˆà¸­à¸‡ show hide column
        $.each(column, function(idx,val){
            if(!val){ //à¸•à¸£à¸§à¸ˆà¸ªà¸­à¸š value à¸ˆà¸²à¸ xml
                $("."+idx).hide(); //à¸à¸£à¸“à¸µà¹„à¸¡à¹ˆà¹ƒà¸«à¹‰à¹à¸ªà¸”à¸‡à¸„à¹ˆà¸²
            }
        });
        //END ADD
        
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportInventoryToExcel_swa")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportInventoryPdf_swa")
        }
        $("#form_report").submit();
    }
    
</script>
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

table.table_report tr:nth-child(odd) td.uom_qty{
    background-color: #ccc;
}

table.table_report tr:nth-child(even) td.uom_qty{
    background-color: #EEEED1;
}


table.table_report tr:nth-child(odd) td.uom_unit_prod{
    background-color: #ccc;
}

table.table_report tr:nth-child(even) td.uom_unit_prod{
    background-color: #EEEED1;
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

    <div id="container" style="width:100%; margin:0 auto;">
        <form  method="post" action="" target="_blank" id="form_report">
            <input type="hidden" name="renter_id" id="renter_id" value="<?php echo $search['renter_id']; ?>">
            <input type="hidden" name="product_id" id="product_id" value="<?php echo $search['product_id']; ?>">
            <input type="hidden" name="status_id" id="as_date" value="<?php echo $search['status_id']; ?>">
            <!--<div class='Tables_wrapper'>-->
                
                
                
                <div class='Tables_wrapper'>
<!--                    <div class="Tables_wrapper fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
                        <div id="defDataTable2_length" class="dataTables_length">
                            <label>Show 
                                <select name="defDataTable2_length" size="1" aria-controls="defDataTable2">
                                    <option value="100" selected="selected">100</option>
                                    <option value="250">250</option>
                                    <option value="500">500</option>
                                    <option value="1000">1000</option>
                                    <option value="-1">All</option>
                                </select> entries
                            </label>
                        </div>
                        <div class="dataTables_filter" id="defDataTable2_filter">
                            <label>Search: <input type="text" aria-controls="defDataTable2"></label>
                        </div>
                    </div>-->
                    <div id="table-wrapper" style="width: <?php echo ($search['w'] - 30)?>px;">
                        <div id='header_title' style="width: <?php echo ($search['w'] - 30)?>px; overflow-x: hidden;">
                            <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" style="max-width: none">
                                <thead>
                                    <tr>
                                        <!--class="border-top"-->
                                        <th class="border-top" style="width: 35px;"><?php echo  _lang('no') ?></th>
                                        <th class="product_code border-top" style="width: 150px;"><?php echo  _lang('product_code') ?></th>
                                        <th class="product_name border-top" style="width: 215px;"><?php echo  _lang('product_name') ?></th>
                                        <th class="lot border-top" style="width: 100px;"><?php echo  _lang('lot') ?></th>
                                        <th class="serial border-top" style="width: 100px;"><?php echo  _lang('serial') ?></th>
                                        <th class="product_mfd border-top" style="width: 100px;"><?php echo  _lang('product_mfd') ?></th>
                                        <th class="product_exp border-top" style="width: 100px;"><?php echo  _lang('product_exp') ?></th>
                                        <th class="invoice border-top " style="width: 100px;"><?php echo  _lang('invoice_no') ?></th>
                                        <th class="container border-top " style="width: 100px;"><?php echo  _lang('container') ?></th>
                                        <th class="pallet_code border-top " style="width: 100px;"><?php echo  _lang('pallet_code') ?></th>
                                        

                                        <?php
                                        $sum_balance = array();
                                        $j = 1;
                                        foreach ($range as $col) {
                                            if (!array_key_exists($j, $sum_balance)) {
                                                $sum_balance[$j] = 0;
                                            }
                                            echo "<th  class=\"border-top\" style='width: 100px;'>$col</th>";
                                            $j++;
                                        }
                                        ?>
                                        
                                        <th class="border-top" style="width: 100px;"><?php echo  _lang('Root Unit') ?></th>
                                        <th class="border-top" style="width: 100px;"><?php echo  _lang('total') ?></th>
                                        <th class="border-top" style="width: 100px;"><?php echo  _lang('booked') ?></th>
                                        <th class="border-top" style="width: 100px;"><?php echo  _lang('dispatch_qty') ?></th>
                                        <th class="unit border-top" style="width: 80px;"><?php echo  _lang('unit') ?></th>
                                        <th class="uom_qty border-top" style="width: 100px;"><?php echo  _lang('uom_qty') ?></th>
                                        <th class="uom_unit_prod border-top" style="width: 80px;"><?php echo  _lang('uom_unit_prod') ?></th>
                                        
                                    </tr>
                                </thead>
                            </table>
                        </div>
                            <div id="scroll_div" style="width: <?php echo ($search['w'] - 30)?>px; height: 100%; overflow-y: hidden; overflow-x: scroll;">
                            <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" >
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $sum_balance_all=0;
                                    $sum_booked_all=0;
                                    $sum_dispatch_all=0;
                                    $sum_qty_uom_all=0;
                                    foreach ($data as $cols) { ?>

                                        <tr style="height: 30px;">
                                            <td style="width: 35px;"><?php echo $i; ?></td>                            
                                            <!--<td class='product_code' style="width: 150px; text-align: left;" <?//php echo ($is_today)?" class='td_click' ONCLICK='showProductEstInbound(\"{$cols->Product_Code}\",\"{$cols->Product_Lot}\",\"{$cols->Product_Serial}\",\"{$cols->Product_Mfd}\",\"{$cols->Product_Exp}\")'":""?>><?php echo $cols->Product_Code; ?></td>-->                        
                                            <td class='product_code' style="width: 150px; text-align: left;"><?php echo $cols->Product_Code; ?></td>                        
                                            <td class='product_name' style="width: 215px; text-align: left;"><?php echo $cols->Product_NameEN; ?></td>
                                            <td class='lot' style="width: 100px;" align="left"><?php echo @$cols->Product_Lot; ?></td>
                                            <td class='serial' style="width: 100px;" align="left"><?php echo @$cols->Product_Serial; ?></td>
                                            <td class='product_mfd' style="width: 100px;" align="center"><?php echo @$cols->Product_Mfd; ?></td>
                                            <td class='product_exp' style="width: 100px;" align="center"><?php echo @$cols->Product_Exp; ?></td>
                                            <td class='invoice' style="width: 100px;" align="center"><?php echo @$cols->Invoice_No; ?></td>
                                            <td class='container' style="width: 100px;" align="center"><?php echo @$cols->Cont; ?></td>
                                            <td class='pallet_code' style="width: 100px;" align="center"><?php echo @$cols->Pallet_Code; ?></td>
                                            
                                            
                                            <?php
                                            $name = "counts";

                                            $count_range = count($range); // Add By Akkarapol, 04/11/2013, à¹€à¸žà¸´à¹ˆà¸¡à¸•à¸±à¸§à¹à¸›à¸£ $count_range à¹„à¸§à¹‰à¸£à¸±à¸šà¸„à¹ˆà¸²à¸‚à¸­à¸‡ count($range); à¸‹à¸¶à¹ˆà¸‡à¸ˆà¸°à¸™à¸³à¹„à¸›à¹ƒà¸Šà¹‰à¸•à¹ˆà¸­à¹ƒà¸™ loop for
                                            for ($j = 1; $j <= $count_range; $j++) { // Add By Akkarapol, 04/11/2013, à¹€à¸›à¸¥à¸µà¹ˆà¸¢à¸™à¸ˆà¸²à¸ $j <= count($range) à¹ƒà¸«à¹‰à¹€à¸›à¹‡à¸™ $j <= $count_range à¸—à¸µà¹ˆà¹„à¸”à¹‰à¹€à¸à¹‡à¸šà¸„à¹ˆà¸²à¹„à¸§à¹‰à¸‹à¸° à¹€à¸žà¸·à¹ˆà¸­à¸¥à¸”à¸à¸²à¸£à¸—à¸³à¸‡à¸²à¸™à¸‚à¸­à¸‡ PHP 
                                                $balance = 'counts_' . $j;

                                                //balance
                                                $sum_balance[$j]+=$cols->{$balance};
                                                $sum_balance_all+=$cols->{$balance}; 

                                                ?>
                                                <td style="text-align:right; width: 100px;">
                                                    <?php
                                                    if ($cols->{$balance} == 0) {
                                                        $balance = "";
                                                    } else {
                                                        $balance = "<b>" . set_number_format($cols->{$balance}) . "</b>";
                                                    }
                                                    echo $balance;
                                                    ?>
                                                </td>

                                                <?php
                                            }
                                            ?>
                                            
                                            <td style="text-align:right; width: 100px;"><b><?php echo set_number_format($cols->PKG*$cols->totalbal.'&nbsp'); ?></b></td>
                                            <td style="text-align:right; width: 100px;"><b><?php echo set_number_format($cols->totalbal); ?></b></td>
                                            <td style="text-align:right; width: 100px;"><b><?php echo set_number_format($cols->Booked); ?></b></td>
                                            <td style="text-align:right; width: 100px;"><b><?php echo set_number_format($cols->Dispatch); ?></b></td>
                                            <td class='unit' style="text-align:right;width: 80px;"><?php echo @$cols->Unit_Value; ?></td>
                                            <td class='uom_qty' style="text-align:right;width: 100px;"><b><?php echo set_number_format(@$cols->Uom_Qty); ?></b></td>
                                            <td class='uom_unit_prod' style="text-align:right;width: 80px;"><?php echo @$cols->Uom_Unit_Val; ?></td>
                                            
                                        </tr>
                                        <?php
                                        //Qty of UOM
                                        $sum_qty_uom_all+=$cols->Uom_Qty;
                                        
                                        //booked
                                        $sum_booked_all+=$cols->Booked; 

                                        //dispatch
                                        $sum_dispatch_all+=$cols->Dispatch;

                                        //sum root unit
                                        $sum_root_unit+=$cols->PKG*$cols->totalbal;
                                        $i++;
                                    }
                                    ?>  
                                </tbody>
                                <tfoot>
                                    <tr bgcolor="#EEEED1">
                                        
                                       <th colspan="<?php echo ($colspan[1]);?>" align="center"><b>Total Balance</b></th>
                                        
                                        <?php
                                        foreach ($sum_balance as $sb) {
                                            ?>
                                            <th style="width:100px;"><?php echo set_number_format($sb); ?></th>
                                            <?php
                                        }
                                        ?>
                                        <th  align="right"><b><?php echo set_number_format($sum_root_unit); ?></b></th> 
                                        <th  align="right"><b><?php echo set_number_format($sum_balance_all); ?></b></th> 
                                        <th  align="right"><b><?php echo set_number_format($sum_booked_all); ?></b></th>
                                        <th  align="right"><b><?php echo set_number_format($sum_dispatch_all); ?></b></th>
                                        <th  class='unit'></th>
                                        <th  class='uom_qty' align="right"><b><?php echo set_number_format($sum_qty_uom_all); ?></b></th>
                                        <th  class='uom_unit_prod'></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                    </div>
               </div>
<!--                     <div class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
                        <div class="dataTables_info" id="defDataTable2_info">Showing 0 to 0 of 0 entries</div>
                        <div class="dataTables_info" id="defDataTable2_info"></div>
                        <div class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers" id="defDataTable2_paginate">
                            <a class="first ui-corner-tl ui-corner-bl fg-button ui-button ui-state-default ui-state-disabled" tabindex="0" id="defDataTable2_first">First</a>
                            <a class="previous fg-button ui-button ui-state-default ui-state-disabled" tabindex="0" id="defDataTable2_previous">Previous</a>
                            <span></span>
                            <a class="next fg-button ui-button ui-state-default ui-state-disabled" tabindex="0" id="defDataTable2_next">Next</a>
                            <a class="last ui-corner-tr ui-corner-br fg-button ui-button ui-state-default ui-state-disabled" tabindex="0" id="defDataTable2_last">Last</a>
                        </div>
                    </div>-->
                </div>
               
            </div>
            <!--</div>-->
            
            
        </form>
    </div>
<?php } ?>
<script>
$(document).ready(function(){
	$("#scroll_div").scroll(function(){
		$('#header_title').scrollLeft($(this).scrollLeft());
	});
});
</script>
<?php $this->load->view('element_showEstBal_inventory'); ?>
