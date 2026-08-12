<?php
session_start();

require_once "config/database.php";

/* ===============================
   PROSES TEST
================================ */

$result = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_id = $_POST["payment_id"] ?? null;
    $status = $_POST["status"] ?? null;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://localhost/stc2026-backend/admin/update_payment_status.php");
    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        "payment_id" => $payment_id,
        "status" => $status
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Kirim cookie session yang sedang aktif
    if (isset($_COOKIE[session_name()])) {
        curl_setopt(
            $ch,
            CURLOPT_COOKIE,
            session_name() . "=" . $_COOKIE[session_name()]
        );
    }

    $response = curl_exec($ch);

    if ($response === false) {
        $result = [
            "success" => false,
            "message" => "cURL Error: " . curl_error($ch)
        ];
    } else {
        $result = json_decode($response, true);

        if ($result === null) {
            $result = [
                "success" => false,
                "message" => "Response tidak valid.",
                "response" => $response
            ];
        }
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Test Update Payment</title>
</head>

<body>

<h2>Test Update Status Pembayaran</h2>

<form method="POST">

    <label>Payment ID:</label>
    <br>

    <input
        type="number"
        name="payment_id"
        value="1"
        required
    >

    <br><br>

    <label>Status:</label>
    <br>

    <select name="status" required>

        <option value="MENUNGGU_VERIFIKASI">
            MENUNGGU_VERIFIKASI
        </option>

        <option value="VALID">
            VALID
        </option>

        <option value="TIDAK_VALID">
            TIDAK_VALID
        </option>

    </select>

    <br><br>

    <button type="submit">
        Update Status Pembayaran
    </button>

</form>

<?php if ($result !== null): ?>

    <hr>

    <h3>Hasil:</h3>

    <pre><?php
        echo json_encode(
            $result,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    ?></pre>

<?php endif; ?>

</body>
</html>