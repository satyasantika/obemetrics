<script>
    (function () {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) {
            return;
        }

        jQuery.extend(true, jQuery.fn.dataTable.defaults, {
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ entri per halaman',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 entri',
                infoFiltered: '(disaring dari _MAX_ total entri)',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Tidak ada data tersedia',
                processing: 'Memproses...',
                loadingRecords: 'Memuat...',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                },
                aria: {
                    sortAscending: ': aktifkan untuk mengurutkan kolom naik',
                    sortDescending: ': aktifkan untuk mengurutkan kolom turun'
                }
            }
        });
    })();
</script>
<script>
    (function () {
        var labelEl = document.getElementById('topbar-menu-label');
        if (!labelEl) {
            return;
        }

        var activeLink = document.querySelector('.main-sidebar .nav-sidebar .nav-link.active');
        if (!activeLink) {
            return;
        }

        var sourceEl = activeLink.querySelector('p') || activeLink;
        var text = (sourceEl.textContent || '').replace(/\s+/g, ' ').trim();
        if (!text) {
            return;
        }

        labelEl.textContent = text;
        if (labelEl.parentElement) {
            labelEl.parentElement.setAttribute('title', text);
        }
    })();
</script>
