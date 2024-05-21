<?php
session_start();
include 'connect.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$explanation = "";

// Call the Flask application using cURL for topic explanation only when the page loads
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST['question'])) {
    if (isset($_POST['topic']) && isset($_POST['chapter']) && isset($_POST['path']) && isset($_POST['difficulty'])) {
        $_SESSION['selected_topic'] = $_POST['topic'];
        $_SESSION['selected_chapter'] = $_POST['chapter'];
        $path = $_POST['path'];
        $difficulty = $_POST['difficulty'];

        // Convert difficulty to 1, 2, or 3
        if ($difficulty === "beginner") {
            $difficulty = 1;
        } elseif ($difficulty === "intermediate") {
            $difficulty = 2;
        } elseif ($difficulty === "advanced") {
            $difficulty = 3;
        }

        // Call the Flask application using cURL for topic explanation
        $url_topic = "http://127.0.0.1:5000/topic_explanation/" . urlencode($_SESSION['selected_topic']) . "/" . urlencode($difficulty) . "/" . urlencode($path);
        $curl_topic = curl_init();
        curl_setopt_array($curl_topic, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url_topic
        ));
        $output_topic = curl_exec($curl_topic);
        curl_close($curl_topic);
        $explanation = $output_topic;
    }
}

// Check if session variables are set
if (isset($_SESSION['selected_topic']) && isset($_SESSION['selected_chapter'])) {
    $selected_topic = $_SESSION['selected_topic'];
    $selected_chapter = $_SESSION['selected_chapter'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question']) && !empty($_POST['question'])) {
    // If a question is submitted, call the Flask application using cURL for handling question
    $question = $_POST['question'];
    $path = $_POST['path']; // Added path variable
    $url_question = "http://127.0.0.1:5000/handle_question"; // Updated URL
    $curl_question = curl_init();
    curl_setopt_array($curl_question, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url_question,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode(array('question' => $question, 'chapter_path' => $path)), // Send question and path
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    ));
    $response = curl_exec($curl_question);
    curl_close($curl_question);
    // Display the question-answer pairs
    $qa_pairs = json_decode($response, true);
    foreach ($qa_pairs as $index => $qa_pair) {
        $explanation .= "<br><br>" . $index . ". " . $qa_pair[0] . "<br><br>- " . $qa_pair[1];
    }
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Interlearn - Topic Explanation</title>
    <link rel="stylesheet" href="display_explanation.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
</head>

<body>
    <div class="banner">
        <div class="container">
            <div class="title">Topic Explanation: <?php echo $selected_topic; ?></div>
            <div class="content">
                <?php if (!empty($explanation)) : ?>
                    <h2>Chapter: <?php echo $selected_chapter; ?></h2><br>
                    <h3>Transcript and Textual Question Responses: </h3>
                    <div class="explanation"><p><?php echo $explanation; ?></p></div>
                
                    <?php if (file_exists('static/abcd.mp4')): ?>
                        <video width="640" height="480" controls autoplay>
                            <source src="static/abcd.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                    <br>
                    <div class="input-container">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="topic" value="<?php echo htmlspecialchars($selected_topic); ?>">
                            <input type="hidden" name="chapter" value="<?php echo htmlspecialchars($selected_chapter); ?>">
                            <input type="hidden" name="path" value="<?php echo isset($path) ? htmlspecialchars($path) : ''; ?>">
                            <input type="hidden" name="difficulty" value="<?php echo isset($difficulty) ? htmlspecialchars($difficulty) : ''; ?>">
                            <input type="text" name="question" placeholder="Enter your question">
                            <input type="submit" value="ASK">
                            <button type="button" id="recordButton">Speak</button>
                        </form>
                    </div>
                <?php else : ?>
                    <video width="640" height="480" controls autoplay>
                        <source src="static/abcd.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="input-container">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="text" name="question" placeholder="Enter your question">
                            <input type="submit" value="ASK">
                            <button type="button" id="recordButton">Speak</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <h5>Speech Question Responses:</h5>
        <div id="transcriptionResult"></div>
    </div>

    <div class="back">
        <a href="topicby.php?chapter=<?php echo urlencode($selected_chapter); ?>">Back to Topics</a>
    </div> 
    <!-- Include the recorder.js library -->
    <script src="recorder.js"></script>
    <script>
        // Initialize variables for audio recording
        let audioContext;
        let recorder;

        // Function to start recording audio
        function startRecording() {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(function(stream) {
                    audioContext = new AudioContext();
                    const input = audioContext.createMediaStreamSource(stream);
                    recorder = new Recorder(input);
                    recorder.record();
                })
                .catch(function(err) {
                    console.error('Error recording audio: ' + err);
                });
        }

        // Function to stop recording and save audio
        function stopRecording() {
            recorder.stop();
            recorder.exportWAV(function(blob) {
                const formData = new FormData();
                formData.append('audio', blob, 'new_audio.wav');
                
                fetch('http://127.0.0.1:5000/save_audio', { // Update the URL
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Audio saved successfully');
                    // Now, you can call the function to transcribe the audio
                    fetch('http://127.0.0.1:5000/transcribe_audio') // Update the URL
                        .then(response => response.text())
                        .then(transcript => {
                            console.log(transcript);
                            // Do something with the transcript
                            // Assuming the transcript is displayed on this page, you can update the HTML content here
                            document.getElementById('transcriptionResult').innerHTML = transcript;

                            // After receiving the transcript, call handle_question1
                            fetch('http://127.0.0.1:5000/handle_question1', { // Update the URL
                                method: 'POST',
                                body: JSON.stringify({ 'question': transcript, 'chapter_path': '<?php echo isset($path) ? htmlspecialchars($path) : ''; ?>' }), // Send transcribed text as question
                                headers: { 'Content-Type': 'application/json' }
                            })
                            .then(response => response.json())
                            .then(qa_pairs_handle_question1 => {
                                // Clear previous content
                                document.getElementById('transcriptionResult').innerHTML = '';
                            
                                // Display the question-answer pairs
                                for (const index in qa_pairs_handle_question1) {
                                    if (qa_pairs_handle_question1.hasOwnProperty(index)) {
                                        const qa_pair = qa_pairs_handle_question1[index];
                                        document.getElementById('transcriptionResult').innerHTML += "<br><br>" + (parseInt(index) + 1) + ". " + qa_pair[0] + "<br><br>- " + qa_pair[1];
                                    }
                                }
                            })
                            .catch(error => console.error('Error handling question:', error));
                        })
                        .catch(error => console.error('Error transcribing audio:', error));
                })
                .catch(error => console.error('Error saving audio:', error));
            });
        }

        // Event listener for record button
        document.getElementById('recordButton').addEventListener('click', function() {
            if (recorder && recorder.recording) {
                stopRecording();
                this.textContent = 'Speak';
                recorder.clear(); // Clear the recorder for next recording
            } else {
                startRecording();
                this.textContent = 'Stop';
            }
        });
    </script>

    <!-- Container to display transcribed text -->
    
</body>

</html>
