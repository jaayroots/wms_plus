<script lang="javascript">
    $(document).ready(function () {
        $(".input_rcv_dip").hide();
        $(".input_pa_pk").hide();
        $(".input_pk").hide();
        $("#btn_print").hide();
        $(".input_barcode_by_doc").hide();
        $(".input_barcode_by_item").hide();

        $("#doc_type").change(function () {
            $(":text").each(function () {
                $(this).val("");
            });

            if ($(this).val() === "rc" || $(this).val() === "dp") {
                $(".input_rcv_dip").show();
                $(".input_pa_pk").hide();
//                $(".input_pa").hide();
                $(".input_pk").hide();
                $("#btn_print").show();
                $(this).removeClass('required');

                $('input:radio[name="op_re_doc"][value="doc_no"]').prop('checked', true);
                $('input:radio[name="order_by"][value="keyin"]').prop('checked', true);

                $("#re_doc_refer_ext").attr("readonly", true);
                $("#re_doc_refer_ext").removeClass('required');

                $("#re_document_no").attr("readonly", false);
                $("#re_document_no").addClass('required');
                $("#re_document_no").focus();
                
            } else if ($(this).val() === "BARCODE_BY_DOC") {
                $(this).removeClass('required');
                $(".input_barcode_by_doc").show();
                $(".input_barcode_by_item").hide();
                $("#barcode_document_no").addClass('required');
                $("#barcode_document_no").focus();
                $("#btn_print").show();
            } else if ($(this).val() === "BARCODE_BY_ITEM") {
                $(this).removeClass('required');
                $(".input_barcode_by_item").show();
                $(".input_barcode_by_doc").hide();
                $("#quantity").addClass('required');
                $("#barcode_product_code").addClass('required');
                $("#barcode_product_code").focus();
                $("#btn_print").show();
            } else if ($(this).val() === "pa" || $(this).val() === "pk") {
                $(".input_rcv_dip").hide();
                $(".input_pa_pk").show();
                $("#btn_print").show();
                $(this).removeClass('required');

                if ($(this).val() === "pa") {
                    $(".input_pa").show();
                    $(".input_pk").hide();
                    $('input:radio[name="order_by"][value="pallet"]').prop('checked', true);
                } else if ($(this).val() === "pk") {
                    $(".input_pa").hide();
                    $(".input_pk").show();
                    $('input:radio[name="order_by"][value="location"]').prop('checked', true);
                }

                $('input:radio[name="op_doc"][value="doc_no"]').prop('checked', true);

                $("#doc_refer_ext").attr("readonly", true);
                $("#doc_refer_ext").removeClass('required');

                $("#document_no").attr("readonly", false);
                $("#document_no").addClass('required');
                $("#document_no").focus();
            } else {
                $(".input_rcv_dip").hide();
                $(".input_pa_pk").hide();
                $(".input_pa").hide();
                $(".input_pk").hide();
                $(".input_barcode_by_doc").hide();
                $(".input_barcode_by_item").hide();
                $("#btn_print").hide();
                $(this).addClass('required');
            }
        });

        $('[name="op_re_doc"]').change(function () {
            $(":text").each(function () {
                $(this).val("");
            });

            if ($(this).val() === "doc_no") {
                $("#re_doc_refer_ext").attr("readonly", true);
                $("#re_doc_refer_ext").removeClass('required');

                $("#re_document_no").attr("readonly", false);
                $("#re_document_no").addClass('required');
                $("#re_document_no").focus();
            } else if ($(this).val() === "doc_ext") {
                $("#re_document_no").attr("readonly", true);
                $("#re_document_no").removeClass('required');

                $("#re_doc_refer_ext").attr("readonly", false);
                $("#re_doc_refer_ext").addClass('required');
                $("#re_doc_refer_ext").focus();
            }
        });

        $('[name="op_doc"]').change(function () {
            $(":text").each(function () {
                $(this).val("");
            });

            if ($(this).val() === "doc_no") {
                $("#doc_refer_ext").attr("readonly", true);
                $("#doc_refer_ext").removeClass('required');

                $("#document_no").attr("readonly", false);
                $("#document_no").addClass('required');
                $("#document_no").focus();
            } else if ($(this).val() === "doc_ext") {
                $("#document_no").attr("readonly", true);
                $("#document_no").removeClass('required');

                $("#doc_refer_ext").attr("readonly", false);
                $("#doc_refer_ext").addClass('required');
                $("#doc_refer_ext").focus();
            }
        });

        $("#btn_print").click(function () {
            $("#frm_reprint").attr('target', "_blank");

            var doc_type = $("#doc_type").val();
            var file_type = $("#file_type").val();

            if (doc_type === "") {
                alert("Please Select Document Type.");
            } else {

                if (doc_type === "rc" || doc_type === "dp") {
                    if ($("#re_document_no").val() == '' && $("#re_doc_refer_ext").val() == '') {
                        alert('Please input Document No.');
                        return;
                    }
                } else if (doc_type === "pa" || doc_type === "pk") {
                    if ($("#document_no").val() == '' && $("#doc_refer_ext").val() == '') {
                        alert('Please input Document No.');
                        return;
                    }
                } else if (doc_type === "BARCODE_BY_DOC") {
                    if ($("#barcode_document_no").val() == '') {
                        alert('Please input Document No.');
                        $("#barcode_document_no").focus();
                        return;
                    }
                } else if (doc_type === "BARCODE_BY_ITEM") {
                    if ($("#barcode_product_code").val() == '') {
                        alert('Please input Product Code.');
                        $("#barcode_document_no").focus();
                        return;
                    }

                }

                if (confirm("You want to Reprint " + $("#doc_type option:selected").text() + "?")) {
                    if (doc_type === "rc") {
                        if ($("#re_document_no").val() == '' && $("#re_doc_refer_ext").val() == '') {
                            alert('Please input Document No.');
                        } else {
                            var export_order_by = $("input[name='order_by']:checked").val();
                            var url = '<?php echo site_url("/receive/export_to_pdf/") ?>?o=' + export_order_by + '&document_no=' + $("#re_document_no").val() + '&doc_refer_ext=' + $("#re_doc_refer_ext").val();
                            window.open(url, '_blank');
                        }
                    } else if (doc_type === "dp") {
                        if ($("#re_document_no").val() == '' && $("#re_doc_refer_ext").val() == '') {
                            alert('Please input Document No.');
                        } else {
                            var export_order_by = $("input[name='order_by']:checked").val();
                            var url = '<?php echo site_url("/dispatch/export_dispatch_pdf/") ?>?o=' + export_order_by + '&document_no=' + $("#re_document_no").val() + '&doc_refer_ext=' + $("#re_doc_refer_ext").val();
                            window.open(url, '_blank');
                        }
                    } else if (doc_type === "pa") {
                        if (file_type === "pdf") {
                            $("#o").val($("input[name='order_by']:checked").val());
                            $("#showfooter").val('show');
                            $("#frm_reprint").attr('action', "<?php echo site_url("/report/export_putaway_pdf"); ?>");
                        } else if (file_type === "xls") {

                        }
                        $("#frm_reprint").submit();
                    } else if (doc_type === "pk") {
                        if (file_type === "pdf") {
                            $("#o").val($("input[name='order_by']:checked").val());
                            $("#showfooter").val('show');
                            $("#frm_reprint").attr('action', "<?php echo site_url("/report/export_picking_pdf?showfooter=show"); ?>");
                        } else if (file_type === "xls") {

                        }
                        $("#frm_reprint").submit();
                    } else if (doc_type === "BARCODE_BY_DOC") {
                            $("#d").val($("#barcode_document_no").val());
                            $("#frm_reprint").attr('action', "<?php echo site_url("/pre_receive/printBarcode"); ?>");
                            $("#frm_reprint").submit();
                    } else if (doc_type === "BARCODE_BY_ITEM") {
                            $("#p").val($("#barcode_product_code").val());
                            $("#q").val($("#quantity").val());
                            $("#frm_reprint").attr('action', "<?php echo site_url("/pre_receive/printBarcodeByItem"); ?>");
                            $("#frm_reprint").submit();
                    }
                }
            }
        });

        $("#btn_clear").click(function () {
            $(":text").each(function () {
                $(this).val("");
            });

            $(".input_rcv_dip").hide();
            $(".input_pa_pk").hide();
            $("#btn_print").hide();
            $("#doc_type").val('');
            $("#doc_type").addClass('required');
        });

        $("#btn_back").click(function () {
            if (confirm("You want back to Main Menu?")) {
                window.location = "<?php echo site_url() ?>/welcome";
            }
        });

        $("#re_document_no").change(function () {
            if ($(this).val().length <= 0) {
                $(this).addClass('required');
            } else {
                $(this).removeClass('required');
            }
        });

        $("#document_no").change(function () {
            if ($(this).val().length <= 0) {
                $(this).addClass('required');
            } else {
                $(this).removeClass('required');
            }
        });

        $("#barcode_document_no").change(function () {
            if ($(this).val().length <= 0) {
                $(this).addClass('required');
            } else {
                $(this).removeClass('required');
            }
        });
        
        $("#barcode_product_code").change(function () {
            if ($(this).val().length <= 0) {
                $(this).addClass('required');
            } else {
                $(this).removeClass('required');
            }
        });

        $("#quantity").change(function () {
            if ($(this).val().length <= 0) {
                $(this).addClass('required');
            } else {
                $(this).removeClass('required');
            }
        });
        
    });
