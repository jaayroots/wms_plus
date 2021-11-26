<html>
    <head>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery-1.9.1.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery-ui-1.10.2.custom.min.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jsmin.js") ?>"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/jquery.maskedinput.js") ?>"></script>

        <script>

//            var id_card = "1170600064503";
//            var id_card = "0839900360292";
//            var id_card = "0745539002753";
            var id_card = "0435536000019";
            
            

            $(document).ready(function() {
                if (checkIDcard(id_card)) {
                    alert('true');
                } else {
                    alert('false');
                }
            });

            function checkIDcard(id) {
                if (id.length != 13)
                    return false;
                for (i = 0, sum = 0; i < 12; i++)
                    sum += parseFloat(id.charAt(i)) * (13 - i);
                if ((11 - sum % 11) % 10 != parseFloat(id.charAt(12)))
                    return false;
                return true;
            }
        </script>
    </head>
    <body>

    </body>
</html>