// KKM Settings
let kkmSettings = {
    nilai_kkm: 70,
    bobot_tp: 1,
    bobot_lm: 1,
    bobot_as: 2
};

// Function to fetch KKM settings
async function fetchKkmSettings(mataPelajaranId) {
    try {
        const response = await fetch(`/pengajar/kkm/${mataPelajaranId}/settings`);
        if (!response.ok) {
            throw new Error('Failed to fetch KKM settings');
        }
        const data = await response.json();
        if (data.success && data.data) {
            kkmSettings = data.data;
            // Update any display elements that show KKM info
            updateKkmDisplay();
        }
    } catch (error) {
        console.error('Error fetching KKM settings:', error);
    }
}

// Update KKM display
function updateKkmDisplay() {
    const kkmDisplayEl = document.getElementById('kkm-display');
    if (kkmDisplayEl) {
        kkmDisplayEl.textContent = `KKM: ${kkmSettings.nilai_kkm}`;
    }
    
    const kkmFormulaEl = document.getElementById('kkm-formula');
    if (kkmFormulaEl) {
        const totalBobot = parseFloat(kkmSettings.bobot_tp) + parseFloat(kkmSettings.bobot_lm) + parseFloat(kkmSettings.bobot_as);
        kkmFormulaEl.textContent = `NA RAPOR = (${kkmSettings.bobot_tp}*S.TP + ${kkmSettings.bobot_lm}*S.LM + ${kkmSettings.bobot_as}*S.AS)/${totalBobot}`;
    }
    
    // Recalculate all rows with new settings
    document.querySelectorAll('#students-table tbody tr').forEach(row => {
        calculateAverages(row);
    });
}

// Single source of truth for form change state
let formChanged = false;

// Initialize form change tracking - only attach once
document.addEventListener('DOMContentLoaded', function() {
    // Get mata pelajaran ID from URL
    const urlParts = window.location.pathname.split('/');
    const mataPelajaranId = urlParts[urlParts.length - 1];
    
    // Fetch KKM settings
    fetchKkmSettings(mataPelajaranId);
    
    // Remove any existing event listeners first to avoid duplicates
    document.querySelectorAll('input').forEach(input => {
        input.removeEventListener('change', markFormChanged);
        input.removeEventListener('input', updateCalculations);
        
        // Add listeners
        input.addEventListener('change', markFormChanged);
        input.addEventListener('input', updateCalculations);
    });
    
    // Initialize calculations
    document.querySelectorAll('#students-table tbody tr').forEach(row => {
        calculateAverages(row);
    });
    
    // Only add these event listeners once
    setupNavigationListeners();
});

function markFormChanged() {
    formChanged = true;
    window.$store.formProtection.markAsChanged();
}

function updateCalculations(e) {
    if (e.target.matches('.tp-score, .lm-score, .nilai-semester')) {
        calculateAverages(e.target.closest('tr'));
        markFormChanged();
    }
}

function calculateAverages(row) {
    // 1. Calculate NA Sumatif TP
    let tpInputs = row.querySelectorAll('.tp-score');
    let tpSum = 0;
    let validTpCount = 0;

    tpInputs.forEach(input => {
        let value = parseFloat(input.value);
        if (!isNaN(value) && value > 0) {
            tpSum += value;
            validTpCount++;
        }
    });

    if (validTpCount > 0) {
        let naTP = tpSum / validTpCount;
        row.querySelector('.na-tp').value = naTP.toFixed(2);
    }

    // 2. Calculate NA Sumatif LM
    let lmInputs = row.querySelectorAll('.lm-score');
    let lmSum = 0;
    let validLmCount = 0;

    lmInputs.forEach(input => {
        let value = parseFloat(input.value);
        if (!isNaN(value) && value > 0) {
            lmSum += value;
            validLmCount++;
        }
    });

    if (validLmCount > 0) {
        let naLM = lmSum / validLmCount;
        row.querySelector('.na-lm').value = naLM.toFixed(2);
    }

    // 3. Calculate NA Sumatif Akhir Semester
    let nilaiTes = parseFloat(row.querySelector('input[name*="[nilai_tes]"]').value) || 0;
    let nilaiNonTes = parseFloat(row.querySelector('input[name*="[nilai_non_tes]"]').value) || 0;

    if (nilaiTes > 0 || nilaiNonTes > 0) {
        let nilaiAkhirSemester = (nilaiTes * 0.6) + (nilaiNonTes * 0.4);
        row.querySelector('input[name*="[nilai_akhir]"]').value = nilaiAkhirSemester.toFixed(2);
    }

    // 4. Calculate Nilai Akhir Rapor dengan bobot KKM
    let naTP = parseFloat(row.querySelector('.na-tp').value) || 0;
    let naLM = parseFloat(row.querySelector('.na-lm').value) || 0;
    let nilaiAkhirSemester = parseFloat(row.querySelector('input[name*="[nilai_akhir]"]').value) || 0;

    if (naTP > 0 || naLM > 0 || nilaiAkhirSemester > 0) {
        // Gunakan bobot dari KKM settings
        const bobotTP = parseFloat(kkmSettings.bobot_tp) || 1;
        const bobotLM = parseFloat(kkmSettings.bobot_lm) || 1;
        const bobotAS = parseFloat(kkmSettings.bobot_as) || 2;
        const totalBobot = bobotTP + bobotLM + bobotAS;
        
        let nilaiAkhirRapor = 0;
        if (totalBobot > 0) {
            nilaiAkhirRapor = ((naTP * bobotTP) + (naLM * bobotLM) + (nilaiAkhirSemester * bobotAS)) / totalBobot;
        }
        
        row.querySelector('input[name*="[nilai_akhir_rapor]"]').value = Math.round(nilaiAkhirRapor);
        
        // Highlight jika di bawah KKM
        const nilaiAkhirRaporInput = row.querySelector('input[name*="[nilai_akhir_rapor]"]');
        if (nilaiAkhirRapor < kkmSettings.nilai_kkm) {
            nilaiAkhirRaporInput.classList.add('bg-red-100');
        } else {
            nilaiAkhirRaporInput.classList.remove('bg-red-100');
        }
    }
}

