<?php
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userMessage = strtolower(trim($_POST["message"]));
    $response = "I'm sorry, I didn't understand that.";

    // Predefined responses
    $responses = [
        "what movies are available?" => "Currently, we have movies like Chhava, Umbarro, Deva, The Brutalist and more. Check our homepage for details!",
        "how can i book a ticket?" => "You can book a ticket by selecting a movie and clicking the '🎟 Book Now' button.",
        "what are the show timings?" => "Show timings vary for each movie. Please check the movie details page for exact times.",
        "hi" => "👋 Hello! Welcome to CineTime. How can I assist you today?",
        "hello" => "Hey there! 😊 Welcome to CineTime. What would you like to know?",
        "hey" => "Hi! 🎬 Looking for movie recommendations or booking details?",
    ];

    // Check if the user's message matches a predefined response
    if (array_key_exists($userMessage, $responses)) {
        $response = $responses[$userMessage];
    }

    echo json_encode(["reply" => $response]);
}
?>