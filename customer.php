<?php
include "db.php";
$message = "";
$product = null;

if (isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];

    $sql = "SELECT * FROM item WHERE barcode='$barcode'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    } else {
        $message = "❌ Product Not Found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Price</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light" onload="document.priceForm.barcode.focus();">

<div class="container mt-5">
    <h2 class="text-center mb-4">🛒 Check Product Price</h2>

    <div class="card p-4 shadow-sm">
        <form method="POST" name="priceForm">
            <input type="text" name="barcode" class="form-control form-control-lg" placeholder="Scan Barcode Here..." required autofocus>
        </form>
    </div>

    <div class="mt-3">
        <?php if ($message): ?>
            <div class='alert alert-danger text-center'><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($product): ?>
            <div class="alert alert-success text-center">
                <h4>✅ Product Found</h4>
                <p><strong>Name:</strong> <?php echo $product['name']; ?></p>
                <p><strong>Price:</strong> L.L<?php echo number_format( $product['price'], 0, ',',','); ?></p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
