<?php
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Administration - TGTravail' ?></title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="../siteweb/css/style.css">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background: var(--bg-page); color: #1e293b; margin: 0; }
        .admin-main-deprecated { margin-left: 280px; padding: 2rem; min-height: 100vh; }
        
        .table-wrap { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; margin-top: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; }
        
        .btn-primary { background: #2563EB; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-outline { background: white; color: #475569; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        
        .badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: bold; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #b91c1c; }
        .badge-gray { background: #e2e8f0; color: #475569; }
        
        /* Modal Base */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.7); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 2rem; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        
        @media (max-width: 992px) {
            .admin-mobile-header { display: flex !important; }
            main.dashboard-content-main { padding: 1rem !important; }
            .table-wrap { overflow-x: auto; }
            .stat-card { padding: 1rem !important; }
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
<?php require_once __DIR__ . '/sidebar.php'; ?>
<main class="dashboard-content-main" style="flex-grow: 1; padding: 2rem; min-height: 100vh; background: var(--bg-page);">

  <div class="admin-mobile-header" style="display: none; align-items: center; margin-bottom: 1.5rem; background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();" style="margin-right: 1rem; display:flex;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>
    <h2 style="margin: 0; font-size: 1.1rem; color: #1e293b; font-weight: 700;">Menu Administrateur</h2>
  </div>

  <!-- Clic à l'extérieur pour fermer la sidebar mobile -->
  <script>
    document.addEventListener('click', function(e) {
      const sidebar = document.querySelector('.dashboard-sidebar-dark');
      if (sidebar && sidebar.classList.contains('open')) {
        if (!e.target.closest('.dashboard-sidebar-dark') && !e.target.closest('.dashboard-mobile-btn')) {
          sidebar.classList.remove('open');
        }
      }
    });
  </script>
