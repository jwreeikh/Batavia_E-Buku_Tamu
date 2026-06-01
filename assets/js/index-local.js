$(document).ready(function () {
    // Initialize Select2 for Guru with AJAX
    const initSelect2Guru = () => {
        // Fungsi ini tidak lagi digunakan karena dropdown guru spesifik dihapus
        console.warn("initSelect2Guru dipanggil, tetapi dropdown guru spesifik telah dihapus.");
    };

    // Toggle tampilan field berdasarkan kategori yang dipilih
    $('#kategori_tamu').on('change', function() {
        const kategori = $(this).val();
        
        // Sembunyikan semua grup, kecuali field-guru-spesifik karena sudah dihapus dari HTML
        $('.field-instansi, .field-siswa, .field-umum-instansi, .field-siswa-biasa').hide();
        
        // Reset required attributes
        $('#instansi, #nisn, #sub_kategori, #tujuan, #tujuan_id, #tujuan_id_siswa, #tujuan_siswa').prop('required', false);

        if (kategori === 'siswa') {
            // Tampilkan field siswa
            $('.field-siswa').fadeIn();
            $('#nisn, #sub_kategori').prop('required', true);
            // Trigger sub-kategori check
            $('#sub_kategori').trigger('change');
        } else if (kategori === 'instansi') {
            $('.field-instansi, .field-umum-instansi').fadeIn(); // Tampilkan field instansi dan umum/instansi
            $('#instansi, #tujuan_id, #tujuan').prop('required', true); // Set required
        } else {
            $('.field-umum-instansi').fadeIn(); // Tampilkan field umum/instansi
            $('#tujuan_id, #tujuan').prop('required', true); // Set required
        }
    });

    // Toggle Sub-Kategori Siswa (Legalisir vs Biasa)
    $('#sub_kategori').on('change', function() {
        const sub = $(this).val();

        if (sub === 'biasa') {
            $('.field-siswa-biasa').fadeIn();
            $('#tujuan_id_siswa, #tujuan_siswa').prop('required', true);
        } else {
            $('.field-siswa-biasa').hide();
            $('#tujuan_id_siswa, #tujuan_siswa').prop('required', false).val('');
            $('#error-tujuan-id-siswa, #error-tujuan-siswa').text('');
        }
    });

    // Fungsi pembantu untuk cek apakah field Guru harus muncul (ID 3 = Guru)
    function checkGuruDisplay(tujuanId) {
        // Fungsi ini tidak lagi digunakan karena dropdown guru spesifik dihapus
        console.warn("checkGuruDisplay dipanggil, tetapi dropdown guru spesifik telah dihapus.");
    }

    // Listener untuk dropdown Tujuan Layanan
    // Listener ini tidak lagi diperlukan karena tidak ada dropdown guru spesifik
    // $('#tujuan_id, #tujuan_id_siswa').on('change', function() {
    //     checkGuruDisplay($(this).val());
    // });

    // Fungsi untuk menampilkan Alert Bootstrap Modern
    const showAlert = (message, type = 'danger') => {
        const alertPlaceholder = $('#alertPlaceholder');
        const html = `<div class="alert alert-${type} alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>`;
        alertPlaceholder.html(html);
        // Scroll modal ke atas agar user melihat pesan error
        $('#modalTamu').animate({ scrollTop: 0 }, 'fast');
    };

    // Submit Form Tamu
    const form = document.getElementById("formTamu");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            
            // Bersihkan error lama
            $('.text-danger').text('');
            $('#alertPlaceholder').empty();

            let formData = new FormData(form);
            const guestName = formData.get('nama') || 'Tamu';
            const kategori = formData.get('kategori_tamu');

            // --- VALIDASI FRONTEND ---
            const nameRegex = /^[A-Za-zÀ-ÿ\s.,'-]+$/;
            const phoneRegex = /^\d{10,13}$/;
            const nisnRegex = /^\d{10}$/;

            if (!nameRegex.test(formData.get('nama'))) {
                showAlert("Nama hanya boleh huruf, spasi, titik, koma, tanda petik (') dan strip (-)");
                return;
            }
            if (formData.get('nama').length > 60) {
                showAlert("Nama maksimal 60 karakter");
                return;
            }
            if (!phoneRegex.test(formData.get('no_hp'))) {
                showAlert("Nomor HP harus berupa angka 10 - 13 digit");
                return;
            }
            if (kategori === 'siswa') {
                if (!nisnRegex.test(formData.get('nisn'))) {
                    showAlert("NISN wajib 10 digit angka");
                    return;
                }
                if (formData.get('sub_kategori') === 'legalisir') {
                    formData.set('tujuan_id_siswa', '');
                    formData.set('tujuan_siswa', '');
                    formData.set('tujuan_id', '');
                    formData.set('tujuan', '');
                }
            }
            // --- END VALIDASI FRONTEND ---

            const submitBtn = form.querySelector("button[type='submit']");
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            }

            fetch("proses_tamu.php", { method: "POST", body: formData })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        if (data.errors) {
                            let firstError = data.errors[0].message;
                            data.errors.forEach(error => {
                                if (error.field) {
                                    $(`#error-${error.field}`).text(error.message);
                                }
                            });
                            showAlert(firstError);
                            return;
                        }
                        throw new Error(data.message || 'Gagal menyimpan data');
                    }

                    const targetLog = (kategori === 'siswa') ? 'Data Siswa/Alumni' : 
                                     (kategori === 'instansi' ? 'Data Tamu Instansi' : 'Data Tamu Umum');

                    $('#modalTamu').modal('hide');
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Kunjungan Berhasil Disimpan!', 
                        text: `Terima kasih, ${guestName}! Data Anda telah disimpan ke ${targetLog}.`, 
                        timer: 3000, 
                        showConfirmButton: false 
                    });
                    form.reset();
                    $('#kategori_tamu').val('umum').trigger('change'); // Reset kategori dan tampilkan field default
                    
                    // Simpan kategori untuk redirect nanti
                    window.lastKategori = kategori;
                    
                    setTimeout(() => openReview(data.tamu_id, guestName, kategori), 3000); 
                })
                .catch(err => Swal.fire({ icon: 'error', title: 'Gagal', text: err.message }))
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalHtml || 'Kirim Kunjungan';
                    }
                });
        });
    }

    // Jalankan trigger saat awal untuk memastikan state form benar
    $('#kategori_tamu').trigger('change');
});

