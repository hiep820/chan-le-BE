<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\TbGameResult;
use App\Models\TbTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookReceiverController extends Controller
{

    public function webhook(Request $request)
    {
        try {

            $data = json_decode(file_get_contents('php://input'));
            if(!is_object($data)) {
                echo json_encode(['success'=>FALSE, 'message' => 'No data']);
                die();
            }
            log::info('Webhook received: ' . json_encode($data));

            $transaction_id = $data->id;

            $gateway = $data->gateway;
            $transaction_date = $data->transactionDate;
            $account_number = $data->accountNumber;
            $sub_account = $data->subAccount;
            $transfer_type = $data->transferType;
            $transfer_amount = $data->transferAmount;
            $accumulated = $data->accumulated;
            $code = $data->code;
            $transaction_content = $data->content;
            $reference_number = $data->referenceCode;
            $body = $data->description;
            $amount_in = 0;
            $amount_out = 0;
            // Kiem tra giao dich tien vao hay tien ra
            if($transfer_type == "in")
                $amount_in = $transfer_amount;
            else if($transfer_type == "out")
                $amount_out = $transfer_amount;

                $transaction = TbTransaction::create([
                    'transaction_id' => $transaction_id,
                    'gateway' => $gateway,
                    'transaction_date' => $transaction_date,
                    'account_number' => $account_number,
                    'sub_account' => $sub_account,
                    'amount_in' => $amount_in,
                    'amount_out' => $amount_out,
                    'accumulated' => $accumulated,
                    'code' => $code,
                    'transaction_content' => $transaction_content,
                    'reference_number' => $reference_number,
                    'body' => $body,
                ]);

                if (!empty($data->content)) {
                    $this->processGame($transaction_id, $data->content, $data->referenceCode, $data->transferAmount, $data->transactionDate);
                }


            return response()->json([
                'status' => 'success',
                'message' => 'Webhook received',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Lỗi ' . $e->getMessage(),
            ], 500);
        }
    }

    private function processGame($transaction_id, $transaction_content, $reference_number, $amount, $transaction_date)
    {

        $username = null;
        $bet_key  = null;

        $validKeys = ['BC','BL','BX','BT','KAA','KBB','KCC','KDD','GBA','S','NM','NH','NB'];

        $pattern = '/\b([a-zA-Z0-9_]+)\s+(' . implode('|', $validKeys) . ')\b/';

        if (preg_match($pattern, $transaction_content, $match)) {
            $username = $match[1];
            $bet_key  = $match[2];
        }

        Log::info('Debug username & key', [
            'username' => $username,
            'bet_key'  => $bet_key,
        ]);

        $customer_id = Customer::where('name', $username)->value('id');

        if (is_null($customer_id)) {
            $customer_id = null; // hoặc giá trị mặc định
        }
        // Mảng game
        $games = [
            'CLTX' => [
                'BC' => [2, 4, 6, 8],
                'BL' => [1, 3, 5, 7],
                'BT' => [5, 6, 7, 8],
                'BX' => [1, 2, 3, 4],
            ],
            'CLTX2' => [
                'KBB' => [1, 3, 5, 7, 9],
                'KAA' => [0, 2, 4, 6, 8],
                'KDD' => [0, 1, 2, 3, 4],
                'KCC' => [5, 6, 7, 8, 9],
            ],
            'Gấp 3' => [
                'GBA' => [
                    'x3' => [02, 13, 17, 19, 21, 29, 35, 37, 47, 49, 51, 54, 57, 63, 64, 74, 83, 91, 95, 96],
                    'x4' => [66, 99],
                    'x5' => [123, 234, 456, 678, 789],
                ],
            ],
            'TỔNG 3 SỐ' => [
                'S' => [
                    'x2'   => [7, 17, 27],   // trúng thì nhân 2
                    'x3'   => [8, 18],       // trúng thì nhân 3
                    'x3_5' => [9, 19],       // trúng thì nhân 3.5
                ],
            ],
            '1 PHẦN 3' => [
                'NM' => [1, 5, 7],
                'NH' => [2, 4, 8],
                'NB' => [3, 6, 9],
            ],
            // 'XIÊN' => [
            //     'CX' => [2, 4],
            //     'LT' => [5, 7, 9],
            //     'CT' => [6, 8],
            //     'LX' => [1, 3],
            // ],
            // 'XSMB2' => [
            //     'XS' => [92],
            //     'XM' => [14, 23, 28, 34, 37, 39, 40, 41, 42, 45, 55, 60, 62, 66, 67, 68, 69, 73, 74, 76, 83, 86, 90, 91, 94],
            // ],
        ];

        $game_name = null;
        $target_numbers = [];

        // Xác định game theo bet_key
        foreach ($games as $gName => $options) {
            if (isset($options[$bet_key])) {
                $game_name = $gName;
                $target_numbers = $options[$bet_key];
                break;
            }
        }

        if (!$game_name) {
            // Không tìm thấy game
            return TbGameResult::create([
                'transaction_id'   => $transaction_id,
                'customer_id'      => $customer_id,
                'game_name'        => 'Unknown',
                'bet_key'          => 'Unknown',
                'reference_number' => $reference_number,
                'amount'           => $amount,
                'result'           => 'lose',
                'reward_amount'    => 0,
                'is_paid'          => 0,
                'note'             => "Không tìm thấy game",
                'transaction_date' => $transaction_date,
            ]);
        }

        // Xử lý số theo luật từng game
        $is_win = false;
        $reward_amount = 0;

        $ref_len = strlen($reference_number);

        switch ($game_name) {
            case 'CLTX':
                $last_digit = (int)substr($reference_number, -1);
                $is_win = in_array($last_digit, $target_numbers);
                break;

            case 'CLTX2':
                $last2 = substr($reference_number, -2);
                $sum = array_sum(str_split($last2));
                $is_win = in_array($sum, $target_numbers);
                break;

            // case 'Gấp 3':
            //     if ($bet_key === 'GNAM') {
            //         $last3 = (int)substr($reference_number, -3);
            //         $is_win = in_array($last3, $target_numbers);
            //     } else {
            //         $last2 = (int)substr($reference_number, -2);
            //         $is_win = in_array($last2, $target_numbers);
            //     }
            case 'Gấp 3':
                $last2 = (int)substr($reference_number, -2);
                $last3 = (int)substr($reference_number, -3);

                $targets = $games['Gấp 3'][$bet_key] ?? null;
                $is_win  = false;
                $multiplier = 1; // mặc định không thắng

                if ($targets) {
                    if (in_array($last2, $targets['x3'])) {
                        $is_win = true;
                        $multiplier = 3;
                    } elseif (in_array($last2, $targets['x4'])) {
                        $is_win = true;
                        $multiplier = 4;
                    } elseif (in_array($last3, $targets['x5'])) {
                        $is_win = true;
                        $multiplier = 5;
                    }
                }
                break;


            // case 'TỔNG 3 SỐ':
            //     $last3 = substr($reference_number, -3);
            //     $sum = array_sum(str_split($last3));
            //     $is_win = in_array($sum, $target_numbers);
            //     break;

            case 'TỔNG 3 SỐ':
                $last3 = substr($reference_number, -3);
                $sum = array_sum(str_split($last3));

                $targets = $games['TỔNG 3 SỐ'][$bet_key] ?? null;
                $is_win  = false;
                $multiplier = 1;

                if ($targets) {
                    if (in_array($sum, $targets['x2'])) {
                        $is_win = true;
                        $multiplier = 2;
                    } elseif (in_array($sum, $targets['x3'])) {
                        $is_win = true;
                        $multiplier = 3;
                    } elseif (in_array($sum, $targets['x3_5'])) {
                        $is_win = true;
                        $multiplier = 3.5;
                    }
                }
                break;




            case '1 PHẦN 3':
            // case 'XIÊN':
                $last2 = substr($reference_number, -2);
                $sum = array_sum(str_split($last2));
                $is_win = in_array($sum, $target_numbers);
                break;

            // case 'XSMB2':
            //     $last2 = (int)substr($reference_number, -2);
            //     $is_win = in_array($last2, $target_numbers);
            //     break;
        }

        // Bảng tỷ lệ trả thưởng
        $multipliers = [
            'CLTX' => [
                // theo khoảng số tiền cược
                'ranges' => [
                    ['min' => 6000,     'max' => 99999,    'multiplier' => 2.7],
                    ['min' => 100000,   'max' => 3000000,  'multiplier' => 2.6],
                ],
                'default' => 2.63, // fallback nếu không rơi vào range nào
            ],
            'CLTX2' => [
                'default' => 1.93,
            ],
            'Gấp 3' => [
                'GBA' => [
                    'x3' => 3,
                    'x4' => 4,
                    'x5' => 5,
                ],
            ],
            'TỔNG 3 SỐ' => [
                'S' => [
                    'x2'   => 2,
                    'x3'   => 3,
                    'x3_5' => 3.5,
                ],
            ],
            '1 PHẦN 3' => [
                'default' => 3,
            ],
            // 'XIÊN' => [
            //     'CX' => 4,
            //     'LT' => 3,
            //     'CT' => 3.3,
            //     'LX' => 3.3,
            // ],
            // 'XSMB2' => [
            //     'XS' => 6.7,
            //     'XM' => 3.4,
            // ],
        ];

        if ($is_win) {
            if ($game_name === 'Gấp 3' && $bet_key === 'GBA') {
                // multiplier đã được gán sẵn từ case 'Gấp 3' ở switch
                // ví dụ 3, 4, hoặc 5
            }
            elseif ($game_name === 'TỔNG 3 SỐ' && $bet_key === 'S') {

            } elseif ($game_name === 'CLTX') {
                $multiplier = $this->getMultiplier($game_name, $bet_key, $amount, $multipliers);

            }
             elseif (isset($multipliers[$game_name][$bet_key])) {
                $multiplier = $multipliers[$game_name][$bet_key];
            } elseif (isset($multipliers[$game_name]['default'])) {
                $multiplier = $multipliers[$game_name]['default'];
            } else {
                $multiplier = 2; // fallback
            }
            $reward_amount = $amount * $multiplier;
        }


        // if ($is_win) {
        //     // Nếu có tỷ lệ theo bet_key → ưu tiên
        //     if (isset($multipliers[$game_name][$bet_key])) {
        //         $multiplier = $multipliers[$game_name][$bet_key];
        //     } elseif (isset($multipliers[$game_name]['default'])) {
        //         // Nếu chỉ có default → dùng default
        //         $multiplier = $multipliers[$game_name]['default'];
        //     } else {
        //         // Nếu không có config → fallback x2
        //         $multiplier = 2;
        //     }

        //     $reward_amount = $amount * $multiplier;
        // }

        if($amount<=6000 || $amount>3000000) {
           return    TbGameResult::create([
            'transaction_id'   => $transaction_id,
            'customer_id'      => $customer_id,
            'game_name'        => $game_name,
            'bet_key'          => $bet_key,
            'reference_number' => $reference_number,
            'amount'           => $amount,
            'result'           =>'lose',
            'reward_amount'    => 0,
            'is_paid'          => 0,
            'note'             => "Người chơi vi phạm quy định cược tối thiểu 6K và tối đa 3000k",
            'transaction_date' => $transaction_date,
        ]);
        }


        // Lưu vào bảng game_results
        TbGameResult::create([
            'transaction_id'   => $transaction_id,
            'customer_id'      => $customer_id,
            'game_name'        => $game_name,
            'bet_key'          => $bet_key,
            'reference_number' => $reference_number,
            'amount'           => $amount,
            'result'           => $is_win ? 'win' : 'lose',
            'reward_amount'    => $reward_amount,
            'is_paid'          => 0,
            'note'             => "Người chơi $username chọn $bet_key",
            'transaction_date' => $transaction_date,
        ]);
    }

    private function getMultiplier(string $game_name, string $bet_key, int $amount, array $multipliers): float
    {
        if (!isset($multipliers[$game_name])) {
            return 2; // fallback
        }

        $config = $multipliers[$game_name];

        // Trường hợp có ranges (theo số tiền)
        if (isset($config['ranges'])) {
            foreach ($config['ranges'] as $range) {
                if ($amount >= $range['min'] && $amount <= $range['max']) {
                    return $range['multiplier'];
                }
            }
        }

        // Nếu có multiplier riêng theo bet_key
        if (isset($config[$bet_key]) && is_numeric($config[$bet_key])) {
            return (float)$config[$bet_key];
        }

        // Nếu có default
        if (isset($config['default'])) {
            return (float)$config['default'];
        }

        return 2; // fallback
    }




}