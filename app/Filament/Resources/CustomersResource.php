<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomersResource\Pages;
use App\Models\Customers;
use App\Models\Products;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class CustomersResource extends Resource
{
    protected static ?string $model = Customers::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Delivery';

    protected static function getNavigationBadge(): ?string
    {
        return Customers::all()->count();
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('firstname')->required(),
                Forms\Components\TextInput::make('lastname')->required(),
                Forms\Components\TextInput::make('email')->required(),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
                Forms\Components\Select::make('product_id')->required()
                    ->label('Select an Item')
                    ->options(Products::all()->pluck('product_name', 'id'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('firstname')->searchable(),
                Tables\Columns\TextColumn::make('lastname')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->searchable(),
                Tables\Columns\TextColumn::make('purchased_item')->searchable(),

            ])
            ->poll('5s')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->before(function ($record) {
                    $onDeleteCustomer = Products::where('product_name', $record->purchased_item)->first();
                    $onDeleteCustomer->item_on_hand = (int) $onDeleteCustomer->item_on_hand + $record->quantity;
                    $onDeleteCustomer->save();
                }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomers::route('/create'),
            // 'view' => Pages\ViewCustomers::route('/{record}'),
            'edit' => Pages\EditCustomers::route('/{record}/edit'),
        ];
    }
}
