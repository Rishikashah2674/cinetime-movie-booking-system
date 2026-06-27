<?php
session_start();
include_once "connection.php";

// Function to convert YouTube watch URL to embed URL
function convertWatchToEmbed($watchUrl) {
    preg_match('/v=([a-zA-Z0-9_-]+)/', $watchUrl, $matches);
    if (!empty($matches[1])) {
        return "https://www.youtube.com/embed/" . $matches[1];
    }
    return $watchUrl; // Return original if not matched
}

// Fetch movies from the database   
$sql = "SELECT * FROM movies";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching movies: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineTime - Home</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #141414; color: white; font-family: 'Open Sans', sans-serif; }
        .navbar { background-color: #1c1c1c; padding: 15px; }
        .card {
            background-color: #1c1c1c;
            color: white;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: scale(1.05);
            background-color: #292929;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.5);
        }       
        .card img {
            height: 400px;
            object-fit: contain;
        }
        .btn-danger, .btn-primary { background-color: red; border: none; }
        .btn-danger:hover, .btn-primary:hover { background-color: #ff1e22; }
        .modal-content { border-radius: 10px; }

        /* Chatbot Styles */
        .chatbot-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: red;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 50px;
            font-size: 18px;
            cursor: pointer;
        }
        .chatbot-container {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 350px;
            background: #1c1c1c;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(255, 0, 0, 0.4);
            display: none;
        }
        .chat-header {
            background: red;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .chat-box {
            height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: #292929;
        }
        .chat-message {
            margin: 5px 0;
            padding: 8px;
            border-radius: 8px;
            max-width: 80%;
        }
        .user-message {
            background: red;
            color: white;
            text-align: right;
            margin-left: auto;
        }
        .bot-message {
            background: #444;
            color: white;
            text-align: left;
        }
        .chat-footer {
            display: flex;
            padding: 10px;
            background: #222;
        }
        .chat-footer input {
            flex: 1;
            padding: 5px;
            border: none;
            border-radius: 5px;
        }
        .chat-footer button {
            background: red;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-left: 5px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a href="index.php" class="navbar-brand fw-bold">🎟 CineTime</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="about.php" class="nav-link">About Us</a></li>
                    <li class="nav-item"><a href="contact.php" class="nav-link">Contact Us</a></li>
                    <li class="nav-item"><a href="login.php" class="nav-link">Login</a></li>
                    <li class="nav-item"><a href="register.php" class="nav-link">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Movie Listings -->
    <div class="container py-5 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold mb-0">🎬 Now Showing</h2>
            <a href="wizard.php" class="btn fw-bold px-4 py-2" style="background:#f84464; color:white; border-radius:50px; box-shadow: 0 4px 15px rgba(248, 68, 100, 0.4);">
                <i class="fas fa-magic me-2"></i> Book via Wizard!
            </a>
        </div>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-lg">
                        <img src="<?= htmlspecialchars($row['poster_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']); ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['title']); ?></h5>
                            <?php if(is_null($row['rating'])): ?>
                                <p class="fw-bold text-secondary">Not Rated Yet</p>
                            <?php else: ?>
                                <p class="fw-bold text-warning">⭐ <?= number_format($row['rating'], 1); ?> / 10</p>
                            <?php endif; ?>
                            <p class="card-text"><?= htmlspecialchars($row['description'] ?? 'No description available.'); ?></p>
                            <a href="booking.php?movie_id=<?= $row['movie_id']; ?>" class="btn btn-danger">🎟 Book Now</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Chatbot Button -->
<button class="chatbot-btn" onclick="toggleChatbot()">💬</button>

<!-- Chatbot Modal -->
<div class="chatbot-container" id="chatbot">
    <div class="chat-header">CineTime Chatbot 🤖</div>
    
    <!-- Chatbox -->
    <div class="chat-box" id="chatbox">
        <!-- Greeting Message -->
        <div class="chat-message bot-message">👋 Hello! Welcome to CineTime. How can I help you today?</div>
    </div>

    <!-- Predefined Questions -->
    <div class="chat-questions">
        <button onclick="sendPredefinedMessage('What movies are available?')">🎬 What movies are available?</button>
        <button onclick="sendPredefinedMessage('How can I book a ticket?')">🎟 How can I book a ticket?</button>
        <button onclick="sendPredefinedMessage('What are the show timings?')">⏰ What are the show timings?</button>
    </div>

    <!-- User Input -->
    <div class="chat-footer">
        <input type="text" id="chatInput" placeholder="Type a message...">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
    function toggleChatbot() {
    var chat = document.getElementById("chatbot");
    
    if (chat.style.display === "block") {
        chat.style.display = "none";
    } else {
        chat.style.display = "block";
        
        // Check if greeting already exists
        let chatbox = document.getElementById("chatbox");
        let existingGreeting = document.querySelector(".bot-greeting");
        
        //if (!existingGreeting) {
         //   chatbox.innerHTML += 
           //     '<div class="chat-message bot-message bot-greeting">👋 Hello! Welcome to CineTime. How can I help you today?</div>';
        //}
    }
}


function sendPredefinedMessage(question) {
    document.getElementById("chatbox").innerHTML += 
        '<div class="chat-message user-message">' + question + '</div>';
    fetchChatbotResponse(question);
}

function sendMessage() {
    let input = document.getElementById("chatInput").value.trim();
    if (!input) return;
    
    document.getElementById("chatbox").innerHTML += 
        '<div class="chat-message user-message">' + input + '</div>';
    fetchChatbotResponse(input);
    document.getElementById("chatInput").value = "";
}

function fetchChatbotResponse(message) {
    fetch("chatbot.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "message=" + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("chatbox").innerHTML += 
            '<div class="chat-message bot-message">' + data.reply + '</div>';
    });
}

</script>


</body>
</html>