<?php
require_once 'db.php';

$bookingId = $_GET['booking_id'] ?? 0;
$booking = getBookingById($pdo, $bookingId);

if (!$booking) {
    header('Location: index.php');
    exit;
}

// Calculate nights
$checkinDate = new DateTime($booking['checkin_date']);
$checkoutDate = new DateTime($booking['checkout_date']);
$nights = $checkinDate->diff($checkoutDate)->days;
$totalWithTax = $booking['total_price'] * 1.12;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Hotels.com Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        /* Confirmation Content */
        .confirmation-container {
            background: white;
            margin: 3rem 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .success-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .success-message {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .booking-details {
            padding: 3rem 2rem;
        }

        .booking-id {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 2rem;
            border-left: 4px solid #667eea;
        }

        .booking-id strong {
            color: #667eea;
            font-size: 1.2rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
        }

        .detail-section h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.3rem 0;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: #666;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
        }

        .hotel-summary {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .hotel-summary h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .hotel-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hotel-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .hotel-details h4 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .hotel-location {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: #ffd700;
        }

        .price-summary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .price-summary h3 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .price-row.total {
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 2rem;
            border-top: 1px solid #e1e5e9;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: transform 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .important-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .important-info h4 {
            color: #856404;
            margin-bottom: 1rem;
        }

        .important-info ul {
            color: #856404;
            padding-left: 1.5rem;
        }

        .important-info li {
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .hotel-info {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                flex-direction: column;
            }

            .success-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">🏨 Hotels.com</a>
                <nav class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="hotels.php">Hotels</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="confirmation-container">
            <div class="success-header">
                <div class="success-icon">✅</div>
                <h1 class="success-title">Booking Confirmed!</h1>
                <p class="success-message">Your reservation has been successfully confirmed. We've sent a confirmation email to <?php echo htmlspecialchars($booking['guest_email']); ?></p>
            </div>

            <div class="booking-details">
                <div class="booking-id">
                    <p>Your Booking ID: <strong>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong></p>
                    <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">Please save this ID for your records</p>
                </div>

                <div class="hotel-summary">
                    <h3>Hotel Information</h3>
                    <div class="hotel-info">
                        <img src="<?php echo htmlspecialchars($booking['image']); ?>" alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>" class="hotel-image">
                        <div class="hotel-details">
                            <h4><?php echo htmlspecialchars($booking['hotel_name']); ?></h4>
                            <div class="hotel-location">📍 <?php echo htmlspecialchars($booking['city']); ?></div>
                            <div class="hotel-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span>Excellent Rating</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="details-grid">
                    <div class="detail-section">
                        <h3>Guest Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['guest_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['guest_email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['guest_phone']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Guests:</span>
                            <span class="detail-value"><?php echo $booking['guests']; ?> <?php echo $booking['guests'] == 1 ? 'Guest' : 'Guests'; ?></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Stay Details</h3>
                        <div class="detail-item">
                            <span class="detail-label">Check-in:</span>
                            <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['checkin_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Check-out:</span>
                            <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['checkout_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nights:</span>
                            <span class="detail-value"><?php echo $nights; ?> <?php echo $nights == 1 ? 'Night' : 'Nights'; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Booking Date:</span>
                            <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="price-summary">
                    <h3>Payment Summary</h3>
                    <div class="price-row">
                        <span>Room Rate (<?php echo $nights; ?> nights)</span>
                        <span>$<?php echo number_format($booking['total_price'], 2); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Taxes & Fees</span>
                        <span>$<?php echo number_format($booking['total_price'] * 0.12, 2); ?></span>
                    </div>
                    <div class="price-row total">
                        <span>Total Paid</span>
                        <span>$<?php echo number_format($totalWithTax, 2); ?></span>
                    </div>
                </div>

                <div class="important-info">
                    <h4>📋 Important Information</h4>
                    <ul>
                        <li>Check-in time is 3:00 PM, Check-out time is 11:00 AM</li>
                        <li>Please bring a valid photo ID and credit card at check-in</li>
                        <li>Cancellation policy: Free cancellation up to 24 hours before check-in</li>
                        <li>For any changes or questions, contact the hotel directly</li>
                        <li>A confirmation email has been sent to your email address</li>
                    </ul>
                </div>

                <div class="action-buttons">
                    <a href="index.php" class="btn btn-primary">Book Another Hotel</a>
                    <a href="javascript:window.print()" class="btn btn-secondary">Print Confirmation</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to top on page load
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });

        // Print functionality
        function printConfirmation() {
            window.print();
        }
    </script>
</body>
</html>
