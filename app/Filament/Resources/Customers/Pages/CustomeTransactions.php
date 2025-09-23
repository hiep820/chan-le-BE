<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\TbGameResult;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class CustomeTransactions extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.resources.customers.pages.custome-transactions';
    protected static ?string $title = 'Lịch sử chơi';

    public int $customerId;

    public function mount(int $record): void
    {
        $this->customerId = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(TbGameResult::where('customer_id', $this->customerId)) // Fetch transactions for this wallet
            ->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('transaction_id')->sortable()->toggleable(),
            TextColumn::make('customer.name')->sortable()->label('Tên người chơi')->searchable()->sortable()->toggleable(),
            TextColumn::make('game_name')->label('Trò chơi')->sortable()->searchable()->toggleable(),
            TextColumn::make('bet_key')->label('Đã chọn')->sortable()->searchable()->toggleable(),
            TextColumn::make('reference_number')->label('Mã giao dịch')->sortable()->searchable()->toggleable(),
            TextColumn::make('amount')->label('Tiền cược')->sortable() ->toggleable(),
            TextColumn::make('result')
            ->sortable()
            ->badge()
            ->color(fn (string $state): string => match ($state) {
                'win' => 'success', // Màu xanh lá cây
                'lose' => 'danger',  // Màu đỏ
                'pending' => 'warning', // Màu vàng
                default => 'gray', // Màu xám cho các giá trị khác
            }),
            TextColumn::make('reward_amount')->label('Tiền thắng')->sortable()->toggleable(),
            TextColumn::make('note')->sortable(),
            ToggleColumn::make('is_paid')
            ->label('Thanh toán')
            ->sortable()
            ->onIcon('heroicon-o-check-circle')   // icon khi true
            ->offIcon('heroicon-o-x-circle')      // icon khi false
            ->onColor('success')                  // màu khi true
            ->offColor('danger')                  // màu khi false
            ->disabled(fn ($record) => $record?->result === 'lose'),
            TextColumn::make('transaction_date')        ->dateTime('d/m/Y H:i') // format theo Carbon
            ->sortable()
            ->toggleable(),
            TextColumn::make('created_at')
            ->dateTime('d/m/Y H:i') // format theo Carbon
            ->sortable()
            ->toggleable(),
        ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                DateRangeFilter::make('created_at')
                ->label('Khoảng thời gian')
                ->icon('heroicon-o-x-mark'),
            ]);
    }
}
