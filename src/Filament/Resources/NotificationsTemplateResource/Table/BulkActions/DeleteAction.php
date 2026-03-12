<?php

namespace TomatoPHP\FilamentAlerts\Filament\Resources\NotificationsTemplateResource\Table\BulkActions;

use Filament\Actions\DeleteBulkAction;

class DeleteAction extends Action
{
    public static function make(): DeleteBulkAction
    {
        return DeleteBulkAction::make();
    }
}
