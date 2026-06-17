<ol class="ssuk-list">
    <li>Hak dan Kewajiban
        <ol type="a">
            <li>Penyedia
                <ol type="1">
                    <li>Penyedia memiliki hak menerima pembayaran atas pembelian barang sesuai dengan total harga dan waktu yang tercantum di dalam SP ini.</li>
                    <li>Penyedia memiliki kewajiban:
                        <ol type="a">
                            <li>tidak membuat dan/atau menyampaikan dokumen dan/atau keterangan lain yang tidak benar untuk memenuhi persyaratan Katalog Elektronik;</li>
                            <li>tidak menjual barang melalui e-Purchasing lebih mahal dari harga barang yang dijual selain melalui e-Purchasing pada periode penjualan, jumlah, dan tempat serta spesifikasi teknis dan persyaratan yang sama;</li>
                            <li>mengirimkan barang sesuai spesifikasi dalam SP ini selambat-lambatnya pada tanggal sejak SP ini diterima oleh Penyedia;</li>
                            <li>bertanggungjawab atas keamanan, kualitas, dan kuantitas barang yang dipesan;</li>
                            <li>mengganti barang setelah Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian melakukan pemeriksaan barang dan menemukan bahwa:<br>
                                (1) barang rusak akibat cacat produksi;<br>
                                (2) barang rusak pada saat pengiriman barang hingga barang diterima oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian; dan/atau<br>
                                (3) barang yang diterima tidak sesuai dengan spesifikasi barang sebagaimana tercantum pada SP ini.
                            </li>
                            <li>memberikan layanan tambahan yang diperjanjikan seperti instalasi, testing, dan pelatihan (apabila ada);</li>
                            <li>memberikan layanan purnajual sesuai dengan ketentuan garansi masing-masing barang (apabila ada).</li>
                        </ol>
                    </li>
                </ol>
            </li>
            <li>PEJABAT PENANDATANGAN/PENGESAHAN TANDA BUKTI PERJANJIAN
                <ol type="1">
                    <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian memiliki hak:
                        <ol type="a">
                            <li>menerima barang dari Penyedia sesuai dengan spesifikasi yang tercantum di dalam SP ini.</li>
                            <li>mendapatkan jaminan keamanan, kualitas, dan kuantitas barang yang dipesan;</li>
                            <li>mendapatkan penggantian barang, dalam hal:<br>
                                (1) barang rusak akibat cacat produksi;<br>
                                (2) barang rusak pada saat pengiriman barang hingga barang diterima oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian; dan/atau<br>
                                (3) barang yang diterima tidak sesuai dengan spesifikasi barang sebagaimana tercantum pada SP ini.
                            </li>
                            <li>Mendapatkan layanan tambahan yang diperjanjikan seperti instalasi, testing, dan pelatihan (apabila ada);</li>
                            <li>Mendapatkan layanan purnajual sesuai dengan ketentuan garansi masing-masing barang (apabila ada).</li>
                        </ol>
                    </li>
                    <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian memiliki kewajiban:
                        <ol type="a">
                            <li>melakukan pembayaran sesuai dengan total harga yang tercantum di dalam SP</li>
                            <li>memeriksa kualitas dan kuantitas barang;</li>
                            <li>memastikan layanan tambahan telah dilaksanakan oleh penyedia seperti instalasi, testing, dan pelatihan (apabila ada).</li>
                        </ol>
                    </li>
                </ol>
            </li>
        </ol>
    </li>

    <li>Waktu Pengiriman Barang
        <div style="margin-top: 5px;">
            Penyedia mengirimkan barang dimulai sejak Surat Pesanan ini ditandatangani dan sudah diterima paling lambat tanggal {{ optional($process->tanggal_barang_diterima)->translatedFormat('d F Y') }}
        </div>
    </li>

    <li>Alamat Pengiriman Barang
        <div style="margin-top: 5px;">
            Penyedia mengirimkan barang ke alamat sebagai berikut:<br>
            Kantor Dinas Perumahan Rakyat dan Kawasan Permukiman, Pertanahan dan Lingkungan Hidup Kabupaten Bengkayang<br>
            Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat, Kode Pos : 79211
        </div>
    </li>

    <li>Tanggal Barang Diterima
        <div style="margin-top: 5px;">
            Barang diterima mulai tanggal {{ optional($process->tanggal_surat_pesanan)->translatedFormat('d F Y') }} sampai dengan tanggal {{ optional($process->tanggal_barang_diterima)->translatedFormat('d F Y') }}
        </div>
    </li>

    <li>Penerimaan, Pemeriksaan, dan Retur Barang
        <ol type="a">
            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian menerima barang dan melakukan pemeriksaan barang berdasarkan ketentuan di dalam SP ini.</li>
            <li>Dalam hal pada saat pemeriksaan barang, Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian menemukan bahwa:
                <ol type="1">
                    <li>barang rusak akibat cacat produksi;</li>
                    <li>barang rusak pada saat pengiriman barang hingga barang diterima oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian; dan/atau</li>
                    <li>barang yang diterima tidak sesuai dengan spesifikasi barang sebagaimana tercantum pada SP ini.</li>
                </ol>
                Maka Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dapat menolak penerimaan barang dan menyampaikan pemberitahuan tertulis kepada Penyedia atas cacat mutu atau kerusakan barang tersebut.
            </li>
            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dapat meminta Tim Teknis untuk melakukan pemeriksaan atau uji mutu terhadap barang yang diterima.</li>
            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dapat memerintahkan Penyedia untuk menemukan dan mengungkapkan cacat mutu serta melakukan pengujian terhadap barang yang dianggap Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian mengandung cacat mutu atau kerusakan.</li>
            <li>Penyedia bertanggungjawab atas cacat mutu atau kerusakan barang dengan memberikan penggantian barang selambat-lambatnya 7 (tujuh) hari kerja.</li>
        </ol>
    </li>

    <li>Harga
        <ol type="a">
            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian membayar kepada Penyedia atas pelaksanaan pekerjaan sebesar harga yang tercantum pada SP ini.</li>
            <li>Harga SP telah memperhitungkan keuntungan, pajak, biaya overhead, biaya pengiriman, biaya asuransi, biaya layanan tambahan (apabila ada) dan biaya layanan purna jual.</li>
            <li>Rincian harga SP sesuai dengan rincian yang tercantum dalam daftar kuantitas dan harga.</li>
        </ol>
    </li>

    <li>Perpajakan
        <div style="margin-top: 5px;">
            Penyedia berkewajiban untuk membayar semua pajak, bea, retribusi, dan pungutan lain yang sah yang dibebankan oleh hukum yang berlaku atas pelaksanaan SP. Semua pengeluaran perpajakan ini dianggap telah termasuk dalam harga SP.
        </div>
    </li>

    <li>Pengalihan dan/atau subkontrak
        <ol type="a">
            <li>Pengalihan seluruh Kontrak hanya diperbolehkan dalam hal terdapat pergantian nama Penyedia, baik sebagai akibat peleburan (merger), konsolidasi, atau pemisahan.</li>
            <li>Pengalihan sebagian pelaksanaan Kontrak dilakukan dengan ketentuan sebagai berikut:
                <ol type="1">
                    <li>Pengalihan sebagian pelaksanaan Kontrak untuk barang/jasa yang bersifat standar dilakukan untuk pekerjaan seperti pengiriman barang (distribusi barang) dari Penyedia kepada Kementerian/Lembaga/Satuan Kerja Perangkat Daerah/Institusi</li>
                    <li>Pengalihan sebagian pelaksanaan Kontrak dapat dilakukan untuk barang/jasa yang bersifat tidak standar misalnya untuk pekerjaan konstruksi (minor), pengadaan ambulans, ready mix, hot mix dan lain sebagainya.</li>
                </ol>
            </li>
        </ol>
    </li>

    <li>Perubahan SP
        <ol type="a">
            <li>SP hanya dapat diubah melalui adendum SP.</li>
            <li>Perubahan SP dapat dilakukan apabila disetujui oleh para pihak dalam hal terjadi perubahan jadwal pengiriman barang atas permintaan Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian atau permohonan Penyedia yang disepakati oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian.</li>
        </ol>
    </li>

    <li>Peristiwa Kompensasi
        <ol type="a">
            <li>Peristiwa Kompensasi dapat diberikan kepada penyedia dalam hal Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian terlambat melakukan pembayaran prestasi pekerjaan kepada Penyedia.</li>
            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dikenakan ganti rugi atas keterlambatan pembayaran sebesar.</li>
        </ol>
    </li>

    <li>Hak Atas Kekayaan Intelektual
        <ol type="a">
            <li>Penyedia berkewajiban untuk memastikan bahwa barang yang dikirimkan/dipasok tidak melanggar Hak Atas Kekayaan Intelektual (HAKI) pihak manapun dan dalam bentuk apapun.</li>
            <li>Penyedia berkewajiban untuk menanggung Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dari atau atas semua tuntutan, tanggung jawab, kewajiban, kehilangan, kerugian, denda, gugatan atau tuntutan hukum, proses pemeriksaan hukum, dan biaya yang dikenakan terhadap Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian sehubungan dengan klaim atas pelanggaran HAKI, termasuk pelanggaran hak cipta, merek dagang, hak paten, dan bentuk HAKI lainnya yang dilakukan atau diduga dilakukan oleh Penyedia.</li>
        </ol>
    </li>

    <li>Jaminan Bebas Cacat Mutu/Garansi
        <ol type="a">
            <li>Penyedia dengan jaminan pabrikan dari produsen pabrikan (jika ada) berkewajiban untuk menjamin bahwa selama penggunaan secara wajar oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian, Barang tidak mengandung cacat mutu yang disebabkan oleh tindakan atau kelalaian Penyedia, atau cacat mutu akibat desain, bahan, dan cara kerja.</li>
            <li>Jaminan bebas cacat mutu ini berlaku sampai dengan 12 (dua belas) bulan setelah serah terima Barang atau jangka waktu lain yang ditetapkan dalam SP ini.</li>
            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian akan menyampaikan pemberitahuan cacat mutu kepada Penyedia segera setelah ditemukan cacat mutu tersebut selama Masa Layanan Purnajual.</li>
            <li>Terhadap pemberitahuan cacat mutu oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian, Penyedia berkewajiban untuk memperbaiki atau mengganti Barang dalam jangka waktu yang ditetapkan dalam pemberitahuan tersebut.</li>
            <li>Jika Penyedia tidak memperbaiki atau mengganti Barang akibat cacat mutu dalam jangka waktu yang ditentukan, maka Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian akan menghitung biaya perbaikan yang diperlukan dan Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian secara langsung atau melalui pihak ketiga yang ditunjuk oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian akan melakukan perbaikan tersebut. Penyedia berkewajiban untuk membayar biaya perbaikan atau penggantian tersebut sesuai dengan klaim yang diajukan secara tertulis oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian. Biaya tersebut dapat dipotong oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dari nilai tagihan Penyedia.</li>
        </ol>
    </li>

    <li>Pembayaran
        <ol type="a">
            <li>pembayaran prestasi hasil pekerjaan yang disepakati dilakukan oleh Pejabat Penandatangan / Pengesahan Tanda Bukti Perjanjian, dengan ketentuan:
                <ol type="1">
                    <li>penyedia telah mengajukan tagihan;</li>
                    <li>pembayaran dilakukan dengan sekaligus secara non tunai ke rekening penyedia; dan</li>
                    <li>pembayaran harus dipotong denda (apabila ada) dan pajak.</li>
                </ol>
            </li>
            <li>pembayaran terakhir hanya dilakukan setelah pekerjaan selesai 100% (seratus perseratus) dan bukti penyerahan pekerjaan diterbitkan.</li>
            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian melakukan proses pembayaran atas pembelian barang selambat-lambatnya 7 (tujuh) hari kerja setelah PPK menilai bahwa dokumen pembayaran lengkap dan sah.</li>
        </ol>
    </li>

    <li>Sanksi
        <ol type="a">
            <li>Penyedia dikenakan sanksi apabila:
                <ol type="1">
                    <li>Tidak menanggapi pesanan barang selambat-lambatnya 14 (empat belas) hari kerja</li>
                    <li>Tidak dapat memenuhi pesanan sesuai dengan kesepakatan dalam transaksi melalui e-Purchasing dan SP ini tanpa disertai alasan yang dapat diterima;</li>
                    <li>menjual barang melalui proses e-Purchasing dengan harga yang lebih mahal dari harga Barang/Jasa yang dijual selain melalui e-Purchasing pada periode penjualan, jumlah, dan tempat serta spesifikasi teknis dan persyaratan yang sama.</li>
                </ol>
            </li>
            <li>Penyedia yang melakukan perbuatan sebagaimana dimaksud dalam huruf a dikenakan sanksi administratif berupa:
                <ol type="1">
                    <li>peringatan tertulis;</li>
                    <li>denda; dan</li>
                    <li>pelaporan kepada LKPP untuk dilakukan:
                        <ol type="a">
                            <li>penghentian sementara dalam sistem transaksi e-Purchasing; atau</li>
                            <li>penurunan pencantuman dari Katalog Elektronik (e-Catalogue).</li>
                        </ol>
                    </li>
                </ol>
            </li>
            <li>Tata Cara Pengenaan Sanksi<br>
                Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian mengenakan sanksi sebagaimana dimaksud dalam huruf a dan huruf b berdasarkan ketentuan mengenai sanksi sebagaimana diatur dalam Peraturan Kepala LKPP tentang e-Purchasing.
            </li>
        </ol>
    </li>

    <li>Penghentian dan Pemutusan SP
        <ol type="a">
            <li>Penghentian SP dapat dilakukan karena pekerjaan sudah selesai atau terjadi Keadaan Kahar</li>
            <li>Pemutusan SP oleh Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian
                <ol type="1">
                    <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dapat melakukan pemutusan SP apabila:
                        <ol type="a">
                            <li>kebutuhan barang/jasa tidak dapat ditunda melebihi batas berakhirnya SP;</li>
                            <li>berdasarkan penelitian Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian, Penyedia tidak akan mampu menyelesaikan keseluruhan pekerjaan walaupun diberikan kesempatan sampai dengan 50 (lima puluh) hari kalender sejak masa berakhirnya pelaksanaan pekerjaan untuk menyelesaikan pekerjaan;</li>
                            <li>setelah diberikan kesempatan menyelesaikan pekerjaan sampai dengan 50 (lima puluh) hari kalender sejak masa berakhirnya pelaksanaan pekerjaan, Penyedia Barang/Jasa tidak dapat menyelesaikan pekerjaan;</li>
                            <li>Penyedia lalai/cidera janji dalam melaksanakan kewajibannya dan tidak memperbaiki kelalaiannya dalam jangka waktu yang telah ditetapkan;</li>
                            <li>Penyedia terbukti melakukan KKN, kecurangan dan/atau pemalsuan dalam proses Pengadaan yang diputuskan oleh instansi yang berwenang; dan/atau</li>
                            <li>pengaduan tentang penyimpangan prosedur, dugaan KKN dan/atau pelanggaran persaingan sehat dalam pelaksanaan pengadaan dinyatakan benar oleh instansi yang berwenang.</li>
                        </ol>
                    </li>
                    <li>Pemutusan SP sebagaimana dimaksud pada angka 1) dilakukan selambat-lambatnya 30 (Tiga Puluh) hari kerja setelah Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian menyampaikan pemberitahuan rencana pemutusan SP secara tertulis kepada Penyedia.</li>
                </ol>
            </li>
            <li>Pemutusan SP oleh Penyedia
                <ol type="1">
                    <li>Penyedia dapat melakukan pemutusan Kontrak jika terjadi hal-hal sebagai berikut:
                        <ol type="a">
                            <li>akibat keadaan kahar sehingga Penyedia tidak dapat melaksanakan pekerjaan sesuai ketentuan SP atau adendum SP;</li>
                            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian gagal mematuhi keputusan akhir penyelesaian perselisihan; atau</li>
                            <li>Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian tidak memenuhi kewajiban sebagaimana dimaksud dalam SP atau Adendum SP.</li>
                        </ol>
                    </li>
                    <li>Pemutusan SP sebagaimana dimaksud No. 1) dilakukan selambat-lambatnya 30 (Tiga Puluh) hari kerja setelah Penyedia menyampaikan pemberitahuan rencana pemutusan SP secara tertulis kepada Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian.</li>
                </ol>
            </li>
        </ol>
    </li>

    <li>Denda Keterlambatan Pelaksanaan Pekerjaan
        <div style="margin-top: 5px;">
            Penyedia yang terlambat menyelesaikan pekerjaan dalam jangka waktu sebagaimana ditetapkan dalam SP ini karena kesalahan Penyedia, dikenakan denda keterlambatan sebesar 1/1000 (satu perseribu) dari total harga sebagaimana tercantum dalam SP ini untuk setiap hari keterlambatan.
        </div>
    </li>

    <li>Keadaan Kahar
        <ol type="a">
            <li>Keadaan Kahar adalah suatu keadaan yang terjadi diluar kehendak para pihak dan tidak dapat diperkirakan sebelumnya, sehingga kewajiban yang ditentukan dalam SP menjadi tidak dapat dipenuhi.</li>
            <li>Dalam hal terjadi Keadaan Kahar, Penyedia memberitahukan tentang terjadinya Keadaan Kahar kepada Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian secara tertulis dalam waktu selambat-lambatnya 14 (empat belas) hari kalender sejak terjadinya Keadaan Kahar yang dikeluarkan oleh pihak/instansi yang berwenang sesuai ketentuan peraturan perundang-undangan.</li>
            <li>Tidak termasuk Keadaan Kahar adalah hal-hal merugikan yang disebabkan oleh perbuatan atau kelalaian para pihak.</li>
            <li>Keterlambatan pelaksanaan pekerjaan yang diakibatkan oleh terjadinya Keadaan Kahar tidak dikenakan sanksi.</li>
            <li>Setelah terjadinya Keadaan Kahar, para pihak dapat melakukan kesepakatan, yang dituangkan dalam perubahan SP.</li>
        </ol>
    </li>

    <li>Penyelesaian Perselisihan
        <div style="margin-top: 5px;">
            Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian dan penyedia berkewajiban untuk berupaya sungguh-sungguh menyelesaikan secara damai semua perselisihan yang timbul dari atau berhubungan dengan SP ini atau interpretasinya selama atau setelah pelaksanaan pekerjaan. Jika perselisihan tidak dapat diselesaikan secara musyawarah maka perselisihan akan diselesaikan melalui arbitrase, mediasi, konsiliasi atau pengadilan negeri dalam wilayah hukum Republik Indonesia.
        </div>
    </li>

    <li>Larangan Pemberian Komisi
        <div style="margin-top: 5px;">
            Penyedia menjamin bahwa tidak satu pun personil satuan kerja Pejabat Penandatangan/Pengesahan Tanda Bukti Perjanjian telah atau akan menerima komisi dalam bentuk apapun (gratifikasi) atau keuntungan tidak sah lainnya baik langsung maupun tidak langsung dari SP ini. Penyedia menyetujui bahwa pelanggaran syarat ini merupakan pelanggaran yang mendasar terhadap SP ini.
        </div>
    </li>

    <li>Masa Berlaku SP
        <div style="margin-top: 5px;">
            SP ini berlaku sejak tanggal SP ini ditandatangani oleh para pihak sampai dengan tanggal {{ optional($process->tanggal_barang_diterima)->translatedFormat('d F Y') }} atau selesainya pelaksanaan pekerjaan.
        </div>
    </li>
</ol>
