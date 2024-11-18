import './bootstrap';

import 'flowbite';

import Turbolinks from 'turbolinks';
Turbolinks.start();

document.addEventListener('turbolinks:load', () => {
    // Inisialisasi ulang dropdown menu

    // Seleksi tombol toggle dropdown
    const toggle = document.querySelector('[data-collapse-toggle="dropdown-rapor"]');
    const target = document.getElementById('dropdown-rapor');

    if (toggle && target) {
        // Hapus event listener sebelumnya
        toggle.removeEventListener('click', handleToggle);

        // Tambahkan event listener baru
        toggle.addEventListener('click', handleToggle);

        // Pastikan state dropdown tertutup pada awalnya
        target.classList.add('hidden');
    }

    function handleToggle(event) {
        event.preventDefault();
        target.classList.toggle('hidden');
    }
});