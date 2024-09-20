<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransformerRequest;
use App\Services\Converter\FileHandler;
use App\Services\Converter\PdfHandler;
use Illuminate\Http\JsonResponse;

class TransformerController extends Controller
{
    public function pdfConvert(TransformerRequest $request): JsonResponse
    {
        $valid = $request->validated();
        if ( ! $valid) {
            return response()->json([
                'success' => false,
                'message' => 'Error validation',
            ], 400);
        }
        $fileService = new FileHandler(
            $request->file('target'),
            $request->safe()->input('pages')
        );
        $fileService->save();
        $pdfService = new PdfHandler(
            $fileService->getGroupId(),
            $fileService->getPath(),
            $fileService->getFileNameWithExtension(),
            $fileService->getPages()
        );
        $pdfService->convert();
        $pdfService->clear();

        return response()->json([
            'success' => true,
            'message' => 'PDF converted successfully',
            'data'    => [
                'pdf'   => $fileService->getUrlPDFFile(),
                'pages' => $pdfService->getUrls(),
            ],
        ], 200);
    }
}
