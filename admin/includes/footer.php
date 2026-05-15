        </main><!-- end admin-content -->
    </div><!-- end main-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuToggle = document.getElementById('menuToggle');
        const sidebarClose = document.getElementById('sidebarClose');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('show');
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        menuToggle?.addEventListener('click', openSidebar);
        sidebarClose?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Global SweetAlert2 Delete Handler
        function confirmDelete(formId, itemName = 'item ini') {
            Swal.fire({
                title: 'Hapus Data?',
                html: `Yakin ingin menghapus <strong>${itemName}</strong>?<br><small style="color:#94a3b8;">Tindakan ini tidak bisa dibatalkan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => resolve(true), 600);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        icon: 'info',
                        timer: 800,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        didClose: () => {
                            document.getElementById(formId).submit();
                        }
                    });
                }
            });
        }

        // Global Modal Helper
        function openModal(id) {
            document.getElementById(id).classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('modal-open');
            document.body.style.overflow = '';
        }
        // Close modal on outside click
        document.querySelectorAll('.admin-modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });
    </script>
</body>
</html>
