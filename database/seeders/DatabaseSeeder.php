<?php

namespace Database\Seeders;

use App\Models\AhpComparison;
use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use App\Services\SpkAhpSawService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed 5 Kriteria Seleksi Eligible SNBP SMKN 8 Surabaya
        $dataKriteria = [
            ['kode' => 'C1', 'nama' => 'Rapor Akademik S1 - S5', 'atribut' => 'benefit'],
            ['kode' => 'C2', 'nama' => 'Nilai Kejuruan',         'atribut' => 'benefit'],
            ['kode' => 'C3', 'nama' => 'Prestasi Akademik',      'atribut' => 'benefit'],
            ['kode' => 'C4', 'nama' => 'Prestasi Non-Akademik',  'atribut' => 'benefit'],
            ['kode' => 'C5', 'nama' => 'Rata-rata Nilai TKA',    'atribut' => 'benefit'],
        ];

        $kMap = [];
        foreach ($dataKriteria as $dk) {
            $kMap[$dk['kode']] = Kriteria::create($dk);
        }

        // 2. Seed Matriks Perbandingan Berpasangan Awal (Kuesioner Pakar)
        $pairs = [
            ['C1', 'C2', 1 / 2], ['C1', 'C3', 1 / 3], ['C1', 'C4', 1 / 3], ['C1', 'C5', 9],
            ['C2', 'C3', 1 / 4], ['C2', 'C4', 1 / 4], ['C2', 'C5', 7],
            ['C3', 'C4', 1],     ['C3', 'C5', 5],
            ['C4', 'C5', 5],
        ];

        foreach ($pairs as [$cA, $cB, $val]) {
            AhpComparison::create([
                'kriteria_1_id' => $kMap[$cA]->id,
                'kriteria_2_id' => $kMap[$cB]->id,
                'nilai'         => $val,
            ]);
        }

        app(SpkAhpSawService::class)->hitungAhp(true);

        // 3. Seed 132 Data Alternatif Siswa SMKN 8 Surabaya (Terverifikasi C3 & C4)
        // Format: [kode, nama, kelas, c3, ket_c3, c4, ket_c4]
        $daftarSiswa = [
            ['A1',   'Dzakiyah Putri Sholeha',                  'XII Kuliner 4',    0, '-', 0, '-'],
            ['A2',   'DIRGANTARA PAMUNGKAS PUTRA POERNOMO',     'XII DKV 1',        4, 'Finalis LKS Nasional (Skor 4)', 1, 'Ketua Pelaksana Pentas Seni Sekolah (Skor 1)'],
            ['A3',   'Andrea Louise Neizya Arauna',             'XII Perhotelan 1', 0, '-', 0, '-'],
            ['A4',   'NUR AINI',                                'XII Busana 3',     0, '-', 1, 'Juara 1 Classmeeting Futsal/Basket (Skor 1)'],
            ['A5',   'Nabila Syakira',                          'XII DKV 2',        0, '-', 4, 'Juara 1 Lomba Desain Busana/Ilustrasi Nasional (Skor 4)'],
            ['A6',   'NAURA ISNAINI PUTRI DEWANTI',             'XII Kuliner 3',    0, '-', 0, '-'],
            ['A7',   'Bilqis Aulia',                            'XII Busana 3',     0, '-', 0, '-'],
            ['A8',   'Amelia Azzura',                           'XII Kuliner 3',    0, '-', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A9',   'silmy Ziad Baswedan',                     'XII Busana 1',     0, '-', 0, '-'],
            ['A10',  'Novelia Putri Fiasti',                    'XII Kuliner 3',    0, '-', 0, '-'],
            ['A11',  'DYAH AYU KESUMANINGTYAS',                 'XII Kuliner 3',    0, '-', 3, 'Juara 2 Kejurprov Atletik Jawa Timur (Skor 3)'],
            ['A12',  'Aisyatul mutaalifah',                     'XII Perhotelan 2', 3, 'Medali Perunggu Olimpiade Sains Provinsi Jatim (Skor 3)', 0, '-'],
            ['A13',  'Muhammad Naufal Athallah',                'XII Kuliner 3',    0, '-', 4, 'Peraih Penghargaan Pramuka Garuda Nasional (Skor 4)'],
            ['A14',  'Yuni Yana',                               'XII Busana 1',     2, 'Juara 3 Lomba Karya Tulis Ilmiah Pelajar Surabaya (Skor 2)', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A15',  'Aisyah Febriani Dewi',                    'XII Busana 2',     0, '-', 5, 'Juara 3 ASEAN Youth Cultural & Art Festival (Skor 5); Ketua Pelaksana Pentas Seni Sekolah (Skor 1) -> Diambil Max: 5'],
            ['A16',  'ZAHRATUL WARDAH',                         'XII Kecantikan 3', 0, '-', 0, '-'],
            ['A17',  'Fennyta aprilia',                         'XII Kecantikan 3', 0, '-', 0, '-'],
            ['A18',  'joefixa qumaira gloxinia handriani',      'XII DKV 2',        0, '-', 2, 'Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2); Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1) -> Diambil Max: 2'],
            ['A19',  'Citra Tania Eka Amelia',                  'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A20',  'kayla mahdaviqia azalea',                 'XII Perhotelan 1', 5, 'Medali Perunggu International Youth Innovation Summit (Skor 5); Juara 2 LKS Provinsi Jawa Timur (Skor 3) -> Diambil Max: 5', 5, 'Juara 3 ASEAN Youth Cultural & Art Festival (Skor 5)'],
            ['A21',  'Farel Yasril El Manda',                   'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A22',  'KIRANI NOVITA SARI',                      'XII Busana 3',     3, 'Medali Perunggu Olimpiade Sains Provinsi Jatim (Skor 3); Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2) -> Diambil Max: 3', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A23',  'RIKAH MAULIDYYAH HANUM',                  'XII Kecantikan 3', 4, 'Juara 3 Lomba Esai Ilmiah Nasional (Skor 4)', 3, 'Juara 1 Lomba Tata Rias/Kuliner Tingkat Provinsi Jatim (Skor 3)'],
            ['A24',  'Aulia swastinika naima',                  'XII Kuliner 5',    0, '-', 2, 'Juara 2 Kejuaraan Pencak Silat Pelajar Kota Surabaya (Skor 2)'],
            ['A25',  'MARTASYA AURELIA DAMAYANTI SUPRAPTO',     'XII Kuliner 1',    0, '-', 0, '-'],
            ['A26',  'Nindy Arnelia Putri',                     'XII Kuliner 1',    0, '-', 2, 'Juara 2 Kejuaraan Pencak Silat Pelajar Kota Surabaya (Skor 2); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 2'],
            ['A27',  'Febyana Rachmaningsih',                   'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A28',  'Fidela Zerlina Latuconsina',              'XII Kuliner 1',    0, '-', 0, '-'],
            ['A29',  'Putri Aprilia Dewi Sujarwo',              'XII Kuliner 1',    0, '-', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2)'],
            ['A30',  'Ardianti Litasetyana',                    'XII Busana 1',     5, 'Finalis International Vocational Skills Competition (Skor 5)', 0, '-'],
            ['A31',  'Nilna Azha Faradisa',                     'XII Busana 2',     0, '-', 2, 'Juara 2 Kejuaraan Pencak Silat Pelajar Kota Surabaya (Skor 2); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 2'],
            ['A32',  'Alifta Febrianti Retno Palupi',           'XII Kuliner 1',    3, 'Juara 2 LKS Provinsi Jawa Timur (Skor 3); Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2) -> Diambil Max: 3', 0, '-'],
            ['A33',  'NAFLA QATRUNNADA PUTRI RIBOWO',           'XII Busana 2',     0, '-', 0, '-'],
            ['A34',  'Nur Laila',                               'XII Kuliner 4',    0, '-', 0, '-'],
            ['A35',  'Kamila Fahmi Bajamal',                    'XII Kecantikan 3', 0, '-', 3, 'Juara 1 Lomba Tata Rias/Kuliner Tingkat Provinsi Jatim (Skor 3)'],
            ['A36',  'Adi Aldho Duvano',                        'XII DKV 2',        0, '-', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 3'],
            ['A37',  'Adelia Salsabila Fitria',                 'XII Kuliner 4',    1, 'Juara 1 Lomba Cerdas Cermat Tingkat Sekolah (Skor 1)', 3, 'Juara 2 Kejurprov Atletik Jawa Timur (Skor 3); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 3'],
            ['A38',  'Aqqilah Barekat',                         'XII Kuliner 1',    0, '-', 4, 'Peraih Penghargaan Pramuka Garuda Nasional (Skor 4)'],
            ['A39',  'VANIA DELLA CAHYA PUTRI',                 'XII DKV 2',        0, '-', 0, '-'],
            ['A40',  'Naouroh Wahyuning Asih Putri',            'XII DKV 1',        2, 'Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2)', 4, 'Juara 1 Lomba Desain Busana/Ilustrasi Nasional (Skor 4); Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2) -> Diambil Max: 4'],
            ['A41',  'Syahid Rahman Hadi',                      'XII DKV 2',        0, '-', 0, '-'],
            ['A42',  'REGITA PUTRI CAHYANI',                    'XII DKV 2',        0, '-', 0, '-'],
            ['A43',  'Anella Mahya Tara Kinasih',               'XII Busana 1',     0, '-', 0, '-'],
            ['A44',  'FEIRA RAISSA PUTRI',                      'XII DKV 2',        2, 'Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2)', 0, '-'],
            ['A45',  'Farah Noearsy fadilla',                   'XII Busana 3',     2, 'Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2)', 0, '-'],
            ['A46',  'Gysella Ayega Setya',                     'XII Busana 3',     0, '-', 2, 'Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 2'],
            ['A47',  'Putri Ayu Diah Saraswati',                'XII Kuliner 3',    2, 'Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2)', 3, 'Juara 2 Kejurprov Atletik Jawa Timur (Skor 3)'],
            ['A48',  'Rodiyah',                                 'XII Busana 3',     4, 'Juara 3 Lomba Esai Ilmiah Nasional (Skor 4)', 0, '-'],
            ['A49',  'Amanda Prameswara putri',                 'XII Kuliner 1',    0, '-', 1, 'Juara 1 Classmeeting Futsal/Basket (Skor 1)'],
            ['A50',  'Catherine Veronica Angelina Laurance',    'XII DKV 2',        0, '-', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2); Ketua Pelaksana Pentas Seni Sekolah (Skor 1) -> Diambil Max: 2'],
            ['A51',  'FELY RACHMA CHARIR',                      'XII Busana 3',     5, 'Medali Perunggu International Youth Innovation Summit (Skor 5); Peringkat 1 Try Out UTBK Sekolah (Skor 1) -> Diambil Max: 5', 3, 'Juara 2 Kejurprov Atletik Jawa Timur (Skor 3)'],
            ['A52',  'Nurul Istiqomah',                         'XII Kuliner 5',    1, 'Juara 1 Lomba Cerdas Cermat Tingkat Sekolah (Skor 1)', 4, 'Peraih Penghargaan Pramuka Garuda Nasional (Skor 4)'],
            ['A53',  'DWI APRILIA',                             'XII Busana 1',     4, 'Medali Perak Olimpiade Sains Nasional (OSN) (Skor 4); Juara 3 Lomba Karya Tulis Ilmiah Pelajar Surabaya (Skor 2) -> Diambil Max: 4', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3)'],
            ['A54',  'REVA KEYLLA ZEYNICK PRATIWI',             'XII Busana 1',     1, 'Peringkat 1 Try Out UTBK Sekolah (Skor 1)', 0, '-'],
            ['A55',  'Nia ramadani',                            'XII Perhotelan 2', 2, 'Juara 3 Lomba Karya Tulis Ilmiah Pelajar Surabaya (Skor 2)', 0, '-'],
            ['A56',  'Erlita Dayu Lestari',                     'XII Busana 1',     4, 'Juara 3 Lomba Esai Ilmiah Nasional (Skor 4)', 0, '-'],
            ['A57',  'Nindya Putri Anggreini',                  'XII Busana 1',     3, 'Juara 1 Kompetisi Inovasi Vokasi Jawa Timur (Skor 3)', 1, 'Juara 1 Classmeeting Futsal/Basket (Skor 1)'],
            ['A58',  'Raisyah Putri Ramadhani',                 'XII Busana 1',     0, '-', 0, '-'],
            ['A59',  'Widya Chintya Ahmad',                     'XII DKV 2',        0, '-', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2)'],
            ['A60',  'Yemima Giovanni Wibowo',                  'XII Kuliner 2',    0, '-', 5, 'Finalis International Junior Culinary Challenge (Skor 5)'],
            ['A61',  'Raisya Alifta Putri',                     'XII Perhotelan 1', 0, '-', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3)'],
            ['A62',  'Adinda Cesyara Anwar',                    'XII Perhotelan 1', 0, '-', 0, '-'],
            ['A63',  'Ryshalum Queen Khanza Zulkarnain',        'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A64',  'Fairus Zahro',                            'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A65',  'Chelsea Asmarani Sastrawanti',            'XII Kuliner 2',    2, 'Juara 2 LKS Tingkat Kota Surabaya (Skor 2); Juara 2 Olimpiade Sains Internal SMKN 8 (Skor 1) -> Diambil Max: 2', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A66',  'Rahayu wulandari',                        'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A67',  'Nanda Febriani',                          'XII DKV 2',        0, '-', 0, '-'],
            ['A68',  'EKA KARTIKA SARI',                        'XII Kuliner 2',    0, '-', 0, '-'],
            ['A69',  'Radinnia Wulan Syabanie',                 'XII DKV 2',        1, 'Peringkat 1 Try Out UTBK Sekolah (Skor 1)', 4, 'Juara 2 Kejurnas Taekwondo Junior (Skor 4)'],
            ['A70',  'Setiana Neng Asih',                       'XII DKV 2',        4, 'Finalis LKS Nasional (Skor 4)', 2, 'Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 2'],
            ['A71',  'Saqiya Ziya\' Al-Wahdani. I',             'XII Busana 1',     3, 'Juara 1 Kompetisi Inovasi Vokasi Jawa Timur (Skor 3)', 0, '-'],
            ['A72',  'CHIESA APRILIA VIRID',                    'XII DKV 2',        3, 'Juara 1 Kompetisi Inovasi Vokasi Jawa Timur (Skor 3); Juara 2 Olimpiade Sains Internal SMKN 8 (Skor 1) -> Diambil Max: 3', 4, 'Juara 1 Lomba Desain Busana/Ilustrasi Nasional (Skor 4)'],
            ['A73',  'Jasmine Syalia Viano',                    'XII Kecantikan 3', 0, '-', 2, 'Juara 2 Kejuaraan Pencak Silat Pelajar Kota Surabaya (Skor 2); Ketua Pelaksana Pentas Seni Sekolah (Skor 1) -> Diambil Max: 2'],
            ['A74',  'Andi Dwi Dita Ananda',                    'XII Perhotelan 1', 3, 'Juara 1 Kompetisi Inovasi Vokasi Jawa Timur (Skor 3)', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A75',  'Stefandi Achmad Renata',                  'XII Perhotelan 1', 0, '-', 1, 'Ketua Pelaksana Pentas Seni Sekolah (Skor 1)'],
            ['A76',  'Hervida Naflah Az Zahra',                 'XII Kuliner 3',    0, '-', 0, '-'],
            ['A77',  'Nabila Azza Fauziyah Nazlie',             'XII Kuliner 2',    3, 'Juara 1 Kompetisi Inovasi Vokasi Jawa Timur (Skor 3)', 0, '-'],
            ['A78',  'nadiah zhakeysa candra wita',             'XII Kuliner 3',    0, '-', 2, 'Juara 2 Kejuaraan Pencak Silat Pelajar Kota Surabaya (Skor 2)'],
            ['A79',  'Chelsea Frisca Permata',                  'XII Kuliner 3',    1, 'Juara 1 Lomba Cerdas Cermat Tingkat Sekolah (Skor 1)', 0, '-'],
            ['A80',  'Rista ariyanti',                          'XII Busana 3',     0, '-', 0, '-'],
            ['A81',  'Rahmania Syahra Setyawati',               'XII Kuliner 1',    2, 'Juara 3 Lomba Karya Tulis Ilmiah Pelajar Surabaya (Skor 2)', 4, 'Peraih Penghargaan Pramuka Garuda Nasional (Skor 4); Ketua Pelaksana Pentas Seni Sekolah (Skor 1) -> Diambil Max: 4'],
            ['A82',  'Armilda Rosiana Azarine',                 'XII Perhotelan 1', 0, '-', 0, '-'],
            ['A83',  'Nabila Avrilia',                          'XII Busana 2',     3, 'Juara 1 Kompetisi Inovasi Vokasi Jawa Timur (Skor 3); Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2) -> Diambil Max: 3', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A84',  'Sava Hajarul Aswadinata',                 'XII Busana 2',     0, '-', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3)'],
            ['A85',  'MUCH. YUSUF ALDIANSYAH',                  'XII DKV 1',        0, '-', 0, '-'],
            ['A86',  'zaskia syaula sari',                      'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A87',  'Laila Hijjatul Azzaroh',                  'XII Kuliner 2',    3, 'Juara 2 LKS Provinsi Jawa Timur (Skor 3)', 2, 'Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2)'],
            ['A88',  'suluh wirda firdausa',                    'XII Kecantikan 3', 0, '-', 0, '-'],
            ['A89',  'Evania Nesha',                            'XII Kecantikan 2', 0, '-', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2)'],
            ['A90',  'Bisma Christo Davi Pratama',              'XII Perhotelan 1', 0, '-', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2)'],
            ['A91',  'raissa ardiyanti',                        'XII Kuliner 4',    0, '-', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3)'],
            ['A92',  'MUHAMMAD FARHAN RAMADHANI',               'XII DKV 1',        1, 'Peringkat 1 Try Out UTBK Sekolah (Skor 1)', 0, '-'],
            ['A93',  'nailah dwi novianti',                     'XII Kuliner 4',    0, '-', 1, 'Ketua Pelaksana Pentas Seni Sekolah (Skor 1)'],
            ['A94',  'AQILA NAFISA RASHAQA',                    'XII DKV 1',        0, '-', 2, 'Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 2'],
            ['A95',  'Areva Hikmadianty',                       'XII DKV 1',        3, 'Juara 2 LKS Provinsi Jawa Timur (Skor 3)', 0, '-'],
            ['A96',  'Jihan malika ainun naswah',               'XII Kuliner 4',    4, 'Medali Perak Olimpiade Sains Nasional (OSN) (Skor 4)', 0, '-'],
            ['A97',  'Chika Wulan Sari',                        'XII DKV 1',        0, '-', 0, '-'],
            ['A98',  'CHECE ZASKIA PUTRI ARDIANSYAH',           'XII Kecantikan 3', 1, 'Peringkat 1 Try Out UTBK Sekolah (Skor 1)', 0, '-'],
            ['A99',  'Nadya Shafwah Salsabila',                 'XII Kuliner 2',    0, '-', 3, 'Juara 2 Kejurprov Atletik Jawa Timur (Skor 3)'],
            ['A100', 'Quthaifa Zahra Awwaliya',                 'XII Kuliner 5',    2, 'Juara 3 Lomba Karya Tulis Ilmiah Pelajar Surabaya (Skor 2)', 0, '-'],
            ['A101', 'DITA AINUR RAHMA YULIATI',                'XII DKV 2',        0, '-', 0, '-'],
            ['A102', 'Aulia Putri Timur Sidhiq',                'XII Kuliner 4',    0, '-', 2, 'Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2)'],
            ['A103', 'Moza Ayrini',                             'XII Busana 1',     4, 'Juara 3 Lomba Esai Ilmiah Nasional (Skor 4)', 0, '-'],
            ['A104', 'NABILA AZ ZAHRA ALBHAZAR',                'XII Kecantikan 3', 0, '-', 1, 'Juara 1 Classmeeting Futsal/Basket (Skor 1)'],
            ['A105', 'Berlian Swastanti Putri',                 'XII Busana 1',     0, '-', 2, 'Juara 1 Lomba Fashion Design Tingkat Kota (Skor 2)'],
            ['A106', 'AULIA NUR RAKHMAH',                       'XII Kecantikan 2', 1, 'Juara 1 Lomba Cerdas Cermat Tingkat Sekolah (Skor 1)', 0, '-'],
            ['A107', 'Bayu Priyatna',                           'XII Kuliner 4',    3, 'Juara 2 LKS Provinsi Jawa Timur (Skor 3)', 0, '-'],
            ['A108', 'ICHSAN HABIB RAHMATULLAH',                'XII Kuliner 4',    1, 'Juara 2 Olimpiade Sains Internal SMKN 8 (Skor 1)', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3)'],
            ['A109', 'Dzakiyatus Sholehah',                     'XII Busana 2',     4, 'Juara 3 Lomba Esai Ilmiah Nasional (Skor 4); Juara 2 LKS Provinsi Jawa Timur (Skor 3) -> Diambil Max: 4', 0, '-'],
            ['A110', 'Anisa putri',                             'XII Kecantikan 3', 3, 'Juara 1 Kompetisi Inovasi Vokasi Jawa Timur (Skor 3)', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2); Ketua Pelaksana Pentas Seni Sekolah (Skor 1) -> Diambil Max: 2'],
            ['A111', 'Nur Aini/25',                             'XII Kuliner 2',    0, '-', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A112', 'MOCH RAFLY ABD ROCHIM',                   'XII DKV 2',        2, 'Finalis Olimpiade Akuntansi/Ekonomi Kota Surabaya (Skor 2)', 0, '-'],
            ['A113', 'Rachelia Habatus Sayyidah Hayyi',         'XII DKV 2',        1, 'Juara 1 Lomba Cerdas Cermat Tingkat Sekolah (Skor 1)', 2, 'Juara 2 Kejuaraan Pencak Silat Pelajar Kota Surabaya (Skor 2)'],
            ['A114', 'Salwa Naomi Nessya',                      'XII Perhotelan 1', 1, 'Juara 2 Olimpiade Sains Internal SMKN 8 (Skor 1)', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3)'],
            ['A115', 'Lucretia Leine Zafitri Nugroho',          'XII Perhotelan 1', 0, '-', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2)'],
            ['A116', 'Aisyatul Azizah',                         'XII Perhotelan 2', 0, '-', 0, '-'],
            ['A117', 'Bintang Restika Nila Syafi’i',            'XII Kuliner 5',    3, 'Medali Perunggu Olimpiade Sains Provinsi Jatim (Skor 3)', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3)'],
            ['A118', 'Naila Afifah Azzahra',                    'XII Kuliner 1',    0, '-', 4, 'Peraih Penghargaan Pramuka Garuda Nasional (Skor 4); Juara 2 Kejurprov Atletik Jawa Timur (Skor 3) -> Diambil Max: 4'],
            ['A119', 'Nikmatus Soleha',                         'XII Kuliner 1',    0, '-', 1, 'Juara 2 Lomba Desain Poster Hari Kartini Sekolah (Skor 1)'],
            ['A120', 'Sayhila Zahratu Syita',                   'XII Perhotelan 2', 0, '-', 2, 'Juara 2 Kejuaraan Pencak Silat Pelajar Kota Surabaya (Skor 2)'],
            ['A121', 'Nazwa Aurel Febriany',                    'XII Perhotelan 2', 1, 'Juara 1 Lomba Cerdas Cermat Tingkat Sekolah (Skor 1)', 2, 'Juara 3 Festival Band Pelajar Surabaya (Skor 2)'],
            ['A122', 'Aura Kusuma Wardhani',                    'XII Kuliner 2',    1, 'Juara 1 Lomba Cerdas Cermat Tingkat Sekolah (Skor 1)', 1, 'Juara 1 Classmeeting Futsal/Basket (Skor 1)'],
            ['A123', 'AZSAHRA PUTRI FEBRINA',                   'XII Kuliner 3',    0, '-', 3, 'Juara 3 Paskibra Tingkat Provinsi (Skor 3); Juara 1 Classmeeting Futsal/Basket (Skor 1) -> Diambil Max: 3'],
            ['A124', 'Sinta Asnawan Putri',                     'XII Busana 2',     0, '-', 0, '-'],
            ['A125', 'Ardityo Kresna Pandyatma',                'XII Perhotelan 2', 1, 'Juara 2 Olimpiade Sains Internal SMKN 8 (Skor 1)', 3, 'Juara 1 Lomba Tata Rias/Kuliner Tingkat Provinsi Jatim (Skor 3)'],
            ['A126', 'akbarrrakawinata@gmail.com',              'XII DKV 2',        0, '-', 0, '-'],
            ['A127', 'RHEZA DWI ARIYANTO',                      'XII DKV 2',        0, '-', 0, '-'],
            ['A128', 'JIHAN MUTIARA FEBRIANTI',                 'XII Kecantikan 2', 0, '-', 0, '-'],
            ['A129', 'Sekar Azzahro P',                         'XII Kuliner 3',    4, 'Medali Perak Olimpiade Sains Nasional (OSN) (Skor 4)', 0, '-'],
            ['A130', 'Qurrotul ainiyah',                        'XII Kuliner 4',    0, '-', 0, '-'],
            ['A131', 'Indi Lutfiyati Ummaroh',                  'XII Kuliner 2',    1, 'Peringkat 1 Try Out UTBK Sekolah (Skor 1)', 0, '-'],
            ['A132', 'Vitri Nabila',                            'XII Perhotelan 1', 0, '-', 1, 'Juara 1 Classmeeting Futsal/Basket (Skor 1)'],
        ];

        foreach ($daftarSiswa as [$kode, $nama, $kelas, $c3, $ketC3, $c4, $ketC4]) {
            $alt = Alternatif::create([
                'kode'   => $kode,
                'nama'   => $nama,
                'kelas'  => $kelas,
                'ket_c3' => $ketC3,
                'ket_c4' => $ketC4,
            ]);

            Penilaian::insert([
                ['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C1']->id, 'nilai' => 0,   'created_at' => now(), 'updated_at' => now()],
                ['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C2']->id, 'nilai' => 0,   'created_at' => now(), 'updated_at' => now()],
                ['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C3']->id, 'nilai' => $c3, 'created_at' => now(), 'updated_at' => now()],
                ['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C4']->id, 'nilai' => $c4, 'created_at' => now(), 'updated_at' => now()],
                ['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C5']->id, 'nilai' => 0,   'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}