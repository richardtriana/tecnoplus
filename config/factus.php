<?php
return [
    'endpoint'                          => env('FACTUS_ENDPOINT', 'https://api-sandbox.factus.com.co/v1/bills/validate'),
    'auth_endpoint'                     => env('FACTUS_AUTH_ENDPOINT', 'https://api-sandbox.factus.com.co/v1/oauth/token'),
    'support_document_endpoint'         => env('FACTUS_SUPPORT_DOCUMENT_ENDPOINT', 'https://api-sandbox.factus.com.co/v1/support-documents/validate'),
    'adjustment_note_endpoint'          => env('FACTUS_ADJUSTMENT_NOTE_ENDPOINT', 'https://api-sandbox.factus.com.co/v1/adjustment-notes/validate'),
    'receptions_upload_endpoint'        => env('FACTUS_RECEPTIONS_UPLOAD_ENDPOINT', 'https://api-sandbox.factus.com.co/v1/receptions/upload'),
    'receptions_bills_endpoint'         => env('FACTUS_RECEPTIONS_BILLS_ENDPOINT', 'https://api-sandbox.factus.com.co/v1/receptions/bills'),

];