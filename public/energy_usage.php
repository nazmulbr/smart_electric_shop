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
    $quantity = intval($_POST['quantity'] ?? 1);
    $voltage = floatval($_POST['voltage'] ?? 220);
    $consumer_type = $_POST['consumer_type'] ?? 'residential';
    // Determine rate: residential/business or custom
    if (isset($default_rates[$consumer_type])) {
        $rate = $default_rates[$consumer_type];
    } else {
        $rate = floatval($_POST['rate'] ?? 0);
    }
    if ($wattage > 0 && $hours_used > 0 && $quantity > 0) {
        $total_watt = $wattage * $quantity; // W
        $energy_daily = ($total_watt * $hours_used) / 1000; // kWh per day
        $energy_monthly = $energy_daily * 30; // approximate
        $energy_yearly = $energy_daily * 365;
        $cost_daily = $energy_daily * $rate;
        $cost_monthly = $energy_monthly * $rate;
        $cost_yearly = $energy_yearly * $rate;
        // Compute current (amps) and suggest breaker size (next standard)
        $amps = $voltage > 0 ? ($total_watt / $voltage) : 0;
        $breaker_sizes = [6, 10, 16, 20, 25, 32, 40, 50, 63, 80, 100, 125, 150];
        $suggested_breaker = null;
        foreach ($breaker_sizes as $b) {
            if ($amps <= $b) {
                $suggested_breaker = $b;
                break;
            }
        }
        if ($suggested_breaker === null) $suggested_breaker = end($breaker_sizes);

        $result = [
            'total_watt' => $total_watt,
            'energy_daily' => round($energy_daily, 3),
            'energy_monthly' => round($energy_monthly, 2),
            'energy_yearly' => round($energy_yearly, 2),
            'cost_daily' => round($cost_daily, 2),
            'cost_monthly' => round($cost_monthly, 2),
            'cost_yearly' => round($cost_yearly, 2),
            'wattage' => $wattage,
            'hours_used' => $hours_used,
            'rate' => $rate,
            'consumer_type' => $consumer_type,
            'quantity' => $quantity,
            'voltage' => $voltage,
            'amps' => round($amps, 2),
            'suggested_breaker' => $suggested_breaker
        ];
    } else {
        $message = 'Enter valid wattage, quantity and usage hours!';
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
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control mx-1" value="1" min="1" />
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
                <label>Voltage (V)</label>
                <input type="number" name="voltage" id="voltage" class="form-control mx-1" value="220" step="1" />
            </div>
            <div class="form-group mr-2">
                <label>Hours/day</label>
                <input type="number" name="hours_used" class="form-control mx-1" step="0.01" required />
            </div>
            <button type="submit" class="btn btn-primary">Calculate</button>
        </form>
        <?php if ($result): ?>
            <div class="alert alert-info col-md-8">
                <strong>Total Wattage:</strong> <?= htmlspecialchars($result['total_watt']) ?> W<br>
                <strong>Quantity:</strong> <?= htmlspecialchars($result['quantity']) ?> units<br>
                <strong>Hours/day:</strong> <?= htmlspecialchars($result['hours_used']) ?> hrs<br>
                <strong>Estimated Daily Energy:</strong> <b><?= $result['energy_daily'] ?> kWh</b><br>
                <strong>Estimated Monthly Energy:</strong> <b><?= $result['energy_monthly'] ?> kWh</b><br>
                <strong>Estimated Yearly Energy:</strong> <b><?= $result['energy_yearly'] ?> kWh</b><br>
                <strong>Daily Cost:</strong> <b><?= $result['cost_daily'] ?> BDT</b><br>
                <strong>Monthly Cost:</strong> <b><?= $result['cost_monthly'] ?> BDT</b><br>
                <strong>Yearly Cost:</strong> <b><?= $result['cost_yearly'] ?> BDT</b><br>
                <strong>Estimated Current:</strong> <b><?= $result['amps'] ?> A</b> at <?= htmlspecialchars($result['voltage']) ?> V<br>
                <strong>Suggested Breaker Size:</strong> <b><?= $result['suggested_breaker'] ?> A</b>
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