<SCRIPT>
    var oTable;

    $(document).ready(function () {

        oTable = $('#response_table').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "iDisplayLength": 250,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
        })

        $("#frm_company_master").submit(function (e) {
            e.preventDefault();
            if ($("#document_no").val().length != 13) {
                alert("document no invalid format!");
                return;
            } else {
                $.post("<?php echo site_url("return_document/get_document") ?>", {document: $("#document_no").val()}, function (r) {

                    $("#document").val($("#document_no").val());
                    $("#response").show();

                    oTable.fnClearTable();

                    $.each(r, function (row, data) {
                        $('#response_table').dataTable().fnAddData([
                            data.Product_Code,
                            data.Product_NameEN,
                            data.Product_Lot,
                            data.Product_Serial,
                            data.Product_Mfd,
                            data.Product_Exp,
                            data.Reserv_Qty,
                            data.Pallet_Id,
                            data.Pallet_Id_Out
                        ]);
                    });

                    oTable.fnDraw();

                }, "JSON");
            }
        });

        $("#document_no").keyup(function () {
            if ($(this).val().length == 0) {
                $(this).addClass("required")
            } else {
                $(this).removeClass("required")
            }
        });

        $("#confirm_return").click(function () {

            if (oTable.fnGetNodes().length == 0) {
                alert("No Data!");
            } else {
                if (confirm("All item will return to old location\n\nAre you want to return to open pre-dispatch?")) {
                    $.post("<?php echo site_url("return_document/process_return_document") ?>", {document: $("#document").val()}, function (r) {
                        if (r == "SUCCESS") {
                            alert("Complete!");
                            window.location.reload();
                        } else if (r == "ERROR_ROWS") {
                            alert("No Data.");
                        }
                    }, "JSON");
                }
            }
        });

        $("#document_no").trigger("keyup");

    });

    function clearData() {

    }

    function backToList() {
        window.location = "<?php echo site_url() ?>";
    }
</SCRIPT>
<form class="form-horizontal" id="frm_company_master" name="frm_company_master" method="post">
    <table width="96%" align="center">
        <tr>
            <td>
                <fieldset class="well" >
                    <table>
                        <tr>
                            <td>Document No : </td>
                            <td style="padding-left: 20px;"><input type="text" class="required string_special_characters-f" id="document_no" name="document_no" value="" placeholder="document no" maxlength="13"></td>
                            <td style="padding-left: 20px;"><button type="submit">Search</button></td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
</form>
<div id="response" style="padding: 20px; display: none;">
    <table id="response_table">
        <thead>
            <tr>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Lot</th>
                <th>Serial</th>
                <th>MFD</th>
                <th>EXP</th>
                <th>Quantity</th>
                <th>Pallet In</th>
                <th>Pallet Out</th>
            </tr>
        </thead>
        <tbody id="response_table_body"></tbody>
    </table>
    <div style="text-align: center; margin-top: 20px;">
        <input type="hidden" name="document" id="document" value="" />
        <button class="btn btn-large btn-success" id="confirm_return">Confirm</button>
    </div>
</div>