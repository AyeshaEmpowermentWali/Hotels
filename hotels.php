<?php
require_once 'db.php';

// Get search parameters
$destination = $_GET['destination'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = $_GET['guests'] ?? '1';

// Search hotels or get all hotels
if (!empty($destination)) {
    $hotels = searchHotels($pdo, $destination, $checkin, $checkout, $guests);
} else {
    $hotels = getAllHotels($pdo);
}

// Filter parameters
$minPrice = $_GET['min_price'] ?? 0;
$maxPrice = $_GET['max_price'] ?? 1000;
$rating = $_GET['rating'] ?? 0;
$hotelType = $_GET['hotel_type'] ?? '';

// Apply filters
if ($minPrice > 0 || $maxPrice < 1000 || $rating > 0 || !empty($hotelType)) {
    $hotels = array_filter($hotels, function($hotel) use ($minPrice, $maxPrice, $rating, $hotelType) {
        $priceMatch = $hotel['price_per_night'] >= $minPrice && $hotel['price_per_night'] <= $maxPrice;
        $ratingMatch = $hotel['rating'] >= $rating;
        $typeMatch = empty($hotelType) || $hotel['hotel_type'] === $hotelType;
        return $priceMatch && $ratingMatch && $typeMatch;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Search Results - Hotels.com Clone</title>
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

        /* Search Bar */
        .search-bar {
            background: white;
            padding: 2rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group input, .form-group select {
            padding: 0.8rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
        }

        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 2rem 0;
        }

        /* Filters Sidebar */
        .filters {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .filter-section {
            margin-bottom: 2rem;
        }

        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }

        .filter-group {
            margin-bottom: 1rem;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .filter-group input, .filter-group select {
            width: 100%;
            padding: 0.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
        }

        .price-range {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .price-range input {
            width: 80px;
        }

        /* Results Section */
        .results-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .results-header {
            padding: 2rem;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-count {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .sort-dropdown {
            padding: 0.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
        }

        /* Hotel Cards */
        .hotel-list {
            padding: 0;
        }

        .hotel-card {
            display: grid;
            grid-template-columns: 300px 1fr auto;
            gap: 2rem;
            padding: 2rem;
            border-bottom: 1px solid #e1e5e9;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .hotel-card:hover {
            background-color: #f8f9fa;
        }

        .hotel-card:last-child {
            border-bottom: none;
        }

        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .hotel-info {
            flex: 1;
        }

        .hotel-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .hotel-location {
            color: #666;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .hotel-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #ffd700;
        }

        .hotel-amenities {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .hotel-description {
            color: #555;
            line-height: 1.5;
        }

        .hotel-price-section {
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }

        .hotel-price {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .price-unit {
            font-size: 0.9rem;
            color: #666;
            font-weight: normal;
        }

        .book-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .book-btn:hover {
            transform: translateY(-2px);
        }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .hotel-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hotel-price-section {
                align-items: center;
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

    <section class="search-bar">
        <div class="container">
            <form class="search-form" method="GET">
                <div class="form-group">
                    <input type="text" name="destination" placeholder="Destination" value="<?php echo htmlspecialchars($destination); ?>">
                </div>
                <div class="form-group">
                    <input type="date" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                </div>
                <div class="form-group">
                    <input type="date" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                </div>
                <div class="form-group">
                    <select name="guests">
                        <option value="1" <?php echo $guests == '1' ? 'selected' : ''; ?>>1 Guest</option>
                        <option value="2" <?php echo $guests == '2' ? 'selected' : ''; ?>>2 Guests</option>
                        <option value="3" <?php echo $guests == '3' ? 'selected' : ''; ?>>3 Guests</option>
                        <option value="4" <?php echo $guests == '4' ? 'selected' : ''; ?>>4 Guests</option>
                        <option value="5" <?php echo $guests == '5' ? 'selected' : ''; ?>>5+ Guests</option>
                    </select>
                </div>
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>
    </section>

    <div class="container">
        <div class="main-content">
            <aside class="filters">
                <form method="GET" id="filterForm">
                    <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                    <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
                    <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
                    <input type="hidden" name="guests" value="<?php echo htmlspecialchars($guests); ?>">
                    
                    <div class="filter-section">
                        <h3 class="filter-title">Price Range</h3>
                        <div class="filter-group">
                            <div class="price-range">
                                $<input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice; ?>" min="0">
                                -
                                $<input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice; ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-title">Rating</h3>
                        <div class="filter-group">
                            <select name="rating">
                                <option value="0">Any Rating</option>
                                <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3+ Stars</option>
                                <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4+ Stars</option>
                                <option value="4.5" <?php echo $rating == '4.5' ? 'selected' : ''; ?>>4.5+ Stars</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3 class="filter-title">Hotel Type</h3>
                        <div class="filter-group">
                            <select name="hotel_type">
                                <option value="">All Types</option>
                                <option value="Luxury" <?php echo $hotelType == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                                <option value="Business" <?php echo $hotelType == 'Business' ? 'selected' : ''; ?>>Business</option>
                                <option value="Resort" <?php echo $hotelType == 'Resort' ? 'selected' : ''; ?>>Resort</option>
                                <option value="Budget" <?php echo $hotelType == 'Budget' ? 'selected' : ''; ?>>Budget</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="search-btn" style="width: 100%;">Apply Filters</button>
                </form>
            </aside>

            <main class="results-section">
                <div class="results-header">
                    <div class="results-count">
                        <?php echo count($hotels); ?> hotels found
                        <?php if (!empty($destination)): ?>
                            in <?php echo htmlspecialchars($destination); ?>
                        <?php endif; ?>
                    </div>
                    <select class="sort-dropdown" onchange="sortHotels(this.value)">
                        <option value="rating">Sort by Rating</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                    </select>
                </div>

                <div class="hotel-list">
                    <?php if (empty($hotels)): ?>
                        <div class="no-results">
                            <h3>No hotels found</h3>
                            <p>Try adjusting your search criteria or filters.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($hotels as $hotel): ?>
                        <div class="hotel-card" onclick="viewHotel(<?php echo $hotel['id']; ?>)">
                            <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
                            <div class="hotel-info">
                                <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                <div class="hotel-location">
                                    📍 <?php echo htmlspecialchars($hotel['city']); ?>, <?php echo htmlspecialchars($hotel['country']); ?>
                                </div>
                                <div class="hotel-rating">
                                    <span class="stars">⭐⭐⭐⭐⭐</span>
                                    <span><?php echo $hotel['rating']; ?>/5</span>
                                </div>
                                <div class="hotel-amenities">
                                    <?php echo htmlspecialchars($hotel['amenities']); ?>
                                </div>
                                <div class="hotel-description">
                                    <?php echo htmlspecialchars(substr($hotel['description'], 0, 150)) . '...'; ?>
                                </div>
                            </div>
                            <div class="hotel-price-section">
                                <div class="hotel-price">
                                    $<?php echo number_format($hotel['price_per_night'], 2); ?>
                                    <span class="price-unit">per night</span>
                                </div>
                                <button class="book-btn" onclick="event.stopPropagation(); bookHotel(<?php echo $hotel['id']; ?>)">
                                    Book Now
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        function viewHotel(hotelId) {
            const params = new URLSearchParams(window.location.search);
            window.location.href = `hotel-details.php?id=${hotelId}&${params.toString()}`;
        }

        function bookHotel(hotelId) {
            const params = new URLSearchParams(window.location.search);
            window.location.href = `booking.php?hotel_id=${hotelId}&${params.toString()}`;
        }

        function sortHotels(sortBy) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sortBy);
            window.location.href = '?' + params.toString();
        }
    </script>
</body>
</html>
