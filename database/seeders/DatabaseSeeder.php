<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            InvoiceDocumentTypeSeeder::class,
            CorrectionCodeSeeder::class,
            OperationTypeSeeder::class,
            ProductIdentificationStandardSeeder::class,
            ClaimConceptCodeSeeder::class,
            EventCodeSeeder::class,
            IdentityDocumentTypeSeeder::class,
            ClientTributeSeeder::class,
            OrganizationTypeSeeder::class,
            PaymentMethodSeeder::class,
            PaymentFormSeeder::class,
            NumberingRangeDocumentTypeSeeder::class,
            IdentityDocumentTypeSupportSeeder::class,
            AdjustmentNoteReasonSeeder::class,
        ]);
    }
}