function validateForm() {
    const form = document.getElementById('saveForm');
    const inputs = form.querySelectorAll('input[type="number"]:not([readonly])');
    let hasEmptyValues = false;

    inputs.forEach(input => {
        if (!input.value && !input.readOnly) {
            hasEmptyValues = true;
        }
    });

    if (hasEmptyValues) {
        return confirm('Beberapa nilai masih kosong. Apakah Anda yakin ingin melanjutkan?');
    }
    return true;
}

function setupNavigationListeners() {
    // Single event listener for beforeunload - handles browser close/refresh
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });

    // Single event listener for Turbo navigation
    document.addEventListener('turbo:before-visit', (event) => {
        if (formChanged) {
            if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
                event.preventDefault();
            } else {
                // User confirmed to leave, reset the flag
                formChanged = false;
            }
        }
    });
}

function deleteNilai(siswaId, mapelId) {
    Swal.fire({
        title: 'Hapus Nilai?',
        text: "Nilai yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/pengajar/score/nilai/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    siswa_id: siswaId,
                    mata_pelajaran_id: mapelId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let row = document.querySelector(`input[name^="scores[${siswaId}]"]`).closest('tr');
                    row.querySelectorAll('input[type="number"]').forEach(input => {
                        input.value = '';
                    });
                    calculateAverages(row);
                    markFormChanged();
                    
                    Swal.fire(
                        'Terhapus!',
                        'Nilai berhasil dihapus.',
                        'success'
                    );
                } else {
                    Swal.fire(
                        'Gagal!',
                        data.message || 'Gagal menghapus nilai',
                        'error'
                    );
                }
            });
        }
    });
}

window.saveData = async function() {
    try {
        if (!validateForm()) {
            return;
        }

        Swal.fire({
            title: 'Menyimpan Nilai...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(document.getElementById('saveForm'));
        
        const urlParts = window.location.pathname.split('/');
        const mataPelajaranId = urlParts[urlParts.length - 1];
        const saveUrl = `/pengajar/score/${mataPelajaranId}/save`;
        
        const response = await fetch(saveUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        
        if (data.success) {
            // Reset the form changed flag after successful save
            formChanged = false;
            Alpine.store('formProtection').reset();
            
            // Simpan data untuk ditampilkan nanti jika user klik detail
            const detailData = data;
            
            const result = await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Nilai berhasil disimpan!',
                confirmButtonText: 'Lihat Preview',
                confirmButtonColor: '#10b981', // Green color
                showCancelButton: true,
                cancelButtonText: 'Lihat Detail',
                cancelButtonColor: '#6b7280', // Gray color
                reverseButtons: true
            });
            
            if (result.isConfirmed) {
                // User memilih "Lihat Preview"
                window.location.href = `/pengajar/score/${mataPelajaranId}/preview`;
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // User memilih "Lihat Detail"
                let detailMessage = '<ul class="text-left max-h-60 overflow-y-auto">';
                detailData.details.forEach(student => {
                    detailMessage += `<li class="mb-2"><strong>${student.nama}</strong>:<br>`;
                    student.nilai.forEach(nilai => {
                        let kkmClass = '';
                        if ('memenuhi_kkm' in nilai && !nilai.memenuhi_kkm) {
                            kkmClass = 'class="text-red-600"';
                        }
                        const kode = nilai.kode ? `${nilai.kode}: ` : '';
                        detailMessage += `- <span ${kkmClass}>${nilai.tipe} ${kode}${nilai.nilai}${nilai.memenuhi_kkm === false ? ' (Tidak memenuhi KKM)' : ''}</span><br>`;
                    });
                    detailMessage += '</li>';
                });
                detailMessage += '</ul>';

                if (detailData.warnings && Object.keys(detailData.warnings).length > 0) {
                    detailMessage += '<div class="mt-4 p-3 bg-yellow-100 text-yellow-700 rounded">';
                    detailMessage += '<strong>Peringatan:</strong><br>';
                    Object.entries(detailData.warnings).forEach(([siswa, warnings]) => {
                        detailMessage += `<strong>${siswa}:</strong><br>`;
                        warnings.forEach(warning => {
                            detailMessage += `- ${warning}<br>`;
                        });
                    });
                    detailMessage += '</div>';
                }
                
                const detailResult = await Swal.fire({
                    icon: 'info',
                    title: 'Detail Nilai',
                    html: detailMessage,
                    width: '600px',
                    confirmButtonText: 'Lihat Preview',
                    confirmButtonColor: '#10b981' // Green color
                });
                
                if (detailResult.isConfirmed) {
                    window.location.href = `/pengajar/score/${mataPelajaranId}/preview`;
                }
            }
        } else {
            throw new Error(data.message || 'Terjadi kesalahan saat menyimpan nilai');
        }
    } catch (error) {
        console.error('Error:', error);
        Alpine.store('formProtection').isSubmitting = false;
        await Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: error.message || 'Terjadi kesalahan saat menyimpan nilai'
        });
    }
};