<style>
    .img_dv , .selected_data {
        cursor: pointer;
    }
    .clabel {
        position: relative;
        top: 8px;
        visibility: hidden;
    }

    .cactive {
        visibility: visible;
    }

    .report_printing {
        display: none;
    }

    #tblData_length , #tblData_filter {
        margin-bottom: 10px;
    }
</style>
<script lang="javascript">
    var fS = false;
    var base_url = '<?php echo base_url($parameter['path']); ?>/';
    $(document).ready(function () {
        $("#operation_date").datepicker({format: 'yyyy-mm-dd'}).keypress(function (event) {
            event.preventDefault();
        }).on('changeDate', function (e) {
            e.preventDefault();
        }).bind("cut copy paste", function (e) {
            e.preventDefault();
        });

        // Tab Customize
        $('#image_tab a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        })

        // Search
        $("#btn_search").click(function (e) {
            var request = {document_type: $("#document_type").val()
                , operation_date: $("#operation_date").val()
                , container_no: $("#container_no").val()
                , document_no: $("#document_no").val()
                , product_code: $("#product_code").val()
                , product_status: $("#product_status").val()
            };
            if (fS) {
                alert('Please check service again.');
                return;
            }
            fS = true;
            $.post("<?php echo base_url('index.php/report_picture/search'); ?>", request, function (data) {
                fS = false;
                $(".report_printing").hide();
                $('#image_tab a:first').tab('show');
                var result = '<table class="well table_report table-hover" id="tblData">';
                result += '<thead>';
                result += '<tr>';
                result += '<th>Document Type</th>';
                result += '<th>Operation Date</th>';
                result += '<th>Container No.</th>';
                result += '<th>Document No.</th>';
                result += '<th>Product Code</th>';
                result += '<th>Pallet Code</th>';
                result += '</thead>';
                result += '</tr>';
                result += '<tbody>';
                $.each(data.RESPONSE, function (i, v) {
                    result += '<tr class="selected_data" data-item-id="' + v.Item_Id + '" data-order-id="' + v.Order_Id + '" data-cont-id="' + v.Cont_Id + '" data-pallet-id="' + v.Pallet_Id + '">';
                    result += '<td>' + v.Process_Type + '</td>';
                    result += '<td>' + v.Actual_Action_Date + '</td>';
                    result += '<td>' + v.Cont_No + '</td>';
                    result += '<td>' + v.Doc_Refer_Ext + '</td>';
                    result += '<td>' + v.Product_Code + '</td>';
                    result += '<td>' + v.Pallet_Code + '</td>';
                    result += '</tr>';
                });
                result += '</tbody>';
                result += '</table>';
                $("#response").html(result);
                initRowClick();
                $("#tblData").dataTable({
                    aLengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                    , iDisplayLength: 25
                    , bSort: false
                });
            }, "JSON");
        });

        var initImageClick = function () {
            $(".img_dv").click(function () {
                var isUsed = $(this).hasClass('selected');
                if (isUsed) {
                    $(this).removeClass('selected');
                    $(this).find('span').removeClass('cactive');
                } else {
                    $(this).addClass('selected');
                    $(this).find('span').addClass('cactive');
                }

            });
        }


        var initRowClick = function () {
            $(".selected_data").click(function () {
                var request = {
                    item_id: $(this).data('item-id')
                    , order_id: $(this).data('order-id')
                    , cont_id: $(this).data('cont-id')
                    , pallet_id: $(this).data('pallet-id')
                }
                var _this = this;
                $.post("<?php echo base_url('index.php/report_picture/get_detail'); ?>", request, function (data) {
                    if (data.container.length == 0 && data.item.length == 0 && data.pallet.length == 0) {
                        $(".report_printing").hide();
                        swal('Image not found!', 'This item didn\'t have any image!', 'warning');
                        return;
                    }
                    $(".report_printing").show();
                    $("#tb_container").find("ul.thumbnails").empty();
                    $("#tb_pallet").find("ul.thumbnails").empty();
                    $("#tb_item").find("ul.thumbnails").empty();
                    var idx = 1;
                    $.each(data.container, function (i, v) {
                        var im = '<li class="span4 img_dv" data-image-id="' + v.name + '"><div class="thumbnail"><span class="label label-success clabel">Selected</span><img class="img-rounded" src="' + base_url + v.path + '" width="260" /><h3 style="padding-left: 18px;">' + v.time + '</h3></div></li>';
                        $("#tb_container").find("ul.thumbnails").append(im);
                        idx++;
                    });
                    $.each(data.pallet, function (i, v) {
                        var im = '<li class="span4 img_dv" data-image-id="' + v.name + '"><div class="thumbnail"><span class="label label-success clabel">Selected</span><img class="img-rounded" src="' + base_url + v.path + '" width="260" /><h3 style="padding-left: 18px;">' + v.time + '</h3></div></li>';
                        $("#tb_pallet").find("ul.thumbnails").append(im);
                        idx++;
                    });
                    $.each(data.item, function (i, v) {
                        var im = '<li class="span4 img_dv" data-image-id="' + v.name + '"><div class="thumbnail"><span class="label label-success clabel">Selected</span><img class="img-rounded" src="' + base_url + v.path + '" width="260" /><h3 style="padding-left: 18px;">' + v.time + '</h3></div></li>';
                        $("#tb_item").find("ul.thumbnails").append(im);
                        idx++;
                    });
                    initImageClick();
                    $("#item_id").val($(_this).data('item-id'));
                    $("#order_id").val($(_this).data('order-id'));
                }, "JSON");
            });
        };

        $("#export_dg_report_pdf").click(function () {
            var selected = $(".img_dv.selected");
            var frm = $("#frm_print");
            if (selected.length == 0) {
                swal('Warning!', 'Please select atleast one picture for print!', 'warning');
                return;
            }
            $.post("<?php echo base_url("index.php/report_picture/getProductList") ?>", {order_id: $("#order_id").val()}, function (data) {
                // ADD PRODUCT LIST
                $("#product_list_result").html("");
                $.each(data, function (i, v) {
                    $("#product_list_result").append('<div style="padding: 2px 0;"><input type="checkbox" id="product_list_' + i + '" class="form-control" name="product_list[]" value="' + v.Product_NameEn + '" style="vertical-align: text-bottom;" /><label for="product_list_' + i + '" style="display: inline;"> ' + v.Product_NameEn + '</label></div>');
                });
                // END

                // BEGIN ADD IMAGE
                frm.find("input.image").remove();
                $.each(selected, function (i, v) {
                    frm.append('<input type="hidden" class="image" name="image_id[]" value="' + $(v).data("image-id") + '" />');
                });
                // END IMAGE
                $("#md_report_picture").modal("show");
            }, "JSON");
        });

        $("#export_dg_report_with_email").click(function () {
            var selected = $(".img_dv.selected");
            if (selected.length == 0) {
                swal('Warning!', 'Please select atleast one picture for print!', 'warning');
                return;
            }
            swal({
                title: 'Please specific email recipient',
                input: 'email',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                showLoaderOnConfirm: true,
                allowOutsideClick: false
            }).then(function (email) {
                var em = email;
                swal({
                    title: 'Confirm Print and Send Email',
                    text: "Please confirm printing Damage / Non Conform report and send to \"" + em + "\"",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, print it!',
                    input: 'select',
                    inputOptions: {damage_report: "DAMAGED REPORT"
                        , intake: "INTAKE REPORT"
                        , outtake: "OUTTAKE REPORT"
                        , cross_dock: "CROSS DOCK REPORT"}
                }).then(function (result) {
                    var frm = $("#frm_print");
                    frm.prop("action", "report_picture/export_dg_report_pdf");
                    $("#email_address").val(em);
                    frm.find("input.image").remove();
                    $("#document_export_type").val(result);
                    $.each(selected, function (i, v) {
                        frm.append('<input type="hidden" class="image" name="image_id[]" value="' + $(v).data("image-id") + '" />');
                    });
                    frm.submit();
                })
            })
        });

        $('#md_report_picture').on('show.bs.modal', function () {
            $(this).css({'z-index': ''});
            $(this).find('.modal-body').css({
                'max-height': '100%'
            });
        });

        $('#md_report_picture').on('hidden.bs.modal', function () {
            $(this).css({'z-index': '-1'});
        });

        $("#lot_or_serial").change(function(){
            if ($("#lot_or_serial").val() == "Free_Text") {
                $(".dv_free_text").show();
            } else {
                $(".dv_free_text").hide();
            }            
        });

        $("#lot_or_serial").trigger("change");
        
    });
</script>
<form class="form-horizontal" style="padding: 10px;">
    <fieldset style="padding: 10px;">
        <legend>Search Criteria</legend>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="document_type">Document Type. : </label>
                        <div class="controls">
                            <select id="document_type">
                                <option value="ALL">Show All</option>
                                <option value="INBOUND">Inbound</option>
                                <option value="OUTBOUND">Outbound</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="product_status">Status. : </label>
                        <div class="controls">
                            <select id="product_status">
                                <option value="">All Status</option>
                                <?php
                                foreach ($parameter['status'] as $idx => $val) :
                                    echo '<option value="' . $val->Dom_Code . '">' . $val->Dom_EN_Desc . '</option>';
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="operation_date">Operation Date. : </label>
                        <div class="controls">
                            <input type="text" id="operation_date" placeholder="Date Format">
                        </div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="container_no">Container No. : </label>
                        <div class="controls">
                            <input type="text" id="container_no" placeholder="Container">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="document_no">Document No. : </label>
                        <div class="controls">
                            <input type="text" id="document_no" placeholder="Document No">
                        </div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="product_code">Product Code. : </label>
                        <div class="controls">
                            <input type="text" id="product_code" placeholder="Product Code">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12" align="center">
                    <button type="button" class="btn btn-large btn-info" id="btn_search">Search</button>
                </div>
            </div>
        </div>
    </fieldset>
</form>
<div id="response" style="text-align: center; padding: 20px;">Please click search</div>
<div class="report_printing" id="image_tab_container" style="padding: 20px;">
    <ul class="nav nav-tabs" id="image_tab">
        <li class="active"><a href="#tb_container">By Container</a></li>
        <li><a href="#tb_pallet">By Pallet</a></li>
        <li><a href="#tb_item">By Item</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tb_container"><ul class="thumbnails" align="center"></ul></div>
        <div class="tab-pane" id="tb_pallet"><ul class="thumbnails" align="center"></ul></div>
        <div class="tab-pane" id="tb_item"><ul class="thumbnails" align="center"></ul></div>
    </div>
    <div class="report_printing" align="center" style="margin-top: 35px;">
        <button class="btn btn-success btn-large" id="export_dg_report_pdf">Export</button>
        <button class="btn btn-info btn-large" id="export_dg_report_with_email">Export and Send Email</button>
    </div>
</div>
<div class="modal fade" id="md_report_picture" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel" style="z-index: -1;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form target="_blank" id="frm_print" method="POST" action="report_picture/export_dg_report_pdf">
                <div class="modal-header" style="height: 30px;">
                    <div style="width: 50%;float: left;font-size: 17.5px;line-height: 32px;" align="left">Form Export</div>
                    <div style="width: 50%;float: right;" align="right">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn_submit">Export</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="document_export_type" class="control-label" style="font-weight: bold;">Document Type :</label>
                        <select name="document_export_type" id="document_export_type" class="form-control">
                            <option value="damage_report">DAMAGED REPORT</option>
                            <option value="intake">INTAKE REPORT</option>
                            <option value="outtake">OUTTAKE REPORT</option>
                            <option value="cross_dock">CROSS DOCK REPORT</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="product_list_result" class="control-label" style="font-weight: bold;">Product:</label>
                        <div id="product_list_result"></div>
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="lot_or_serial" class="control-label" style="font-weight: bold;">Lot / Serial :</label>
                        <select name="lot_or_serial" id="lot_or_serial" class="form-control">
                            <option value="Product_Lot">Product Lot</option>
                            <option value="Product_Serial">Product Serial</option>
                            <option value="Free_Text">Free Text</option>
                        </select>
                    </div>
                    <div class="form-group dv_free_text" style="margin-bottom: 10px;">
                        <label for="quantity" class="control-label" style="font-weight: bold;">Free text for lot/serial :</label>
                        <input type="text" class="form-control" name="free_text">
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="quantity" class="control-label" style="font-weight: bold;">Quantity:</label>
                        <input type="text" class="form-control" name="quantity" id="quantity">
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="remark" class="control-label" style="font-weight: bold;">Remark:</label>
                        <textarea class="form-control" name="remark" id="remark" style="width: 95%;"></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="remark" class="control-label" style="font-weight: bold;">Description:</label>
                        <div style="padding: 2px 0;"><input type="checkbox" class="form-control" name="description_type_1" value="1" style="vertical-align: text-bottom;" /> <?php echo utf8_to_tis620("สินค้าเสียหายจากในตู้") ?></div>
                        <div style="padding: 2px 0;"><input type="checkbox" class="form-control" name="description_type_2" value="1" style="vertical-align: text-bottom;" /> <?php echo utf8_to_tis620("จำนวนสินค้าไม่ครบ") ?></div>
                        <div style="padding: 2px 0;"><input type="checkbox" class="form-control" name="description_type_3" value="1" style="vertical-align: text-bottom;" /> <?php echo utf8_to_tis620("อุบัติเหตุ") ?> <input type="text" class="form-control" name="description_type_3_desc" placeholder="<?php echo utf8_to_tis620("รายงานอุบัติเหตุ เลขที่") ?>" /></div>
                        <div style="padding: 2px 0;"><input type="checkbox" class="form-control" name="description_type_4" value="1" style="vertical-align: text-bottom;" /> <?php echo utf8_to_tis620("อื่น ๆ") ?> <input type="text" class="form-control" name="description_type_4_desc" placeholder="<?php echo utf8_to_tis620("หมายเหตุ") ?>"/> </div>
                    </div>
                    <input type="hidden" name="order_id" id="order_id" />
                    <input type="hidden" name="item_id" id="item_id" />
                    <input type="hidden" name="email_address" id="email_address" />                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn_submit">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>