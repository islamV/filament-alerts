<?php

namespace TomatoPHP\FilamentAlerts\Filament\Resources\NotificationsTemplateResource\Table\Actions;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Illuminate\Support\Facades\Notification;
use TomatoPHP\FilamentAlerts\Facades\FilamentAlerts;
use TomatoPHP\FilamentAlerts\Notifications\SendTemplateNotification;
use Filament\Notifications\Notification as FilamentNotification;

class SendAction extends Action
{
    public static function make(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('send')
            ->requiresConfirmation()
            ->iconButton()
            ->label(trans('filament-alerts::messages.actions.send.label'))
            ->tooltip(trans('filament-alerts::messages.actions.send.label'))
            ->icon('heroicon-o-bell')
            ->form(fn ($record) => [
                Forms\Components\Hidden::make('template_id')->default($record->id),
                Forms\Components\Select::make('privacy')
                    ->label(trans('filament-alerts::messages.actions.send.form.privacy'))
                    ->searchable()
                    ->columnSpanFull()
                    ->options([
                        'public' => trans('filament-alerts::messages.actions.send.form.public'),
                        'private' => trans('filament-alerts::messages.actions.send.form.private'),
                    ])
                    ->live()
                    ->required()
                    ->default('public'),
                Forms\Components\Select::make('model_type')
                    ->searchable()
                    ->label(trans('filament-alerts::messages.actions.send.form.model_type'))
                    ->options(FilamentAlerts::loadUsers()->pluck('label', 'model')->toArray())
                    ->preload()
                    ->required()
                    ->live(),
                Forms\Components\Select::make('model_id')
                    ->label(trans('filament-alerts::messages.actions.send.form.model_id'))
                    ->searchable()
                    ->hidden(fn (Forms\Get $get): bool => $get('privacy') !== 'private')
                    ->options(fn (Forms\Get $get) => $get('model_type') ? $get('model_type')::pluck('name', 'id')->toArray() : [])
                    ->required(),
            ])
            ->action(function (array $data, $record) {

                if ($data['privacy'] === 'private') {
                    // Private notification to one user
                    FilamentAlerts::notify()
                        ->model($data['model_type'])
                        ->modelId($data['model_id'])
                        ->template($record->id)
                        ->send();

                } else {
                    // Public notification to all users via queued job
                    User::chunkById(200, function ($users) use ($record , $data) {
                        foreach ($users as $user) {
                            FilamentAlerts::notify()
                            ->model($data['model_type'])
                            ->modelId($user->id ?? null)
                            ->template($record->id)
                            ->send();

                        }
                    });
                }

                FilamentNotification::make()
                    ->title(trans('filament-alerts::messages.actions.send.notification'))
                    ->success()
                    ->send();
            });
    }
}
