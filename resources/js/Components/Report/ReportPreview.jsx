import React, { useState, useEffect } from 'react';

const ReportPreview = ({ reportData, onSave, onUpload }) => {
  const [editMode, setEditMode] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [formData, setFormData] = useState({
    subjects: [],
    extracurricular: [],
    attendance: {
      sick: 0,
      permitted: 0,
      noPermission: 0
    },
    teacherNote: '',
    type: '',
    title: '',
    tahunAjaran: ''
  });

  const getReportTitle = () => {
    const baseTitle = "Data Rapor";
    if (reportData?.type === "UTS") {
      return `${baseTitle} UTS`;
    } else if (reportData?.type === "UAS") {
      return `${baseTitle} UAS`;
    }
    return baseTitle;
  };

  useEffect(() => {
    if (reportData) {
      setFormData({
        subjects: reportData.subjects || [],
        extracurricular: reportData.extracurricular || [],
        attendance: reportData.attendance || {
          sick: 0,
          permitted: 0,
          noPermission: 0
        },
        teacherNote: reportData.teacherNote || '',
        type: reportData.type || '',
        title: reportData.title || '',
        tahunAjaran: reportData.tahunAjaran || ''
      });
    }
  }, [reportData]);

  const handleSubjectChange = (index, field, value) => {
    const updatedSubjects = [...formData.subjects];
    updatedSubjects[index][field] = value;
    setFormData({ ...formData, subjects: updatedSubjects });
  };

  const handleExtracurricularChange = (index, field, value) => {
    const updatedExtracurricular = [...formData.extracurricular];
    updatedExtracurricular[index][field] = value;
    setFormData({ ...formData, extracurricular: updatedExtracurricular });
  };

  const handleSave = async () => {
    try {
      setIsLoading(true);
      await onSave(formData);
      setEditMode(false);
    } catch (error) {
      console.error('Error saving report:', error);
      alert('Gagal menyimpan data rapor');
    } finally {
      setIsLoading(false);
    }
  };

  const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
      setIsLoading(true);
      const formData = new FormData();
      formData.append('template', file);
      formData.append('type', reportData?.type || 'UTS');
      await onUpload(formData);
    } catch (error) {
      console.error('Error uploading file:', error);
      alert('Gagal mengupload file');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
      {/* Header */}
      <div className="flex justify-between items-center mb-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            {getReportTitle()}
          </h2>
          <div className="mt-2 space-y-1">
            <p className="text-sm text-gray-500 dark:text-gray-400">Nama: {reportData?.studentName}</p>
            <p className="text-sm text-gray-500 dark:text-gray-400">NISN/NIS: {reportData?.studentId}</p>
            <p className="text-sm text-gray-500 dark:text-gray-400">Kelas: {reportData?.class}</p>
            <p className="text-sm text-gray-500 dark:text-gray-400">Tahun Pelajaran: {reportData?.academicYear}</p>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="space-x-2">
          {!formData.subjects.length ? (
            <label className="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 cursor-pointer dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
              <input 
                type="file"
                accept=".docx,.pdf"
                className="hidden"
                onChange={handleFileUpload}
                disabled={isLoading}
              />
              {isLoading ? 'Uploading...' : 'Upload Template'}
            </label>
          ) : (
            editMode ? (
              <>
                <button
                  onClick={handleSave}
                  disabled={isLoading}
                  className="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800"
                >
                  {isLoading ? 'Menyimpan...' : 'Simpan'}
                </button>
                <button
                  onClick={() => setEditMode(false)}
                  disabled={isLoading}
                  className="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                >
                  Batal
                </button>
              </>
            ) : (
              <button
                onClick={() => setEditMode(true)}
                className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
              >
                Edit
              </button>
            )
          )}
        </div>
      </div>

      {/* Main Content */}
      {!formData.subjects.length ? (
        <div className="flex items-center justify-center h-48 mb-4 rounded bg-gray-50 dark:bg-gray-700">
          <p className="text-2xl text-gray-400 dark:text-gray-500">
            Belum ada template rapor yang diupload untuk {reportData?.type || 'tipe ini'}
          </p>
        </div>
      ) : (
        <>
          {/* Subjects Table */}
          <div className="mb-6 relative overflow-x-auto shadow-md sm:rounded-lg">
            <table className="w-full text-sm text-left text-gray-500 dark:text-gray-400">
              <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                  <th scope="col" className="px-6 py-3">No</th>
                  <th scope="col" className="px-6 py-3">Mata Pelajaran</th>
                  <th scope="col" className="px-6 py-3">Nilai</th>
                  <th scope="col" className="px-6 py-3">Capaian Kompetensi</th>
                </tr>
              </thead>
              <tbody>
                {formData.subjects.map((subject, index) => (
                  <tr key={index} className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td className="px-6 py-4">{index + 1}</td>
                    <td className="px-6 py-4">{subject.name}</td>
                    <td className="px-6 py-4">
                      {editMode ? (
                        <input
                          type="number"
                          value={subject.score}
                          onChange={(e) => handleSubjectChange(index, 'score', e.target.value)}
                          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-20 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        />
                      ) : (
                        subject.score
                      )}
                    </td>
                    <td className="px-6 py-4">
                      {editMode ? (
                        <input
                          value={subject.competency}
                          onChange={(e) => handleSubjectChange(index, 'competency', e.target.value)}
                          className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        />
                      ) : (
                        subject.competency
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Extracurricular Table */}
          <div className="mb-6">
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">Ekstrakurikuler</h3>
            <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
              <table className="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                  <tr>
                    <th scope="col" className="px-6 py-3">No</th>
                    <th scope="col" className="px-6 py-3">Kegiatan Ekstrakurikuler</th>
                    <th scope="col" className="px-6 py-3">Predikat</th>
                    <th scope="col" className="px-6 py-3">Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  {formData.extracurricular.map((extra, index) => (
                    <tr key={index} className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                      <td className="px-6 py-4">{index + 1}</td>
                      <td className="px-6 py-4">{extra.name}</td>
                      <td className="px-6 py-4">
                        {editMode ? (
                          <input
                            value={extra.grade}
                            onChange={(e) => handleExtracurricularChange(index, 'grade', e.target.value)}
                            className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-20 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                          />
                        ) : (
                          extra.grade
                        )}
                      </td>
                      <td className="px-6 py-4">
                        {editMode ? (
                          <input
                            value={extra.description}
                            onChange={(e) => handleExtracurricularChange(index, 'description', e.target.value)}
                            className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                          />
                        ) : (
                          extra.description
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          {/* Attendance and Teacher's Note */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">Ketidakhadiran</h3>
              <div className="p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                <div className="space-y-4">
                  <div className="flex items-center">
                    <span className="w-32 text-gray-500 dark:text-gray-400">Sakit</span>
                    <span className="text-gray-500 dark:text-gray-400">: </span>
                    {editMode ? (
                      <input
                        type="number"
                        value={formData.attendance.sick}
                        onChange={(e) => setFormData({
                          ...formData,
                          attendance: { ...formData.attendance, sick: e.target.value }
                        })}
                        className="ml-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-20 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                      />
                    ) : (
                      <span className="ml-2 text-gray-500 dark:text-gray-400">{formData.attendance.sick} Hari</span>
                    )}
                  </div>
                  <div className="flex items-center">
                    <span className="w-32 text-gray-500 dark:text-gray-400">Izin</span>
                    <span className="text-gray-500 dark:text-gray-400">: </span>
                    {editMode ? (
                      <input
                        type="number"
                        value={formData.attendance.permitted}
                        onChange={(e) => setFormData({
                          ...formData,
                          attendance: { ...formData.attendance, permitted: e.target.value }
                        })}
// ... lanjutan dari kode sebelumnya ...
className="ml-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-20 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
/>
) : (
<span className="ml-2 text-gray-500 dark:text-gray-400">{formData.attendance.permitted} Hari</span>
)}
</div>
<div className="flex items-center">
<span className="w-32 text-gray-500 dark:text-gray-400">Tanpa Keterangan</span>
<span className="text-gray-500 dark:text-gray-400">: </span>
{editMode ? (
<input
  type="number"
  value={formData.attendance.noPermission}
  onChange={(e) => setFormData({
    ...formData,
    attendance: { ...formData.attendance, noPermission: e.target.value }
  })}
  className="ml-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-20 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
/>
) : (
<span className="ml-2 text-gray-500 dark:text-gray-400">{formData.attendance.noPermission} Hari</span>
)}
</div>
</div>
</div>
</div>

<div>
<h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">Catatan Guru</h3>
<div className="p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
{editMode ? (
<textarea
value={formData.teacherNote}
onChange={(e) => setFormData({ ...formData, teacherNote: e.target.value })}
className="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
rows="4"
placeholder="Masukkan catatan guru..."
/>
) : (
<p className="text-sm text-gray-500 dark:text-gray-400 min-h-[100px]">
{formData.teacherNote || 'Tidak ada catatan'}
</p>
)}
</div>
</div>
</div>
</>
)}
</div>
);
};

export default ReportPreview;