<?php

namespace TomatoPHP\FilamentAlerts\Filament\Resources\NotificationsTemplateResource\Actions\Components;

use Filament\Actions\Action as FilamentAction;
use Illuminate\Database\Eloquent\Model;

abstract class Action
{
    abstract public static function make(?Model $record = null): FilamentAction;
}
