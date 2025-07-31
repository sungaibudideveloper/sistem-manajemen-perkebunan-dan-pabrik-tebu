import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  FiArrowLeft, FiRefreshCw, FiCamera, FiUsers, FiCheck, FiCalendar, FiEye, FiX
} from 'react-icons/fi';
import Camera from '../components/camera';

interface Worker {
  tenagakerjaid: string;
  nama: string;
  nik: string;
  gender: string;
  jenistenagakerja: number;
}

interface AttendanceRecord {
  tenagakerjaid: string;
  absenmasuk: string;
  foto_base64: string;
  lokasi_lat: number | null;
  lokasi_lng: number | null;
  tenaga_kerja: {
    nama: string;
    nik: string;
    gender: string;
    jenistenagakerja: number;
  };
}

interface AbsenMandorProps {
  routes: {
    workers: string;
    attendance_today: string;
    process_checkin: string;
  };
  csrf_token: string;
  onSectionChange: (section: string) => void;
}

const AbsenMandor: React.FC<AbsenMandorProps> = ({ 
  routes,
  csrf_token,
  onSectionChange 
}) => {
  const [workers, setWorkers] = useState<Worker[]>([]);
  const [todayAttendance, setTodayAttendance] = useState<AttendanceRecord[]>([]);
  const [selectedWorker, setSelectedWorker] = useState<Worker | null>(null);
  const [isCameraOpen, setIsCameraOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [attendanceDate, setAttendanceDate] = useState(new Date().toISOString().split('T')[0]); // Separate state for attendance filter
  const [viewingPhoto, setViewingPhoto] = useState<string | null>(null);

  useEffect(() => {
    loadWorkersData(); // Workers always for today
  }, []);

  useEffect(() => {
    loadAttendanceData(); // Attendance data based on selected date
  }, [attendanceDate]);

  const loadWorkersData = async () => {
    try {
      const response = await fetch(routes.workers);
      const data = await response.json();
      setWorkers(data.workers || []);
    } catch (error) {
      console.error('Error loading workers:', error);
    }
  };

  const loadAttendanceData = async () => {
    try {
      const response = await fetch(`${routes.attendance_today}?date=${attendanceDate}`);
      const data = await response.json();
      setTodayAttendance(data.attendance || []);
    } catch (error) {
      console.error('Error loading attendance:', error);
    }
  };

  const getAvailableWorkers = () => {
    const attendedWorkerIds = todayAttendance.map(att => att.tenagakerjaid);
    return workers.filter(worker => !attendedWorkerIds.includes(worker.tenagakerjaid));
  };

  const handleWorkerSelect = (worker: Worker) => {
    // Only allow check-in for today
    setSelectedWorker(worker);
    setIsCameraOpen(true);
  };

  const handlePhotoCapture = async (photoDataUrl: string) => {
    if (!selectedWorker) return;

    setIsSubmitting(true);
    try {
      const response = await fetch(routes.process_checkin, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf_token
        },
        body: JSON.stringify({
          tenagakerjaid: selectedWorker.tenagakerjaid,
          photo: photoDataUrl
        })
      });

      const result = await response.json();

      if (result.success) {
        // AUTO REFRESH data setelah berhasil absen
        await Promise.all([
          loadWorkersData(),
          loadAttendanceData()
        ]);
        
        alert(`Absen berhasil untuk ${selectedWorker.nama}`);
      } else {
        alert(result.error || 'Gagal menyimpan absen');
      }
    } catch (error) {
      console.error('Error submitting attendance:', error);
      alert('Terjadi kesalahan saat menyimpan absen');
    } finally {
      setIsSubmitting(false);
      setSelectedWorker(null);
    }
  };

  const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('id-ID', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long', 
      day: 'numeric'
    });
  };

  const getJenisKerja = (jenis: number) => {
    switch(jenis) {
      case 1: return 'Harian';
      case 2: return 'Borongan';
      default: return 'Lainnya';
    }
  };

  const availableWorkers = getAvailableWorkers();
  const isToday = selectedDate === new Date().toISOString().split('T')[0];

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="mb-8">
          <button
            onClick={() => onSectionChange('dashboard')}
            className="flex items-center gap-2 text-gray-600 hover:text-black mb-4 transition-colors"
          >
            <FiArrowLeft className="w-4 h-4" />
            <span className="text-sm font-medium">Kembali ke Beranda</span>
          </button>
          
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-black mb-2">Sistem Absensi</h1>
              <p className="text-gray-600">Pencatatan kehadiran pekerja dengan foto</p>
            </div>
          </div>
          
          {/* Date Info - Only for Today */}
          <div className="mt-4 p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-semibold text-black">
                  {formatDate(new Date().toISOString().split('T')[0])}
                  <span className="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Hari ini</span>
                </h3>
                <p className="text-sm text-gray-500">
                  {availableWorkers.length} pekerja belum absen • {workers.length} total pekerja
                </p>
              </div>
              <div className="text-right">
                <div className="text-2xl font-bold text-black">
                  {workers.length > 0 ? Math.round(((workers.length - availableWorkers.length) / workers.length) * 100) : 0}%
                </div>
                <div className="text-sm text-gray-500">Kehadiran</div>
              </div>
            </div>
          </div>
        </div>

        {/* Main Content - 2 Cards Side by Side (Desktop) */}
        <div className="grid grid-cols-1 2xl:grid-cols-2 gap-8">
          
          {/* Left Card - Belum Absen */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="border-b border-gray-200 p-6">
              <div className="flex items-center gap-3">
                <FiUsers className="w-5 h-5 text-orange-500" />
                <h2 className="text-xl font-semibold text-black">
                  Belum Absen ({availableWorkers.length})
                </h2>
              </div>
            </div>
            
            <div className="max-h-96 overflow-y-auto">
              {availableWorkers.length > 0 ? (
                <div className="divide-y divide-gray-100">
                  {availableWorkers.map((worker) => (
                    <div
                      key={worker.tenagakerjaid}
                      className="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                    >
                      <div className="flex-1">
                        <div className="font-medium text-black">{worker.nama}</div>
                        <div className="text-sm text-gray-500">
                          {worker.nik} • {worker.gender === 'L' ? 'Laki-laki' : 'Perempuan'} • {getJenisKerja(worker.jenistenagakerja)}
                        </div>
                      </div>
                      
                      {isToday && (
                        <button 
                          onClick={() => handleWorkerSelect(worker)}
                          className="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors"
                        >
                          <FiCamera className="w-4 h-4" />
                          <span className="text-sm">Absen</span>
                        </button>
                      )}
                    </div>
                  ))}
                </div>
              ) : (
                <div className="p-8 text-center">
                  <FiCheck className="w-12 h-12 text-green-500 mx-auto mb-4" />
                  <p className="text-gray-500">Semua pekerja sudah absen</p>
                </div>
              )}
            </div>
          </div>

          {/* Right Card - Data Absen dengan Filter */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="border-b border-gray-200 p-6">
              <div className="flex items-center gap-3 mb-3">
                <FiCheck className="w-5 h-5 text-blue-500" />
                <h2 className="text-xl font-semibold text-black">
                  Data Absen ({todayAttendance.length})
                </h2>
              </div>
              
              {/* Date Filter - Below title */}
              <div className="flex items-center gap-2">
                <FiCalendar className="w-4 h-4 text-gray-500" />
                <input
                  type="date"
                  value={attendanceDate}
                  onChange={(e) => setAttendanceDate(e.target.value)}
                  className="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span className="text-sm text-gray-500">
                  {formatDate(attendanceDate)}
                </span>
              </div>
            </div>
            
            <div className="max-h-96 overflow-y-auto">
              {todayAttendance.length > 0 ? (
                <div className="divide-y divide-gray-100">
                  {todayAttendance.map((record) => (
                    <div
                      key={record.tenagakerjaid}
                      className="flex items-center justify-between p-4"
                    >
                      <div className="flex-1">
                        <div className="font-medium text-black">{record.tenaga_kerja.nama}</div>
                        <div className="text-sm text-gray-500">
                          {record.tenaga_kerja.nik} • {formatTime(record.absenmasuk)}
                        </div>
                      </div>
                      
                      <button
                        onClick={() => setViewingPhoto(record.foto_base64)}
                        className="flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                      >
                        <FiEye className="w-4 h-4" />
                        <span className="text-sm">Lihat Foto</span>
                      </button>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="p-8 text-center">
                  <FiUsers className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                  <p className="text-gray-500">Belum ada yang absen</p>
                  <p className="text-xs text-gray-400 mt-1">untuk {formatDate(attendanceDate)}</p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Camera Modal */}
        <Camera
          isOpen={isCameraOpen}
          onClose={() => {
            setIsCameraOpen(false);
            setSelectedWorker(null);
          }}
          onCapture={handlePhotoCapture}
          workerName={selectedWorker?.nama}
        />

        {/* Photo Viewer Modal */}
        {viewingPhoto && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90">
            <div className="relative max-w-4xl max-h-[90vh] p-4">
              <button
                onClick={() => setViewingPhoto(null)}
                className="absolute -top-2 -right-2 p-2 bg-white rounded-full shadow-lg hover:bg-gray-100 transition-colors z-10"
              >
                <FiX className="w-5 h-5" />
              </button>
              <img
                src={viewingPhoto}
                alt="Foto Absensi"
                className="max-w-full max-h-full object-contain rounded-lg"
              />
            </div>
          </div>
        )}

        {/* Loading Overlay */}
        {isSubmitting && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-xl p-6 text-center max-w-sm mx-4">
              <div className="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-black mx-auto mb-4"></div>
              <p className="text-black font-medium">Menyimpan absensi...</p>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default AbsenMandor;