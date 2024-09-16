<?php

namespace App\Controller;

use App\Enum\ExcelFormat;
use App\Exporter\ExcelOpenSpoutExporter;
use App\Form\ExcelExportType;
use App\Form\ExcelImportType;
use App\Importer\ExcelOpenSpoutImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class ExcelController extends AbstractController
{
    public function __construct(
        private readonly ExcelOpenSpoutExporter $exporter,
        private readonly ExcelOpenSpoutImporter $importer
    ) {
    }

    #[Route('/excel/export', name: 'excel.export')]
    public function export(Request $request): Response
    {
        $form = $this
            ->createForm(ExcelExportType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $columnNames = ['Column 1', 'Column 2', 'Column 3'];

            $data = [
                ['c1r1','c2r1','c3r1'],
                ['c1r2','c2r3','c3r4'],
            ];

            /** @var ExcelFormat $format */
            $format = $form->get('format')->getData();

            $response = new StreamedResponse(fn () => $this->exporter->export($columnNames, $data, $format));

            $response->headers->set('Content-Type', $format->contentType());

            return $response;
        }

        return $this->render('excel/export.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/excel/import', name: 'excel.import')]
    public function import(Request $request): Response
    {
        $form = $this
            ->createForm(ExcelImportType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            $data = $this->importer->import($file);
        }

        return $this->render('excel/import.html.twig', [
            'form' => $form->createView(),
            'data' => $data ?? null,
        ]);
    }
}
