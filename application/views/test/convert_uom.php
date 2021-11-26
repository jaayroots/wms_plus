<html>
    <header>
        <title>Convert UOM</title>
    </header>
    <body>
        <form action="" method="post">
            <table border="1">
                <tr>
                    <td colspan="3">
                        Convert UOM
                    </td>
                </tr>
                <tr>
                    <td>
                        From Uom
                    </td>
                    <td>
                        <?php echo form_dropdown('from_uom', $optionUom, @$data_post['from_uom'], 'id=from_uom'); ?>
                    </td>
                    <td>
                        <?php echo form_input('from_qty', @$data_post['from_qty'], 'id=from_qty placeholder="QTY"'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        To Uom
                    </td>
                    <td>
                        <?php echo form_dropdown('to_uom', $optionUom, @$data_post['to_uom'], 'id=to_uom'); ?>
                    </td>
                    <td>
                        <?php echo @$quotient; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <input type="submit">
                    </td>
                </tr>
            </table>            
        </form>
    </body>
</html>