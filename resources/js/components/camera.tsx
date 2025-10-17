// resources/js/components/camera.tsx
import React, { useState, useEffect, useRef } from 'react';
import {
  FiX, FiRefreshCw, FiCamera, FiCheck, FiMapPin, FiAlertTriangle
} from 'react-icons/fi';

interface CameraProps {
  isOpen: boolean;
  onClose: () => void;
  onCapture: (photoDataUrl: string, gpsCoordinates?: { latitude: number; longitude: number }) => void;
  workerName?: string;
  requireGPS?: boolean;
}

const Camera: React.FC<CameraProps> = ({ 
  isOpen, 
  onClose, 
  onCapture, 
  workerName,
  requireGPS = false
}) => {
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [stream, setStream] = useState<MediaStream | null>(null);
  const [capturedPhoto, setCapturedPhoto] = useState<string | null>(null);
  const [isReady, setIsReady] = useState(false);
  const [facingMode, setFacingMode] = useState<'user' | 'environment'>('user');
  const [gpsCoordinates, setGpsCoordinates] = useState<{ latitude: number; longitude: number } | null>(null);
  const [gpsError, setGpsError] = useState<string | null>(null);
  const [isGettingGPS, setIsGettingGPS] = useState(false);

  const startCamera = async (facing: 'user' | 'environment') => {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      setStream(null);
      setIsReady(false);
    }

    try {
      const mediaStream = await navigator.mediaDevices.getUserMedia({ 
        video: { 
          width: { ideal: 1280 },
          height: { ideal: 720 },
          facingMode: { ideal: facing }
        } 
      });

      setStream(mediaStream);
      
      if (videoRef.current) {
        videoRef.current.srcObject = mediaStream;
        videoRef.current.onloadedmetadata = () => {
          setIsReady(true);
          console.log('✅ Camera ready');
        };
      }
    } catch (err) {
      console.error('❌ Camera error:', err);
      alert('Error: ' + (err instanceof Error ? err.message : 'Unknown camera error'));
    }
  };

  const getGPSCoordinates = () => {
    if (!navigator.geolocation) {
      setGpsError('GPS tidak tersedia di perangkat ini');
      return;
    }

    setIsGettingGPS(true);
    setGpsError(null);

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const coords = {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude
        };
        setGpsCoordinates(coords);
        setIsGettingGPS(false);
        console.log('📍 GPS coordinates:', coords);
      },
      (error) => {
        let errorMsg = 'Gagal mendapatkan lokasi GPS';
        switch (error.code) {
          case error.PERMISSION_DENIED:
            errorMsg = 'Izin lokasi ditolak. Aktifkan GPS di pengaturan browser.';
            break;
          case error.POSITION_UNAVAILABLE:
            errorMsg = 'Lokasi tidak tersedia. Pastikan GPS aktif.';
            break;
          case error.TIMEOUT:
            errorMsg = 'Request lokasi timeout. Coba lagi.';
            break;
        }
        setGpsError(errorMsg);
        setIsGettingGPS(false);
        console.error('❌ GPS error:', error);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  };

  useEffect(() => {
    if (isOpen && videoRef.current) {
      startCamera(facingMode);
      getGPSCoordinates();
    }

    return () => {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        setStream(null);
        setIsReady(false);
      }
    };
  }, [isOpen, facingMode]);

  const switchCamera = () => {
    setFacingMode(prev => prev === 'user' ? 'environment' : 'user');
  };

  const capturePhoto = async () => {
    if (!videoRef.current || !canvasRef.current || !isReady) {
      alert('Camera not ready');
      return;
    }

    if (requireGPS && !gpsCoordinates) {
      alert('GPS coordinates diperlukan untuk absen LOKASI. Pastikan GPS aktif dan izin lokasi diberikan.');
      return;
    }

    // ✅ FETCH SERVER TIME pas capture (bukan realtime)
    let serverTimestamp = '';
    try {
      console.log('🔍 BEFORE FETCH - Client time:', new Date().toLocaleString('id-ID'));
      const response = await fetch('/api/mandor/server-time');
      const data = await response.json();
        console.log('🔍 RAW SERVER DATA:', data);
  console.log('🔍 RAW TIMESTAMP:', data.timestamp);
    const parsedDate = new Date(data.timestamp);
  console.log('🔍 PARSED DATE:', parsedDate);
      serverTimestamp = new Date(data.timestamp).toLocaleString('id-ID', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      });
        console.log('🔍 FINAL FORMATTED:', serverTimestamp);
      console.log('📡 Fetched server time for photo:', serverTimestamp);
    } catch (error) {
      console.error('❌ Failed to fetch server time:', error);
      // Fallback: pakai client time
      serverTimestamp = new Date().toLocaleString('id-ID', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      });
    }

    const video = videoRef.current;
    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');

    if (!ctx) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    // Draw timestamp (dari server)
    ctx.font = 'bold 28px Arial';
    ctx.fillStyle = 'rgba(0, 0, 0, 0.75)';
    const timestampWidth = ctx.measureText(serverTimestamp).width;
    ctx.fillRect(10, canvas.height - 100, timestampWidth + 20, 45);
    ctx.fillStyle = 'white';
    ctx.fillText(serverTimestamp, 20, canvas.height - 67);

    // Draw GPS coordinates
    if (gpsCoordinates) {
      const gpsText = `📍 ${gpsCoordinates.latitude.toFixed(6)}, ${gpsCoordinates.longitude.toFixed(6)}`;
      ctx.font = 'bold 24px Arial';
      const gpsWidth = ctx.measureText(gpsText).width;
      ctx.fillStyle = 'rgba(147, 51, 234, 0.75)';
      ctx.fillRect(10, canvas.height - 50, gpsWidth + 20, 40);
      ctx.fillStyle = 'white';
      ctx.fillText(gpsText, 20, canvas.height - 22);
    }

    const photoDataUrl = canvas.toDataURL('image/jpeg', 0.8);
    setCapturedPhoto(photoDataUrl);
    
    console.log('📸 Photo captured with server timestamp:', serverTimestamp);
    if (gpsCoordinates) {
      console.log('📍 GPS embedded:', gpsCoordinates);
    }
  };

  const confirmPhoto = () => {
    if (capturedPhoto) {
      console.log('✅ Confirming photo with GPS:', gpsCoordinates);
      
      // Backend akan pakai now() sendiri, timestamp dari foto cuma untuk display
      onCapture(capturedPhoto, gpsCoordinates || undefined);
      handleClose();
    }
  };

  const handleClose = () => {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
    }
    setStream(null);
    setIsReady(false);
    setCapturedPhoto(null);
    setGpsCoordinates(null);
    setGpsError(null);
    setIsGettingGPS(false);
    onClose();
  };

  if (!isOpen) return null;

  const canCapture = isReady && (!requireGPS || gpsCoordinates !== null);

  return (
    <div style={{
      position: 'fixed',
      inset: 0,
      zIndex: 50,
      backgroundColor: 'rgba(0,0,0,0.9)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }}>
      <div style={{
        backgroundColor: 'white',
        borderRadius: '12px',
        overflow: 'hidden',
        maxWidth: '600px',
        width: '90%'
      }}>
        {/* Header */}
        <div style={{
          padding: '16px',
          borderBottom: '1px solid #e5e5e5',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}>
          <div>
            <h3 style={{ margin: 0, fontSize: '18px', fontWeight: 'bold' }}>
              Ambil Foto Absensi
            </h3>
            {workerName && (
              <p style={{ margin: '4px 0 0 0', fontSize: '14px', color: '#666' }}>
                {workerName}
              </p>
            )}
            {requireGPS && (
              <p style={{ 
                margin: '4px 0 0 0', 
                fontSize: '12px', 
                color: '#9333ea',
                display: 'flex',
                alignItems: 'center',
                gap: '4px'
              }}>
                <FiMapPin size={12} />
                GPS diperlukan untuk absen LOKASI
              </p>
            )}
          </div>
          <button 
            onClick={handleClose}
            style={{
              border: 'none',
              background: 'none',
              fontSize: '20px',
              cursor: 'pointer',
              padding: '8px'
            }}
          >
            <FiX />
          </button>
        </div>

        {/* GPS Status Bar - Only show for LOKASI */}
        {requireGPS && (
          <div style={{
            padding: '12px 16px',
            backgroundColor: gpsCoordinates ? '#dcfce7' : (gpsError ? '#fee2e2' : '#fef3c7'),
            borderBottom: '1px solid #e5e5e5',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              {isGettingGPS ? (
                <>
                  <div style={{
                    width: '16px',
                    height: '16px',
                    border: '2px solid #f59e0b',
                    borderTopColor: 'transparent',
                    borderRadius: '50%',
                    animation: 'spin 1s linear infinite'
                  }} />
                  <span style={{ fontSize: '14px', color: '#92400e' }}>
                    Mendapatkan lokasi GPS...
                  </span>
                </>
              ) : gpsCoordinates ? (
                <>
                  <FiMapPin style={{ color: '#16a34a' }} />
                  <span style={{ fontSize: '14px', color: '#166534' }}>
                    GPS Ready: {gpsCoordinates.latitude.toFixed(6)}, {gpsCoordinates.longitude.toFixed(6)}
                  </span>
                </>
              ) : gpsError ? (
                <>
                  <FiAlertTriangle style={{ color: '#dc2626' }} />
                  <span style={{ fontSize: '14px', color: '#991b1b' }}>
                    {gpsError}
                  </span>
                </>
              ) : null}
            </div>
            {gpsError && (
              <button
                onClick={getGPSCoordinates}
                style={{
                  backgroundColor: '#dc2626',
                  color: 'white',
                  border: 'none',
                  padding: '4px 12px',
                  borderRadius: '4px',
                  fontSize: '12px',
                  cursor: 'pointer'
                }}
              >
                Coba Lagi
              </button>
            )}
          </div>
        )}

                  {/* Video/Photo Area */}
        <div style={{
          backgroundColor: '#000',
          position: 'relative',
          height: '400px'
        }}>
          <video
            ref={videoRef}
            autoPlay
            muted
            playsInline
            style={{
              width: '100%',
              height: '100%',
              objectFit: 'cover',
              display: capturedPhoto ? 'none' : 'block'
            }}
          />

          {capturedPhoto && (
            <img
              src={capturedPhoto}
              alt="Captured"
              style={{
                width: '100%',
                height: '100%',
                objectFit: 'cover'
              }}
            />
          )}

          <div style={{
            position: 'absolute',
            top: '10px',
            left: '10px',
            backgroundColor: isReady ? '#10b981' : '#f59e0b',
            color: 'white',
            padding: '4px 8px',
            borderRadius: '4px',
            fontSize: '12px'
          }}>
            {isReady ? '✓ Camera Ready' : 'Loading...'}
          </div>

          <button
            onClick={switchCamera}
            disabled={!isReady}
            style={{
              position: 'absolute',
              top: '10px',
              right: '10px',
              backgroundColor: 'rgba(255,255,255,0.9)',
              color: 'black',
              border: '2px solid white',
              borderRadius: '50%',
              width: '44px',
              height: '44px',
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '18px',
              boxShadow: '0 2px 8px rgba(0,0,0,0.3)'
            }}
          >
            <FiRefreshCw />
          </button>

          {!capturedPhoto && (
            <div style={{
              position: 'absolute',
              top: '10px',
              left: '50%',
              transform: 'translateX(-50%)',
              backgroundColor: 'rgba(0,0,0,0.6)',
              color: 'white',
              padding: '4px 8px',
              borderRadius: '4px',
              fontSize: '12px'
            }}>
              {facingMode === 'user' ? '📷 Front Camera' : '📷 Back Camera'}
            </div>
          )}

          {!capturedPhoto && gpsCoordinates && (
            <div style={{
              position: 'absolute',
              bottom: '10px',
              right: '10px',
              backgroundColor: 'rgba(16, 185, 129, 0.8)',
              color: 'white',
              padding: '6px 10px',
              borderRadius: '4px',
              fontSize: '11px',
              display: 'flex',
              alignItems: 'center',
              gap: '4px',
              fontWeight: 'bold'
            }}>
              <FiMapPin size={12} />
              GPS Ready
            </div>
          )}

          <canvas ref={canvasRef} style={{ display: 'none' }} />
        </div>

        {/* Controls */}
        <div style={{ padding: '16px', textAlign: 'center' }}>
          {!capturedPhoto ? (
            <button
              onClick={capturePhoto}
              disabled={!canCapture}
              style={{
                backgroundColor: canCapture ? '#3b82f6' : '#9ca3af',
                color: 'white',
                border: 'none',
                padding: '12px 24px',
                borderRadius: '8px',
                fontSize: '16px',
                cursor: canCapture ? 'pointer' : 'not-allowed',
                display: 'inline-flex',
                alignItems: 'center',
                gap: '8px'
              }}
            >
              <FiCamera /> Ambil Foto
            </button>
          ) : (
            <div style={{ display: 'flex', gap: '12px', justifyContent: 'center' }}>
              <button
                onClick={() => {
                  setCapturedPhoto(null);
                  console.log('🔄 Retaking photo');
                }}
                style={{
                  backgroundColor: '#6b7280',
                  color: 'white',
                  border: 'none',
                  padding: '10px 20px',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '6px'
                }}
              >
                <FiRefreshCw /> Ulangi
              </button>
              <button
                onClick={confirmPhoto}
                style={{
                  backgroundColor: '#10b981',
                  color: 'white',
                  border: 'none',
                  padding: '10px 20px',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '6px'
                }}
              >
                <FiCheck /> Konfirmasi
              </button>
            </div>
          )}
        </div>

        {requireGPS && !gpsCoordinates && !capturedPhoto && (
          <div style={{
            padding: '12px 16px',
            backgroundColor: '#fef3c7',
            borderTop: '1px solid #e5e5e5',
            fontSize: '12px',
            color: '#92400e',
            textAlign: 'center'
          }}>
            💡 GPS WAJIB untuk absen LOKASI. Pastikan GPS aktif dan izin lokasi diberikan ke browser
          </div>
        )}
        
        {!requireGPS && !capturedPhoto && (
          <div style={{
            padding: '12px 16px',
            backgroundColor: '#dbeafe',
            borderTop: '1px solid #e5e5e5',
            fontSize: '12px',
            color: '#1e40af',
            textAlign: 'center'
          }}>
            💡 GPS akan dicatat otomatis untuk absen HADIR (untuk tracking lokasi)
          </div>
        )}
      </div>

      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
};

export default Camera;