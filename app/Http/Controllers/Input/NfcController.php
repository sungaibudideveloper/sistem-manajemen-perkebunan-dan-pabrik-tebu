<?php
// app\Http\Controllers\Input\NfcController.php

namespace App\Http\Controllers\Input;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * NfcController
 * 
 * Manages NFC card inventory tracking
 * Balance system: Kantor (warehouse) vs Mandor
 * POS balance is auto-calculated from suratjalanpos (1 SJ = 1 NFC card)
 */
class NfcController extends Controller
{
    /**
     * Display NFC card inventory
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        
        // Get kantor balance
        $kantorBalance = DB::table('nfc')
            ->where('companycode', $companycode)
            ->whereNull('mandorid')
            ->value('balance') ?? 0;
        
        // ✅ POS balance = COUNT of suratjalanpos (1 SJ = 1 NFC card)
        $posBalance = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->count();
        
        // Get cards at POS per mandor for reference
        $posDataByMandor = DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->groupBy('mandorid')
            ->select([
                'mandorid',
                DB::raw('COUNT(*) as cards_at_pos')
            ])
            ->get()
            ->keyBy('mandorid');
        
        // Get all mandor balances
        $mandorBalances = DB::table('nfc as n')
            ->leftJoin('user as u', 'n.mandorid', '=', 'u.userid')
            ->where('n.companycode', $companycode)
            ->whereNotNull('n.mandorid')
            ->select([
                'n.id',
                'n.mandorid',
                'u.name as mandorname',
                'n.balance',
                'n.lasttransaction',
                'n.notes'
            ])
            ->orderBy('u.name')
            ->get()
            ->map(function($mandor) use ($posDataByMandor) {
                $cardsAtPos = $posDataByMandor[$mandor->mandorid]->cards_at_pos ?? 0;
                $mandor->cards_at_pos = $cardsAtPos;
                $mandor->actual_balance = $mandor->balance - $cardsAtPos;
                return $mandor;
            });
        
        // Get recent transactions (exclude auto POS transactions)
        $recentTransactions = DB::table('nfctransaction as nt')
            ->leftJoin('user as u', 'nt.mandorid', '=', 'u.userid')
            ->where('nt.companycode', $companycode)
            ->select([
                'nt.id',
                'nt.transactionno',
                'nt.transactiondate',
                'nt.transactiontype',
                'nt.mandorid',
                'u.name as mandorname',
                'nt.qty',
                'nt.notes',
                'nt.inputby',
                'nt.createdat'
            ])
            ->orderBy('nt.createdat', 'desc')
            ->limit(30)
            ->get();
        
        // Get mandor list for dropdown
        $mandorList = DB::table('user')
            ->where('companycode', $companycode)
            ->where('idjabatan', 5) // Mandor jabatan
            ->where('isactive', 1)
            ->select('userid', 'name')
            ->orderBy('name')
            ->get();
        
        return view('input.nfc.index', [
            'title' => 'NFC Card Management',
            'navbar' => 'Input',
            'nav' => 'NFC',
            'kantorBalance' => $kantorBalance,
            'posBalance' => $posBalance,
            'mandorBalances' => $mandorBalances,
            'recentTransactions' => $recentTransactions,
            'mandorList' => $mandorList
        ]);
    }

    /**
     * Process OUT transaction (Kantor → Mandor)
     */
    public function transactionOut(Request $request)
    {
        $request->validate([
            'mandorid' => 'required|exists:user,userid',
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            // Check kantor balance
            $kantorBalance = $this->getBalance($companycode, null);
            
            if ($kantorBalance < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok kantor tidak cukup. Tersedia: {$kantorBalance} kartu"
                ], 400);
            }

            // Generate transaction number
            $transactionNo = $this->generateTransactionNo($companycode);

            // Create transaction record
            DB::table('nfctransaction')->insert([
                'transactionno' => $transactionNo,
                'companycode' => $companycode,
                'transactiondate' => $request->transactiondate,
                'transactiontype' => 'OUT',
                'mandorid' => $request->mandorid,
                'qty' => $request->qty,
                'notes' => $request->notes,
                'inputby' => $currentUser,
                'createdat' => now()
            ]);

            // Update kantor balance (decrease)
            $this->updateBalance($companycode, null, -$request->qty);

            // Update mandor balance (increase)
            $this->updateBalance($companycode, $request->mandorid, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengeluarkan {$request->qty} kartu NFC untuk mandor",
                'transactionno' => $transactionNo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC Transaction OUT Error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process IN transaction (Mandor → Kantor)
     */
    public function transactionIn(Request $request)
    {
        $request->validate([
            'mandorid' => 'required|exists:user,userid',
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            // Check mandor balance (harus cek actual balance - cards at POS)
            $mandorBalance = $this->getBalance($companycode, $request->mandorid);
            $cardsAtPos = $this->getCardsAtPos($companycode, $request->mandorid);
            $actualBalance = $mandorBalance - $cardsAtPos;
            
            if ($actualBalance < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Balance mandor tidak cukup. Tersedia: {$actualBalance} kartu (Total: {$mandorBalance}, At POS: {$cardsAtPos})"
                ], 400);
            }

            // Generate transaction number
            $transactionNo = $this->generateTransactionNo($companycode);

            // Create transaction record
            DB::table('nfctransaction')->insert([
                'transactionno' => $transactionNo,
                'companycode' => $companycode,
                'transactiondate' => $request->transactiondate,
                'transactiontype' => 'IN',
                'mandorid' => $request->mandorid,
                'qty' => $request->qty,
                'notes' => $request->notes,
                'inputby' => $currentUser,
                'createdat' => now()
            ]);

            // Update mandor balance (decrease)
            $this->updateBalance($companycode, $request->mandorid, -$request->qty);

            // Update kantor balance (increase)
            $this->updateBalance($companycode, null, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menerima {$request->qty} kartu NFC dari mandor",
                'transactionno' => $transactionNo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC Transaction IN Error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process IN from POS (POS → Kantor)
     * Return NFC cards from POS back to warehouse
     */
    public function posIn(Request $request)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            // Check POS balance (real-time from suratjalanpos count)
            $posBalance = DB::table('suratjalanpos')
                ->where('companycode', $companycode)
                ->count();
            
            if ($posBalance < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Balance POS tidak cukup. Tersedia: {$posBalance} kartu (dari SJ yang sudah dicetak)"
                ], 400);
            }

            // Generate transaction number
            $transactionNo = $this->generateTransactionNo($companycode);

            // Create transaction record
            DB::table('nfctransaction')->insert([
                'transactionno' => $transactionNo,
                'companycode' => $companycode,
                'transactiondate' => $request->transactiondate,
                'transactiontype' => 'IN',
                'mandorid' => 'POS',
                'qty' => $request->qty,
                'notes' => $request->notes ?? 'Return from POS to warehouse',
                'inputby' => $currentUser,
                'createdat' => now()
            ]);

            // Update kantor balance (increase)
            $this->updateBalance($companycode, null, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menerima {$request->qty} kartu NFC dari POS",
                'transactionno' => $transactionNo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC POS IN Error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process EXTERNAL IN transaction (Pembelian/Penambahan stock dari luar)
     */
    public function externalIn(Request $request)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'transactiondate' => 'required|date',
            'notes' => 'required|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            // Generate transaction number
            $transactionNo = $this->generateTransactionNo($companycode);

            // Create transaction record with mandorid = 'EXTERNAL'
            DB::table('nfctransaction')->insert([
                'transactionno' => $transactionNo,
                'companycode' => $companycode,
                'transactiondate' => $request->transactiondate,
                'transactiontype' => 'IN',
                'mandorid' => 'EXTERNAL',
                'qty' => $request->qty,
                'notes' => 'EXTERNAL IN: ' . $request->notes,
                'inputby' => $currentUser,
                'createdat' => now()
            ]);

            // Update kantor balance (increase)
            $this->updateBalance($companycode, null, $request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menambah {$request->qty} kartu NFC ke stock kantor",
                'transactionno' => $transactionNo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC External IN Error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process EXTERNAL OUT transaction (Kartu rusak/hilang/disposal)
     */
    public function externalOut(Request $request)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'reason' => 'required|in:DAMAGED,LOST,DISPOSAL',
            'transactiondate' => 'required|date',
            'notes' => 'required|string|max:500'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user()->userid;

            DB::beginTransaction();

            // Check kantor balance
            $kantorBalance = $this->getBalance($companycode, null);
            
            if ($kantorBalance < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok kantor tidak cukup. Tersedia: {$kantorBalance} kartu"
                ], 400);
            }

            // Generate transaction number
            $transactionNo = $this->generateTransactionNo($companycode);

            $reasonText = match($request->reason) {
                'DAMAGED' => 'Damaged',
                'LOST' => 'Lost',
                'DISPOSAL' => 'Disposal',
                default => 'Unknown'
            };

            // Create transaction record with mandorid = 'EXTERNAL'
            DB::table('nfctransaction')->insert([
                'transactionno' => $transactionNo,
                'companycode' => $companycode,
                'transactiondate' => $request->transactiondate,
                'transactiontype' => 'OUT',
                'mandorid' => 'EXTERNAL',
                'qty' => $request->qty,
                'notes' => "EXTERNAL OUT ({$reasonText}): " . $request->notes,
                'inputby' => $currentUser,
                'createdat' => now()
            ]);

            // Update kantor balance (decrease)
            $this->updateBalance($companycode, null, -$request->qty);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengurangi {$request->qty} kartu NFC dari stock kantor ({$reasonText})",
                'transactionno' => $transactionNo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("NFC External OUT Error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Get current balance for holder (kantor or mandor)
     */
    private function getBalance($companycode, $mandorid)
    {
        $query = DB::table('nfc')
            ->where('companycode', $companycode);
        
        if ($mandorid === null) {
            $query->whereNull('mandorid');
        } else {
            $query->where('mandorid', $mandorid);
        }
        
        return $query->value('balance') ?? 0;
    }

    /**
     * Get cards at POS for specific mandor (from suratjalanpos count)
     */
    private function getCardsAtPos($companycode, $mandorid)
    {
        return DB::table('suratjalanpos')
            ->where('companycode', $companycode)
            ->where('mandorid', $mandorid)
            ->count();
    }

    /**
     * Update balance (create if not exists)
     */
    private function updateBalance($companycode, $mandorid, $qtyChange)
    {
        $existing = DB::table('nfc')
            ->where('companycode', $companycode)
            ->where(function($q) use ($mandorid) {
                if ($mandorid === null) {
                    $q->whereNull('mandorid');
                } else {
                    $q->where('mandorid', $mandorid);
                }
            })
            ->first();

        if ($existing) {
            // Update existing
            DB::table('nfc')
                ->where('id', $existing->id)
                ->update([
                    'balance' => DB::raw("balance + {$qtyChange}"),
                    'lasttransaction' => now(),
                    'updateby' => Auth::user()->userid ?? 'SYSTEM',
                    'updatedat' => now()
                ]);
        } else {
            // Create new
            DB::table('nfc')->insert([
                'companycode' => $companycode,
                'mandorid' => $mandorid,
                'balance' => max(0, $qtyChange), // Prevent negative initial balance
                'lasttransaction' => now(),
                'inputby' => Auth::user()->userid ?? 'SYSTEM',
                'createdat' => now()
            ]);
        }
    }

    /**
     * Generate unique transaction number
     * Format: NFCYYYYMMDDXXXX
     */
    private function generateTransactionNo($companycode)
    {
        $date = now()->format('Ymd');
        $prefix = "NFC{$date}";

        $lastTx = DB::table('nfctransaction')
            ->where('companycode', $companycode)
            ->where('transactionno', 'like', "{$prefix}%")
            ->orderBy('transactionno', 'desc')
            ->value('transactionno');

        if ($lastTx) {
            $sequence = (int) substr($lastTx, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}