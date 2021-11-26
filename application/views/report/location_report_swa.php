<script>

    var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';

    $(document).ready(function () {
        var area_width = $('#formLocationReport').width() - 20;
        $('#table-wrapper').width(area_width);

        $("#scroll_div").scroll(function () {
            $('#header_title').scrollLeft($(this).scrollLeft());
        });

    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportLocationToExcel_swa")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/export_location_swa_ToPDF")
        }
        $("#form_report").submit();
    }

</script>
<style>
    td.group{
        background:#E6F1F6;
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
	background: -webkit-gradient(linear, center top, center bottom, from(#013953), to(#002232)) !important;
        border-left: 1px solid #D0D0D0 !important;
        border-radius: 0 0 0 0 !important;
        color : white !important;
    }

    table.table_report tr:nth-child(odd) td{
        background-color: #E2E4FF;
        word-wrap: break-word;
    }

    table.table_report tr:nth-child(even) td{
        background-color: #FFFFFF;
        word-wrap: break-word;
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

<form  method="post" target="_blank" id="form_report">

    <input type="hidden" name="renter_id" value="<?php echo $search['renter_id']; ?>" />
    <input type="hidden" name="sort_by" value="<?php echo $search['sort_by']; ?>" />
    <input type="hidden" name="show_cond" value="<?php echo $search['show_cond']; ?>" />
    <input type="hidden" name="cond_deatil" value="<?php echo $search['cond_deatil']; ?>" />
    <input type="hidden" name="txt_keyword" value="<?php echo $search['txt_keyword']; ?>" />
    <input type="hidden" name="txt_from" value="<?php echo $search['txt_from']; ?>" />
    <input type="hidden" name="txt_to" value="<?php echo $search['txt_to']; ?>" />
    <input type="hidden" name="inv" value="<?php echo $search['inv']; ?>" />
    <input type="hidden" name="inv_book" value="<?php echo $search['inv_book']; ?>" />
    <input type="hidden" name="book" value="<?php echo $search['book']; ?>" />
    <div class='Tables_wrapper'>
        <div class=" fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
            <div id="defDataTable2_length" class="dataTables_length">
                <?php echo $display_items_per_page ?>
            </div>
            <div class="dataTables_filter" id="defDataTable2_filter">
                <label>
                    <!--Search: <input type="text" aria-controls="defDataTable2">-->
                </label>
            </div>
        </div>
        <div id="table-wrapper" style="width: <?php echo ($search['w'] - 30) ?>px;">
            <div id='header_title' style="width: <?php echo ($search['w'] - 30) ?>px; overflow-x: hidden;">
                <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" style="max-width: none">
                    <thead>
                        <tr>
                            <th style="width:50px;"><?php echo _lang('no'); ?></th>
                            <th style="width:100px;"><?php echo _lang('doc_refer_ext'); ?></th>
                            <th style="width:80px;"><?php echo _lang('receive_date'); ?></th>
                            <th style="width:100px;"><?php echo _lang('product_code'); ?></th>
                            <th style="width:200px;"><?php echo _lang('product_name'); ?></th>
                            <th style="width:110px;"><?php echo _lang('lot_serial'); ?></th>
                            <th style="width:80px;"><?php echo _lang('product_mfd'); ?></th>
                            <th style="width:80px;"><?php echo _lang('product_exp'); ?></th>
                            <th style="width:80px;"><?php echo _lang('product_status'); ?></th>
			                <th style="width:80px;"><?php echo _lang('Type'); ?></th>
                            <th style="width:90px;"><?php echo _lang('location'); ?></th>
                            <th style="width:80px;"><?php echo _lang('balance'); ?></th>
                            <th style="width:80px;"><?php echo _lang('RSV'); ?></th>
                            <th style="width:80px;"><?php echo _lang('pre_dispatch'); ?></th>
                            <th style="width:80px;"><?php echo _lang('remain'); ?></th>
                            <?php
                            if (!empty($build_pallet)):
                                if ($build_pallet):
                                    ?>
                                    <th style="width:150px;"><?php echo _lang('pallet_code'); ?></th>
                                    <?php
                                endif;
                            endif;
                            ?>
                            <th style="width:150px;"><?php echo _lang('remark'); ?></th>
                            <th style="width:80px;"><?php echo _lang('unit'); ?></th>
                            <th style="width:80px;"><?php echo _lang('uom_qty'); ?></th>
                            <th style="width:80px;"><?php echo _lang('uom_unit_prod'); ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="scroll_div" style="width: <?php echo ($search['w'] - 30) ?>px; height: 100%; overflow-y: hidden; overflow-x: scroll;">

                <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info">

                    <tbody>
                        <?php
                        $sum_balance = 0;
                        $sum_booked = 0;
                        $sum_dispatch = 0;
                        $sum_remain = 0;
                        $sum_uom_qty = 0;

                        if (empty($data)):
                            echo "<tr><td colspan=15 align=center><b>No Data Available.</b></td></tr>";
                        else:
                            foreach ($data as $key => $value) {
                                ?>

                                <tr>
                                    <td style="text-align:center;width:50px;"><?php echo ($low + ($key + 1)) ?></td>
                                    <td style="text-align:left;width:100px;"><?php echo $value->Doc_Refer_Ext ?></td>
                                    <td style="text-align:left;width:80px;"><?php echo $value->Receive_Date ?></td>
                                    <td style="text-align:left;width:100px;"><?php echo $value->Product_Code ?></td>
                                    <td style="text-align:left;width:200px;"><?php echo $value->Product_NameEN ?></td>
                                    <td style="text-align:center;width:110px;"><?php echo $value->Product_Lot ?>/<?php echo $value->Product_Serial ?></td>
                                    <td style="text-align:left;width:80px;"><?php echo $value->Product_Mfd ?></td>
                                    <td style="text-align:left;width:80px;"><?php echo $value->Product_Exp ?></td>
                                    <td style="text-align:left;width:80px;"><?php echo $value->Product_Status ?></td>
				                    <td style="text-align:left;width:80px;"><?php echo $value->Prod_Type ?></td>
                                    <td style="text-align:center;width:90px;"><?php echo $value->location ?></td>
                                    <td style="text-align:right;width:80px;" ><?php echo set_number_format($value->Balance_Qty) ?></td>
                                    <td style="text-align:right;width:80px;" ><?php echo set_number_format($value->booked) ?></td>
                                    <td style="text-align:right;width:80px;"><?php echo set_number_format($value->dispatch) ?></td>
                                    <td style="text-align:right;width:80px;"><?php echo set_number_format($value->remain) ?></td>
                                    <?php
                                    if (!empty($build_pallet)):
                                        if ($build_pallet):
                                            ?>
                                            <td style="text-align:left;width:150px;"><?php echo $value->Pallet_Code ?></td>
                                            <?php
                                        endif;
                                    endif;
                                    ?>
                                    <td style="text-align:left;width:150px;"><?php echo $value->Remark ?></td>
                                    <td style="text-align:left;width:80px;"><?php echo $value->unit ?></td>
                                    <td style="text-align:right;width:80px;"><?php echo set_number_format($value->uom_qty) ?></td>
                                    <td style="text-align:left;width:80px;"><?php echo $value->uom_unit_prod ?></td>
                                </tr>
                                <?php
                                $sum_balance+= $value->Balance_Qty;
                                $sum_booked+= $value->booked;
                                $sum_dispatch+= $value->dispatch;
                                $sum_remain+= $value->remain;
                                $sum_uom_qty+= $value->uom_qty;
                            }
                        endif;
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="11" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_balance); ?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_booked); ?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_dispatch); ?></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_remain); ?></th>
                            <th colspan="3"></th>
                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_uom_qty); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>

                </table>

            </div>

        </div>

    </div>
    <div id="pagination" class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
        <div class="dataTables_info" id="defDataTable2_info">Showing <?php echo $low + 1 ?> to <?php echo $show_to ?> of <?php echo $items_total; ?> entries</div>
        <div style="padding:3px;"class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers" id="defDataTable2_paginate">
            <?php echo $pagination ?>
        </div>
    </div>



</form>
