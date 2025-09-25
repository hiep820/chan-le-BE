<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\TbGameResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TbGameResultController extends Controller
{

    public function historyAll(Request $request)
    {
        try {
            $gameResult = TbGameResult::where('result','win')->take(10)->get();
            if (! $gameResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu trống.'
                ], 404);
            }
            return response()->json([
                'success'  => true,
                'historyAll' => $gameResult,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $e->errors(),
            ], 422);
        }
    }


}
