<script>
    $(document).ready(function () {

        $('#defDataTable2 tbody tr td[title]').hover(function () {
            var chk_title = $(this).attr('title');
            if (chk_title.length > 1) {
                $(this).show_tooltip();
            }
        }, function () {
            $(this).hide_tooltip();
        });

        oTable = $('#defDataTable2').dataTable({
            "aaSortingFixed": [[0, 'asc']],
            "sPaginationType": "full_numbers",
            "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "fnDrawCallback": function (oSettings) {
                if (oSettings.aiDisplay.length == 0)
                {
                    return;
                }

                var nTrs = $('#defDataTable2 tbody tr');
                var iColspan = nTrs[0].getElementsByTagName('td').length;
                var iHash = "";
                var sLastGroup = "";
                var total_qty = 0;
                var total_cbm = 0;
                var sum_cbm = 0;
                var loop = nTrs.length;
                for (var i = 0; i < loop; i++)
                {
                    var iDisplayIndex = oSettings._iDisplayStart + i;
                    var date_load = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[1];
                    var Product_Name = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[2];
                    var inv = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[3];
                    var lot = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[4];
                    var myclass = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[5];
                    var qty = parseFloat(oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[8]);
                    var cbm = parseFloat(oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[10]);
                    var sGroup = oSettings.aoData[ oSettings.aiDisplay[iDisplayIndex] ]._aData[12];

                    if (iHash != "" && iHash != date_load)
                    {
                        var nGroup = document.createElement('tr');

                        var colTotalText = document.createElement('td');
                        colTotalText.colSpan = 8;
                        colTotalText.className = "group";
                        colTotalText.innerHTML = "End of " + iHash;
                        nGroup.appendChild(colTotalText);

                        var colTotal = document.createElement('td');
                        colTotal.className = "group_right";
                        colTotal.innerHTML = set_number_format(total_qty);
                        nGroup.appendChild(colTotal);

                        var colTemp1 = document.createElement('td');
                        colTemp1.colSpan = 1;
                        colTemp1.className = "group";
                        colTemp1.innerHTML = "";
                        nGroup.appendChild(colTemp1);

                        var colCBM = document.createElement('td');
                        colCBM.className = "group_right";
                        colCBM.innerHTML = set_number_format(total_cbm);
                        nGroup.appendChild(colCBM);

                        var colTotalCBM = document.createElement('td');
                        colTotalCBM.className = "group_right";
                        colTotalCBM.innerHTML = set_number_format(sum_cbm);
                        nGroup.appendChild(colTotalCBM);

                        var colUnit = document.createElement('td');
                        colUnit.className = "group";
//                        colUnit.innerHTML = sLastGroup;
                        colUnit.innerHTML = "";
                        nGroup.appendChild(colUnit);

                        nTrs[i].parentNode.insertBefore(nGroup, nTrs[i]);

                        total_qty = qty;
                        total_cbm = cbm;
                        sum_cbm = (parseFloat(qty) * parseFloat(cbm));
                        sLastGroup = sGroup;
                    } else {
                        total_qty += qty;
                        total_cbm += cbm;
                        sum_cbm += (parseFloat(qty) * parseFloat(cbm));
                        sLastGroup = sGroup;
                    }
                    iHash = date_load;
                }

                if (total_qty > 0) {

                    var nGroup = document.createElement('tr');

                    var colTotalText = document.createElement('td');
                    colTotalText.colSpan = 8;
                    colTotalText.className = "group";
                    colTotalText.innerHTML = "End of " + iHash;
                    nGroup.appendChild(colTotalText);

                    var colTotal = document.createElement('td');
                    colTotal.className = "group_right";
                    colTotal.innerHTML = set_number_format(total_qty);
                    nGroup.appendChild(colTotal);

                    var colTemp1 = document.createElement('td');
                    colTemp1.colSpan = 1;
                    colTemp1.className = "group";
                    colTemp1.innerHTML = "";
                    nGroup.appendChild(colTemp1);

                    var colCBM = document.createElement('td');
                    colCBM.className = "group_right";
                    colCBM.innerHTML = set_number_format(total_cbm);
                    nGroup.appendChild(colCBM);

                    var colTotalCBM = document.createElement('td');
                    colTotalCBM.className = "group_right";
                    colTotalCBM.innerHTML = set_number_format(sum_cbm);
                    nGroup.appendChild(colTotalCBM);

                    var colUnit = document.createElement('td');
                    colUnit.className = "group";
                    colUnit.innerHTML = '';
                    nGroup.appendChild(colUnit);

                    nTrs[loop - 1].parentNode.appendChild(nGroup, nTrs[loop - 1]);

                }
            },
        });
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url("/report_jcs/exportDispatchToExcel"); ?>")
        } else {
            $("#form_report").attr('action', "<?php echo site_url("/report_jcs/exportDispatchToPDF"); ?>")
        }
        $("#form_report").submit();
    }
