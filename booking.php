<?php
require_once 'db.php';

$hotelId = $_GET['hotel_id'] ?? 0;
$hotel = getHotelById($pdo, $hotelId);

if (!$hotel) {
    header('Location: hotels.php');
    exit;
}

$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = $_GET['guests'] ?? '1';

// Calculate total price
$totalPrice = 0;
$nights = 0;
if ($checkin && $checkout) {
    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    $nights = $checkinDate->diff($checkoutDate)->days;
    $totalPrice = $nights * $hotel['price_per_night'];
}

// Handle form submission
if ($_POST) {
    $guestName = $_POST['guest_name'] ?? '';
    $guestEmail = $_POST['guest_email'] ?? '';
    $guestPhone = $_POST['guest_phone'] ?? '';
    
    if ($guestName && $guestEmail && $guestPhone && $checkin && $checkout) {
        $bookingId = createBooking($pdo, $hotelId, $guestName, $guestEmail, $guestPhone, $checkin, $checkout, $guests, $totalPrice);
        if ($bookingId) {
            // Get the last inserted ID
            $bookingId = $pdo->lastInsertId();
            header("Location: confirmation.php?booking_id=$bookingId");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($hotel['name']); ?> - Hotels.com Clone</title>
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
            max-width: 1000px;
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

        /* Back Button */
        .back-btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin: 2rem 0;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: transform 0.3s;
        }

        .back-btn:hover {
            transform: translateY(-2px);
        }

        /* Booking Container */
        .booking-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        /* Booking Form */
        .booking-form-section {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #333;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .form-group input, .form-group select, .form-group textarea {
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1.2rem 3rem;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        /* Booking Summary */
        .booking-summary {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .hotel-summary {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e1e5e9;
        }

        .hotel-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .hotel-info h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .hotel-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
        }

        .stars {
            color: #ffd700;
        }

        .booking-details {
            margin-bottom: 2rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .detail-row.total {
            border-top: 2px solid #e1e5e9;
            padding-top: 1rem;
            font-weight: bold;
            font-size: 1.2rem;
            color: #667eea;
        }

        .price-breakdown {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .price-breakdown h4 {
            margin-bottom: 1rem;
            color: #333;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .booking-summary {
                position: static;
                order: -1;
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
        <a href="javascript:history.back()" class="back-btn">
            ← Back to Hotel Details
        </a>

        <div class="booking-container">
            <div class="booking-form-section">
                <h2 class="section-title">Complete Your Booking</h2>
                
                <form method="POST" id="bookingForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="guest_name">Full Name *</label>
                            <input type="text" id="guest_name" name="guest_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="guest_email">Email Address *</label>
                            <input type="email" id="guest_email" name="guest_email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="guest_phone">Phone Number *</label>
                            <input type="tel" id="guest_phone" name="guest_phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country">
                                <option value="USA">United States</option>
                                <option value="Canada">Canada</option>
                                <option value="UK">United Kingdom</option>
                                <option value="Australia">Australia</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="special_requests">Special Requests (Optional)</label>
                            <textarea id="special_requests" name="special_requests" placeholder="Any special requests or requirements..."></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Complete Booking</button>
                </form>
            </div>

            <div class="booking-summary">
                <div class="hotel-summary">
                    <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
                    <div class="hotel-info">
                        <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <div class="hotel-location">
                            📍 <?php echo htmlspecialchars($hotel['city']); ?>, <?php echo htmlspecialchars($hotel['country']); ?>
                        </div>
                        <div class="hotel-rating">
                            <span class="stars">⭐⭐⭐⭐⭐</span>
                            <span><?php echo $hotel['rating']; ?>/5</span>
                        </div>
                    </div>
                </div>

                <div class="booking-details">
                    <h4>Booking Details</h4>
                    <div class="detail-row">
                        <span>Check-in:</span>
                        <span><?php echo $checkin ? date('M d, Y', strtotime($checkin)) : 'Not selected'; ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Check-out:</span>
                        <span><?php echo $checkout ? date('M d, Y', strtotime($checkout)) : 'Not selected'; ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Guests:</span>
                        <span><?php echo $guests; ?> <?php echo $guests == 1 ? 'Guest' : 'Guests'; ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Nights:</span>
                        <span><?php echo $nights; ?> <?php echo $nights == 1 ? 'Night' : 'Nights'; ?></span>
                    </div>
                </div>

                <div class="price-breakdown">
                    <h4>Price Breakdown</h4>
                    <div class="detail-row">
                        <span>$<?php echo number_format($hotel['price_per_night'], 2); ?> × <?php echo $nights; ?> nights</span>
                        <span>$<?php echo number_format($totalPrice, 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Taxes & Fees</span>
                        <span>$<?php echo number_format($totalPrice * 0.12, 2); ?></span>
                    </div>
                    <div class="detail-row total">
                        <span>Total</span>
                        <span>$<?php echo number_format($totalPrice * 1.12, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const name = document.getElementById('guest_name').value.trim();
            const email = document.getElementById('guest_email').value.trim();
            const phone = document.getElementById('guest_phone').value.trim();
            
            if (!name || !email || !phone) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            // Phone validation
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if (!phoneRegex.test(phone.replace(/[\s\-$$$$]/g, ''))) {
                e.preventDefault();
                alert('Please enter a valid phone number.');
                return false;
            }
        });
    </script>
</body>
</html>
