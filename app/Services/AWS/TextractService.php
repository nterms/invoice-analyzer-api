<?php

namespace App\Services\AWS;

use Aws\Credentials\CredentialProvider;
use Aws\Textract\TextractClient;

class TextractService
{
    /**
     * Analyse the given file using AWS Textract
     * 
     * @param string $filePath Path of the file to be used
     */
    public function getExpenseDocuments(string $filePath)
    {
        try {
            $client = new TextractClient([
                'region' => getenv('AWS_DEFAULT_REGION'),
                'version' => '2018-06-27',
                'credentials' => CredentialProvider::env()
            ]);

            $file = fopen($filePath, "rb");
            $contents = fread($file, filesize($filePath));
            fclose($file);
            $options = [
                'Document' => [
                    'Bytes' => $contents
                ],
                'FeatureTypes' => ['FORMS'], // REQUIRED
            ];

            $result = $client->analyzeExpense($options);
            $expenseDocuments = $result->search('ExpenseDocuments');

            if (!empty($expenseDocuments)) {
                return $expenseDocuments;
            }
        } catch (\Throwable $th) {
            logger()->error($th->getMessage());
            logger()->info($th->getTraceAsString());
        }
    }
}