<script>
    $(document).ready(function() {
        var area_width = $('#frmReceive').width() - 20;
		$('#table-wrapper').width(area_width);

			$("#scroll_div").scroll(function(){
				$('#header_title').scrollLeft($(this).scrollLeft());
		});
    });

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url("/report_booking/export_booking2Excel"); ?>")
        } else {
            $("#form_report").attr('action', "<?php echo site_url("/report_booking/export_booking2PDF"); ?>")
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
<script type="text/javascript">
        function UpdateTableHeaders() {
            $("div.divTableWithFloatingHeader").each(function() {
                var originalHeaderRow = $(".tableFloatingHeaderOriginal", this);
                var floatingHeaderRow = $(".tableFloatingHeader", this);
                var offset = $(this).offset();
                var scrollTop = $(window).scrollTop();
                if ((scrollTop > offset.top) && (scrollTop < offset.top + $(this).height())) {
                    floatingHeaderRow.css("visibility", "visible");
                    floatingHeaderRow.css("top", Math.min(scrollTop - offset.top, $(this).height() - floatingHeaderRow.height()) + "px");

                    // Copy cell widths from original header
                    $("th", floatingHeaderRow).each(function(index) {
                        var cellWidth = $("th", originalHeaderRow).eq(index).css('width');
                        $(this).css('width', cellWidth);
                    });

                    // Copy row width from whole table
                    floatingHeaderRow.css("width", $(this).css("width"));
                }
                else {
                    floatingHeaderRow.css("visibility", "hidden");
                    floatingHeaderRow.css("top", "0px");
                }
            });
        }

        $(document).ready(function() {
            $("table.tableWithFloatingHeader").each(function() {
                $(this).wrap("<div class=\"divTableWithFloatingHeader\" style=\"position:relative\"></div>");

                var originalHeaderRow = $("tr:first", this)
                originalHeaderRow.before(originalHeaderRow.clone());
                var clonedHeaderRow = $("tr:first", this)

                clonedHeaderRow.addClass("tableFloatingHeader");
                clonedHeaderRow.css("position", "absolute");
                clonedHeaderRow.css("top", "0px");
                clonedHeaderRow.css("left", $(this).css("margin-left"));
                clonedHeaderRow.css("visibility", "hidden");

                originalHeaderRow.addClass("tableFloatingHeaderOriginal");
            });
            UpdateTableHeaders();
            $(window).scroll(UpdateTableHeaders);
            $(window).resize(UpdateTableHeaders);
        });
    </script>

<form  method="post" target="_blank" id="form_report">
    <input type="hidden" name="doc_type" value="<?php echo $search['doc_type']; ?>" />
    <input type="hidden" name="doc_value" value="<?php echo $search['doc_value']; ?>" />
    <input type="hidden" name="renter_id" value="<?php echo $search['renter_id']; ?>" />
    <div class='Tables_wrapper'>
        <div class=" fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">
            <div id="defDataTable2_length" class="dataTables_length">
                <?php echo $display_items_per_page?>
            </div>
            <div class="dataTables_filter" id="defDataTable2_filter">
                <label>
                </label>
            </div>
        </div>

        <div id="table-wrapper" style="width: <?php echo ($search['w'] - 30)?>px;">
            
            <div id="scroll_div" style="width: <?php echo ($search['w'] - 30)?>px; height: 100%; overflow-y: hidden; overflow-x: scroll;">
                <table class ='tableWithFloatingHeader well table_report' cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th class="" style="width:100px;">Row</th> 
                            <th class="" style="width:150px;">Product Code</th> 
                            <th class="" style="width:150px;">Product Name</th> 
                            <th class="" style="width:100px;">Product Status</th> 
                            <th class="" style="width:150px;">Product subStatus</th> 
                            <th class="" style="width:150px;">Receive Date</th> 
			    <th class="" style="width:150px;">Customs Entry</th> 
                            <th class="" style="width:150px;">Customs Sequence</th> 
                            <th class="" style="width:130px;">HS Code</th> 
                            <th class="" style="width:150px;">Product Lot</th> 
                            <th class="" style="width:150px;">Product Serial</th> 
                            <th class="" style="width:140px;">Invoice No</th> 
                            <th class="" style="width:130px;">PD Reserv Qty</th> 
                            <th class="" style="width:130px;">Unit</th> 
			    <th class="" style="width:130px;">Price</th>
                            <th class="" style="width:160px;">Pallet Code</th> 
                            <th class="" style="width:170px;">remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        if(empty($data)):
                            echo '<tr><td colspan="17" align=center><b>No Data Available.</b></td></tr>';
                        else:
			    $idx = 1;
                            foreach ($data as $key => $value) : ?>
                                        <tr>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $idx; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Product_Code; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Product_NameEN; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Product_Status; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Product_Sub_Status; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Receive_Date; ?></td>
					    <td class="" style="text-align:left;width:80px;"><?php echo $value->Doc_Refer_CE; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Customs_Sequence; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->HS_Code; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Product_Lot; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Product_Serial; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Invoice_No; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->PD_Reserv_Qty; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->name; ?></td>
					    <td class="" style="text-align:left;width:80px;"><?php echo $value->Price_Per_Unit * $value->PD_Reserv_Qty; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->Pallet_Code; ?></td>
                                            <td class="" style="text-align:left;width:80px;"><?php echo $value->remark; ?></td>
                                        </tr>                                        
                                        <?php
                                        $total +=$value->PD_Reserv_Qty ;
					$idx++;
                            endforeach;
                         endif;
                        ?>
                    </tbody>

                    <tfoot>
                        <tr >
                            <th style='text-align: left;' colspan="12"><b>Total</b></th>
                            <th style='text-align: right;'><?php echo set_number_format($total); ?></th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>

                </table>
            </div>
         </div>
    </div>
    <div id="pagination" class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix">
        <div class="dataTables_info" id="defDataTable2_info">Showing <?php echo $low+1 ?> to <?php echo $show_to ?> of <?php echo $items_total;?> entries</div>
        <div style="padding:3px;"class="dataTables_paginate fg-buttonset ui-buttonset fg-buttonset-multi ui-buttonset-multi paging_full_numbers" id="defDataTable2_paginate">
            <?php echo $pagination?>
        </div>
    </div>
</form>
