<?php
include "db.php";
$error = "";
$success = "";

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $barcode = $_POST['barcode'];
    $price = $_POST['price'];
    $date = date("d-m-Y H:i:s");

    $check = mysqli_query($conn, "SELECT * FROM item WHERE barcode='$barcode'");
    if (mysqli_num_rows($check) > 0) {
        $error = "❌ هذا الباركود مسجل مسبقًا!";
    } else {
        $sql = "INSERT INTO item(name, barcode, price, datereg) VALUES('$name','$barcode','$price','$date')";
        mysqli_query($conn, $sql);
        $success = "✅ تم إضافة المنتج بنجاح!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container mt-4">

    <h2 class="text-center mb-4">📌 Product Dashboard</h2>

    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="card p-4 shadow-sm">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Barcode</label>
                <input type="text" name="barcode" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (L.L)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <button type="submit" name="save" class="btn btn-primary w-100">Save Product</button>

        </form>

        <form action="customer.php">
            <button type="submit" name="save" class="btn btn-success flex grow-1">Go to price page</button>
        </form>
        <form action="pos.php">
            <button type="submit" name="save" class="btn btn-warning flex-grow-1">Go to cash page</button>
        </form>
    </div>

    <hr class="my-4">
    


    <h4>📍 Products List</h4>
    <table class="table table-bordered table-striped">
        <tr class="table-dark">
            <th>ID</th>
            <th>Name</th>
            <th>Barcode</th>
            <th>Price</th>
            <th>Date</th>
        </tr>

        <?php
        $result = mysqli_query($conn, "SELECT * FROM item ORDER BY id DESC");
        while($row = mysqli_fetch_assoc($result)){
            echo "<tr>
                    <td>".$row['id']."</td>
                    <td>".$row['name']."</td>
                    <td>".$row['barcode']."</td>
                    <td>L.L/".number_format($row['price'],0,',', ',')."</td>
                    <td>".$row['datereg']."</td>
                    <td>
                    <a href='edit.php?id=".$row['id']."' class='btn btn-warning btn-sm'>✏️ Edit</a>
                    <a href='delete.php?id=".$row['id']."' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure?')\">🗑️ Delete</a>
                </td
                  </tr>";
        }
        ?>
    </table>


</div>

</body>
</html>
