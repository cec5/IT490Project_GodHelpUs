<?php
// Include necessary files (header, client RabbitMQ)
include 'header.php';
require 'client_rmq_db.php';

// Helper function to get the JWT token from the cookie
function getJwtTokenFromCookie() {
    return isset($_COOKIE['jwt_token']) ? $_COOKIE['jwt_token'] : null;
}

// Check if the user is logged in by checking the JWT token
$token = getJwtTokenFromCookie();

if (!$token) {
    // No token found, redirect to the homepage with a message
    echo "<script>
        alert('You must be logged in to access this page.');
        window.location.href = 'index.php';
    </script>";
    exit();
}

// Validate the token by sending it to the backend
$request = array();
$request['type'] = 'validate_session';
$request['token'] = $token;

$response = createRabbitMQClientDatabase($request);

if (!$response['success']) {
    // Token is invalid or expired, redirect to the homepage
    echo "<script>
        alert('Session expired or invalid. Please log in again.');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// Store the user ID for future use
$userId = $response['userId'];

// Get the league ID from the URL
$leagueId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$leagueId) {
    // No league ID found, redirect to the homepage
    echo "<script>
        alert('Invalid league.');
        window.location.href = 'index.php';
    </script>";
    exit();
}

// Check if the user is part of this league
$request = array();
$request['type'] = 'validate_league_access';
$request['user_id'] = $userId;
$request['league_id'] = $leagueId;

$accessResponse = createRabbitMQClientDatabase($request);

if (!$accessResponse['success']) {
    // User is not part of this league, deny access
    echo "<script>
        alert('You are not a member of this league.');
        window.location.href = 'myleagues.php';
    </script>";
    exit();
}

// Fetch the leaderboard
$request = array();
$request['type'] = 'get_leaderboard';
$request['league_id'] = $leagueId;

$leaderboardResponse = createRabbitMQClientDatabase($request);
$leaderboard = $leaderboardResponse['leaderboard'];

// Fetch the messages for the league
$request = array();
$request['type'] = 'get_messages';
$request['league_id'] = $leagueId;

$messagesResponse = createRabbitMQClientDatabase($request);
$messages = $messagesResponse['messages'];

// Handle message posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $messageContent = trim($_POST['message']);

    if (!empty($messageContent)) {
        // Send request to post the message
        $postRequest = array();
        $postRequest['type'] = 'post_message';
        $postRequest['user_id'] = $userId;
        $postRequest['league_id'] = $leagueId;
        $postRequest['message'] = $messageContent;

        $postResponse = createRabbitMQClientDatabase($postRequest);

        if ($postResponse['success']) {
            echo "<script>alert('Message posted successfully!'); window.location.href = 'league.php?id={$leagueId}';</script>";
        } else {
            echo "<div class='alert alert-danger'>Failed to post the message: {$postResponse['message']}</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Please enter a valid message.</div>";
    }
}

// Handle leaving the league
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_league'])) {
    // Send request to leave the league
    $leaveRequest = array();
    $leaveRequest['type'] = 'leave_league';
    $leaveRequest['user_id'] = $userId;
    $leaveRequest['league_id'] = $leagueId;

    $leaveResponse = createRabbitMQClientDatabase($leaveRequest);

    if ($leaveResponse['success']) {
        echo "<script>alert('You have left the league.'); window.location.href = 'myleagues.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Failed to leave the league: {$leaveResponse['message']}</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>League Details</title>
    <!-- Bootstrap CSS -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">League Leaderboard</h2>

        <!-- Display the leaderboard -->
        <?php if (!empty($leaderboard)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo $user['points']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No points available yet for this league.</p>
        <?php endif; ?>

        <hr>

        <h4>League Message Board</h4>

        <!-- Display the messages -->
        <?php if (!empty($messages)): ?>
            <ul class="list-group">
                <?php foreach ($messages as $message): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($message['username']); ?>:</strong>
                        <?php echo htmlspecialchars($message['message']); ?>
                        <br><small><?php echo $message['created_at']; ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No messages posted yet.</p>
        <?php endif; ?>

        <hr>

        <!-- Form to post a message -->
        <h5>Post a Message</h5>
        <form action="league.php?id=<?php echo $leagueId; ?>" method="POST">
            <div class="mb-3">
                <textarea class="form-control" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post Message</button>
        </form>

        <hr>

        <!-- Red button to leave the league -->
        <form action="league.php?id=<?php echo $leagueId; ?>" method="POST">
            <input type="hidden" name="leave_league" value="1">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to leave this league?');">Leave League</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

