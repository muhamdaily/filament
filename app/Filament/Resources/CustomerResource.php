<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\District;
use App\Models\PostalCode;
use App\Models\Subdistrict;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Custom Detail')
                    ->schema([
                        TextInput::make('name')
                            ->label('Customer Name')
                            ->maxValue(50)
                            ->required(),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->numeric()
                            ->required(),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                    ])->columns(2),
                Section::make('Customer Address')
                    ->schema([
                        Select::make('province_id')
                            ->label('Provinsi')
                            ->relationship(name: 'province', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('district_id', null);
                                $set('subdistrict_id', null);
                                $set('postal_code_id', null);
                            })
                            ->required(),
                        Select::make('district_id')
                            ->label('Kabupaten atau Kota')
                            ->options(fn (Get $get): Collection => District::query()
                                ->where('province_id', $get('province_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->preload()
                            ->afterStateUpdated(fn (Set $set) => $set('subdistrict_id', null))
                            ->required(),
                        Select::make('subdistrict_id')
                            ->label('Kecamatan')
                            ->options(fn (Get $get): Collection => Subdistrict::query()
                                ->where('district_id', $get('district_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->preload()
                            ->afterStateUpdated(fn (Set $set) => $set('postal_code_id', null))
                            ->required(),
                        Select::make('postal_code_id')
                            ->label('Kode Pos')
                            ->options(fn (Get $get): Collection => PostalCode::query()
                                ->where('subdistrict_id', $get('subdistrict_id'))
                                ->pluck('code', 'id'))
                            ->searchable()
                            ->live()
                            ->preload()
                            ->required(),
                        Textarea::make('address')
                            ->label('Detail Alamat')
                            ->placeholder('Nama jalan, Gedung, No. Rumah')
                            ->required()
                            ->autosize()
                            ->maxLength(1024)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Customer Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone Number')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('address')
                    ->label('Address')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (Customer $record) => $record->delete()),
                ])
                    ->tooltip('Actions')
                    ->icon('heroicon-m-ellipsis-horizontal'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
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
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
