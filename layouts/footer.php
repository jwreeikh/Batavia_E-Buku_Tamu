<?php
// layouts/footer.php
?>
</div> <!-- end container-fluid -->
</main>

<footer class="footer px-4 py-4 bg-dark text-light">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="../assets/img/logoo.png" 
                     alt="Logo" 
                     style="height: 38px">
                <span class="ms-3 fw-semibold">E-Buku Tamu Digital</span>
            </div>
            
            <div class="text-end">
                <small>
                    &copy; <?= date('Y') ?> E-Buku Tamu Digital • Attendya<br>
                    <span class="text-muted">All Rights Reserved</span>
                </small>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.getElementById("toggleSidebar");
  const body = document.body;
  const overlay = document.getElementById("sidebarOverlay");

  if (!toggleBtn || !overlay) return;

  function updateExpandedState() {
    const isOpen = window.innerWidth < 1200
      ? body.classList.contains("sidebar-open")
      : !body.classList.contains("sidebar-collapsed");
    toggleBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
  }

  toggleBtn.addEventListener("click", function () {
    if (window.innerWidth < 1200) {
      body.classList.toggle("sidebar-open");
    } else {
      body.classList.toggle("sidebar-collapsed");
    }
    updateExpandedState();
  });

  overlay.addEventListener("click", function () {
    body.classList.remove("sidebar-open");
    updateExpandedState();
  });

  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1200) {
      body.classList.remove("sidebar-open");
    }
    updateExpandedState();
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      body.classList.remove("sidebar-open");
      updateExpandedState();
    }
  });

  updateExpandedState();

  document.querySelectorAll("a.guest-action, button[type='submit'], .btn-submit-loading").forEach(function (el) {
    el.addEventListener("click", function () {
      if (el.classList.contains("btn-loading")) return;
      el.classList.add("btn-loading");

      if (el.tagName === "BUTTON") {
        el.dataset.originalHtml = el.innerHTML;
        el.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
      }
    });
  });
});
</script>
</body>
</html>
