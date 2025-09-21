<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Customer';
    public static function getLabel(): ?string
    {
        return __('Người chơi');
    }

    public static function getNavigationLabel(): string
    {
        return __('Người chơi');
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // return CustomersTable::configure($table);
        return $table
        ->columns([
            TextColumn::make('id'),
            TextColumn::make('name')->label('Tên người chơi')->searchable()->sortable(),
            TextColumn::make('bank_name')->label('Tên ngân hàng')->searchable()->sortable(),
            TextColumn::make('account_number')->label('Số tài khoản')->searchable()->sortable(),
            TextColumn::make('account_holder')->label('Chủ tài khoản')->searchable()->sortable(),
            TextColumn::make('created_at')
                ->dateTime(),
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
