$guru = App\Models\Guru::where('username', 'pengajar')->first();
if ($guru) {
    $guru->mataPelajarans()->each(function ($mataPelajaran) {
        $mataPelajaran->nilais()->delete();
        $mataPelajaran->lingkupMateris->each(function ($lingkupMateri) {
            $lingkupMateri->tujuanPembelajarans()->delete();
        });
        $mataPelajaran->lingkupMateris()->delete();
    });
    $guru->mataPelajarans()->delete();
    echo "Data mata pelajaran dan terkait untuk guru 'pengajar' telah dihapus.";
} else {
    echo "Guru dengan username 'pengajar' tidak ditemukan.";
}