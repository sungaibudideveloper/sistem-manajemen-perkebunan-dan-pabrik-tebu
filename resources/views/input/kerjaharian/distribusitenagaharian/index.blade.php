<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Laporan DTH & Borongan</title>
  <style>
    @page {
      size: A4;
      margin: 20mm;
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20mm;
      box-sizing: border-box;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .header-left {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .header-left a button,
    .header-left button {
      padding: 6px 12px;
      font-size: 14px;
      cursor: pointer;
    }

    .header-right {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      font-size: 14px;
      margin-top: 10px;
    }

    .section-title {
      text-align: center;
      font-size: 18px;
      margin: 20px 0 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      margin-bottom: 20px;
      table-layout: fixed;
    }

    table, th, td {
      border: 1px solid #000;
    }

    th, td {
      padding: 4px;
      text-align: center;
      word-wrap: break-word;
    }

    tfoot td {
      font-weight: bold;
    }

    .footer {
      margin-top: 80px;
      font-size: 12px;
      display: flex;
      justify-content: space-between;
      text-align: center;
    }

    .footer .signature {
      width: 30%;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="header-left">
        <a href="{{ url()->previous() }}"><button>Kembali</button></a>
        <button onclick="window.print()">Cetak</button>
        <a href="#"><button>Export PDF</button></a>
        <a href="#"><button>Export Excel</button></a>
      </div>
      <div class="header-right">
        <div class="date">Tanggal: <span id="report-date"></span></div>
        <div class="revision">Revisi: 1</div>
      </div>
    </div>

    <h2 class="section-title">DISTRIBUSI TENAGA HARIAN</h2>
    <table>
    <colgroup>
        <col style="width: 5%;">
        <col style="width: 15%;">
        <col style="width: 20%;">
        <col style="width: 6%;">
        <col style="width: 6%;">
        <col style="width: 6%;">
        <col style="width: 5%;">
        <col style="width: 5%;">
        <col style="width: 6%;">
        <col style="width: 12%;">
      </colgroup>
      <thead>
  <tr>
    <th rowspan="2">No.</th>
    <th rowspan="2">Mandor</th>
    <th rowspan="2">Kegiatan</th>
    <th rowspan="2">Blok</th>
    <th rowspan="2">Plot</th>
    <th rowspan="2">RKH</th>
    <th colspan="3">Tenaga</th>
    <th rowspan="2">Keterangan</th>
  </tr>
  <tr>
    <th>L</th>
    <th>P</th>
    <th>Total</th>
  </tr>
</thead>
      <tbody id="dth-rows"></tbody>
      <tfoot>
        <tr>
          <td colspan="6">TOTAL</td>
          <td id="sum-laki"></td>
          <td id="sum-perempuan"></td>
          <td id="sum-total"></td>
          <td></td>
        </tr>
      </tfoot>
    </table>

    <h2 class="section-title">DISTRIBUSI TENAGA BORONGAN</h2>
    <table>
      <colgroup>
        <col style="width: 5%;">
        <col style="width: 15%;">
        <col style="width: 20%;">
        <col style="width: 6%;">
        <col style="width: 6%;">
        <col style="width: 6%;">
        <col style="width: 5%;">
        <col style="width: 5%;">
        <col style="width: 6%;">
        <col style="width: 12%;">
      </colgroup>
      <thead>
  <tr>
    <th rowspan="2">No.</th>
    <th rowspan="2">Mandor</th>
    <th rowspan="2">Kegiatan</th>
    <th rowspan="2">Blok</th>
    <th rowspan="2">Plot</th>
    <th rowspan="2">RKH</th>
    <th colspan="3">Tenaga</th>
    <th rowspan="2">Keterangan</th>
  </tr>
  <tr>
    <th>L</th>
    <th>P</th>
    <th>Total</th>
  </tr>
</thead>
      <tbody id="borongan-rows"></tbody>
      <tfoot>
        <tr>
          <td colspan="6">TOTAL</td>
          <td id="sum-laki-borongan"></td>
          <td id="sum-perempuan-borongan"></td>
          <td id="sum-total-borongan"></td>
          <td></td>
        </tr>
      </tfoot>
    </table>

    <div class="footer">
      <div class="signature">Mengetahui<br><br><br><br><br>Asisten Lapangan</div>
      <div class="signature">Diperiksa<br><br><br><br><br>Asisten Kepala</div>
      <div class="signature">Disiapkan<br><br><br><br><br>PPC</div>
    </div>
  </div>

  <script>
    // Tanggal hari ini
    document.getElementById('report-date').textContent = new Date().toLocaleDateString('id-ID');

    // Data DTH
    const dthData = [
      {no: 1, mandor: 'Gerald', kegiatan: 'Perbaikan Jalan', blok: 'A', plot: 'A12', rkh: '12 ha', laki: 3, perempuan: 1, keterangan: ''},
      {no: 2, mandor: 'Gerald', kegiatan: 'Pembakaran Api', blok: 'B', plot: 'B43', rkh: '15 ha', laki: 10, perempuan: 0, keterangan: ''},
      {no: 3, mandor: 'Gerald', kegiatan: 'Herbisida Jalan', blok: 'C', plot: 'C27', rkh: '10 ha', laki: 4, perempuan: 2, keterangan: ''},
      {no: 4, mandor: 'Nathan', kegiatan: 'Drainase', blok: 'D', plot: 'D08', rkh: '8 ha', laki: 2, perempuan: 0, keterangan: ''},
      {no: 5, mandor: 'Nathan', kegiatan: 'Perbaikan Jalan', blok: 'A', plot: 'A35', rkh: '14 ha', laki: 5, perempuan: 1, keterangan: ''},
      {no: 6, mandor: 'Nathan', kegiatan: 'Weeding', blok: 'B', plot: 'B19', rkh: '11 ha', laki: 3, perempuan: 4, keterangan: ''},
      {no: 7, mandor: 'Angky', kegiatan: 'Pengamatan Hama', blok: 'C', plot: 'C05', rkh: '9 ha', laki: 4, perempuan: 0, keterangan: ''},
      {no: 8, mandor: 'Angky', kegiatan: 'Weeding', blok: 'D', plot: 'D22', rkh: '13 ha', laki: 8, perempuan: 6, keterangan: ''}
    ];

    const dthBody = document.getElementById('dth-rows');
    let totalL = 0, totalP = 0;
    dthData.forEach(item => {
      const row = document.createElement('tr');
      const total = item.laki + item.perempuan;
      row.innerHTML = `
        <td>${item.no}</td>
        <td>${item.mandor}</td>
        <td>${item.kegiatan}</td>
        <td>${item.blok}</td>
        <td>${item.plot}</td>
        <td>${item.rkh}</td>
        <td>${item.laki}</td>
        <td>${item.perempuan}</td>
        <td>${total}</td>
        <td>${item.keterangan}</td>
      `;
      dthBody.appendChild(row);
      totalL += item.laki;
      totalP += item.perempuan;
    });

    document.getElementById('sum-laki').textContent = totalL;
    document.getElementById('sum-perempuan').textContent = totalP;
    document.getElementById('sum-total').textContent = totalL + totalP;

    // Data Borongan
    const boronganData = [
      {no: 1, mandor: 'Suhardi', kegiatan: 'Pemanenan Buah', blok: 'E', plot: 'E01', rkh: '20 ha', laki: 12, perempuan: 0, keterangan: ''},
      {no: 2, mandor: 'Darto', kegiatan: 'Pemangkasan', blok: 'F', plot: 'F07', rkh: '18 ha', laki: 6, perempuan: 3, keterangan: ''},
      {no: 3, mandor: 'Samsul', kegiatan: 'Pemupukan', blok: 'G', plot: 'G15', rkh: '22 ha', laki: 7, perempuan: 2, keterangan: ''},
      {no: 4, mandor: 'Budi S.', kegiatan: 'Perawatan Jalan', blok: 'H', plot: 'H09', rkh: '16 ha', laki: 5, perempuan: 1, keterangan: ''},
      {no: 5, mandor: 'Tatang R.', kegiatan: 'Pembersihan Parit', blok: 'I', plot: 'I04', rkh: '19 ha', laki: 8, perempuan: 2, keterangan: ''}
    ];

    const boronganBody = document.getElementById('borongan-rows');
    let totalBorL = 0, totalBorP = 0;
    boronganData.forEach(item => {
      const row = document.createElement('tr');
      const total = item.laki + item.perempuan;
      row.innerHTML = `
        <td>${item.no}</td>
        <td>${item.mandor}</td>
        <td>${item.kegiatan}</td>
        <td>${item.blok}</td>
        <td>${item.plot}</td>
        <td>${item.rkh}</td>
        <td>${item.laki}</td>
        <td>${item.perempuan}</td>
        <td>${total}</td>
        <td>${item.keterangan}</td>
      `;
      boronganBody.appendChild(row);
      totalBorL += item.laki;
      totalBorP += item.perempuan;
    });

    document.getElementById('sum-laki-borongan').textContent = totalBorL;
    document.getElementById('sum-perempuan-borongan').textContent = totalBorP;
    document.getElementById('sum-total-borongan').textContent = totalBorL + totalBorP;
  </script>
</body>
</html>
