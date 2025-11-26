<?php
/**
 * Main Client Page - Single Page Application
 * clientside/index.php
 * 
 * This loads different "pages" as tabs dynamically - NO flicker!
 */

session_start();
include "../db.php";

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$avatar = !empty($_SESSION['avatar']) 
    ? "../photos/avatars/" . $_SESSION['avatar'] 
    : "../photos/avatars/default.png";

// Get current page/tab (default: dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$valid_pages = ['dashboard', 'browse', 'requests', 'borrowings'];
if (!in_array($page, $valid_pages)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Borrow System | Resident Portal</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  
  <link rel="preconnect" href="https://cdn.tailwindcss.com">
  <link rel="preconnect" href="https://unpkg.com">
  
  <style>
    html { opacity: 0; }
    html.loaded { opacity: 1; transition: opacity 0.15s; }
    
    /* Content area - NO transitions for instant swap */
    #pageContent {
      min-height: calc(100vh - 4rem);
    }
    
    /* Loading indicator */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, #3b82f6, #2563eb);
      z-index: 9999;
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.3s ease;
    }
    
    .loading-overlay.active {
      transform: scaleX(1);
    }
    
    [data-feather] { 
      display: inline-block; 
      width: 1.25rem; 
      height: 1.25rem; 
      vertical-align: middle;
    }
  </style>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: { 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' }
          }
        }
      }
    }
  </script>

  <link rel="preload" href="https://unpkg.com/feather-icons" as="script">
  <script src="https://unpkg.com/feather-icons"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.documentElement.classList.add('loaded');
      if (typeof feather !== 'undefined') feather.replace();
    });
  </script>
</head>
<body class="flex flex-col bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

  <div class="loading-overlay" id="loadingBar"></div>

  <?php include "resident_sidebar.php"; ?>
  <?php include "header.php"; ?>

  <!-- Dynamic Content Area -->
  <main id="pageContent" class="flex-1 ml-16 md:ml-64 p-4 sm:p-6 lg:p-10 pt-24 md:pt-28 lg:pt-32">
    <?php
    // Load the current page content
    $page_file = __DIR__ . "/pages/{$page}.php";
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        echo '<div class="text-red-600">Page not found</div>';
    }
    ?>
  </main>

  <footer class="ml-16 md:ml-64">
    <?php include "footer.php"; ?>
  </footer>

  <!-- AJAX Page Loader - Same as Admin Tabs -->
  <script>
    (function() {
      'use strict';

      const PageLoader = {
        content: document.getElementById('pageContent'),
        loading: document.getElementById('loadingBar'),
        currentPage: '<?php echo $page; ?>',

        init() {
          this.setupLinks();
          this.setupHistory();
        },

        setupLinks() {
          // Intercept sidebar clicks
          document.addEventListener('click', (e) => {
            const link = e.target.closest('aside a[data-page]');
            if (!link) return;
            
            e.preventDefault();
            
            const page = link.getAttribute('data-page');
            if (page === this.currentPage) return;
            
            this.loadPage(page);
          });
        },

        setupHistory() {
          window.addEventListener('popstate', (e) => {
            if (e.state && e.state.page) {
              this.loadPage(e.state.page, false);
            }
          });

          // Set initial state
          history.replaceState({ page: this.currentPage }, '', `?page=${this.currentPage}`);
        },

        async loadPage(page, updateHistory = true) {
          try {
            // Show loading
            this.loading.classList.add('active');

            // Fetch page content
            const response = await fetch(`load_page.php?page=${page}`);
            
            if (!response.ok) throw new Error('Load failed');
            
            const html = await response.text();

            // Update content instantly
            this.content.innerHTML = html;

            // Update active link
            this.updateActiveLink(page);

            // Re-initialize icons
            if (typeof feather !== 'undefined') {
              feather.replace();
            }

            // Update URL
            if (updateHistory) {
              history.pushState({ page }, '', `?page=${page}`);
            }

            this.currentPage = page;

            // Hide loading
            setTimeout(() => {
              this.loading.classList.remove('active');
            }, 200);

            // Scroll to top
            window.scrollTo(0, 0);

            // Dispatch event
            window.dispatchEvent(new CustomEvent('pageChanged', { detail: { page } }));

          } catch (error) {
            console.error('Page load error:', error);
            this.loading.classList.remove('active');
            
            // Fallback to full reload
            window.location.href = `?page=${page}`;
          }
        },

        updateActiveLink(page) {
          document.querySelectorAll('aside a').forEach(link => {
            link.classList.remove('bg-white', 'text-blue-900', 'font-semibold');
            if (!link.id || link.id !== 'logoutBtn') {
              link.classList.add('hover:bg-blue-800');
            }
          });

          const activeLink = document.querySelector(`aside a[data-page="${page}"]`);
          if (activeLink) {
            activeLink.classList.add('bg-white', 'text-blue-900', 'font-semibold');
            activeLink.classList.remove('hover:bg-blue-800');
          }
        }
      };

      document.addEventListener('DOMContentLoaded', () => {
        PageLoader.init();
      });

    })();
  </script>

</body>
</html>