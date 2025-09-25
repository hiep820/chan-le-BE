<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TbGameResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name'=> [
                    'required',
                    'string',
                    'max:255',
                    'unique:customers,name',
                    'regex:/^[a-zA-Z0-9_]+$/', // chỉ cho phép chữ, số, gạch dưới
                ],
                'password' => 'required|string|min:6|confirmed',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'account_holder' => 'required|string',
            ]);

            $customer = Customer::create([
                'name'     => $data['name'],
                'password' => bcrypt($data['password']),
                'bank_name' =>  $data['bank_name'],
                'account_number' =>  $data['account_number'],
                'account_holder' =>  $data['account_holder'],

            ]);

            $token = $customer->createToken('customer_token')->plainTextToken;

            return response()->json([
                'success'  => true,
                'message'  => 'Đăng ký thành công',
                'token'    => $token,
                'customer' => $customer,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Lỗi validate
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Các lỗi khác (DB, server, ...)
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra, vui lòng thử lại',
                'error'   => $e->getMessage(), // ⚠️ Chỉ bật debug khi dev, disable khi production
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string',
                'password' => 'required|string',
            ]);

            $customer = Customer::where('name', $data['name'])->first();

            if (! $customer || ! Hash::check($data['password'], $customer->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sai thông tin đăng nhập.'
                ], 401);
            }

            $token = $customer->createToken('customer_token')->plainTextToken;

            return response()->json([
                'success'  => true,
                'message'  => 'Đăng nhập thành công',
                'token'    => $token,
                'customer' => $customer,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Lỗi validate
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Lỗi khác
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function profile(Request $request)
    {

        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    public function historyCustomer(Request $request)
    {
        try {
            $customer = Customer::with(['gameResults' => function ($query) {
                $query->orderBy('created_at', 'desc')->take(10);
            }])->find($request->customer_id);
            if (! $customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người chơi không tồn tại.'
                ], 404);
            }
            return response()->json([
                'success'  => true,
                'customer' => $customer,
                'history'  => $customer->gameResults,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    public function totalBetDate($id)
    {
        try {
            $date = Carbon::now();

            $sumAmount = TbGameResult::where('customer_id',$id)->whereDate('created_at',$date)->sum('amount');

            return response()->json([
                'success'  => true,
                'sum' => $sumAmount,
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
