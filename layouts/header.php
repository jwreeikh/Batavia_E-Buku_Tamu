<?php

if (!function_exists('e')) {
  function e($value)
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('renderPagination')) {
  function renderPagination($currentPage, $totalPages, $params = [])
  {
    $currentPage = max(1, (int) $currentPage);
    $totalPages = max(1, (int) $totalPages);

    if ($totalPages <= 1) {
      return;
    }

    $buildUrl = function ($page) use ($params) {
      $params['page'] = $page;
      return '?' . http_build_query($params);
    };

    $pages = [1, $totalPages, $currentPage - 1, $currentPage, $currentPage + 1];
    if ($currentPage <= 3) {
      $pages[] = 2;
      $pages[] = 3;
    }
    if ($currentPage >= $totalPages - 2) {
      $pages[] = $totalPages - 1;
      $pages[] = $totalPages - 2;
    }

    $pages = array_values(array_unique(array_filter($pages, function ($page) use ($totalPages) {
      return $page >= 1 && $page <= $totalPages;
    })));
    sort($pages);

    echo '<nav class="pagination-wrap" aria-label="Navigasi halaman"><ul class="pagination pagination-modern">';

    $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
    echo '<li class="page-item' . $prevDisabled . '"><a class="page-link" href="' . ($currentPage > 1 ? e($buildUrl($currentPage - 1)) : '#') . '">&lt; Prev</a></li>';

    $last = 0;
    foreach ($pages as $page) {
      if ($last && $page > $last + 1) {
        echo '<li class="page-item disabled"><span class="page-link page-ellipsis">...</span></li>';
      }

      $active = $page === $currentPage ? ' active' : '';
      echo '<li class="page-item' . $active . '"><a class="page-link" href="' . e($buildUrl($page)) . '">' . $page . '</a></li>';
      $last = $page;
    }

    $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
    echo '<li class="page-item' . $nextDisabled . '"><a class="page-link" href="' . ($currentPage < $totalPages ? e($buildUrl($currentPage + 1)) : '#') . '">Next &gt;</a></li>';
    echo '</ul></nav>';
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Attendya - E-Buku Tamu Digital</title>
  <link rel="icon" type="image/png" href="../assets/img/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet">
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet">
  <link href="../assets/css/soft-ui-dashboard.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="../assets/css/custom.css" rel="stylesheet">
  <link href="../assets/css/admin-consistency.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="../assets/css/theme-premium-green-gold.css" rel="stylesheet">
  <style>
    body {
      overflow-x: hidden;
      -webkit-text-size-adjust: 100%;
    }

    /* SIDEBAR */
    #sidenav-main {
      transition: all 0.3s ease;
      z-index: 1040;
      background-color: #ffffff !important;
    }

    /* FIX NAVBAR LENGKET & GLITCH */
    .navbar-main {
      position: sticky;
      top: 0;
      z-index: 1030;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }

    .main-content {
      padding-top: 0 !important;
    }

    /* Pastikan tidak ada jarak antara navbar dan konten */
    .container-fluid.py-4 {
      padding-top: 1.5rem !important;
    }

    /* Fix Glitch Final */
    @media (max-width: 1199.98px) {
      .main-content {
        padding-bottom: 130px !important;
      }
    }

    /* OVERLAY */
    .sidebar-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      opacity: 0;
      visibility: hidden;
      transition: 0.3s;
      z-index: 1030;
    }

    .main-content {
      min-height: calc(100vh - 72px);
      transition: margin-left 0.3s ease;
    }

    .navbar-main {
      min-height: 64px;
      gap: 12px;
    }

    #toggleSidebar {
      width: 42px;
      height: 42px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      flex: 0 0 auto;
    }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      border-radius: 14px;
    }

    .table-responsive .table {
      margin-bottom: 0;
      vertical-align: middle;
    }

    .table-fixed {
      table-layout: fixed;
      min-width: 760px;
      width: 100%;
    }

    .table-compact th,
    .table-compact td {
      padding: 0.75rem 0.7rem !important;
      vertical-align: middle;
    }

    .table-compact th {
      color: #64748b;
      font-size: 0.72rem;
      letter-spacing: 0;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .cell-truncate,
    .table-compact td[data-bs-toggle="tooltip"] {
      display: block;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .text-wrap-balance {
      overflow-wrap: anywhere;
    }

    .action-group {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      white-space: nowrap;
    }

    /* STANDARISASI TOMBOL AKSI */
    .guest-action {
      width: 34px;
      height: 34px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      color: #fff !important;
      border: none;
      transition: all 0.2s;
      text-decoration: none;
      cursor: pointer;
      font-size: 0.85rem;
    }

    .guest-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      opacity: 0.85;
      color: #fff !important;
    }

    .guest-action.approve {
      background: linear-gradient(310deg, #2dce89, #2dcecc) !important;
    }

    .guest-action.reject {
      background: linear-gradient(310deg, #f5365c, #f56036) !important;
    }

    .guest-action.detail {
      background: linear-gradient(310deg, #344767, #212529) !important;
    }

    /* Hitam keabu-abuan tua */
    .guest-action.pdf {
      background: linear-gradient(310deg, #825ee4, #5e72e4) !important;
    }

    .guest-action.finish {
      background: linear-gradient(310deg, #11cdef, #1171ef) !important;
    }

    .guest-action.undo {
      background: linear-gradient(310deg, #fb6340, #fbb140) !important;
    }

    .guest-action.disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none !important;
      box-shadow: none !important;
    }

    .btn-icon-only {
      width: 36px;
      height: 36px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      padding: 0;
    }

    .pagination-wrap {
      display: flex;
      justify-content: center;
      margin-top: 1.25rem;
      overflow: visible;
      padding: 0 8px 4px;
    }

    .pagination-modern {
      flex-wrap: wrap;
      justify-content: center;
      gap: 7px;
      margin-bottom: 0;
      max-width: 100%;
    }

    .pagination-modern .page-link {
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      color: #5e72e4;
      min-width: 38px;
      min-height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      box-shadow: 0 8px 18px rgba(94, 114, 228, 0.08);
      white-space: nowrap;
    }

    .pagination-modern .active .page-link {
      background: linear-gradient(135deg, #5e72e4, #cb0c9f);
      border-color: transparent;
      color: #fff;
    }

    .pagination-modern .disabled .page-link {
      color: #94a3b8;
      background: #f8fafc;
      box-shadow: none;
      pointer-events: none;
    }

    .pagination-modern .page-ellipsis {
      min-width: 32px;
    }

    img {
      max-width: 100%;
    }

    /* MOBILE */
    @media (max-width: 1199px) {
      #sidenav-main {
        transform: translateX(-100%);
      }

      body.sidebar-open #sidenav-main {
        transform: translateX(0);
      }

      body.sidebar-open .sidebar-overlay {
        opacity: 1;
        visibility: visible;
      }

      .main-content {
        margin-left: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
      }

      #sidenav-main {
        width: min(285px, 86vw);
        box-shadow: 16px 0 36px rgba(15, 23, 42, 0.18);
      }
    }

    /* DESKTOP */
    @media (min-width: 1200px) {
      body.sidebar-collapsed #sidenav-main {
        transform: translateX(-100%);
      }

      body.sidebar-collapsed .main-content {
        margin-left: 0 !important;
      }
    }

    /* FOOTER FIX */
    .footer {
      margin-left: 270px;
      transition: 0.3s;
    }

    body.sidebar-collapsed .footer {
      margin-left: 0;
    }

    @media (max-width:1199px) {
      .footer {
        margin-left: 0;
      }

      .container-fluid.py-4 {
        padding-left: 14px !important;
        padding-right: 14px !important;
      }

      .navbar-main {
        padding-left: 14px !important;
        padding-right: 14px !important;
      }

      .navbar-main h6 {
        max-width: 42vw;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .card {
        border-radius: 14px !important;
      }

      .table-fixed {
        min-width: 680px;
      }

      .table-compact th,
      .table-compact td {
        padding: 0.65rem 0.55rem !important;
        font-size: 0.82rem;
      }

      .pagination-modern .page-link {
        min-width: 34px;
        min-height: 34px;
        padding: 0.35rem 0.55rem;
        font-size: 0.78rem;
      }
    }

    @media (max-width: 480px) {
      .pagination-wrap {
        margin-top: 1rem;
        padding-left: 0;
        padding-right: 0;
      }

      .pagination-modern {
        gap: 5px;
      }

      .pagination-modern .page-link {
        min-width: 32px;
        min-height: 34px;
        border-radius: 9px;
        padding: 0.32rem 0.48rem;
        font-size: 0.74rem;
      }
    }
  </style>

</head>

<body class="g-sidenav-show bg-gray-100">