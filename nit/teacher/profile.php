<?php
require_once '../db.php';
checkRole(['teacher']);

$user = getCurrentUser();
$teacher_id = $user['id'];

// Get teacher's full information including photo
$teacher_query = "SELECT u.*, d.dept_name,
                  (SELECT COUNT(*) FROM classes WHERE teacher_id = u.id) as class_count
                  FROM users u
                  LEFT JOIN departments d ON u.department_id = d.id
                  WHERE u.id = ?";
$stmt = $conn->prepare($teacher_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Teacher</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .profile-container {
            max-width: 900px;
            margin: 30px auto;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 20px;
            padding: 40px;
            color: white;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .profile-photo-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .profile-photo-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            border: 5px solid white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .upload-btn-wrapper {
            position: relative;
            display: inline-block;
            margin-top: 15px;
        }
        
        .upload-btn {
            background: white;
            color: #11998e;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .upload-btn-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .profile-info-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #11998e;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="dashboard-container">
    <nav class="navbar">
        <div>
            <h1>🎓 NIT College - My Profile</h1>
        </div>
        <div class="user-info">
            <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
            <span>👨‍🏫 <?php echo htmlspecialchars($user['full_name']); ?></span>
            <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="profile-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    ✅ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-header">
                <div class="profile-photo-container">
                    <?php if ($teacher['photo']): ?>
                        <img src="../<?php echo htmlspecialchars($teacher['photo']); ?>" 
                             alt="Profile Photo" 
                             class="profile-photo"
                             id="profilePhotoImg">
                    <?php else: ?>
                        <div class="profile-photo-placeholder" id="profilePhotoPlaceholder">
                            👨‍🏫
                        </div>
                    <?php endif; ?>
                </div>
                
                <h2 style="margin: 0 0 10px 0; font-size: 32px;">
                    <?php echo htmlspecialchars($teacher['full_name']); ?>
                </h2>
                <p style="font-size: 18px; opacity: 0.9;">
                    📧 <?php echo htmlspecialchars($teacher['username']); ?>
                </p>
                <p style="font-size: 16px; opacity: 0.8; margin-top: 5px;">
                    🎓 Role: Teacher
                </p>
                
                <form action="../upload_photo.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-btn-wrapper">
                        <button class="upload-btn" type="button" onclick="document.getElementById('photoInput').click()">
                            📸 <?php echo $teacher['photo'] ? 'Change Photo' : 'Upload Photo'; ?>
                        </button>
                        <input type="file" 
                               id="photoInput" 
                               name="photo" 
                               accept="image/*" 
                               onchange="handlePhotoSelect(this)"
                               style="display: none;">
                    </div>
                </form>
                
                <p style="font-size: 12px; margin-top: 10px; opacity: 0.8;">
                    📌 Accepted: JPG, PNG, GIF (Max 5MB)
                </p>
            </div>
            
            <div class="profile-info-card">
                <h3 style="color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span>📋</span> Personal Information
                </h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['full_name']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['username']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['email']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['phone']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($teacher['dept_name'] ?? 'Not Assigned'); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Teaching Classes</div>
                        <div class="info-value"><?php echo $teacher['class_count']; ?> Class(es)</div>
                    </div>
                </div>
            </div>
            
            <div class="profile-info-card">
                <h3 style="color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span>📊</span> Account Status
                </h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Account Status</div>
                        <div class="info-value">
                            <?php if ($teacher['is_active']): ?>
                                <span style="color: #28a745;">✅ Active</span>
                            <?php else: ?>
                                <span style="color: #dc3545;">❌ Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Account Created</div>
                        <div class="info-value"><?php echo date('d M Y', strtotime($teacher['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function handlePhotoSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Check file size (5MB max)
                if (file.size > 5242880) {
                    alert('File size must be less than 5MB!');
                    input.value = '';
                    return;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, PNG, and GIF files are allowed!');
                    input.value = '';
                    return;
                }
                
                // Show preview and auto-submit
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update preview
                    const placeholder = document.getElementById('profilePhotoPlaceholder');
                    const img = document.getElementById('profilePhotoImg');
                    
                    if (img) {
                        img.src = e.target.result;
                    } else if (placeholder) {
                        placeholder.innerHTML = `<img src="${e.target.result}" class="profile-photo" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">`;
                    }
                    
                    // Auto-submit form
                    document.getElementById('uploadForm').submit();
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>