<script src="<?php echo base_url("js/jquery.md5.js"); ?>"></script>
<script>
    $(document).ready(function () {
        // Date Picker
        $("#search_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
        });
        $('#clear').click(function () {
            $('#status_id').val('');
            $("#search_date").valid('<?php echo date("d/m/Y"); ?>');
            $('#report').css("display", "");
            $('#report').html('Please click search');
            $("#product_detail").css("display", "none");
            $("#tbody_detail").html("");
            $("#tfooter_detail").html("");
            $("#pdfshow").hide();
            $("#excelshow").hide();
        });
        $('#frmPreDispatch').submit(function () {
            show_data();
            return false;
        });
    });
    function exportFile(file_type) {
        $('#renter_id_swa').val($('#renter_id').val());
        $('#search_date_value').val($("#search_date").val());
        $('#status_id_swa').val($('#status_id').val());
        if (file_type == 'EXCEL') {
            $("#form_report_swa").attr('action', "<?php echo site_url("report_jcs/exportInventoryToExcel"); ?>");
        } else {
            $("#form_report_swa").attr('action', "<?php echo site_url("report_jcs/exportInventoryPdf"); ?>");
        }
        $("#form_report_swa").submit();
    }

    function show_data() {
        if ($("#search_date").val() != "") {
            $("#tbody_detail").html("");
            $("#tfooter_detail").html("");
            $("#report").css("display", "");
            $("#product_detail").css("display", "none");
            $('#report').html('<img src="<?php echo base_url("images/ajax-loader.gif"); ?>" />');
            $.post("<?php echo site_url("report_jcs/showInventoryReport"); ?>",
                    {
                        "search_date": $("#search_date").val(),
                        "w": $('#frmPreDispatch').width()
                    }, function (data) {
                var sum_not_reject = 0;
                var sum_total_cbm = 0;
                var sum_total_cbm_all = 0;
                if (data.data.length != 0) {
                    $("#report").css("display", "none");
                    $("#product_detail").css("display", "");
                    var i = 1;
                    var hash = "";
                    var total_per_group = 0;
                    var cbm_all_per_group = 0;
                    var cbm_per_group = 0;
                    var Unit = "";
                    $.each(data.data, function (key, value) {
                        if (hash != "" && hash != $.md5(value.Product_NameEN + value.Unit_Value)) {
                            $("#tbody_detail").append(
                                    "<tr>" +
                                    "<td  style='background-color: #E6F1F6;' colspan='9'>Total</td>" +
                                    "<td style='background-color: #E6F1F6; text-align:right;'>" + set_number_format(total_per_group) + "</td>" +
                                    "<td style='background-color: #E6F1F6; text-align:right;'></td>" +
                                    "<td style='background-color: #E6F1F6; text-align:right;'>" + set_number_format(cbm_per_group) + "</td>" +
                                    "<td style='background-color: #E6F1F6; text-align:right;'>" + set_number_format(cbm_all_per_group) + "</td>" +
                                    "<td style='background-color: #E6F1F6; text-align:center;'></td>" +
                                    "</tr>"
                                    );
                            total_per_group = parseFloat(value.totalbal);
                            cbm_all_per_group = parseFloat(value.totalCBM_All);
                            cbm_per_group = parseFloat(value.CBM);
                        } else {
                            total_per_group += parseFloat(value.totalbal);
                            cbm_all_per_group += parseFloat(value.totalCBM_All);
                            cbm_per_group += parseFloat(value.CBM);
                            Unit = value.Unit_Value;
                        }
                        hash = $.md5(value.Product_NameEN + value.Unit_Value);
                        $("#tbody_detail").append(
                                "<tr>" +
                                "<td>" + i + "</td>" +
                                "<td>" + value.Receive_Date + "</td>" +
                                "<td style='text-align:left;word-wrap: break-word;'>" + value.Product_Code + "</td>" +
                                "<td style='text-align:left;word-wrap: break-word;'>" + value.Product_NameEN + "</td>" +
                                "<td style='word-wrap: break-word;'>" + value.Doc_Refer_Ext + "</td>" +
                                "<td style='word-wrap: break-word;'>" + value.Product_Lot + "</td>" +
                                "<td style='word-wrap: break-word;'>" + value.Product_Serial + "</td>" +
                                "<td>" + value.DIW_Class + "</td>" +
                                "<td style='text-align:left;word-wrap: break-word;'>" + value.Invoice_No + "</td>" +
                                "<td style='text-align:right;'>" + value.totalbal + "</td>" +
                                "<td>" + value.Dimension + "</td>" +
                                "<td style='text-align:right;'>" + value.CBM + "</td>" +
                                "<td style='text-align:right;'>" + value.totalCBM_All + "</td>" +
                                "<td style='text-align:center;'>" + value.Unit_Value + "</td>" +
                                "</tr>"
                                );
                        i++;
                        sum_not_reject += parseFloat(value.totalbal);
                        sum_total_cbm += parseFloat(value.CBM);
                        sum_total_cbm_all += parseFloat(value.totalCBM_All);
                        Unit = value.Unit_Value;
                    });
                    if (total_per_group > 0) {
                        $("#tbody_detail").append(
                                "<tr>" +
                                "<td  style='background-color: #E6F1F6;' colspan='9'>Total</td>" +
                                "<td style='background-color: #E6F1F6; text-align:right;'>" + set_number_format(total_per_group) + "</td>" +
                                "<td style='background-color: #E6F1F6; text-align:right;'></td>" +
                                "<td style='background-color: #E6F1F6; text-align:right;'>" + set_number_format(cbm_per_group) + "</td>" +
                                "<td style='background-color: #E6F1F6; text-align:right;'>" + set_number_format(cbm_all_per_group) + "</td>" +
                                "<td style='background-color: #E6F1F6; text-align:center;'></td>" +
                                "</tr>"
                                );
                    }
                    $("#tfooter_detail").append(
                            "<tr>" +
                            "<td colspan='9' style='text-align:left;'>Grand Total</td>" +
                            "<td style='text-align:right;'>" + data.total_qty + "</td>" +
                            "<td style='text-align:right;'></td>" +
                            "<td style='text-align:right;'>" + data.total_cbm + "</td>" +
                            "<td style='text-align:right;'>" + data.total_cbm_all + "</td>" +
                            "<td></td>" +
                            "</tr>"
                            );
                    $("#pdfshow").show();
                    $("#excelshow").show();
                } else {
                    $("#report").css("display", "none");
                    $("#product_detail").css("display", "");

                    $("#tbody_detail").append(
                            "<tr>" +
                            "<td style='text-align:center;' colspan='13'>No Data Available.</td>" +
                            "</tr>"
                            );
                    $("#tfooter_detail").append(
                            "<tr>" +
                            "<td colspan='9' style='text-align:left;'>Grand Total</td>" +
                            "<td style='text-align:right;'>0.000</td>" +
                            "<td style='text-align:right;'></td>" +
                            "<td style='text-align:right;'></td>" +
                            "<td style='text-align:right;'>0.000</td>" +
                            "<td></td>" +
                            "</tr>"
                            );
                }
            }, "json");
        } else {
            alert("Please Check Your Require Information (Red label).");
            $("#search_date").css("border", "1px solid red");
            return false;
        }

    }

