# Ruang Cerita

Galeri pribadi dua akun berbasis PHP.

## Menjalankan secara lokal

1. Pasang dan jalankan Apache melalui XAMPP.
2. Letakkan folder ini di `C:\xampp\htdocs\putrarpl\web galeri`.
3. Buka `http://localhost/putrarpl/web%20galeri/`.
4. Pada kunjungan pertama, buat dua akun dengan nama berbeda dan kata sandi berbeda (masing-masing 10–72 karakter).

## Hosting online

Aplikasi ini memerlukan PHP 8.1 atau lebih baru dan penyimpanan berkas yang dapat ditulis oleh PHP. Unggah folder proyek ke hosting yang mendukung PHP, aktifkan HTTPS, lalu buka alamat situs dan buat kedua akun pada halaman setup.

GitHub Pages hanya menyajikan berkas statis dan tidak dapat menjalankan PHP. Gunakan layanan hosting PHP untuk menjalankan aplikasi; repositori GitHub menyimpan kode sumbernya.

Data akun disimpan di `private/accounts.json` pada server dan sengaja tidak disertakan di Git. Pastikan folder `private` dapat ditulis oleh PHP, tetap terlindungi dari akses web, dan dicadangkan dengan aman. Pada hosting dengan sistem berkas sementara, gunakan penyimpanan persisten agar akun tidak hilang saat aplikasi dimulai ulang.

Jangan commit `private/accounts.json` atau kredensial pengguna ke repositori.
