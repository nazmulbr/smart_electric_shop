<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}
require_once '../config/db.php';

$isEdit = isset($_GET['edit']);
$message = '';
$w = [
    'warranty_id'=>'','warranty_duration'=>'','purchase_date'=>''
];
// Edit - fetch
if($isEdit) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM Warranty WHERE warranty_id=?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $r = $stmt->get_result();
    if($r && $row=$r->fetch_assoc()) $w=$row;
}
// POST (add/update)
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=intval($_POST['warranty_id']??0);
    $duration=$_POST['warranty_duration']??'';
    $date=$_POST['purchase_date']??'';
    if ($duration && $date) {
        if ($id) {
            $stmt = $conn->prepare('UPDATE Warranty SET warranty_duration=?, purchase_date=? WHERE warranty_id=?');
            $stmt->bind_param('isi',$duration,$date,$id);
            if($stmt->execute()) $message='Warranty updated!';
        } else {
            $stmt = $conn->prepare('INSERT INTO Warranty (warranty_duration, purchase_date) VALUES (?,?)');
            $stmt->bind_param('is',$duration,$date);
            if($stmt->execute()) $message='Warranty added!';
        }
        header('Location: manage_warranty.php'); exit;
    } else {
        $message = 'Fill all fields!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $isEdit ? 'Edit':'Add'?> Warranty - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_warranty.php" class="btn btn-secondary mb-2">Back to Warranties</a>
        <div class="card">
            <div class="card-header">
                <h4><?= $isEdit ? 'Edit':'Add'?> Warranty</h4>
            </div>
            <div class="card-body">
                <?php if($message):?>
                    <div class="alert alert-info"><?=$message?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="warranty_id" value="<?=htmlspecialchars($w['warranty_id'])?>" />
                    <div class="form-group">
                        <label>Warranty Duration (months)</label>
                        <input type="number" name="warranty_duration" value="<?=htmlspecialchars($w['warranty_duration'])?>" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Purchase Date</label>
                        <input type="date" name="purchase_date" value="<?=htmlspecialchars($w['purchase_date'])?>" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-success"><?=$isEdit?'Update':'Add'?> Warranty</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

