<?php
session_start();
require_once '../config/db.php';
$products = $conn->query('SELECT product_id, name, wattage FROM Product LEFT JOIN EnergyUsage ON Product.product_id=EnergyUsage.product_id')->fetch_all(MYSQLI_ASSOC);
$result = null;
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $hours_used = floatval($_POST['hours_used'] ?? 0);
    $wattage = floatval($_POST['wattage'] ?? 0);
    $rate = 12.5; // Cost per kWh in your area (customize)
    if ($wattage && $hours_used) {
        $energy = ($wattage * $hours_used) / 1000; // kWh used
        $cost = $energy * $rate;
        $result = [
            'energy' => round($energy, 2),
            'cost' => round($cost, 2),
            'wattage' => $wattage,
            'hours_used' => $hours_used
        ];
    } else {
        $message = 'Enter valid wattage and usage hours!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Energy Usage Suggestion Tool - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h4>Estimate Energy Consumption</h4>
    <?php if ($message): ?><div class="alert alert-warning"><?=$message?></div><?php endif; ?>
    <form method="POST" class="form-inline mb-4">
        <div class="form-group mr-2">
            <label>Product</label>
            <select name="product_id" class="form-control mx-1" onchange="document.getElementById('wattage').value=this.options[this.selectedIndex].getAttribute('data-wattage')">
                <option value="0">Select or enter wattage</option>
                <?php foreach($products as $p): ?>
                <option value="<?=$p['product_id']?>" data-wattage="<?=$p['wattage']?>"><?=$p['name']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mr-2">
            <label>Wattage (W)</label>
            <input type="number" name="wattage" id="wattage" class="form-control mx-1" value="" step="0.01" required />
        </div>
        <div class="form-group mr-2">
            <label>Hours/day</label>
            <input type="number" name="hours_used" class="form-control mx-1" step="0.01" required />
        </div>
        <button type="submit" class="btn btn-primary">Calculate</button>
    </form>
    <?php if($result): ?>
    <div class="alert alert-info col-md-6">
        Estimated Energy Used: <b><?=$result['energy']?> kWh</b><br>
        Estimated Cost (@ 12.5 BDT/kWh): <b><?=$result['cost']?> BDT</b>
    </div>
    <?php endif; ?>
    <a href="user_dashboard.php" class="btn btn-secondary mt-2">Back to Dashboard</a>
</div>
</body>
</html>