</script>
<style>
    td.group{
        background: #E6F1F6;
        text-align: center !important;
    }
    td.group_right{
        background: #E6F1F6;
        text-align: right !important;
    }
    td.group_left{
        background: #E6F1F6;
        text-align: left !important;
    }
</style>
<form  method="post" target="_blank" id="form_report">
    <input type="hidden" name="fdate" value="<?php echo $search['fdate']; ?>" />
    <input type="hidden" name="tdate" value="<?php echo $search['tdate']; ?>" />
    <input type="hidden" name="type_dp_date_val" value="<?php echo $search['type_dp_date_val']; ?>" />
    <table id="defDataTable2" class="display dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
        <thead>
            <tr>
                <th>Date In</th>
                <th>Date Load</th>
                <th>Description</th>
                <th>Doc Ext.</th>
                <th>Batch No.</th>
                <th>Lot No.</th>
                <th>Type.</th>
                <th>Invoice No.</th>
                <th>Sum of Q'ty</th>
                <th>Dimension</th>
                <th>CBM</th>
                <th>Sum of Total/CBM</th>
                <th>Type UOM</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sum_all = 0;
            $cbm = 0;
            $total_cbm = 0;
            foreach ($data as $key => $value) {
                $dimension = (!empty($value->w) && !empty($value->h) && !empty($value->l) ? $value->w . " x " . $value->l . " x " . $value->h : "");
                ?>
                <tr>
                    <td><?php echo $value->Receive_Date; ?></td>
                    <td><?php echo $value->Dispatch_Date; ?></td>
                    <td style="text-align:left;"><?php echo $value->Product_Name; ?></td>
                    <td><?php echo $value->Doc_Refer_Ext; ?></td>
                    <td><?php echo $value->Product_Lot; ?></td>
                    <td><?php echo $value->Product_Serial; ?></td>
                    <td><?php echo $value->TypeLicense; ?></td>
                    <td style="text-align:left;"><?php echo $value->Invoice_No; ?></td>
                    <td style="text-align:right;"><?php echo set_number_format($value->Dispatch_Qty); ?></td>
                    <td style="text-align:center;"><?php echo $dimension; ?></td>
                    <td style="text-align:right;"><?php echo $value->cbm; ?></td>
                    <td style="text-align:right;"><?php echo set_number_format($value->Dispatch_Qty * $value->cbm); ?></td>
                    <td><?php echo $value->Unit_Value; ?></td>
                </tr>
                <?php
                $sum_all += $value->Dispatch_Qty;
                $cbm += $value->cbm;
                $total_cbm += $value->Dispatch_Qty * $value->cbm;
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class ='ui-state-default indent'  style='text-align: center;'><b>Grand Total</b></th>
                <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all); ?></th>
                <th class ='ui-state-default indent'  style='text-align: right;'></th>
                <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($cbm); ?></th>
                <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($total_cbm); ?></th>
                <th colspan="1" ></th>
            </tr>
        </tfoot>
    </table>
</form>
<div class="modal fade" id="modal_note" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body"></div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->