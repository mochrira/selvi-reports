<?php 

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/../src/Pdf.php';

$pdf = new Selvi\Report\Pdf();

$pdf->pageStart([
    'margins' => ['bottom' => .5, 'top' => .5]
]);

    $pdf->pageHeader(function ($pdf) {
        $pdf->rowStart();
        $pdf->column('Page Header');
        $pdf->rowEnd();
    });

        $pdf->band(function ($pdf) {
            $pdf->rowStart();
            $pdf->column('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', ['width' => '50%', 'multiline' => true, 'border' => 1]);
            $pdf->column('Halo', ['width' => '50%', 'border' => 1]);
            $pdf->rowEnd();
        });

        $pdf->masterStart();
            $pdf->masterHeader(function ($pdf) {
                $pdf->rowStart();
                $pdf->column('Master Header 1');
                $pdf->rowEnd();
            });
            for($i=1; $i<=125; $i++) {
                $pdf->masterBand(function ($pdf) use ($i) {
                    $pdf->rowStart();
                    $pdf->column('Master Band 1 - '.$i);
                    $pdf->rowEnd();
                });
            }
            $pdf->band(function ($pdf) use ($i) {
                $pdf->rowStart();
                $pdf->column('Band Footer 1 uses Generic Band '.$i);
                $pdf->rowEnd();
                $pdf->Ln();
            });
        $pdf->masterEnd();

        $pdf->masterStart();
            $pdf->masterHeader(function ($pdf) {
                $pdf->rowStart();
                $pdf->column('Master Header 2');
                $pdf->rowEnd();
            });
            for($i=1; $i<=125; $i++) {
                $pdf->masterBand(function ($pdf) use ($i) {
                    $pdf->rowStart();
                    $pdf->column('Master Band 2 - '.$i);
                    $pdf->rowEnd();
                });
            }
            $pdf->band(function ($pdf) use ($i) {
                $pdf->rowStart();
                $pdf->column('Band Footer 2 uses Generic Band '.$i);
                $pdf->rowEnd();
                $pdf->Ln();
            });
        $pdf->masterEnd();

    $pdf->pageFooter(function ($pdf) {
        $pdf->rowStart();
        $pdf->column('Page '.$pdf->PageNo());
        $pdf->rowEnd();
    });

$pdf->pageEnd();

if((isset($_GET['export']) ? $_GET['export'] : '') === 'json') {
    echo json_encode($pdf->components);
    die();
}
$pdf->render();