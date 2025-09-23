<?php
header('Content-Type: application/json');

$exptChat = 60; // Chats are deleted after this many days
$offUser = 500; // User is active in chat if there's a recent message within this many seconds
$outUser = 3000; // User is auto-removed from the list after this many seconds of inactivity
$dir = "./users";
$usersFile = $dir.'/users.txt';
$maxLines = 500;

$action = $_GET['action'] ?? null;
// Use this line to get the JSON from the request body
$input = json_decode(file_get_contents('php://input'), true);

// Check if action is sent via GET or POST with json
if (!$action) { $action = $input['action'] ?? null; }

if (!file_exists($usersFile)) {
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    file_put_contents($usersFile, '');
}

// Delete old chats in days
$cutoff = time() - ($exptChat * 86400);
foreach (glob($dir . "/*.txt") as $file) {
    if ($file == $usersFile) { // Ignore usersFile
    } else if (filemtime($file) < $cutoff) {
        unlink($file);
    }
}

switch ($action) {
    case 'read_users':
        $users = [];
        $chatActivities = [];
        $currentTime = time();
        
        // Step 1: Get the last activity timestamp for each user from chat files
        $chatFiles = glob($dir . "/chat-*.txt");
        foreach ($chatFiles as $file) {
            $baseName = basename($file, '.txt');
            $parts = explode('-', $baseName);
            
            if (count($parts) === 3) {
                $user1_id = (int)$parts[1];
                $user2_id = (int)$parts[2];
                $lastChatTime = filemtime($file);

                // Update last activity time for both users in the chat
                if (!isset($chatActivities[$user1_id]) || $lastChatTime > $chatActivities[$user1_id]) {
                    $chatActivities[$user1_id] = $lastChatTime;
                }
                if (!isset($chatActivities[$user2_id]) || $lastChatTime > $chatActivities[$user2_id]) {
                    $chatActivities[$user2_id] = $lastChatTime;
                }
            }
        }

        // Step 2: Process users from users.txt and determine their status
        if (file_exists($usersFile)) {
            $lines = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $data = explode(',', $line);
                if (count($data) === 5) {
                    $id = (int)$data[0];
                    $last_active_userfile = (int)$data[4];
                    $userInChatLastTime = $chatActivities[$id] ?? 0;
                     
                    // The most recent activity is either from the user file or a chat file
                    $lastActivity = max($last_active_userfile, $userInChatLastTime);

                    // Skip user if their last activity is older than $outUser
                    if (($currentTime - $lastActivity) > $outUser) {
                        continue;
                    }

                    // Determine the status based on activity time
                    $status = 'offline';
                    // Check if the user is currently chatting (active chat within $offUser seconds)
                    if (($currentTime - $userInChatLastTime) < $offUser) {
                        $status = 'in_chat';
                    } 
                    // Otherwise, check if they are online (active in user file within $offUser seconds)
                    elseif (($currentTime - $last_active_userfile) < $offUser) {
                        $status = 'online';
                    }
                    
                    $users[$id] = [
                        'id' => $id,
                        'name' => $data[1],
                        'lat' => (float)$data[2],
                        'lon' => (float)$data[3],
                        'status' => $status,
                    ];
                }
            }
        }
        echo json_encode(array_values($users));
        break;

    case 'read_chat':
        $user1 = $_GET['user1'] ?? null;
        $user2 = $_GET['user2'] ?? null;
        $chatFile = $dir."/chat-{$user1}-{$user2}.txt";
        if (!file_exists($chatFile)) $chatFile = $dir."/chat-{$user2}-{$user1}.txt";
        
        $messages = [];
        if (file_exists($chatFile)) {
            $lines = file($chatFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if ($data) {
                    $messages[] = $data;
                }
            }
        }
        echo json_encode($messages);
        break;

    case 'send_message':
        // This line is correct, so leave it as is
        $user1 = $input['user1'] ?? null;
        $user2 = $input['user2'] ?? null;
        $message = $input['message'] ?? null;
        $chatFile = $dir."/chat-{$user2}-{$user1}.txt";
        if (!file_exists($chatFile)) $chatFile = $dir."/chat-{$user1}-{$user2}.txt";
        
        $newMessage = [
            'sender' => $user1,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($chatFile, json_encode($newMessage) . "\r\n", FILE_APPEND | LOCK_EX);
        echo json_encode(['status' => 'success']);
        break;
 
    case 'create_user':
        $name = $input['name'] ?? null;
        $lat = $input['lat'] ?? null;
        $lon = $input['lon'] ?? null;
        
        if (!$name || !$lat || !$lon) {
            echo json_encode(['status' => 'error', 'message' => 'Name and location must be provided.']);
            exit;
        }

        $currentUsers = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $newId = count($currentUsers) > 0 ? (int)explode(',', end($currentUsers))[0] + 1 : 1;
        $newUser = "{$newId},{$name},{$lat},{$lon},".time();

        $currentUsers[] = $newUser;
        if (count($currentUsers) > $maxLines) {
            $currentUsers = array_slice($currentUsers, count($currentUsers) - $maxLines);
        }
        file_put_contents($usersFile, implode("\r\n", $currentUsers) . "\r\n", LOCK_EX);
        
        echo json_encode(['status' => 'success', 'id' => $newId, 'name' => $name]);
        break;
}
?>
