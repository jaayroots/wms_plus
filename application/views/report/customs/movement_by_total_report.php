<script>

    $(document).ready(function() {
        oTable = $('#defDataTable2').dataTable({"fnDrawCallback": function(oSettings) {
                if (oSettings.aiDisplay.length == 0)
                {
                    return;
                }

                var nTrs = $('#defDataTable2 tbody tr');
                var iColspan = nTrs[0].getElementsByTagName('td').length;
                var sLastGroup = "";
                for (var i = 0; i < nTrs.length; i++)
                {
                    var iDisplayIndex = oSettings._iDisplayStart + i;
                    var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[0];
                    var lastRows = $(nTrs[i].getElementsByTagName('td')[0]).hasClass('sum_group');
                    //console.log($(nTrs[i].getElementsByTagName('td')[0]).hasClass('sum_group'));
                    if (lastRows) {
                        /* $(nTrs[i].getElementsByTagName('td')[2]).prop('colspan','3');
                        $(nTrs[i].getElementsByTagName('td')[0]).remove();
                        $(nTrs[i].getElementsByTagName('td')[1]).remove();
                        $(nTrs[i].getElementsByTagName('td')[2]).remove(); */

                    }
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
            },
            "aoColumnDefs": [
                {"bVisible": false, "aTargets": [0]}
            ],
            "aaSortingFixed": [[0, 'asc']],
            "aaSorting": [[1, 'asc']],
            "sPaginationType": "full_numbers",
            "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "oSearch": {"sSearch": ""},
        });
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            //$("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/customs/export_")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report_customs/export_movement_by_total")
        }
        $("#form_report").submit();
    }

     function show_inven_report(product_code,product_name){
        $('#product_name').val(product_name);
        $('#product_code').val(product_code);
        $("#form_link_inven").submit();

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

<form method="post" target="_blank" id="form_link_inven" action ='<?php echo site_url(); ?>/report/inventory'>
    <input type="hidden" name="product_name" id='product_name'>
    <input type="hidden" name="show_auto" value="Y">
    <input type="hidden" name="product_id" id='product_id'>
    <input type="hidden" name="product_code" id='product_code'>
    <input type="hidden" name="tdate" value="<?php echo $form_value['tdate'];?>">
    <input type="hidden" name="renter_id" value="<?php echo $form_value['renter_id'];?>">
</form>

<form method="post" target="_blank" id="form_report" >
    <table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
        <thead>
            <tr>
                <th>Temp</th>
                <th><?php echo _lang('no') ?></th>
                <th><?php echo _lang('date') ?></th>
                <th><?php echo _lang('number_received') ?></th>
                <th><?php echo _lang('reference_received') ?></th>
                <th><?php echo _lang('number_dispatch') ?></th>
                <th><?php echo _lang('reference_dispatch') ?></th>
                <th><?php echo _lang('receive_qty') ?></th>
                <th><?php echo _lang('dispatch_qty') ?></th>
                <th><?php echo _lang('serial') ?>/<?php echo _lang('lot') ?></th>
                <th><?php echo _lang('product_mfd') ?></th>
                <th><?php echo _lang('product_exp') ?></th>
                <th><?php echo _lang('from_to') ?></th>
                <th><?php echo _lang('location') ?></th>
                <th><?php echo _lang('balance') ?></th>
            </tr>
        </thead>
        <tbody>

           <?php
            if(!empty($datas['no_onhand'])){ ?>
                <font size="3" color="red"><?php echo $datas['no_onhand'];?></font><br>
           <? }else if (!empty($datas)) {
                $i = 1;
                $sumReceiveQty = 0;     //add $sumReservQty variable for calculate total Receive qty : by kik : 31-10-2013
                $sumDispatchQty = 0;    //add $sumReceiveQty variable for calculate total Dispatch qty : by kik : 31-10-2013
                $sumBalanceQty = 0;    //add $sumReceiveQty variable for calculate total Balance qty : by kik : 31-10-2013
                $sumStartBalanceQty = 0;    //add by kik : 2013-11-25
                    foreach ($datas as $key => $values) {

                        $sumReceiveQty_inRow = 0;    // add by kik : 25-11-2013
                        $sumDispatchQty_inRow = 0;   // add by kik : 25-11-2013

                        foreach ($values as $value) {

                            $product_name= $value['0']['Product_NameEN'];
                            ?>
                            <tr>

                                <td data-mydata="NO"><?php echo $key ?> / <?php echo $value['0']['Product_NameEN']; ?></td>
                                <td ><?php echo $i; ?></td>
                                <td><?php echo $value['0']['receive_date']; ?></td>
                                <td>&nbsp;&nbsp;<?php echo $value['0']['receive_doc_no']; ?></td>
                                <td><?php echo $value['0']['receive_refer_ext']; ?></td>
                                <td><?php echo $value['0']['pay_doc_no']; ?></td>
                                <td><?php echo $value['0']['pay_refer_ext']; ?></td>
                                <td style='text-align: right'><?php echo (set_number_format($value['0']['r_qty']) == 0)?'':set_number_format($value['0']['r_qty']);?></td>
                <td style='text-align: right'><?php echo (set_number_format($value['0']['p_qty']) == 0)?'':set_number_format($value['0']['p_qty']);?></td>
                                <td><?php echo $value['0']['Product_SerLot']; ?></td>
                                <td><?php echo $value['0']['Product_Mfd']; ?></td>
                                <td><?php echo $value['0']['Product_Exp']; ?></td>
                                <td><?php echo $value['0']['branch']; ?></td>
                                <td><?php echo $value['0']['Location_Code']; ?></td>
                                <td style='text-align: right'><?php echo set_number_format($value['0']['Balance_Qty']); ?></td>
                            </tr>


                            <?php
                            $i++;
                            $sumReceiveQty+=@$value['0']['r_qty'];     // Add $sumReceiveQty for calculate total qty : by kik : 31-10-2013
                            $sumDispatchQty+=@$value['0']['p_qty'];     // Add $sumDispatchQty for calculate total qty : by kik : 31-10-2013


                            $sumReceiveQty_inRow+=@$value['0']['r_qty']; // add by kik : 25-11-2013
                            $sumDispatchQty_inRow+=@$value['0']['p_qty']; // add by kik : 25-11-2013

                        }
                        ?>
                            <tr>
                                <td class ="sum_group" data-mydata="OK" ><?php echo $key ?> / <?php echo $value['0']['Product_NameEN']; ?></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group"><b>Incoming Balance </b></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group" style="text-align: left;"><b>: <?php echo set_number_format(@$value['0']['start_balance']);?></b></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group" style='text-align: right'><b><?php echo set_number_format(@$sumReceiveQty_inRow);?></b></td>
                                <td class ="sum_group" style='text-align: right'><b><?php echo set_number_format(@$sumDispatchQty_inRow);?></b></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group"></td>
                                <td class ="sum_group" onclick='show_inven_report("<?php echo $key; ?>","<?php echo $product_name; ?>")' style='text-align: right;cursor: pointer;'><b><u><?php echo set_number_format(@$value['0']['Balance_Qty']);?></u></b></td>
                            </tr>

                            <?
                            $sumStartBalanceQty+=@$value['0']['start_balance'];
                            $sumBalanceQty+=@$value['0']['Balance_Qty'];     // Add $sumBalanceQty for calculate total qty : by kik : 31-10-2013
                    }
            }
            ?>

        </tbody>

        <!-- show total qty : by kik : 31-10-2013-->
                    <tfoot>

                                <?php
                                $colspan = 5;
                                ?>
                             <tr>
                                    <th colspan="7" class ='ui-state-default indent'  style='text-align: center;'><b>Total Incoming Balance : <?php echo set_number_format(@$sumStartBalanceQty)?></b></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sumReceiveQty);?></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sumDispatchQty);?></th>
                                    <th colspan="<?php echo $colspan; ?>" class ='ui-state-default indent' ></th>
                                    <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format(@$sumBalanceQty);?></th>

                             </tr>
                    </tfoot>
        <!-- end show total qty : by kik : 31-10-2013-->


    </table>
    <div align="center" style="margin:10px auto">
        <input type="hidden" name="fdate" value="<?php echo $form_value['fdate']; ?>">
        <input type="hidden" name="tdate" value="<?php echo $form_value['tdate']; ?>">
        <input type="hidden" name="renter_id" value="<?php echo $form_value['renter_id']; ?>">
        <input type="hidden" name="doc_type" value="<?php echo $form_value['doc_type']; ?>">
        <input type="hidden" name="doc_value" value="<?php echo $form_value['doc_value']; ?>">
    </div>
</form>