</script>
<style>
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
    table.table_report tfoot tr td{
        text-align: center;
        background-color: #002232 !important;
        color: white !important;
        font-weight: 700;
        font-size: 13px;
    }
</style>

<TR class="content" style='height:100%' valign="top">
    <TD>
        <form  method="post" action="" target="_blank" id="form_report_swa">
            <input type="hidden" name="renter_id" id="renter_id_swa">
            <input type="hidden" name="search_date" id="search_date_value">
            <input type="hidden" name="status_id" id="status_id_swa">
        </form>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmPreDispatch" name="frmPreDispatch" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>  
                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td style="text-align: right;">Date :</td>
                        <td style="width: 20%;"><input type="text" class="" id="search_date" style="margin: 10px 0 10px 10px" value="<?php echo date("d/m/Y"); ?>" /></td>
                        <td>
                            <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" onclick='show_data();' style="margin-bottom: 10px;margin-top: 10px" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" style="margin-bottom: 10px;margin-top: 10px" />
                        </td>
                    </tr>     
                </table>		
            </fieldset>

            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>    

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px;text-align: center;">Please click search</div>

            <table id="product_detail" style="display: none" class="display table_report dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                <thead>
                    <tr>
                        <th style="width: 5%;"><?php echo _lang('no') ?></th>
                        <th>Date In</th>
                        <th style="width: 10%;">Material No.</th>
                        <th style="width: 10%;">Description</th>
                        <th>Document External</th>
                        <th>Batch No.</th>
                        <th>Lot No.</th>
                        <th style="width: 3.5%;">Type</th>
                        <th><?php echo _lang('invoice_no') ?></th>                       
                        <th>Sum of Qty</th>
                        <th style="width: 9%;">Dimension</th>
                        <th>CBM</th>
                        <th>Sum of Total / CBM</th>
                        <th>Type UOM</th>
                    </tr>
                </thead>
                <tbody id="tbody_detail">

                </tbody>
                <tfoot id="tfooter_detail">

                </tfoot>
        </fieldset>
    </TD>
</TR>