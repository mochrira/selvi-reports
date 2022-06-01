# Selvi PDF

Sebuah library untuk membuat laporan pdf yang dibuat menggunakan TCPDF mengadopsi sistem Band dalam pembuatan laporan yang sudah lama diperkenalkan oleh FastReport&copy;.

## Instalasi

```
composer require mochrira/selvi-pdf
```

## Memulai

```
<?php 
    require 'vendor/autoload.php';

    $pdf = new \Selvi\Pdf();
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
            $pdf->column('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', ['width' => '50%', 'multiline' => false, 'border' => 1]);
            $pdf->column('Halo', ['width' => '50%']);
            $pdf->rowEnd();
        });

        $pdf->pageFooter(function ($pdf) {
            $pdf->rowStart();
            $pdf->column('Page '.$pdf->PageNo());
            $pdf->rowEnd();
        });

    $pdf->pageEnd();

    $pdf->render();
?>
```

## Master Band

```
...

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
    $pdf->band(function ($pdf) {
        $pdf->rowStart();
        $pdf->column('Master Footer uses generic band'.$i);
        $pdf->rowEnd();
        $pdf->Ln();
    });
$pdf->masterEnd();

...
```