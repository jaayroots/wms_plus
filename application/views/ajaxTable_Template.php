<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>

<script>
    $(document).ready(function() {
        //$('#defDataTable').dataTable({
        
        if("{table_name}" == "defDataTableModal"){
             $('#{table_name}').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": "{search_value}"},
            "aoColumns": [
                {"sWidth": "40px;","sClass": "right"},
                {"sWidth": "50px;","sClass": "right"},
                {"sWidth": "60px;","sClass": "center"},
                {"sWidth": "40px;","sClass": "center"},
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "60px;","sClass": "center"},
                {"sWidth": "40px;","sClass": "center"},
                {"sWidth": "50px;","sClass": "center"},
                {"sWidth": "60px;","sClass": "center"},
                {"sWidth": "20px;","sClass": "center"},
                {"sWidth": "50px","sClass": "right"}],
            "sPaginationType": "full_numbers"});
        }else{
        $('#{table_name}').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bAutoWidth": false,
            "oSearch": {"sSearch": "{search_value}"},
            "aoColumns": [
                {"onblur": 'submit',"sWidth": "40px;","sClass": "right"},
                {"onblur": 'submit',"sWidth": "50px;","sClass": "right"},
                {"onblur": 'submit',"sWidth": "60px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "40px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "50px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "60px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "40px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "50px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "60px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "20px;","sClass": "center"},
                {"onblur": 'submit',"sWidth": "50px","sClass": "right"}],
            "sPaginationType": "full_numbers"}).makeEditable({
            sUpdateURL: '<?php echo base_url() . "pre_dispatch/saveEditedRecord"; ?>'

        });
        //$('#showDataTable').dataTable()
       //$("#search_param").val("{search_value}");
       }
    });



</script>


