<html>
    <head>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery-1.9.1.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery-ui-1.10.2.custom.min.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jsmin.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery.maskedinput.js") ?>"></script>
        
    </head>
    <body>
        <table border='1'>
            <tr>
                <td class="td1">
                    a
                </td>
                <td class="td2">
                    s
                </td>
                <td class="td3">
                    d
                </td>
                <td class="td4">
                    f
                </td>
                <td class="td5">
                    g
                </td>
            </tr>
            <tr>
                <td class="td1">
                    j
                </td>
                <td class="td2">
                    k
                </td>
                <td class="td3">
                    l
                </td>
                <td class="td4">
                    ;
                </td>
                <td class="td5">
                    '
                </td>
            </tr>
            <tr>
                <td id='coltd' colspan='4'>
                    z
                </td>
                <td class="td5">x</td>
            </tr>
        </table>
        <script>
            $(document).ready(function() {
                $('.td3').hide();
                $('#coltd').attr('colspan',3);
            });
        </script>
    </body>
</html>