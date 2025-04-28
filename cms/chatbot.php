<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = strtolower(trim($_POST['question']));

    // Load FAQ data
    $faq_data = json_decode(file_get_contents('faq.json'), true);

    // Search for a matching question
    $response = "Sorry, I don't have an answer to that. Please contact support.";
    foreach ($faq_data as $faq) {
        if (strpos(strtolower($faq['question']), $user_input) !== false) {
            $response = $faq['answer'];
            break;
        }
    }

    echo json_encode(['answer' => $response]);
}
?>
