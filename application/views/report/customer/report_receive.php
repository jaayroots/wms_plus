<script>
    var column = $.parseJSON('<?php echo $show_hide; ?>');
    $(document).ready(function () {
        var area_width = $('#frmReceive').width() - 20;
        $('#table-wrapper').width(area_width);

        $("#scroll_div").scroll(function () {
            $('#header_title').scrollLeft($(this).scrollLeft());
        });

        $.each(column, function (idx, val) {
            if (!val) {
                $("." + idx).hide();
            }
        });
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url("/report_jcs/exportReceiveToExcel"); ?>")
        } else {
            $("#form_report").attr('action', "<?php echo site_url("/report_jcs/exportReceiveToPDF"); ?>")
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
        background: -moz-linear-gradient(center top, #013953,#002232) repeat scroll 0 0 transparent;
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
    .Tables_wrapper .ui-toolbar {
        padding: 5px 5px 0;
        overflow: hidden;
    }

</style>
<?php
$result = array();

foreach ($data as $key => $value) :

    $hash = md5($value->Receive_Date . $value->Product_Name . $value->Doc_Refer_Ext . $value->Product_Lot. $value->Product_Serial . $value->TypeLicense . $value->Invoice_No . $value->Unit_Value);

    if (array_key_exists($hash, $result)) {
        $result[$hash]['Receive_Date'] = $value->Receive_Date;
        $result[$hash]['Product_Name'] = $value->Product_Name;
        $result[$hash]['Doc_Refer_Ext'] = $value->Doc_Refer_Ext;
        $result[$hash]['Product_Lot'] = $value->Product_Lot;
        $result[$hash]['Product_Serial'] = $value->Product_Serial;
        $result[$hash]['TypeLicense'] = $value->TypeLicense;
        $result[$hash]['Invoice_No'] = $value->Invoice_No;
        $result[$hash]['w'] = $value->w;
        $result[$hash]['l'] = $value->l;
        $result[$hash]['h'] = $value->h;
        $result[$hash]['Receive_Qty'] += $value->Receive_Qty;
        $result[$hash]['cbm'] = $value->cbm;
        $result[$hash]['Unit_Value'] = $value->Unit_Value;
    } else {
        $result[$hash]['Receive_Date'] = $value->Receive_Date;
        $result[$hash]['Product_Name'] = $value->Product_Name;
        $result[$hash]['Doc_Refer_Ext'] = $value->Doc_Refer_Ext;
        $result[$hash]['Product_Lot'] = $value->Product_Lot;
        $result[$hash]['Product_Serial'] = $value->Product_Serial;
        $result[$hash]['TypeLicense'] = $value->TypeLicense;
        $result[$hash]['Invoice_No'] = $value->Invoice_No;
        $result[$hash]['w'] = $value->w;
        $result[$hash]['l'] = $value->l;
        $result[$hash]['h'] = $value->h;
        $result[$hash]['Receive_Qty'] = $value->Receive_Qty;
        $result[$hash]['cbm'] = $value->cbm;
        $result[$hash]['Unit_Value'] = $value->Unit_Value;
    }
endforeach;
?>
<form  method="post" target="_blank" id="form_report">
    <input type="hidden" name="fdate" value="<?php echo $search['fdate']; ?>" />
    <input type="hidden" name="tdate" value="<?php echo $search['tdate']; ?>" />
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
            <table class ='well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" style="max-width: none">
                <thead>
                    <tr>
                        <th class="receive_date" style="width:80px;">Date In.</th>
                        <th class="product_name" style="width:200px;">Description</th>
                        <th class="document_ext" style="width:100px;">Doc Ext.</th>
                        <th class="lot" style="width:100px;">Batch No.</th>
                        <th class="serial" style="width:100px;">Lot No.</th>
                        <th class="imoclass" style="width:100px;">Type.</th>
                        <th class="invoice" style="width:80px;">Invoice No.</th>
                        <th class="sum_qty" style="width:80px;">Sum of Qty</th>
                        <th class="dimension" style="width:100px;">Dimension</th>
                        <th class="cbm" style="width:80px;">CBM</th>
                        <th class="sum_qty_cbm" style="width:80px;">Sum of Total/CBM</th>
                        <th class="unit" style="width:50px;">Type UOM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sum_not_reject = 0;
                    $sum_not_reject_price = 0;
                    $sum_not_reject_all_price = 0;

                    $sum_reject = 0;
                    $sum_reject_price = 0;
                    $sum_reject_all_price = 0;

                    $sum_all = 0;
                    $sum_price = 0;
                    $sum_all_price = 0;

                    $sum_total_cbm = 0;
                    // Add New
                    $sum_cbm = 0;

                    $have_reject = FALSE;
                    if (empty($data)):
                        echo '<tr><td colspan="12" align=center><b>No Data Available.</b></td></tr>';
                    else:
                        $hash = "";
                        $total_per_group = 0;
                        $total_cbm_per_group = 0;
                        $unit_per_group = "";
                        $date = "";
                        foreach ($result as $key => $value) :

                            if ($date != "" && $date != $value['Receive_Date']) {
                                echo '<tr>';
                                echo '<td colspan="7" align="center" style="background-color: #E6F1F6; height: 30px;"><b>End of ' . $date . '</b></td>';
                                echo '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_per_group) . '</td>';
                                echo '<td align="right" style="background-color: #E6F1F6; height: 30px;"></td>';
                                echo '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($sum_cbm) . '</td>';
                                echo '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_cbm_per_group) . '</td>';
                                echo '<td style="background-color: #E6F1F6; height: 30px;"></td>';
                                echo '</tr>';
                                $total_per_group = $value['Receive_Qty'];
                                $total_cbm_per_group = ($value['Receive_Qty'] * $value['cbm']);
                            } else {
                                $total_per_group += $value['Receive_Qty'];
                                $total_cbm_per_group += ($value['Receive_Qty'] * $value['cbm']);
                            }

                            $dimension = (!empty($value['w']) && !empty($value['h']) && !empty($value['l']) ? $value['w'] . " x " . $value['l'] . " x " . $value['h'] : "");
                            ?>
                            <tr>
                                <td class="receive_date" style="text-align:left;width:80px;"><?php echo $value['Receive_Date']; ?></td>
                                <td class="product_name" style="text-align:left;width:200px;"><?php echo $value['Product_Name'] ?></td>
                                <td class="document_ext" style="text-align:left;width:100px;"><?php echo $value['Doc_Refer_Ext'] ?></td>
                                <td class="lot" style="text-align:center;width:100px;"><?php echo $value['Product_Lot'] ?></td>
                                <td class="serial" style="text-align:center;width:100px;"><?php echo $value['Product_Serial'] ?></td>
                                <td class="imoclass" style="text-align:center;width:100px;"><?php echo $value['TypeLicense'] ?></td>
                                <td class="invoice" style="text-align:left;width:80px;"><?php echo @$value['Invoice_No'] ?></td>
                                <td class="sum_qty" style="text-align:right;width:80px;" ><?php echo set_number_format($value['Receive_Qty']); ?></td>
                                <td class="dimension" style="text-align:center;width: 100px;" ><?php echo $dimension ?></td>
                                <td class="cbm" style="text-align:right;width:80px;" ><?php echo set_number_format($value['cbm']); ?></td>
                                <td class="sum_qty_cbm" style="text-align:right;width:80px;" ><?php echo set_number_format($value['Receive_Qty'] * $value['cbm']); ?></td>
                                <td class="unit" style="text-align:center;width:50px;"><?php echo $value['Unit_Value'] ?></td>
                            </tr>
                            <?php
                            $date = $value['Receive_Date'];

                            $sum_not_reject += $value['Receive_Qty'];
                            $sum_all+= $value['Receive_Qty'];
                            $sum_total_cbm += ($value['Receive_Qty'] * $value['cbm']);
                            $sum_cbm += $value['cbm'];

                        endforeach;

                        if ($total_per_group != 0) {
                            echo '<tr>';
                            echo '<td colspan="7" align="center" style="background-color: #E6F1F6; height: 30px;"><b>End of ' . $date . '</b></td>';
                            echo '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_per_group) . '</td>';
                            echo '<td align="right" style="background-color: #E6F1F6; height: 30px;"></td>';
                            echo '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($sum_cbm) . '</td>';
                            echo '<td align="right" style="background-color: #E6F1F6; height: 30px;">' . set_number_format($total_cbm_per_group) . '</td>';
                            echo '<td style="background-color: #E6F1F6; height: 30px;"></td>';
                            echo '</tr>';
                        }
                    endif;
                    ?>
                </tbody>

                <tfoot>
                    <tr >
                        <th style='text-align: left;' colspan="7"><b>Grand Total</b> <?php if ($have_reject): ?> <font style="font-size:10px;">(Exclude Reject)</font><?php endif; ?></th>
                        <th style='text-align: right;'><?php echo set_number_format($sum_not_reject); ?></th>
                        <th style='text-align: right;'></th>
                        <th style='text-align: right;'><?php echo set_number_format($sum_cbm); ?></th>
                        <th style='text-align: right;'><?php echo set_number_format($sum_total_cbm); ?></th>
                        <th class="unit"></th>
                    </tr>
                </tfoot>
            </table>
        </div>  
    </div>
    <div id="pagination" class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
        <div class="dataTables_info" id="defDataTable2_info">Showing <?php echo $low + 1 ?> to <?php echo $show_to ?> of <?php echo $items_total; ?> entries</div>
        <div style="padding:3px;"class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers" id="defDataTable2_paginate">
            <?php echo $pagination ?>
        </div>
    </div>
</form>