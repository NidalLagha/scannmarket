<?php
include "db.php";
$error="";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .pos-style{
            background-image: url();
            height: 400px;
        }
    </style>
</head>
<body onload="document.pos.barcode.focus();">

    <div class="container">
        <form class="pos-style" name="pos" action="" method="POST">
            <div class="form-group">
                <input type="text" name="barcode" class="form-control" placeholder="bar code reader">
            </div>
        </form>

        <?php
        include "barcode_reg.php";
        
        ?>

        <h1 style="color:red" class="error"><?php echo $error; ?></h1>

        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Barcode</th>
                    <th>Date registerred</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $query_grap="select * from item";
                $query_exe=mysqli_query($conn,$query_grap);

                while($row=mysqli_fetch_assoc($query_exe)){
                    $id=$row['id'];
                    $barcode=$row['barcode'];
                    $date=$row['datereg'];

                    ?>
                    <tr>
                    <td><?php echo $id;?></td>
                    <td><?php echo $barcode;?></td>
                    <td><?php echo $date;?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    
</body>
</html>