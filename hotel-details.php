<?php
require_once 'db.php';

$hotelId = $_GET['id'] ?? 0;
$hotel = getHotelById($pdo, $hotelId);

if (!$hotel) {
    header('Location: hotels.php');
    exit;
}

// Get search parameters for booking
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = $_GET['guests'] ?? '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['name']); ?> - Hotels.com Clone</title>
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
            max-width: 1200px;
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

        /* Hotel Details */
        .hotel-details {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .hotel-header {
            position: relative;
            height: 400px;
            background: url('<?php echo htmlspecialchars($hotel['image']); ?>') center/cover;
        }

        .hotel-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.6));
        }

        .hotel-header-content {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            color: white;
            z-index: 2;
        }

        .hotel-name {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hotel-location {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
        }

        .stars {
            color: #ffd700;
        }

        /* Hotel Info Grid */
        .hotel-info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            padding: 3rem;
        }

        .hotel-main-info h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .hotel-description {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
            color: #555;
        }

        .amenities-section h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .amenities-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        /* Booking Card */
        .booking-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .price-display {
            text-align: center;
            margin-bottom: 2rem;
        }

        .price-amount {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
        }

        .price-unit {
            font-size: 1rem;
            color: #666;
        }

        .booking-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .form-group input, .form-group select {
            padding: 0.8rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        .book-now-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
            margin-top: 1rem;
        }

        .book-now-btn:hover {
            transform: translateY(-2px);
        }

        /* Address Section */
        .address-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .address-section h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .address-text {
            font-size: 1.1rem;
            color: #555;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hotel-info-grid {
                grid-template-columns: 1fr;
                padding: 2rem;
            }

            .hotel-name {
                font-size: 2rem;
            }

            .amenities-list {
                grid-template-columns: 1fr;
            }

            .booking-card {
                position: static;
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
            ← Back to Results
        </a>

        <div class="hotel-details">
            <div class="hotel-header">
                <div class="hotel-header-content">
                    <h1 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h1>
                    <div class="hotel-location">
                        📍 <?php echo htmlspecialchars($hotel['city']); ?>, <?php echo htmlspecialchars($hotel['country']); ?>
                    </div>
                    <div class="hotel-rating">
                        <span class="stars">⭐⭐⭐⭐⭐</span>
                        <span><?php echo $hotel['rating']; ?>/5 Rating</span>
                    </div>
                </div>
            </div>

            <div class="hotel-info-grid">
                <div class="hotel-main-info">
                    <h2>About This Hotel</h2>
                    <div class="hotel-description">
                        <?php echo htmlspecialchars($hotel['description']); ?>
                    </div>

                    <div class="amenities-section">
                        <h3>Amenities & Services</h3>
                        <div class="amenities-list">
                            <?php 
                            $amenities = explode(', ', $hotel['amenities']);
                            foreach ($amenities as $amenity): 
                            ?>
                            <div class="amenity-item">
                                ✅ <?php echo htmlspecialchars($amenity); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="booking-card">
                    <div class="price-display">
                        <div class="price-amount">$<?php echo number_format($hotel['price_per_night'], 2); ?></div>
                        <div class="price-unit">per night</div>
                    </div>

                    <form class="booking-form" id="bookingForm">
                        <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                        
                        <div class="form-group">
                            <label for="checkin">Check-in Date</label>
                            <input type="date" id="checkin" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="checkout">Check-out Date</label>
                            <input type="date" id="checkout" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="guests">Number of Guests</label>
                            <select id="guests" name="guests" required>
                                <option value="1" <?php echo $guests == '1' ? 'selected' : ''; ?>>1 Guest</option>
                                <option value="2" <?php echo $guests == '2' ? 'selected' : ''; ?>>2 Guests</option>
                                <option value="3" <?php echo $guests == '3' ? 'selected' : ''; ?>>3 Guests</option>
                                <option value="4" <?php echo $guests == '4' ? 'selected' : ''; ?>>4 Guests</option>
                                <option value="5" <?php echo $guests == '5' ? 'selected' : ''; ?>>5+ Guests</option>
                            </select>
                        </div>

                        <button type="submit" class="book-now-btn">Book Now</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="address-section">
            <h3>📍 Hotel Address</h3>
            <div class="address-text">
                <?php echo htmlspecialchars($hotel['address']); ?>
            </div>
        </div>
    </div>

    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('checkin').min = today;
            document.getElementById('checkout').min = today;
        });

        // Update checkout minimum date when checkin changes
        document.getElementById('checkin').addEventListener('change', function() {
            const checkinDate = new Date(this.value);
            checkinDate.setDate(checkinDate.getDate() + 1);
            document.getElementById('checkout').min = checkinDate.toISOString().split('T')[0];
        });

        // Handle booking form submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            window.location.href = 'booking.php?' + params.toString();
        });
    </script>
</body>
</html>
