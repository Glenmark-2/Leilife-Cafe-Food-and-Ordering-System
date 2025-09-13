<?php
require_once __DIR__ . '/db_script/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Check if file was uploaded
if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    die("No file uploaded or upload error.");
}

$uploadDir = __DIR__ . '/../public/profile_photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate safe file name
$fileTmp = $_FILES['profile_photo']['tmp_name'];
$fileName = basename($_FILES['profile_photo']['name']);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($fileExt, $allowed)) {
    die("Invalid file type. Only JPG, PNG, and GIF are allowed.");
}

// Rename file to avoid conflicts: userID_timestamp.ext
$newFileName = "user_" . $user_id . "_" . time() . "." . $fileExt;
$filePath = $uploadDir . $newFileName;
$fileDbPath = "../public//profile_photos/" . $newFileName; // save relative path to DB

if (!move_uploaded_file($fileTmp, $filePath)) {
    die("Error saving file.");
}

// Save path in database
$sql = "UPDATE users SET profile_picture = :photo WHERE id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':photo' => $fileDbPath,
    ':user_id' => $user_id
]);

// Update session so new photo shows without re-login
$_SESSION['profile_picture'] = $fileDbPath;

header("Location: /leilife/public/index.php?page=user-profile");
exit;

// <!-- Profile Header -->
// <!-- <div class="white-box">
//     <div id="first-box"> 
//         <form action="/leilife/backend/update_profile_photo.php" method="POST" enctype="multipart/form-data">

//             <label for="profile-photo-input" style="cursor:pointer;">
//                 <img 
//                     src="<?= !empty($userInfo['profile_photo']) 
//                         ? htmlspecialchars($userInfo['profile_photo']) 
//                         : '../public/assests/uploadImg.jpg'; ?>" 
//                     alt="profile-photo" 
//                     class="profile-photo"
//                 >
//             </label>
//             <input 
//                 type="file" 
//                 id="profile-photo-input" 
//                 name="profile_photo" 
//                 accept="image/*" 
//                 style="display:none;" 
//                 onchange="document.getElementById('profile-photo-form').submit();"
//             >
//         </form>
        
//         <div> 
//             <h2><?= htmlspecialchars($userInfo["first_name"] ?? 'Unknown') . ' ' . htmlspecialchars($userInfo["last_name"] ?? 'Unknown'); ?></h2> 
//             <p>Customer</p>
//         </div>
//     </div>
// </div> -->
