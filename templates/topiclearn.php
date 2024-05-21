<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['chapter'])) {
    header("Location: courses.php");
    exit;
}

$selected_chapter = $_GET['chapter'];
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Interlearn - Topic Learning</title>
    <link rel="stylesheet" href="topiclearn1.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="banner">
    <div class="container">
        <div class="title">Select how you want to learn chapter :<?php echo $selected_chapter; ?></div>
        <div class="content">
            <div class="button-section">
                <button class="btn" onclick="showLoader(); window.location.href='topicby.php?chapter=<?php echo urlencode($selected_chapter); ?>&action=topic';">
                    <h2>Topic by Topic Learning</h2>
                    <ul>
                        <li>This method uses Retreival Augmented Generation(RAG) to get the important topics from the chapter.</li>
                        <li>You can learn each topic in your preferred difficulty or depth.</li>
                        <li>You will be taught by a video bot.</li>
                        <li>You can ask further questions for better understanding.</li>
                        <li>Interact and learn with the bot.</li>
                    </ul>
                </button>
                <button class="btn" onclick="showLoader(); window.location.href='chatwithchap.php?chapter=<?php echo urlencode($selected_chapter); ?>&action=qa';">
                    <h2>Chat with your Chapter</h2>
                    <ul>
                        <li>You can question the chapter.</li>
                        <li>You can have your doubts cleared, all the answers will be based on the chapter.</li>
                       <li>You can also get answers to your assignments , excercises , etc.</li> 
                        <li>This will also be done by using Retreival Augmented Generation(RAG).</li>
                    </ul>
                </button>
                <div id="loading" style="display:none;">
        <div class="loader"></div>
        <p>This may take a few minutes....</p>
    </div>
            </div>
        </div>
    </div>
    </div>
   
</body>
<script>
function showLoader() {
    document.getElementById("loading").style.display = "block";
}

window.onload = function() {
    document.getElementById("loading").style.display = "none";
};
</script>

</html>
