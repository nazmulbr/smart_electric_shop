<?php
$page_title = 'Energy Usage - Smart Electric Shop';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$products = [];
$pq = $conn->query('SELECT p.product_id, p.name, eu.wattage FROM Product p LEFT JOIN EnergyUsage eu ON p.product_id = eu.product_id');
if ($pq) {
    $products = $pq->fetch_all(MYSQLI_ASSOC);
}
$result = null;
$message = '';
// Default rates (BDT per kWh)
$default_rates = [
    'residential' => 7.74,
    'business' => 12.39
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $hours_used = floatval($_POST['hours_used'] ?? 0);
    $wattage = floatval($_POST['wattage'] ?? 0);
    $consumer_type = $_POST['consumer_type'] ?? 'residential';
    // Determine rate: residential/business or custom
    if (isset($default_rates[$consumer_type])) {
        $rate = $default_rates[$consumer_type];
    } else {
        $rate = floatval($_POST['rate'] ?? 0);
    }
    if ($wattage && $hours_used) {
        $energy = ($wattage * $hours_used) / 1000; // kWh used
        $cost = $energy * $rate;
        $result = [
            'energy' => round($energy, 2),
            'cost' => round($cost, 2),
            'wattage' => $wattage,
            'hours_used' => $hours_used,
            'rate' => $rate,
            'consumer_type' => $consumer_type
        ];
    } else {
        $message = 'Enter valid wattage and usage hours!';
    }
}
?>
<style>
    :root {
        --primary-color: #007bff;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #222;
    }

    .energy-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        margin-top: 24px;
    }
</style>

<div class="container">
    <div class="energy-card">
        <h4>Estimate Energy Consumption</h4>
        <?php if ($message): ?><div class="alert alert-warning"><?= $message ?></div><?php endif; ?>
        <form method="POST" class="form-inline mb-4">
            <div class="form-group mr-2">
                <label>Product</label>
                <select name="product_id" class="form-control mx-1" onchange="document.getElementById('wattage').value=this.options[this.selectedIndex].getAttribute('data-wattage')">
                    <option value="0">Select or enter wattage</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['product_id'] ?>" data-wattage="<?= $p['wattage'] ?>"><?= $p['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mr-2">
                <label>Consumer Type</label>
                <select name="consumer_type" id="consumer_type" class="form-control mx-1" onchange="onConsumerChange()">
                    <option value="residential">Residential (default <?= $default_rates['residential'] ?> BDT/kWh)</option>
                    <option value="business">Business (default <?= $default_rates['business'] ?> BDT/kWh)</option>
                    <option value="custom">Custom rate</option>
                </select>
            </div>
            <div class="form-group mr-2">
                <label>Rate (BDT/kWh)</label>
                <input type="number" name="rate" id="rate" class="form-control mx-1" value="<?= htmlspecialchars($_POST['rate'] ?? $default_rates['residential']) ?>" step="0.01" />
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
        <?php if ($result): ?>
            <div class="alert alert-info col-md-6">
                Estimated Energy Used: <b><?= $result['energy'] ?> kWh</b><br>
                Consumer Type: <b><?= htmlspecialchars(ucfirst($result['consumer_type'])) ?></b><br>
                Rate Used: <b><?= $result['rate'] ?> BDT/kWh</b><br>
                Estimated Cost: <b><?= $result['cost'] ?> BDT</b>
            </div>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</div>
<script>
    // Sync rate input with consumer type selection
    const defaultRates = <?= json_encode($default_rates) ?>;

    function onConsumerChange() {
        const sel = document.getElementById('consumer_type');
        const rateInput = document.getElementById('rate');
        if (!sel || !rateInput) return;
        if (sel.value === 'custom') {
            rateInput.disabled = false;
            rateInput.focus();
        } else if (defaultRates.hasOwnProperty(sel.value)) {
            rateInput.value = defaultRates[sel.value];
            rateInput.disabled = true;
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        onConsumerChange();
    });
</script>
</body>

</html>