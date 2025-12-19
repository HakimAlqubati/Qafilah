<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\Schemas\Components\Sections\BasicInformationSection;
use App\Filament\Resources\Users\Schemas\Components\Sections\SecuritySection;
use App\Filament\Resources\Users\Schemas\Components\Sections\StatusPermissionsSection;
use App\Filament\Resources\Users\Schemas\Components\Sections\VendorAssignmentSection;
use Filament\Schemas\Schema;

class UserForm
{
    /**
     * Configure the form schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section 1: Basic Information
                BasicInformationSection::make(),

                // Section 2: Security
                SecuritySection::make(),

                // Section 3: Status & Permissions
                StatusPermissionsSection::make(),

                // Section 4: Vendor Assignment
                VendorAssignmentSection::make(),
            ]);
    }
}
