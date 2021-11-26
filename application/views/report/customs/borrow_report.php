<?php
if (empty($data)) {
    echo 'no result';
} else {
?>

<script>


    $(document).ready(function() {
       
        var area_width = $('#frmBorrowReport').width() - 20;
	$('#table-wrapper').width(area_width);   
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report_customs/exportBorrowToExcel");
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report_customs/exportBorrowToPDF");
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
    overflow: auto;
}
</style>

<div id="container" style="width:100%; margin:0 auto;">
    <form  method="post" action="" target="_blank" id="form_report">
        <input type="hidden" name="fdate" value="<?php echo $search['fdate']; ?>" />
        <input type="hidden" name="tdate" value="<?php echo $search['tdate']; ?>" />
        <input type="hidden" name="custom_doc_ref" value="<?php echo $search['custom_doc_ref']; ?>" />
        <div class='Tables_wrapper'>
            <div id="table-wrapper" style="width: <?php echo ($search['w'] - 30)?>px;">
                <div id='header_title' style="width: <?php echo ($search['w'] - 30)?>px; overflow-x: hidden;">
                    <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" style="max-width: none">
                        <thead>
                            <tr>
                                <th class="border-top" width="35px" height="30"><?php echo  _lang('no') ?></th>
                                <th class="border-top" width="100px"><?php echo _lang('borrow_date');?></th>
                                <th class="border-top" width="150px"><?php echo _lang("cus_custom_doc_ref");?></th>
                                <th class="border-top" width="150px"><?php echo  _lang('product_code'); ?></th>
                                <th class="border-top" width="215px"><?php echo  _lang('product_name'); ?></th>
                                <th class="border-top" width="100px">Export Qty</th>
                                <th class="border-top" width="100px">All price</th>
                                <th class="border-top" width="100px"><?php echo _lang('return_date');?></th>
                                <th class="border-top" width="150px"><?php echo  _lang('product_code'); ?></th>
                                <th class="border-top" width="215px"><?php echo  _lang('product_name'); ?></th>
                                <th class="border-top" width="100px">Import Qty</th>
                                <th class="border-top" width="100px">All Price</th>   
                                <th class="border-top" width="50px"><?php echo _lang("cus_date_diff");?></th>
                                <th class="border-top" width="100px">Remain Qty</th>
                                <th class="border-top" width="100px">Remark</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="scroll_div" style="width: <?php echo ($search['w'] - 30)?>px; height: 100%; overflow-y: hidden; overflow-x: scroll;">
                    <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" >
                        <tbody>
                            <?php
                            $i=0;
                            $j=0;
                            //p($data);
                            foreach ($data as $key => $datas):
                                $i++;

                                if($i%2==0):
                                    $color_main = "#F5FBFE";
                                else:
                                    $color_main = "#DCE5E9";
                                endif;
                                
                                foreach ($datas as $keys => $value):
                                    $j++;
                                    if($j%2==0):
                                        $color = "#FFFFE0";
                                    else:
                                        $color = "#EEEED1";
                                    endif;
                                    
                                    $num = $i;
                                    $confirm_qty = set_number_format($value["Confirm_Qty"]);
                                    $all_price = set_number_format($value["all_price"]);
                                    $remain_qty = set_number_format($value["remain_qty"]);
                                    $remark = $value["Remark"];
                                    
                                    if(empty($value["out_date"])):
                                        $num="";
                                        $confirm_qty = "";
                                        $all_price = "";
                                        $remain_qty = "";
                                        $remark = "";
                                    endif;
                                    
                                    $import_Receive_Qty = set_number_format($value["import_Receive_Qty"]);
                                    $import_All_Price = set_number_format($value["import_All_Price"]);
                                    if(empty($value["Inbound_Id_His"])):
                                        $import_Receive_Qty = "";
                                        $import_All_Price = "";
                                    endif;
                                    
                                ?>

                                <tr style="height: 30px;" valign="top">
                                    <td width="35px" style="background-color:<?php echo $color_main;?>;"><?php echo $num; ?></td>
                                    <td width="100px" style="background-color:<?php echo $color_main;?>;"><?php echo $value["out_date"]; ?></td>
                                    <td width="150px" style="background-color:<?php echo $color_main;?>;" align="left"><?php echo $value["Custom_Doc_Ref"]; ?></td>
                                    <td width="150px" style="background-color:<?php echo $color_main;?>;"><?php echo $value["Product_Code"]; ?></td>
                                    <td width="215px" style="background-color:<?php echo $color_main;?>;" align="left"><?php echo $value["Product_Name"]; ?></td>
                                    <td width="100px" style="background-color:<?php echo $color_main;?>;" align="right"><?php echo $confirm_qty; ?></td>
                                    <td width="100px" style="background-color:<?php echo $color_main;?>;" align="right"><?php echo $all_price; ?></td>
                                    
                                    <td width="100px" style="background-color:<?php echo $color;?>;"><?php echo $value["import_date"]; ?></td>
                                    <td width="150px"style="background-color:<?php echo $color;?>;"><?php echo $value["import_Product_Code"]; ?></td>
                                    <td width="215px" align="left" style="background-color:<?php echo $color;?>;"><?php echo $value["import_Product_Name"]; ?></td>
                                    <td width="100px" align="right" style="background-color:<?php echo $color;?>;"><?php echo $import_Receive_Qty; ?></td>
                                    <td width="100px" align="right" style="background-color:<?php echo $color;?>;"><?php echo $import_All_Price; ?></td>     
                                    <td width="50px" align="right" style="background-color:<?php echo $color;?>;"><?php echo $value["date_diff"]; ?></td>
                                    <td width="100px" valign="top" align="right" style="background-color:<?php echo $color_main;?>;"><?php echo $remain_qty; ?></td>
                                    <td width="100px" align="left" valign="top" style="background-color:<?php echo $color_main;?>;"><?php echo $remark; ?></td>
                                </tr>
                            <?php
                                endforeach;
                            endforeach;
                            ?>  
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
            </div>
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
