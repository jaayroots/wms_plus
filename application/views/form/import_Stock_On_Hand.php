<?php // Create by Ton! 20130529  ?>
<SCRIPT>
    $(document).ready(function() {
        $('#xfile').val('');
        $("input[type=submit]").attr("disabled", "disabled");
    });

    function checkfile(sender) {
        var validExts = new Array(".xlsx", ".xls");
        var fileExt = sender.value;
        fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
            alert("Invalid file selected, valid files are of " + validExts.toString() + " types.");
            $("input[type=submit]").attr("disabled", "disabled");
            return false;
        }
        else
            $("input[type=submit]").removeAttr("disabled");
        return true;
    }
    
    function download_template(){
        window.open('<?php echo site_url('import_Stock_On_Hand/load_template'); ?>', '_blank');
    }

</SCRIPT>
<HTML>
    <HEAD>
        <TITLE> Import Pre-Dispatch </TITLE>
    </HEAD>
    <BODY>
        <FORM CLASS="form-horizontal" ID="frmImportPreDispatch" NAME="frmImportPreDispatch" METHOD='post' ENCTYPE="multipart/form-data" ACTION="<?php echo site_url() ?>/import_Stock_On_Hand/upload">
            <TABLE>
                <TR>
                    <TD>Select file for import : </TD>
                    <TD><input type="file" name="xfile" id="xfile" onchange="checkfile(this);"/></TD>
                    <TD><INPUT TYPE="submit" CLASS="button dark_blue" VALUE="SUBMIT" ID="submit"></TD>
                    <TD width="30"></TD>
                    <TD><INPUT TYPE="button" CLASS="button dark_blue" VALUE="Load Import Template" ONCLICK="javascript:download_template();" ID="load_template"></TD>
                </TR>   
                <div id="response"></div>
                <ul id="image-list"></ul>
            </TABLE>
        </FORM>
    </BODY>
</HTML>

