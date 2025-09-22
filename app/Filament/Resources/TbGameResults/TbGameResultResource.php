<?php

namespace App\Filament\Resources\TbGameResults;

use App\Filament\Resources\TbGameResults\Pages\CreateTbGameResult;
use App\Filament\Resources\TbGameResults\Pages\EditTbGameResult;
use App\Filament\Resources\TbGameResults\Pages\ListTbGameResults;
use App\Filament\Resources\TbGameResults\Schemas\TbGameResultForm;
use App\Filament\Resources\TbGameResults\Tables\TbGameResultsTable;
use App\Models\TbGameResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TbGameResultResource extends Resource
{
    protected static ?string $model = TbGameResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'TbGameResult';

    public static function getLabel(): ?string
    {
        return __('Kết quả trò chơi');
    }

    public static function getNavigationLabel(): string
    {
        return __('Kết quả trò chơi');
    }

    public static function form(Schema $schema): Schema
    {
        return TbGameResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // return TbGameResultsTable::configure($table);
        return $table
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
            // TextColumn::make('is_paid')
            // ->sortable()
            // ->toggleable()
            // ->badge()
            // ->label('Thanh toán')
            // ->formatStateUsing(fn (bool $state): string => $state ? 'Đã gửi' : 'Chưa gửi')
            // ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            // TextColumn::make('is_paid')
            // ->sortable()
            // ->toggleable()
            // ->badge()
            // ->label('Thanh toán')
            // ->formatStateUsing(fn ($state, $record) =>
            //     $record?->result === 'lose'
            //         ? null   // không hiển thị gì
            //         : ($state ? 'Đã gửi' : 'Chưa gửi')
            // )
            // ->color(fn ($state, $record) =>
            //     $record?->result === 'lose'
            //         ? null   // không gán màu => không hiển thị badge
            //         : ($state ? 'success' : 'danger')
            // ),
            ToggleColumn::make('is_paid')
            ->label('Thanh toán')
            ->sortable()
            ->onIcon('heroicon-o-check-circle')   // icon khi true
            ->offIcon('heroicon-o-x-circle')      // icon khi false
            ->onColor('success')                  // màu khi true
            ->offColor('danger')                  // màu khi false
            ->disabled(fn ($record) => $record?->result === 'lose'),

            TextColumn::make('transaction_date')->dateTime()->sortable()->toggleable(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            //
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTbGameResults::route('/'),
            'create' => CreateTbGameResult::route('/create'),
            'edit' => EditTbGameResult::route('/{record}/edit'),
        ];
    }
}
