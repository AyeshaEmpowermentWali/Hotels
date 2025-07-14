<?php
// Database configuration
$host = 'localhost';
$dbname = 'db0enju00qdwo7';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get all hotels
function getAllHotels($pdo) {
    $stmt = $pdo->query("SELECT * FROM hotels ORDER BY rating DESC");
    return $stmt->fetchAll();
}

// Function to search hotels
function searchHotels($pdo, $destination, $checkin, $checkout, $guests) {
    $sql = "SELECT * FROM hotels WHERE city LIKE ? OR name LIKE ? ORDER BY rating DESC";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$destination%";
    $stmt->execute([$searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

// Function to get hotel by ID
function getHotelById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Function to create booking
function createBooking($pdo, $hotel_id, $guest_name, $guest_email, $guest_phone, $checkin, $checkout, $guests, $total_price) {
    $sql = "INSERT INTO bookings (hotel_id, guest_name, guest_email, guest_phone, checkin_date, checkout_date, guests, total_price, booking_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$hotel_id, $guest_name, $guest_email, $guest_phone, $checkin, $checkout, $guests, $total_price]);
}

// Function to get booking by ID
function getBookingById($pdo, $id) {
    $sql = "SELECT b.*, h.name as hotel_name, h.city, h.image FROM bookings b JOIN hotels h ON b.hotel_id = h.id WHERE b.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?>
