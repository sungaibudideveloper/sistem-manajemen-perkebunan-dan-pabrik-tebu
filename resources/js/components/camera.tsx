// resources\js\components\camera.tsx - WITH REAR CAMERA
import React, { useRef, useState, useEffect } from 'react';
import { FiCamera, FiX, FiRotateCcw, FiCheck, FiRefreshCw } from 'react-icons/fi';

interface CameraProps {
  isOpen: boolean;
  onClose: () => void;
  onCapture: (photoDataUrl: string) => void;
  workerName?: string;
}

const Camera: React.FC<CameraProps> = ({ 
  isOpen, 
  onClose, 
  onCapture, 
  workerName 
}) => {
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [stream, setStream] = useState<MediaStream | null>(null);
  const [capturedPhoto, setCapturedPhoto] = useState<string | null>(null);
  const [isReady, setIsReady] = useState(false);
  const [facingMode, setFacingMode] = useState<'user' | 'environment'>('user'); // user = depan, environment = belakang

  const startCamera = async (facing: 'user' | 'environment') => {
    // Stop existing stream
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      setStream(null);
      setIsReady(false);
    }

    try {
      console.log('Starting camera with facingMode:', facing);
      
      const mediaStream = await navigator.mediaDevices.getUserMedia({ 
        video: { 
          width: { ideal: 1280 },
          height: { ideal: 720 },
          facingMode: { ideal: facing } // gunakan object untuk fleksibilitas
        } 
      });

      console.log('Got stream:', mediaStream);
      setStream(mediaStream);
      
      if (videoRef.current) {
        videoRef.current.srcObject = mediaStream;
        videoRef.current.onloadedmetadata = () => {
          console.log('Video metadata loaded');
          setIsReady(true);
        };
      }
    } catch (err) {
      console.error('Camera error:', err);
      const errorMessage = err instanceof Error ? err.message : 'Unknown camera error';
      alert('Error: ' + errorMessage);
    }
  };

  useEffect(() => {
    if (isOpen && videoRef.current) {
      startCamera(facingMode);
    }

    return () => {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        setStream(null);
        setIsReady(false);
      }
    };
  }, [isOpen, facingMode]); // Tambah facingMode ke dependency

  const switchCamera = () => {
    setFacingMode(prev => prev === 'user' ? 'environment' : 'user');
  };

  const capturePhoto = () => {
    if (!videoRef.current || !canvasRef.current || !isReady) {
      alert('Camera not ready');
      return;
    }

    const video = videoRef.current;
    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');

    if (!ctx) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    const photoDataUrl = canvas.toDataURL('image/jpeg', 0.8);
    setCapturedPhoto(photoDataUrl);
  };

  const confirmPhoto = () => {
    if (capturedPhoto) {
      onCapture(capturedPhoto);
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
    onClose();
  };

  if (!isOpen) return null;

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

        {/* Video/Photo Area */}
        <div style={{
          backgroundColor: '#000',
          position: 'relative',
          height: '400px'
        }}>
          {/* Video */}
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

          {/* Captured Photo */}
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

          {/* Status Indicator */}
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

          {/* Camera Switch Button */}
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
              title={facingMode === 'user' ? 'Switch to Back Camera' : 'Switch to Front Camera'}
            >
              <FiRefreshCw />
            </button>

          {/* Camera Mode Indicator */}
          {!capturedPhoto && (
            <div style={{
              position: 'absolute',
              bottom: '10px',
              left: '10px',
              backgroundColor: 'rgba(0,0,0,0.6)',
              color: 'white',
              padding: '4px 8px',
              borderRadius: '4px',
              fontSize: '12px'
            }}>
              {facingMode === 'user' ? 'Front' : 'Back'}
            </div>
          )}

          {/* Hidden Canvas */}
          <canvas ref={canvasRef} style={{ display: 'none' }} />
        </div>

        {/* Controls */}
        <div style={{ padding: '16px', textAlign: 'center' }}>
          {!capturedPhoto ? (
            <button
              onClick={capturePhoto}
              disabled={!isReady}
              style={{
                backgroundColor: isReady ? '#3b82f6' : '#9ca3af',
                color: 'white',
                border: 'none',
                padding: '12px 24px',
                borderRadius: '8px',
                fontSize: '16px',
                cursor: isReady ? 'pointer' : 'not-allowed',
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
                onClick={() => setCapturedPhoto(null)}
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
                <FiRotateCcw /> Ulangi
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
      </div>
    </div>
  );
};

export default Camera;