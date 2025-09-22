<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\TbGameResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BankAccountController extends Controller
{
    public function list(Request $request)
    {
        try {
            $bankAccount = BankAccount::where('active_is',1)->get();

            return response()->json([
                'success'  => true,
                'bankAccount' => $bankAccount,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại',
                'error'   => $e->getMessage(), // ⚠️ Chỉ bật debug khi dev, disable khi production
            ], 500);
        }

    }


}
