
<script>

    $(document).ready(function() {
        oTable = $('#defDataTable2').dataTable({
            "aaSortingFixed": [[0, 'asc']],
            "aaSorting": [[1, 'asc']],
            "sPaginationType": "full_numbers",
            "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
            "aoColumnDefs": [
                {"sWidth": "5%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "10%", "sClass": "left_text", "aTargets": [1]},
                {"sWidth": "20%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "10%", "sClass": "right_text", "aTargets": [3]}, 
                {"sWidth": "10%", "sClass": "right_text", "aTargets": [4]},
                {"sWidth": "10%", "sClass": "right_text", "aTargets": [5]},
                {"sWidth": "10%", "sClass": "right_text", "aTargets": [6]}
            ]
        });
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportExcelMovement_showTotal")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportReportStockMovementToPDF_showTotal")
        }
        $("#form_report").submit();
    }
</script>
<style>
    td.group{
        background:#BEBEBE;
        text-align: left;
    }
    td.sum_group{
        background:#E8E8E8;
        text-align: right;
    }
</style>
<form method="post" target="_blank" id="form_report" >
    <table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
        <thead>
            <tr>
                <th><?php echo _lang('no') ?></th>
                <th><?php echo _lang('product_code') ?></th>
                <th><?php echo _lang('product_name') ?></th>
                <th><?php echo _lang('income_qty') ?></th>
                <th><?php echo _lang('receive_qty') ?></th>
                <th><?php echo _lang('dispatch_qty') ?></th>
                <th><?php echo _lang('balance') ?></th>
            </tr>
        </thead>
        
        <tbody>
           <?php
           //Edit by Por 2014-04-2 set default variable
           $sumReceiveQty = 0;     //add $sumReservQty variable for calculate total Receive qty : by kik : 31-10-2013
           $sumDispatchQty = 0;    //add $sumReceiveQty variable for calculate total Dispatch qty : by kik : 31-10-2013
           $sumBalanceQty = 0;    //add $sumReceiveQty variable for calculate total Balance qty : by kik : 31-10-2013
           $sumStartBalanceQty = 0;  
           //End edit
           
            if(!empty($datas['no_onhand'])){ ?>
                <font size="3" color="red"><?php echo $datas['no_onhand'];?></font><br>
           <? }else if (!empty($datas)) {
                $i = 1;
                        foreach ($datas as $key=>$value) {
                            $balance_qty = ($value['incoming_qty']+$value['receive_qty'])-$value['dispatch_qty'];
                            ?>
                            <tr>
                                <td ><?php echo $i; ?></td>
                                <td><?php echo $value['Product_Code']; ?></td>
                                <td><?php echo $value['Product_NameEN']; ?></td>
                                <td><?php echo set_number_format($value['incoming_qty']); ?></td>
                                <td><?php echo set_number_format($value['receive_qty']); ?></td>
                                <td><?php echo set_number_format($value['dispatch_qty']); ?></td>
                                <td style='text-align: right'><?php echo set_number_format($balance_qty); ?></td>
                            </tr>
                            
                            <?php
                            $i++;
                            $sumReceiveQty+=@$value['receive_qty'];  
                            $sumDispatchQty+=@$value['dispatch_qty'];    
                            $sumStartBalanceQty+=@$value['incoming_qty'];
                            $sumBalanceQty+=@$balance_qty; 
                        }
             }
            ?>

        </tbody>
        <tfoot>
                 <tr>
                    <th colspan="3" class ="sum_group"><b>Total</b></th>
                    <th class ="sum_group" style="text-align: right;"><b><?php echo set_number_format($sumStartBalanceQty);?></b></th>
                    <th class ="sum_group" style='text-align: right'><b><?php echo set_number_format($sumReceiveQty);?></b></th>
                    <th class ="sum_group" style='text-align: right'><b><?php echo set_number_format($sumDispatchQty);?></b></th>
                    <th class ="sum_group" style='text-align: right'><b><?php echo set_number_format($sumBalanceQty);?></b></th>
                </tr>
        </tfoot>
        
        
    </table>
    <div align="center" style="margin:10px auto">
        <input type="hidden" name="product_name" value="<?php echo $form_value['product']; ?>">
        <input type="hidden" name="product_id2" value="<?php echo $form_value['product_id']; ?>">
        <input type="hidden" name="fdate" value="<?php echo $form_value['fdate']; ?>">
        <input type="hidden" name="tdate" value="<?php echo $form_value['tdate']; ?>">
        <input type="hidden" name="renter_id" value="<?php echo $form_value['renter_id']; ?>">
        <input type="hidden" name="doc_type" value="<?php echo $form_value['doc_type']; ?>">
        <input type="hidden" name="doc_value" value="<?php echo $form_value['doc_value']; ?>">
    </div>
</form>