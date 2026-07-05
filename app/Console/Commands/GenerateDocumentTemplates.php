<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-document-templates')]
#[Description('Command description')]
class GenerateDocumentTemplates extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating base templates...');

        $basePath = storage_path('app/templates/travel-orders');
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        // 1. Surat Permohonan Bupati
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        
        $section->addText('SURAT PERMOHONAN PENANDATANGANAN SURAT TUGAS', ['bold' => true, 'size' => 14], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(2);
        
        $section->addText('Dasar: ${dasar_pelaksanaan}');
        $section->addTextBreak(1);
        $section->addText('Kepada Yth. Bapak Bupati, mohon berkenan menandatangani Surat Tugas untuk pegawai berikut:');
        
        // Loop table placeholder
        $section->addText('${nama}');
        $section->addText('NIP: ${nip}');
        $section->addText('Pangkat/Golongan: ${pangkat_gol}');
        $section->addText('Jabatan: ${jabatan}');
        
        $section->addTextBreak(1);
        $section->addText('Untuk melaksanakan perjalanan dinas dalam rangka: ${maksud_perjalanan}');
        $section->addText('Ke: ${tempat_tujuan}');
        $section->addText('Tanggal: ${tgl_pelaksanaan}');
        
        $section->addTextBreak(3);
        $section->addText('Bengkayang, ${tgl_surat}', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
        
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($basePath . '/surat_permohonan_bupati.docx');
        
        // 2. Surat Tugas Kadis
        $phpWord2 = new \PhpOffice\PhpWord\PhpWord();
        $section2 = $phpWord2->addSection();
        
        $section2->addText('PEMERINTAH KABUPATEN BENGKAYANG', ['bold' => true, 'size' => 14], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section2->addText('DINAS ...', ['bold' => true, 'size' => 14], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section2->addTextBreak(2);
        
        $section2->addText('SURAT TUGAS', ['bold' => true, 'size' => 12, 'underline' => 'single'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section2->addText('Nomor: ${nomor_surat}', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section2->addTextBreak(1);
        
        $section2->addText('Dasar: ${dasar_pelaksanaan}');
        $section2->addTextBreak(1);
        $section2->addText('MEMERINTAHKAN:', ['bold' => true]);
        
        $section2->addText('Kepada:');
        $section2->addText('Nama: ${nama}');
        $section2->addText('Pangkat/Gol.: ${pangkat_gol}');
        $section2->addText('NIP: ${nip}');
        $section2->addText('Jabatan: ${jabatan}');
        
        $section2->addTextBreak(1);
        $section2->addText('Untuk: ${maksud_perjalanan}');
        $section2->addText('Tempat Tujuan: ${tempat_tujuan}');
        $section2->addText('Waktu Pelaksanaan: ${tgl_pelaksanaan}');
        
        $section2->addTextBreak(3);
        $section2->addText('Dikeluarkan di: Bengkayang', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
        $section2->addText('Pada tanggal: ${tgl_surat}', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
        $section2->addText('KEPALA DINAS', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
        $section2->addTextBreak(3);
        $section2->addText('${nama_kadis}', ['bold' => true, 'underline' => 'single'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
        $section2->addText('NIP. ${nip_kadis}', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
        
        $objWriter2 = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord2, 'Word2007');
        $objWriter2->save($basePath . '/surat_tugas_kadis.docx');

        $this->info('Templates generated successfully!');
    }
}
