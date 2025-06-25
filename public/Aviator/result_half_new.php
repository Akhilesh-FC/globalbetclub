<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, Content-Type");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include 'dbconnection.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method Not Allowed", "status" => 405));
    exit;
}

// Read JSON input (if any)
$input = json_decode(file_get_contents("php://input"), true);

// Query for total amount, total users, etc.
$q = "SELECT 
        COALESCE(SUM(amount), 0) AS totalamount, 
        COALESCE(COUNT(`uid`), 0) AS totalusers, 
        COALESCE(`amount`, 0) AS highestamount, 
        (SELECT COALESCE(MAX(game_sr_num), 0)+1 FROM aviator_result) AS game_sr, 
        (SELECT COALESCE(
            (SELECT COALESCE(`multiplier`, 0) 
             FROM `aviator_admin_result` 
             WHERE `game_sr_num` = (SELECT COALESCE(MAX(`game_sr_num`), 0) + 1 FROM `aviator_result`) 
             AND `game_id` = '5'
             LIMIT 1
            ), 0 )) AS adminmultiply, 
        (SELECT `winning_percentage` 
         FROM `game_settings` 
         WHERE `id`=5) AS adminpercent 
      FROM aviator_bet 
      WHERE game_id = '5' 
      AND game_sr_num = (SELECT COALESCE(MAX(game_sr_num), 0)+1 FROM aviator_result) 
      ORDER BY `amount` desc 
      LIMIT 1";

// Execute the query
$query = mysqli_query($conn, $q);

if ($query) {
    // Fetch the results
    $res = mysqli_fetch_assoc($query);
    
    // Debugging: Output the result for troubleshooting (you can remove this once you're sure it's working)
    // var_dump($res);
    
    $totalamount = $res['totalamount'];
    $totalusers = $res['totalusers'];
    $highestamount = $res['highestamount'];
    $adminmultiply = $res['adminmultiply'];
    $adminpercent = $res['adminpercent'];
    $game_sr = $res['game_sr'];

    // Query to get multiplier from admin result
    $qyw = mysqli_query($conn, "SELECT `number` FROM `aviator_admin_result` WHERE `game_sr_num`='$game_sr' LIMIT 1");

    if (mysqli_num_rows($qyw) > 0) {
        $res_ss = mysqli_fetch_assoc($qyw);
        $multiplier = $res_ss['number'];
    } else {
        $multiplier = 0;
    }

    // Query to get total win for the game session
    $tot_win = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(win), 0) AS total_win FROM aviator_bet WHERE game_sr_num = '$game_sr' AND game_id = '5'"));
    $total_win = $tot_win['total_win'];

    // Fetch game settings
    $aviator_setting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM aviator_setting WHERE id=1"));

    // Prepare the response array
    $response = array(
        "message" => "Data fetched successfully",
        "status" => 200,
        'totalamount' => intval($totalamount),
        'totalusers' => intval($totalusers),
        'game_sr' => intval($game_sr),
        'highestamount' => intval($highestamount),
        'adminmultiply' => $multiplier,
        'adminpercent' => $aviator_setting['win_per'] ?? 0,
        'loss_per' => $aviator_setting['loss_per'] ?? 0,
        'min_amount' => $aviator_setting['amount'] ?? 0,
        'total_win' => $total_win
    );

    // Return the response as JSON
    echo json_encode($response);
    exit;
} else {
    // If query fails, return a failure response
    echo json_encode(array("message" => "Database query failed", "status" => 400));
    exit;
}

?>