</script>
<style>
    .w30{ width: 30px; padding: 5px; }
    .w50{ width: 50px;  padding: 5px; }
    .w70{ width: 70px;  padding: 5px; }
    .w100{ width: 100px;  padding: 5px; }
    .w110{ width: 110px;  padding: 5px; }
    .w120{ width: 120px;  padding: 5px; }
    .w150{ width: 150px;  padding: 5px; }
    .txt-r{ text-align: right; }
    .txt-l{ text-align: left; }
    .txt-c{ text-align: center; }
</style>
<html>
    <head>
        <title>Reprint Documents</title>
    </head>
    <body>
        <form id="frm_reprint" name="frm_reprint" method="GET">
            <input type="hidden" name="o" id="o"/>
            <input type="hidden" name="d" id="d"/>
            <input type="hidden" name="p" id="p"/>
            <input type="hidden" name="showfooter" id="showfooter"/>
            <table width="100%">
                <tr>
                    <td class='txt-r w150'>Size : </td>
                    <td>
                        <select name="paper_size" style="width: auto;">
                            <option value="A4">A4</option>
                            <option value="2x3">Barcode 2" x 3"</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="txt-r w150"><label for="doc_type">Documents Type<span class="required">*</span> : </label></td>
                    <td>
                        <select name="doc_type" id="doc_type" class="required" style="width: auto;">
                            <?php
                            if (ISSET($parameter["documents_list"])):
                                foreach ($parameter["documents_list"] as $key => $value) :
                                    ?>
                                    <option value="<?php echo $key ?>"><?php echo $value ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </td>
                </tr>

                <!-- Print Barcode By Document-->
                <tr class="input_barcode_by_doc">
                    <td class='txt-r w150'>Document No : </td>
                    <td>
                        <input type='text' class="document_no" id='barcode_document_no' name='document_no'>
                    </td>
                </tr>

                <!-- Print Barcode By Document-->
                <tr class="input_barcode_by_doc">
                    <td class='txt-r w150'>Print Limit : </td>
                    <td>
                        <input type='text' class="document_no" id='print_start' name='start' placeholder="start" />
                        <input type='text' class="document_no" id='print_end' name='max' placeholder="end" />
                    </td>
                </tr>

                <!-- Print Barcode By Document-->
                <tr class="input_barcode_by_doc">
                    <td class='txt-r w150'>Single Print : </td>
                    <td>
                        <input type='checkbox' class="document_no" id='single_print' name='single_print' value="1" />
                    </td>
                </tr>

                <!-- Print Barcode By Item-->
                <tr class="input_barcode_by_item">
                    <td class='txt-r w150'>Item No : </td>
                    <td>
                        <input type='text' class="product_code" id='barcode_product_code' name='product_code'>
                    </td>
                </tr>
                <tr class="input_barcode_by_item">
                    <td class='txt-r w150'>Quantity : </td>
                    <td>
                        <input type='text' class="quantity" id='quantity' name='quantity'>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>