<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = Database::getConnection();

$reference = $_GET['ref'] ?? '';
if (empty($reference)) {
    $_SESSION['error'] = "Invalid ticket reference.";
    header('Location: my-tickets.php');
    exit();
}

// Fetch booking details
$stmt = $db->prepare("
    SELECT b.*, e.title, e.start_date, e.end_date, e.venue, e.city, e.address
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.booking_reference = ? AND b.user_id = ? AND b.status = 'confirmed'
");
$stmt->execute([$reference, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = "Ticket not found or not confirmed.";
    header('Location: my-tickets.php');
    exit();
}

$qr_data = $booking['booking_reference'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_data);

$event_title = htmlspecialchars($booking['title']);
$event_date = date('l, F j, Y', strtotime($booking['start_date']));
$event_time = date('g:i A', strtotime($booking['start_date'])) . ' - ' . date('g:i A', strtotime($booking['end_date']));
$venue = htmlspecialchars($booking['venue']);
$city = htmlspecialchars($booking['city']);
$ticket_quantity = $booking['ticket_quantity'];
$total_amount = $booking['total_amount'] == 0 ? 'Free' : '₦' . number_format($booking['total_amount'], 2);
$attendee_name = $_SESSION['user_name'] ?? 'Attendee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - <?php echo $reference; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .ticket-container {
            max-width: 700px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e0e7ef;
        }
        .ticket-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 25px 30px;
            text-align: center;
        }
        .ticket-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .ticket-header .sub {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .ticket-body {
            padding: 30px;
        }
        .ticket-body .event-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        .ticket-body .event-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
            background: #f8faff;
            border-radius: 10px;
            padding: 15px 20px;
            margin: 15px 0 20px;
        }
        .ticket-body .event-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #555;
        }
        .ticket-body .event-meta .meta-item .label {
            font-weight: 600;
            color: #333;
            min-width: 60px;
        }
        .ticket-body .event-meta .meta-item .value {
            color: #333;
        }
        .ticket-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 2px dashed #e0e7ef;
            padding-top: 20px;
            margin-top: 20px;
        }
        .ticket-footer .qr-code img {
            width: 120px;
            height: 120px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .ticket-footer .ticket-info {
            text-align: right;
        }
        .ticket-footer .ticket-info .ref {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
        }
        .ticket-footer .ticket-info .attendee {
            font-size: 14px;
            color: #555;
        }
        .ticket-footer .ticket-info .qty {
            font-size: 14px;
            color: #555;
        }
        .ticket-footer .ticket-info .price {
            font-size: 16px;
            font-weight: 600;
            color: #28a745;
        }
        .ticket-bottom {
            text-align: center;
            padding: 15px 30px;
            background: #f8faff;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #e0e7ef;
        }
        .print-btn {
            display: block;
            margin: 20px auto 0;
            padding: 12px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .print-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        /* Print Styles */
        @media print {
            body { background: white; padding: 0; }
            .ticket-container { box-shadow: none; border: 1px solid #ccc; border-radius: 0; }
            .print-btn { display: none; }
            .ticket-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div style="width: 100%; display: flex; flex-direction: column; align-items: center;">
        <div class="ticket-container">
            <div class="ticket-header">
                <h1>🎫 EventEase</h1>
                <div class="sub">Official Ticket</div>
            </div>
            <div class="ticket-body">
                <div class="event-title"><?php echo $event_title; ?></div>
                <div class="event-meta">
                    <div class="meta-item"><span class="label">Date</span><span class="value"><?php echo $event_date; ?></span></div>
                    <div class="meta-item"><span class="label">Time</span><span class="value"><?php echo $event_time; ?></span></div>
                    <div class="meta-item"><span class="label">Venue</span><span class="value"><?php echo $venue; ?></span></div>
                    <div class="meta-item"><span class="label">City</span><span class="value"><?php echo $city; ?></span></div>
                </div>
                <div class="ticket-footer">
                    <div class="qr-code">
                        <img src="<?php echo $qr_url; ?>" alt="QR Code">
                    </div>
                    <div class="ticket-info">
                        <div class="ref"># <?php echo $reference; ?></div>
                        <div class="attendee">Attendee: <?php echo $attendee_name; ?></div>
                        <div class="qty">Tickets: <?php echo $ticket_quantity; ?></div>
                        <div class="price"><?php echo $total_amount; ?></div>
                    </div>
                </div>
            </div>
            <div class="ticket-bottom">
                This ticket is valid for entry to the event. Please present this ticket at the entrance.
            </div>
        </div>
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>
</body>
</html>