-- Tomo | PRATAMA STUDIO

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
`id` int(11) NOT NULL,
`id_user` int(11) ,
`nama` varchar(100) ,
`jk` enum('L','P') ,
`agama` enum('Islam','Kristen','Katolik','Hindu','Budha','Konghucu','Lainnya') ,
`ttl` varchar(100) ,
`jabatan` varchar(100) ,
`alamat` text ,
`gol_darah` enum('A','B','AB','O') ,
`hp` varchar(15) ,
`email` varchar(30) ,
`foto` varchar(200) ,
`ket` text 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `admin`
--

ALTER TABLE `admin`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `admin`
--

ALTER TABLE `admin`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `berkas`
--

DROP TABLE IF EXISTS `berkas`;
CREATE TABLE `berkas` (
`id` int(11) NOT NULL,
`id_pendaftar` int(11) ,
`foto` varchar(200) ,
`cv` varchar(200) ,
`ijazah_smu` varchar(200) ,
`ijazah_pt` varchar(200) ,
`transkrip_smu` varchar(200) ,
`transkrip_pt` varchar(200) ,
`sertifikat1` varchar(200) ,
`sertifikat2` varchar(200) ,
`sertifikat3` varchar(200) ,
`sertifikat4` varchar(200) ,
`sertifikat5` varchar(200) 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `berkas`
--

ALTER TABLE `berkas`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `berkas`
--

ALTER TABLE `berkas`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_berkas`
--

DROP TABLE IF EXISTS `nilai_berkas`;
CREATE TABLE `nilai_berkas` (
`id` int(11) NOT NULL,
`id_pendaftar` int(11) ,
`nilai` int(5) 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `nilai_berkas`
--

ALTER TABLE `nilai_berkas`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `nilai_berkas`
--

ALTER TABLE `nilai_berkas`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_pengetahuan`
--

DROP TABLE IF EXISTS `nilai_pengetahuan`;
CREATE TABLE `nilai_pengetahuan` (
`id` int(11) NOT NULL,
`id_pendaftar` int(11) ,
`nilai` smallint(3) 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `nilai_pengetahuan`
--

ALTER TABLE `nilai_pengetahuan`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `nilai_pengetahuan`
--

ALTER TABLE `nilai_pengetahuan`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_psikotes`
--

DROP TABLE IF EXISTS `nilai_psikotes`;
CREATE TABLE `nilai_psikotes` (
`id` int(11) NOT NULL,
`id_pendaftar` int(11) ,
`nilai` smallint(3) 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `nilai_psikotes`
--

ALTER TABLE `nilai_psikotes`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `nilai_psikotes`
--

ALTER TABLE `nilai_psikotes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

DROP TABLE IF EXISTS `pendaftaran`;
CREATE TABLE `pendaftaran` (
`id` int(11) NOT NULL,
`id_user` int(11) ,
`nama` varchar(100) ,
`jk` enum('L','P') ,
`agama` enum('Islam','Kristen','Katolik','Hindu','Budha','Konghucu','Lainnya') ,
`tempat_lahir` varchar(100) ,
`tanggal_lahir` varchar(30) ,
`alamat` text ,
`gol_darah` enum('A','B','AB','O') ,
`nik` varchar(50) ,
`pendidikan` enum('SLTP','SMA','D1','D2','D3','D4','S1','S2','S3') ,
`jurusan` varchar(100) ,
`lulusan` varchar(100) ,
`tahun_lulus` varchar(15) ,
`hp` varchar(15) ,
`email` varchar(30) ,
`aktif` enum('Off','On') ,
`foto` varchar(200) ,
`nikt` varchar(20) ,
`status` enum('daftar','ujian','psikotes','lulus') ,
`ket` text 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `pendaftaran`
--

ALTER TABLE `pendaftaran`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `pendaftaran`
--

ALTER TABLE `pendaftaran`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `pengetahuan`
--

DROP TABLE IF EXISTS `pengetahuan`;
CREATE TABLE `pengetahuan` (
`id` int(11) NOT NULL,
`soal` text ,
`a` varchar(500) ,
`b` varchar(500) ,
`c` varchar(500) ,
`d` varchar(500) ,
`e` varchar(500) ,
`kunci` enum('a','b','c','d','e') 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `pengetahuan`
--

ALTER TABLE `pengetahuan`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `pengetahuan`
--

ALTER TABLE `pengetahuan`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

DROP TABLE IF EXISTS `pengumuman`;
CREATE TABLE `pengumuman` (
`id` int(11) NOT NULL,
`id_pendaftar` int(11) ,
`nama` varchar(200) ,
`alamat` text ,
`nilai_berkas` smallint(3) ,
`nilai_pengetahuan` smallint(3) ,
`nilai_psikotes` smallint(3) ,
`diterima` tinyint(1) ,
`tampil` tinyint(1) 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `pengumuman`
--

ALTER TABLE `pengumuman`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `pengumuman`
--

ALTER TABLE `pengumuman`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman_berkas`
--

DROP TABLE IF EXISTS `pengumuman_berkas`;
CREATE TABLE `pengumuman_berkas` (
`id` int(11) NOT NULL,
`id_pendaftar` int(11) ,
`nama` varchar(200) ,
`alamat` text ,
`nilai_berkas` smallint(3) ,
`lanjut` tinyint(1) ,
`tampil` tinyint(1) 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `pengumuman_berkas`
--

ALTER TABLE `pengumuman_berkas`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `pengumuman_berkas`
--

ALTER TABLE `pengumuman_berkas`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `psikotes`
--

DROP TABLE IF EXISTS `psikotes`;
CREATE TABLE `psikotes` (
`id` int(11) NOT NULL,
`soal` text ,
`a` varchar(500) ,
`b` varchar(500) ,
`c` varchar(500) ,
`d` varchar(500) ,
`e` varchar(500) ,
`kunci` enum('a','b','c','d','e') 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `psikotes`
--

ALTER TABLE `psikotes`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `psikotes`
--

ALTER TABLE `psikotes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
`id` int(11) NOT NULL,
`username` varchar(100) NOT NULL,
`password` varchar(200) NOT NULL,
`level` enum('admin','hrd','user') ,
`aktif` enum('0','1') 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `user`
--

ALTER TABLE `user`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `user`
--

ALTER TABLE `user`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
