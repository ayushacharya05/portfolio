<?php
session_start();
$storage = 'works_data.json';
$correct_user = "ayushacharya";
$correct_pass = "Ayush@2059";

if (!file_exists($storage)) { file_put_contents($storage, json_encode([])); }

// Login Logic
if (isset($_POST['login'])) {
    if ($_POST['user'] === $correct_user && $_POST['pass'] === $correct_pass) {
        $_SESSION['admin_auth'] = true;
    } else { $error = "Invalid credentials"; }
}
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }

// Delete Logic
if (isset($_GET['delete']) && isset($_SESSION['admin_auth'])) {
    $data = json_decode(file_get_contents($storage), true);
    $data = array_filter($data, function($item) { return $item['id'] !== $_GET['delete']; });
    file_put_contents($storage, json_encode(array_values($data)));
    header("Location: index.php"); exit;
}

// Save/Update Logic
if (isset($_POST['save']) && isset($_SESSION['admin_auth'])) {
    $data = json_decode(file_get_contents($storage), true);
    $img = $_POST['url_img'];
    if (!empty($_FILES['file_img']['name'])) {
        $img = 'uploads/' . time() . "_" . $_FILES['file_img']['name'];
        if (!is_dir('uploads')) mkdir('uploads');
        move_uploaded_file($_FILES['file_img']['tmp_name'], $img);
    }

    $project_data = [
        "id" => $_POST['project_id'] ?: uniqid(),
        "title" => $_POST['title'],
        "link" => $_POST['link'],
        "desc" => $_POST['desc'],
        "img" => $img
    ];

    if (!empty($_POST['project_id'])) {
        foreach ($data as &$item) {
            if ($item['id'] === $_POST['project_id']) { $item = $project_data; }
        }
    } else {
        $data[] = $project_data;
    }

    file_put_contents($storage, json_encode($data));
    header("Location: index.php"); exit;
}

