<?php
require_once '../config/app.php';
require_once '../config/koneksi.php';
require_once '../config/log.php';
/** @var mysqli $conn */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$query = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

include '../layouts/header.php';
include '../layouts/sidebar.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card p-4 shadow-lg border-radius-xl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Management User</h5>
        <button type="button" class="btn bg-gradient-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i> Tambah User
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success text-white border-0"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-white border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="tabelUser" class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Username</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Dibuat</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($u = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><p class="text-xs font-weight-bold mb-0 ps-3"><?= $no++; ?></p></td>
                        <td><p class="text-sm font-weight-bold mb-0"><?= htmlspecialchars($u['username']); ?></p></td>
                        <td><span class="badge badge-sm bg-gradient-secondary"><?= ucfirst($u['role']); ?></span></td>
                        <td><p class="text-xs mb-0"><?= date('d/m/Y', strtotime($u['created_at'])); ?></p></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-info btn-edit" 
                                    data-id="<?= $u['id'] ?>" 
                                    data-username="<?= $u['username'] ?>" 
                                    data-role="<?= $u['role'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="proses-user.php?hapus=<?= $u['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="proses-user.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="resepsionis">Resepsionis</option>
                            <option value="tu">TU</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn bg-gradient-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="proses-user.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="edit-username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (Kosongkan jika tidak ganti)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="edit-role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="resepsionis">Resepsionis</option>
                            <option value="tu">TU</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn bg-gradient-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelUser').DataTable({
            "info": false,
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data",
                "search": "Cari:",
                "paginate": {
                    "next": ">",
                    "previous": "<"
                }
            },
            "pageLength": 10
        });

        $('.btn-edit').on('click', function() {
            const id = $(this).data('id');
            const username = $(this).data('username');
            const role = $(this).data('role');
            
            $('#edit-id').val(id);
            $('#edit-username').val(username);
            $('#edit-role').val(role);
            $('#modalEdit').modal('show');
        });
    });
</script>

<?php include '../layouts/footer.php'; ?>