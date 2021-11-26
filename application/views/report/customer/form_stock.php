<script>
    $(document).ready(function () {
//        $('#product_detail').DataTable({
//            "bJQueryUI": true,
//            "bSortable": false,
//            "order": [[1, 'asc']],
//            "paging": true,
//            "pagingType": "full_numbers"
//        });

        $(".search_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
        });
        $('#clear').click(function () {
            $('#document_external').val('');
            $('#product_code').val('');
            $('#product_lot').val('');
            $(".search_date").val('');
            $('#report').css("display", "");
            $('#report').html('Please click search');
            $("#product_detail").css("display", "none");
            $("#tbody_detail").html("");
            $("#tfooter_detail").html("");
            $("#pdfshow").hide();
            $("#excelshow").hide();
        });

    }) // END Jquery Ready

    function show_data() {
        $('#report').html('<img src="<?php echo base_url("images/ajax-loader.gif"); ?>" />').css("display", "");
        $("#product_detail").css("display", "none");
        var newHTML = [];
        $.post("<?php echo site_url("report_jcs/show_stock_report") ?>", $("#frmPreDispatch").serialize(), function (data) {
            if (data != "") {
                $("#report").css("display", "none");
                $("#product_detail").css("display", "");
                $("#tbody_detail").html("");
                $("#tfooter_detail").html("");
                var newHTML = [];
                $.each(data, function (index, val) {
                    var count_day = 0;
                    var count_qty = 0;
                    var i = 0;
                    newHTML.push(
                            "<tr>" +
                            "<td colspan='11' style='text-align: left;background-color: #c9c9cb;font-weight: 700;'> SOE No. :" + val.group_head + "</td>" +
                            "</tr>");
                    $.each(val, function (key, value) {
                        i++;

                        newHTML.push(
                                "<tr>" +
                                "<td>" + i + "</td>" +
                                "<td>" + value.Document_No + "</td>" +
                                "<td>" + value.Real_Action_Date + "</td>" +
                                "<td>" + value.Cont_No + "</td>" +
                                "<td>" + value.Cont_Size + "</td>" +
                                "<td>" + value.Product_Code + "</td>" +
                                "<td>" + value.Product_NameEN + "</td>" +
                                "<td>" + value.Product_Lot + "</td>" +
                                "<td>" + value.Receive_Date + "</td>" +
                                "<td style='text-align:right'>" + value.Storage_Day + "</td>" +
                                "<td style='text-align:right'>" + set_number_format(value.DP_Qty) + "</td>" +
                                "</tr>");
                        count_day += value.Storage_Day === undefined || value.Storage_Day === null ? 0 : parseInt(value.Storage_Day);
                        count_qty += value.DP_Qty === undefined || value.DP_Qty === null ? 0 : parseInt(value.DP_Qty);
                    });
                    newHTML.pop();
                    newHTML.push(
                            "<tr>" +
                            "<td colspan='9' style='text-align: center;background-color: #79b8d6;'>Total</td>" +
                            "<td style='text-align: right;background-color: #79b8d6;'>" + count_day + "</td>" +
                            "<td style='text-align: right;background-color: #79b8d6;'>" + set_number_format(count_qty) + "</td>" +
                            "</tr>");
                });
                $("#pdfshow").show();
                $("#excelshow").show();

                $("#tbody_detail").html(newHTML.join(""));

            } else {
                $('#report').css("display", "");
                $('#report').html('No data avalible').css("color", "red");
                $("#product_detail").css("display", "none");
            }
        }, 'json');
    }

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#frmPreDispatch").attr('action', "<?php echo site_url("report_jcs/exportStockToExcel"); ?>");
            $("#frmPreDispatch").attr('target', "_blank");
        } else {
            $("#frmPreDispatch").attr('action', "<?php echo site_url("report_jcs/exportStockToPdf"); ?>");
            $("#frmPreDispatch").attr('target', "_blank");
        }
        $("#frmPreDispatch").submit();
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

<tr class="content" style='height:100%' valign="top">
    <td>
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
                        <td style="text-align: right;">SOE No. :</td>
                        <td style="width: 20%;"><input type="text" class="" id="document_external" name="document_external" 
                                                       style="margin: 5px 0 5px 10px" value="" placeholder="<?php echo _lang("document_external") ?>" /></td>
                        <td style="text-align: right;"><?php echo _lang("product_code") ?> :</td>
                        <td style="width: 20%;"><input type="text" class="" id="product_code" name="product_code" style="margin: 5px 0 5px 10px" value="" /></td>
                        <td></td>                        
                    </tr>
                    <tr>
                        <td style="text-align: right;"><?php echo _lang("lot") ?> :</td>
                        <td style="width: 20%;"><input type="text" class="" id="product_lot" name="product_lot" style="margin: 5px 0 5px 10px" value="" /></td>

                        <td style="text-align: right;" colspan="2">
                            <?php echo _lang("from_date") ?> : 
                            <input type="text" class="search_date" name="from_date" style="margin: 5px 0 5px 10px;width: 20%;" value="<?php echo date("d/m/Y") ?>" />

                            <font style="margin-left: 10px;"><?php echo _lang("to_date") ?> : </font>
                            <input type="text" class="search_date" name="to_date" style="margin: 5px 0 5px 10px;width: 20%" value="<?php echo date("d/m/Y") ?>" />
                        </td>  
                        <td style="padding-left: 3%;">
                            <!--<input type="submit" style="display: none;">-->
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" onclick='show_data();' style="margin-bottom: 10px" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" style="margin-bottom: 10px" />
                        </td>
                    </tr>     
                </table>		
            </fieldset>
        </form>    

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px;text-align: center;">Please click search</div>

            <table id="product_detail" style="display: none" class="display table_report dataTable" cellspacing="0" cellpadding="0" border="0" aria-describedby="defDataTable_info">
                <thead>
                    <tr>
                        <th style="width: 5%;"><?php echo _lang('no') ?></th>
                        <!--<th style="width: 10%;">SOE No.</th>-->
                        <th style="width: 10%;">Order ID</th>
                        <th>Load Date</th>
                        <th><?php echo _lang("container_no") ?></th>
                        <th><?php echo _lang("container_size") ?></th>
                        <th><?php echo _lang("product_code") ?></th>
                        <th style="width: 10%;"><?php echo _lang("product_name") ?></th>
                        <th><?php echo _lang("lot") ?></th>
                        <th>Date In</th>                      
                        <th>Storage Days</th>                    
                        <th><?php echo _lang("dispatch_qty") ?></th>                       
                    </tr>
                </thead>
                <tbody id="tbody_detail">

                </tbody>
                <tfoot id="tfooter_detail">

                </tfoot>
        </fieldset>
    </td>
</tr>