$works = array_reverse(json_decode(file_get_contents($storage), true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayush Acharya | Portfolio Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --brand: #6366f1; --bg: #f8fafc; --text: #0f172a; }
        body { background-color: var(--bg); color: var(--text); font-family: 'Inter', system-ui, sans-serif; }
        
        .navbar { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid #e2e8f0; }
        .user-icon-btn { width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; transition: 0.3s; }
        .user-icon-btn:hover { background: var(--brand); color: white; }
        .login-dropdown { width: 280px; padding: 20px; border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }

        .hero { padding: 100px 0 60px; background: radial-gradient(circle at top right, #e0e7ff, transparent); }
        .hero h1 { font-weight: 800; letter-spacing: -1px; font-size: 3.5rem; }

        .admin-panel { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px; margin-bottom: 50px; }

        .work-card { border: none; border-radius: 20px; overflow: hidden; background: white; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); height: 100%; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; }
        .work-card:hover { transform: translateY(-10px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .card-img-wrap { height: 220px; overflow: hidden; position: relative; }
        .card-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .work-card:hover .card-img-wrap img { scale: 1.1; }
        
        .btn-action { background: var(--brand); color: white; border-radius: 12px; font-weight: 600; padding: 12px; border: none; width: 100%; transition: 0.3s; }
        .btn-action:hover { background: #4f46e5; color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4); }

        /* New Admin Floating Buttons */
        .admin-controls { position: absolute; top: 10px; right: 10px; z-index: 10; display: flex; gap: 5px; opacity: 0; transition: 0.3s; }
        .work-card:hover .admin-controls { opacity: 1; }
        .btn-edit { background: #0f172a; color: white; border: none; border-radius: 8px; width: 32px; height: 32px; font-size: 12px; }
        .btn-del { background: #ef4444; color: white; border: none; border-radius: 8px; width: 32px; height: 32px; font-size: 12px; }
    </style>
</head>
<body>

<nav class="navbar fixed-top">
    <div class="container d-flex justify-content-between">
        <a class="navbar-brand fw-bold text-primary" href="index.php">MY PROJECTS.</a>
        
        <div class="dropdown">
            <button class="user-icon-btn" data-bs-toggle="dropdown">
                <i class="fa-solid fa-user"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end login-dropdown">
                <?php if(!isset($_SESSION['admin_auth'])): ?>
                    <h6 class="fw-bold mb-3">Admin Login</h6>
                    <form method="POST">
                        <input type="text" name="user" class="form-control mb-2" placeholder="Username" required>
                        <input type="password" name="pass" class="form-control mb-3" placeholder="Password" required>
                        <button name="login" class="btn btn-primary w-100">Sign In</button>
                    </form>
                <?php else: ?>
                    <p class="small text-muted mb-2">Logged in as <b>Ayush</b></p>
                    <a href="?logout=1" class="btn btn-outline-danger btn-sm w-100">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container text-center">
      
        <h1 class="mb-3">All My Works In One Place</h1>
        <p class="text-muted mx-auto" style="max-width: 600px;">Explore my digital garden of websites, applications, and creative experiments.</p>
    </div>
</header>

<div class="container mb-5">
    
    <?php if(isset($_SESSION['admin_auth'])): ?>
    <div class="admin-panel shadow-sm" id="form-container">
        <h5 class="fw-bold mb-4" id="form-title"><i class="fa-solid fa-plus me-2 text-primary"></i> Upload New Work</h5>
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="project_id" id="p_id">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Project Name</label>
                <input type="text" name="title" id="p_title" class="form-control" placeholder="E.g. E-commerce App" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Access URL</label>
                <input type="text" name="link" id="p_link" class="form-control" placeholder="https://..." required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Thumbnail URL</label>
                <input type="text" name="url_img" id="p_img" class="form-control" placeholder="External link...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Or Upload File</label>
                <input type="file" name="file_img" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Short Description</label>
                <textarea name="desc" id="p_desc" class="form-control" rows="2" placeholder="Tell us about the project..."></textarea>
            </div>
            <div class="col-12 text-end">
                <button type="button" onclick="window.location.reload()" class="btn btn-light me-2">Cancel</button>
                <button name="save" class="btn btn-primary px-5 py-2 fw-bold">Publish Work</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach($works as $work): ?>
        <div class="col-lg-4 col-md-6">
            <div class="work-card card">
                <?php if(isset($_SESSION['admin_auth'])): ?>
                <div class="admin-controls">
                    <button class="btn-edit" onclick="editProject(<?= htmlspecialchars(json_encode($work)) ?>)"><i class="fa-solid fa-pen"></i></button>
                    <a href="?delete=<?= $work['id'] ?>" class="btn-del text-center pt-1" onclick="return confirm('Delete this work?')"><i class="fa-solid fa-trash"></i></a>
                </div>
                <?php endif; ?>

                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($work['img']) ?>" onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=500'">
                </div>
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($work['title']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($work['desc'] ?? '') ?></p>
                    <a href="<?= htmlspecialchars($work['link']) ?>" target="_blank" class="btn-action text-center d-block text-decoration-none">
                        Launch Project <i class="fa-solid fa-arrow-up-right-from-square ms-1 small"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<footer class="py-5 text-center text-muted border-top">
    <p>© 2026 Ayush Acharya. All rights reserved.</p>
</footer>

<script>
function editProject(data) {
    document.getElementById('form-title').innerHTML = '<i class="fa-solid fa-pen me-2 text-warning"></i> Editing: ' + data.title;
    document.getElementById('p_id').value = data.id;
    document.getElementById('p_title').value = data.title;
    document.getElementById('p_link').value = data.link;
    document.getElementById('p_img').value = data.img;
    document.getElementById('p_desc').value = data.desc || '';
    document.getElementById('form-container').scrollIntoView({ behavior: 'smooth' });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
