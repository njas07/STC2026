<?php
/* =========================================================
   STC 2026 — GUARD
   Satu tempat untuk session, cek login, dan cek admin.

   Pakai di setiap endpoint:
       require_once "../config/guard.php";
       $admin = require_admin();      // atau require_login()
   ========================================================= */

require_once __DIR__ . "/database.php";

/* ---------------------------------------------------------
   SESSION
   Cookie tidak bisa dibaca JavaScript (httponly) dan tidak
   ikut terkirim pada request lintas situs (samesite Lax),
   sehingga menutup pencurian session lewat XSS/CSRF dasar.
   --------------------------------------------------------- */

function stc_session_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https =
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $https,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

/* ---------------------------------------------------------
   RESPONS JSON
   --------------------------------------------------------- */

function stc_json($data, $status = 200)
{
    if (!headers_sent()) {
        http_response_code($status);
        header("Content-Type: application/json; charset=UTF-8");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("Referrer-Policy: same-origin");
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function stc_fail($message, $status = 400)
{
    stc_json(["success" => false, "message" => $message], $status);
}

/* ---------------------------------------------------------
   WAJIB LOGIN
   Data user selalu dibaca ulang dari database, tidak dari
   session, supaya perubahan role langsung berlaku dan
   session lama tidak bisa dipakai sebagai admin.
   --------------------------------------------------------- */

function require_login($as_json = true)
{
    global $conn;

    stc_session_start();

    $user_id = $_SESSION["user_id"] ?? null;

    if (!$user_id || !ctype_digit((string) $user_id)) {
        if ($as_json) {
            stc_fail("Silakan login terlebih dahulu.", 401);
        }
        http_response_code(401);
        die("Silakan login terlebih dahulu.");
    }

    $stmt = $conn->prepare("
        SELECT id, nama_lengkap, email, role
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        /* User sudah dihapus tapi session masih ada */
        $_SESSION = [];
        session_destroy();

        if ($as_json) {
            stc_fail("Sesi tidak valid. Silakan login ulang.", 401);
        }
        http_response_code(401);
        die("Sesi tidak valid. Silakan login ulang.");
    }

    return $user;
}

/* ---------------------------------------------------------
   WAJIB ADMIN
   --------------------------------------------------------- */

function require_admin($as_json = true)
{
    $user = require_login($as_json);

    if ($user["role"] !== "admin") {
        if ($as_json) {
            stc_fail("Akses hanya untuk admin.", 403);
        }
        http_response_code(403);
        die("Akses hanya untuk admin.");
    }

    return $user;
}

/* ---------------------------------------------------------
   WAJIB METHOD
   --------------------------------------------------------- */

function require_post()
{
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        stc_fail("Method tidak diperbolehkan. Gunakan POST.", 405);
    }
}

/* ---------------------------------------------------------
   PEMBATAS PERCOBAAN LOGIN

   Disimpan sebagai file, bukan di $_SESSION. Kalau memakai
   session, penyerang tinggal menghapus cookie untuk mereset
   hitungan — jadi tidak menahan apa pun.
   --------------------------------------------------------- */

function throttle_dir()
{
    $dir = __DIR__ . "/../_arsip_lokal/throttle";

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return $dir;
}

function throttle_file($key)
{
    /* $key sudah berupa hash heksadesimal, tapi tetap disaring
       supaya tidak bisa dipakai keluar folder (path traversal). */
    $aman = preg_replace('/[^a-f0-9]/i', '', (string) $key);

    return throttle_dir() . "/" . substr($aman, 0, 40) . ".json";
}

function throttle_baca($key, $window)
{
    $file = throttle_file($key);
    $now = time();

    if (!is_file($file)) {
        return ["count" => 0, "start" => $now];
    }

    $box = json_decode((string) @file_get_contents($file), true);

    if (!is_array($box) || !isset($box["count"], $box["start"])) {
        return ["count" => 0, "start" => $now];
    }

    if ($now - (int) $box["start"] > $window) {
        return ["count" => 0, "start" => $now];
    }

    return ["count" => (int) $box["count"], "start" => (int) $box["start"]];
}

function login_throttle_check($key, $max = 8, $window = 900)
{
    $box = throttle_baca($key, $window);

    if ($box["count"] >= $max) {
        $sisa = $window - (time() - $box["start"]);

        stc_fail(
            "Terlalu banyak percobaan login. Coba lagi dalam "
            . max(1, ceil($sisa / 60)) . " menit.",
            429
        );
    }
}

function login_throttle_fail($key, $window = 900)
{
    $box = throttle_baca($key, $window);
    $box["count"]++;

    @file_put_contents(
        throttle_file($key),
        json_encode($box),
        LOCK_EX
    );
}

function login_throttle_reset($key)
{
    $file = throttle_file($key);

    if (is_file($file)) {
        @unlink($file);
    }
}
