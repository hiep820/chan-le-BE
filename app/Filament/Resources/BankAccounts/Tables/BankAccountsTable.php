<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('bank_name')
                ->label('Tên ngân hàng')
                ->searchable()
                ->sortable(),
            TextColumn::make('account_number')
                ->label('Số tài khoản')
                ->searchable()
                ->sortable(),
            TextColumn::make('account_holder')
                ->label('Chủ tài khoản')
                ->searchable()
                ->sortable(),
            TextColumn::make('max')
                ->label('Giao dịch tối đa')
                ->sortable(),
            TextColumn::make('min')
                ->label('Giao dịch tối thiểu')
                ->sortable(),
            ImageColumn::make('qr_code')
                ->label('Ảnh QR Code')
                ->disk('public')
                ->width(100)
                ->height(100),
            ToggleColumn::make('active_is')
                ->label('Trạng thái')
                ->sortable()
                ->onIcon('heroicon-o-check-circle')   
                ->onColor('success')                 
                ->offColor('danger'),                
        ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
