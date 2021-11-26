<style>
	td.text-left {
		text-align: left !important;
		text-indent: 10px;
	}
</style>
<script>
    var response_data = '{response}';
    $(document).ready(function () {
        var oTable = $('#table_product_master').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "bRetrieve": true,
            "bDestroy": true,
            "iDisplayLength": 100,
            "sAjaxSource": "<?php echo site_url(); ?>/product_master/get_product_list",
            "oLanguage": {
                "sLoadingRecords": "Please wait - loading..."
                , "sProcessing": "<img src=\"<?php echo base_url() ?>images/ajax-loader.gif\" />"
            },
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
		{ "sWidth": "5%", "aTargets" : [0] },
		{ "sWidth": "7%", "sClass": "text-left" , "aTargets" : [1] },
		{ "sWidth" : "23%" , "sClass": "text-left", "aTargets": [2] },
		{ "sWidth" : "7%" , "aTargets": [3] },
		{ "sWidth" : "7%" , "aTargets": [4] },
		{ "sWidth" : "7%" , "aTargets": [5] },
		{ "sWidth" : "5%" , "aTargets": [6] },
		{ "sWidth" : "5%" , "aTargets": [7] },
		{ "sWidth" : "5%" , "aTargets": [8] },
		{ "sWidth" : "5%" , "aTargets": [9] },
		]
        });
    });
</script>
<html>
    <TR class="content" style='height:100%' valign="top">   
        <TD>    
            <table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="table_product_master" >
                <thead>
                    <?php
                    $show_column = $data['show_column'];
                    $str_header = "";
                    foreach ($show_column as $column) :
                        $str_header .= "<th>" . $column . "</th>";
                    endforeach;
                    ?>

                    <tr><?php echo $str_header; ?></tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </TD>
    </TR>
</html>