function openReview(tamuId, guestName, kategori) {
    let rating = 0;
    let selectedTags = [];
    const pTags = ["Ramah", "Cepat", "Modern", "Profesional", "Nyaman", "Memuaskan"];
    const nTags = ["Cukup Baik", "Perlu Pengembangan", "Lumayan"];
    const xTags = ["Lambat", "Kurang Ramah", "Perlu Perbaikan", "Tidak Jelas", "Kurang Nyaman"];

    Swal.fire({
        title: 'Berikan Penilaian',
        html: `<div id="stars" style="margin-bottom:15px">${Array(5).fill(0).map((_,i)=>`<span class="star3d" data-value="${i+1}">&#9733;</span>`).join('')}</div><div id="tags"></div><br><button id="kirimR" class="btn btn-primary w-100" disabled>Kirim Review</button><button id="skipR" class="btn btn-light w-100 mt-2">Lewati</button>`,
        showConfirmButton: false,
        didOpen: () => {
            const stars = document.querySelectorAll('.star3d');
            const tagsEl = document.getElementById('tags');
            const kirim = document.getElementById('kirimR');

            stars.forEach((s, idx) => {
                s.onclick = () => {
                    rating = idx + 1;
                    stars.forEach((item, i) => item.classList.toggle('active', i < rating));
                    const tags = rating >= 4 ? pTags : (rating === 3 ? nTags : xTags);
                    tagsEl.innerHTML = tags.map(t => `<span class="tag3d" data-tag="${t}">${t}</span>`).join('');
                    selectedTags = [];
                    document.querySelectorAll('.tag3d').forEach(btn => {
                        btn.onclick = function() {
                            const t = this.dataset.tag;
                            if (selectedTags.includes(t)) {
                                selectedTags = selectedTags.filter(i => i !== t);
                                this.classList.remove('active');
                            } else if (selectedTags.length < 3) {
                                selectedTags.push(t);
                                this.classList.add('active');
                            }
                        };
                    });
                    kirim.disabled = false;
                };
            });

            kirim.onclick = () => {
                fetch("proses_review.php", { method: "POST", body: new URLSearchParams({ tamu_id: tamuId, rating, tags: selectedTags.join(',') }) })
                    .then(r => r.json())
                    .then(d => {
                        if (!d.success) throw new Error(d.message);
                        Swal.fire({ icon: 'success', title: 'Terima kasih' }).then(() => {
                            window.location.replace('index.php');
                        });
                    }).catch(e => Swal.fire({ icon: 'error', title: 'Gagal', text: e.message }));
            };
            document.getElementById('skipR').onclick = () => {
                Swal.close();
                window.location.replace('index.php');
            };
        }
    });
}
