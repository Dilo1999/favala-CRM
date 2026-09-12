<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'users';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($context) => $context === 'create')
                            ->maxLength(255)
                            ->helperText('Leave blank on edit to keep current password.'),
                        Select::make('role')
                            ->options([
                                User::ROLE_ADMIN => 'Admin',
                                User::ROLE_MEMBER => 'Member (CRM staff)',
                                User::ROLE_MANAGEMENT => 'Management',
                                User::ROLE_EDITOR => 'Editor',
                                User::ROLE_VIEWER => 'Viewer',
                            ])
                            ->required()
                            ->default(User::ROLE_MEMBER)
                            ->helperText('Admin: full access including Settings and Users. Member: CRM staff (deals, quotations, invoices, etc). Management: reviews and approves sales returns before refund. Editor/Viewer: legacy site-content roles.'),
                        Select::make('status')
                            ->options([
                                User::STATUS_PENDING => 'Pending approval',
                                User::STATUS_ACTIVE => 'Active',
                                User::STATUS_INACTIVE => 'Inactive',
                            ])
                            ->required()
                            ->default(User::STATUS_ACTIVE)
                            ->helperText('Switching to Active emails the user that they can now sign in.'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->enum([
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_MEMBER => 'Member',
                        User::ROLE_EDITOR => 'Editor',
                        User::ROLE_VIEWER => 'Viewer',
                    ])
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => User::STATUS_PENDING,
                        'success' => User::STATUS_ACTIVE,
                        'danger' => User::STATUS_INACTIVE,
                    ])
                    ->enum([
                        User::STATUS_PENDING => 'Pending approval',
                        User::STATUS_ACTIVE => 'Active',
                        User::STATUS_INACTIVE => 'Inactive',
                    ])
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        User::STATUS_PENDING => 'Pending approval',
                        User::STATUS_ACTIVE => 'Active',
                        User::STATUS_INACTIVE => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => $record->status !== User::STATUS_ACTIVE)
                    ->requiresConfirmation()
                    ->modalSubheading('This activates the account and emails the user that they can now sign in.')
                    ->action(fn (User $record) => $record->update(['status' => User::STATUS_ACTIVE])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', User::STATUS_PENDING)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
