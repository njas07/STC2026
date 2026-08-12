<?php
session_start();

if (isset($_SESSION["user_id"]) && ($_SESSION["role"] ?? "") === "admin") {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Admin - STC 2026</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        .login-card h1 {
            margin: 0 0 8px;
            color: #1f2937;
        }

        .login-card p {
            margin: 0 0 25px;
            color: #6b7280;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #374151;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        #message {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <h1>Login Admin</h1>

        <p>STC 2026 Competition</p>

        <form id="loginForm">

            <label for="email">
                Username
            </label>

            <input
                type="text"
                id="email"
                name="email"
                placeholder="NJas"
                required>

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                required>

            <button type="submit">
                Login
            </button>

        </form>

        <div id="message"></div>

    </div>

    <script>
        const form = document.getElementById("loginForm");
        const message = document.getElementById("message");

        form.addEventListener("submit", async function(e) {

            e.preventDefault();

            message.textContent = "Memproses login...";

            const formData = new FormData(form);

            try {

                const response = await fetch("../auth/login.php", {
                    method: "POST",
                    body: formData,
                    credentials: "include"
                });

                const data = await response.json();

                if (!data.success) {

                    message.textContent = data.message;
                    return;

                }

                if (data.user.role !== "admin") {

                    message.textContent = "Akun ini bukan akun admin.";
                    return;

                }

                message.textContent = "Login berhasil. Mengalihkan...";

                setTimeout(() => {
                    window.location.href = "dashboard.php";
                }, 500);

            } catch (error) {

                console.error(error);

                message.textContent =
                    "Terjadi kesalahan saat menghubungkan ke server.";

            }

        });
    </script>

</body>

</html>