<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nearby Friends Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f0f2f5;
            color: #333;
        }
        .header {
            background-color: #0084ff;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .content-wrapper {
            width: 100%; /* Set width to a percentage */
            padding-top: 60px;
        }
        .container {
            display: flex;
            height: calc(92vh - 60px);
            overflow: hidden;
        }
        .friends-list {
            width: 30%; /* Set width to a percentage */
            background-color: white;
            overflow-y: auto;
            border-right: 1px solid #e0e0e0;
            padding: 10px;
            min-width: 250px; /* Optional: Set minimum width to prevent it from getting too narrow */
        }
        .chat-container {
            flex: 1; /* Takes all remaining space */
            display: flex;
            flex-direction: column;
        }
        .friend {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .friend:hover {
            background-color: #f0f2f5;
        }
        .friend.active {
            background-color: #e6f2ff;
        }
        .friend-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            color: #555;
        }
        .friend-info {
            flex: 1;
        }
        .friend-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        .friend-distance {
            font-size: 12px;
            color: #666;
        }
        .chat-header {
            padding: 15px;
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex; 
            flex-direction: column;
        }
        .message {
            max-width: 70%;
            padding: 12px 16px;
            margin-bottom: 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        .message.received {
            background-color: #e6f2ff;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .message.sent {
            background-color: #0084ff;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .message-input {
            display: flex;
            padding: 15px;
            background-color: white;
            border-top: 1px solid #e0e0e0;
        }
        .message-input input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            outline: none;
            font-size: 14px;
        }
        .message-input button {
            background-color: #0084ff;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 0 20px;
            margin-left: 10px;
            cursor: pointer;
            font-weight: 600;
        }
        .no-chat-selected {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #999;
            font-size: 18px;
        }
        .location-status {
            font-size: 12px;
            background-color: #f8f9fa;
            padding: 14px 2px;
            text-align: center;
            color: #666;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 20px;
        }
        .login-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-form h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #0084ff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        .btn {
            background-color: #0084ff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            font-weight: 600;
        }
        .btn:hover {
            background-color: #0066cc;
        }
        .notification {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            display: none;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        @media (max-width: 924px) {
            .friends-list {
                width: 35%;
            }
            .message {
                max-width: 75%;
            }
        }
        @media (max-width: 768px) {
            .friends-list {
                width: 40%;
            }
            .message {
                max-width: 60%;
            }
        }
        .friend-avatar.online-status {
            background-color: #4CAF50; /* Green for online */
        }

        .friend-avatar.in-chat-status {
            background-color: #2196F3; /* Blue for in chat */
        }
    </style>
</head>
<body>
    <div id="login-view">
        <div class="header">
            <h1>Nearby Chat</h1> by sourceid
        </div>
        <div class="content-wrapper"><br>
            <div class="login-container">
                <div class="login-form">
                    <h2>Login / Register</h2>
                    <div id="notification" class="notification"></div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" placeholder="Enter your name">
                    </div>
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="text" id="latitude" placeholder="Enter latitude coordinate">
                    </div>
                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="text" id="longitude" placeholder="Enter longitude coordinate">
                    </div>
                    <p class="status" id="status-message">Finding location...</p><br>
                    <button class="btn" onclick="login()">Login</button>
                </div>
            </div><br><center><a href=chatbot.php>Confidant Chatbot</a></center><br><br>
        </div>
    </div>
    
    <div id="app-view" style="display: none;">
        <div class="header">
            <h1>Nearby Chat</h1> by sourceid
            <div style="display: flex; align-items: center;">
                <span id="current-user-name" style="margin-right: 15px;"></span>
                <button class="btn" style="padding: 8px 15px;" onclick="logout()">Logout</button>
            </div>
        </div>
        <div class="content-wrapper">
            <div class="location-status">
                <span id="location-info">Using a simulated location for demonstration.</span>
            </div>
            
            <div class="container">
                <div class="friends-list">
                </div>
                <div class="chat-container">
                    <div class="no-chat-selected">
                        Select a friend to start a conversation
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // Ensure the DOM is fully loaded before running the script
    document.addEventListener('DOMContentLoaded', function() {
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const statusMessage = document.getElementById('status-message');

        // Function to handle success when getting location
        function success(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            latitudeInput.value = lat;
            longitudeInput.value = lon;
             
            statusMessage.textContent = 'Location found successfully!';
            statusMessage.classList.add('success');
            statusMessage.classList.remove('error');
        }

        // Function to handle errors when getting location
        function error(err) {
            let message = '';
            switch(err.code) {
                case err.PERMISSION_DENIED:
                    message = 'You denied the Geolocation request. Please allow location access in your browser settings.';
                    break;
                case err.POSITION_UNAVAILABLE:
                    message = 'Location information is unavailable.';
                    break;
                case err.TIMEOUT:
                    message = 'The request to get location timed out.';
                    break;
                case err.UNKNOWN_ERROR:
                    message = 'An unknown error occurred.';
                    break;
            }
            statusMessage.textContent = 'Failed to get location: ' + message;
            statusMessage.classList.add('error');
            statusMessage.classList.remove('success');
        }

        // Check if the browser supports the Geolocation API
        if ('geolocation' in navigator) {
            // Request the user's location
            navigator.geolocation.getCurrentPosition(success, error);
        } else {
            // If Geolocation is not supported
            statusMessage.textContent = 'Geolocation is not supported by your browser.';
            statusMessage.classList.add('error');
            statusMessage.classList.remove('success');
        }
    });

    // Global variables
    let currentUser = null;
    let friends = [];
    let selectedFriend = null;
    let chatInterval = null;
    let friendsInterval = null; // New variable for the friends list interval
    
    // Function to display a notification
    function showNotification(message, isSuccess = false) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.style.display = 'block';
    
        if (isSuccess) {
            notification.className = 'notification success';
        } else {
            notification.className = 'notification error';
        }
    
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
    
    // Function to log in or register a user
    function login() {
        const username = document.getElementById('username').value;
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
    
        if (!username || !latitude || !longitude) {
            showNotification('Username, latitude, and longitude must be filled in!');
            return;
        }
    
        if (isNaN(parseFloat(latitude)) || isNaN(parseFloat(longitude))) {
            showNotification('Latitude and longitude must be numbers!');
            return;
        }
    
        const data = {
            action: 'create_user',
            name: username,
            lat: parseFloat(latitude),
            lon: parseFloat(longitude)
        };
    
        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                currentUser = {
                    id: result.id,
                    name: result.name,
                    location: { 
                        lat: parseFloat(latitude), 
                        lng: parseFloat(longitude) 
                    }
                };
    
                // Save user info to localStorage
                localStorage.setItem('currentUser', JSON.stringify(currentUser));
    
                // Display the application
                document.getElementById('login-view').style.display = 'none';
                document.getElementById('app-view').style.display = 'flex';
                document.getElementById('current-user-name').textContent = currentUser.name;
                document.getElementById('location-info').textContent = 
                    `Your Location: ${currentUser.location.lat}, ${currentUser.location.lng}`;
    
                // Load the friends list
                loadFriends();
    
                // Start polling for chat updates
                if (chatInterval) clearInterval(chatInterval);
                chatInterval = setInterval(loadChat, 2000);

                // Start polling for friends list updates
                if (friendsInterval) clearInterval(friendsInterval);
                friendsInterval = setInterval(loadFriends, 5000);
            } else {
                showNotification('Login failed: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while contacting the server.');
        });
    }
    
    // Function to log out
    function logout() {
        currentUser = null;
        selectedFriend = null;
        localStorage.removeItem('currentUser');
    
        document.getElementById('login-view').style.display = 'flex';
        document.getElementById('app-view').style.display = 'none';
    
        if (chatInterval) {
            clearInterval(chatInterval);
            chatInterval = null;
        }
        if (friendsInterval) {
            clearInterval(friendsInterval);
            friendsInterval = null;
        }
    }
    
    // Function to load the friends list
    function loadFriends() {
        if (!currentUser) return;
    
        fetch('api.php?action=read_users&user1=${currentUser.id}')
        .then(response => response.json())
        .then(users => {
            friends = users.filter(user => user.id != currentUser.id);
            renderFriendsList();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load user list.');
        });
    }
    
    // Function to calculate the distance between two coordinate points
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in km
        const dLat = deg2rad(lat2 - lat1);
        const dLng = deg2rad(lng2 - lng1);
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
        const distance = R * c; // Distance in km
        return distance.toFixed(1);
    }
    
    function deg2rad(deg) {
        return deg * (Math.PI/180);
    }
    
    // Function to render the friends list
    function renderFriendsList() {
        const friendsList = document.querySelector('.friends-list');
        friendsList.innerHTML = '';

        friends.sort((a, b) => {
            const distA = calculateDistance(
                currentUser.location.lat, 
                currentUser.location.lng,
                parseFloat(a.lat),
                parseFloat(a.lon)
            );

            const distB = calculateDistance(
                currentUser.location.lat, 
                currentUser.location.lng,
                parseFloat(b.lat),
                parseFloat(b.lon)
            );

            return distA - distB;
        });

        friends.forEach(friend => {
            const distance = calculateDistance(
                currentUser.location.lat, 
                currentUser.location.lng,
                parseFloat(friend.lat),
                parseFloat(friend.lon)
            );

            const avatar = friend.name.split(' ').map(n => n[0]).join('').toUpperCase();

            // Determine the CSS class based on status
            const statusClass = friend.status === 'online' ? 'online-status' : 
                                friend.status === 'in_chat' ? 'in-chat-status' : '';

            const friendElement = document.createElement('div');
            friendElement.className = 'friend';
            friendElement.dataset.id = friend.id;

            friendElement.innerHTML = `
            <div class="friend-avatar ${statusClass}">${avatar}</div>
            <div class="friend-info">
                <div class="friend-name">${friend.name}</div>
                <div class="friend-distance">${distance} km</div>
                <div class="friend-status" style="font-size: 10px; color: #666;">${friend.status}</div>
            </div>
            `;

            friendElement.addEventListener('click', () => {
                selectFriend(friend.id);
            });

            friendsList.appendChild(friendElement);
        });
    }
    
    // Function to select a friend and display the chat
    function selectFriend(friendId) {
        // Highlight the selected friend
        document.querySelectorAll('.friend').forEach(friend => {
            friend.classList.remove('active');
            if (parseInt(friend.dataset.id) === friendId) {
                friend.classList.add('active');
            }
        });
    
        // Display the chat container
        const chatContainer = document.querySelector('.chat-container');
        chatContainer.innerHTML = '';
    
        const friend = friends.find(f => f.id == friendId);
        selectedFriend = friend;
    
        const distance = calculateDistance(
            currentUser.location.lat, 
            currentUser.location.lng,
            parseFloat(friend.lat),
            parseFloat(friend.lon)
        );
    
        const avatar = friend.name.split(' ').map(n => n[0]).join('').toUpperCase();
    
        // Create the chat header
        const chatHeader = document.createElement('div');
        chatHeader.className = 'chat-header';
        chatHeader.innerHTML = `
            <div class="friend-avatar">${avatar}</div>
            <div class="friend-info">
                <div class="friend-name">${friend.name}</div>
                <div class="friend-distance">${distance} km</div>
            </div>
        `;
    
        // Create the message area
        const chatMessages = document.createElement('div');
        chatMessages.className = 'chat-messages';
    
        // Create the message input
        const messageInput = document.createElement('div');
        messageInput.className = 'message-input';
        messageInput.innerHTML = `
            <input type="text" placeholder="Type a message...">
            <button>Send</button>
        `;
    
        const inputField = messageInput.querySelector('input');
        const sendButton = messageInput.querySelector('button');
    
        // Function to send a message
        function sendMessage() {
            const text = inputField.value.trim();
            if (text && selectedFriend) {
                const data = {
                    action: 'send_message',
                    user1: currentUser.id,
                    user2: selectedFriend.id,
                    message: text
                };
    
                fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        inputField.value = '';
                        loadChat(); // Reload the chat
                    } else {
                        showNotification('Failed to send message.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while sending the message.');
                });
            }
        }
    
        sendButton.addEventListener('click', sendMessage);
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    
        // Add elements to the chat container
        chatContainer.appendChild(chatHeader);
        chatContainer.appendChild(chatMessages);
        chatContainer.appendChild(messageInput);
    
        // Load messages
        loadChat();
    }
    
    // Function to load the chat
    function loadChat() {
        if (!currentUser || !selectedFriend) return;
    
        fetch(`api.php?action=read_chat&user1=${currentUser.id}&user2=${selectedFriend.id}`)
        .then(response => response.json())
        .then(messages => {
            const chatMessages = document.querySelector('.chat-messages');
            chatMessages.innerHTML = '';
             
            if (messages.length === 0) {
                chatMessages.innerHTML = '<div class="no-chat-selected">No messages yet. Start a conversation now!</div>';
                return;
            }
            
            messages.forEach(msg => {
                const messageElement = document.createElement('div');
                messageElement.className = `message ${msg.sender == currentUser.id ? 'sent' : 'received'}`;
                 
                const time = new Date(msg.timestamp).toLocaleTimeString([], { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                 
                messageElement.innerHTML = `
                    <div>${msg.message}</div>
                    <div style="font-size: 0.7em; text-align: right; margin-top: 5px;">${time}</div>
                `;
                 
                chatMessages.appendChild(messageElement);
            });
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Initialize the application
    document.addEventListener('DOMContentLoaded', () => {
        // Check if the user is already logged in
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            currentUser = JSON.parse(savedUser);
            
            // Display the application
            document.getElementById('login-view').style.display = 'none';
            document.getElementById('app-view').style.display = 'flex';
            document.getElementById('current-user-name').textContent = currentUser.name;
            document.getElementById('location-info').textContent = 
                `Your Location: ${currentUser.location.lat}, ${currentUser.location.lng}`;
            
            // Load the friends list
            loadFriends();
            
            // Start polling for chat updates
            if (chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(loadChat, 2000);

            // Start polling for friends list updates
            if (friendsInterval) clearInterval(friendsInterval);
            friendsInterval = setInterval(loadFriends, 5000);
        }
    })
</script>

</body>
</html>
