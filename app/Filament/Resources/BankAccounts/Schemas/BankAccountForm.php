<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank_name')
                    ->label('Tên ngân hàng')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ví dụ: VPBank, Techcombank'),
                TextInput::make('account_number')
                    ->label('Số tài khoản')
                    ->required()
                    ->unique(table: 'bank_accounts', column: 'account_number', ignoreRecord: true) // Kiểm tra unique, bỏ qua bản ghi hiện tại khi chỉnh sửa
                    ->maxLength(255)
                    ->placeholder('Nhập số tài khoản'),
                TextInput::make('account_holder')
                    ->label('Chủ tài khoản')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nhập tên chủ tài khoản'),
                TextInput::make('max')
                    ->label('Giao dịch tối đa')
                    ->default('0')
                    ->maxLength(255)
                    ->placeholder('Nhập số tiền tối đa cho mỗi giao dịch'),
                TextInput::make('min')
                    ->label('Giao dịch tối thiểu')
                    ->default('0')
                    ->maxLength(255)
                    ->placeholder('Nhập số tiền tối thiểu cho mỗi giao dịch'),
                FileUpload::make('qr_code')
                    ->label('Ảnh QR Code')
                    ->disk('public')
                    ->required()
                    ->directory('uploads/bills')
                    ->image()
                    ->columnSpan(2),

            ]);
    }
}
