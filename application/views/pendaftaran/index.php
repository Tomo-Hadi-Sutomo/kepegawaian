<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Pendaftaran Listing</h3>
                <div class="box-tools">
                    <a href="<?php echo site_url('pendaftaran/add'); ?>" class="btn btn-success btn-sm">Add</a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <tr>
                        <th>ID</th>
                        <th>Id User</th>
                        <th>Nama</th>
                        <th>Jk</th>
                        <th>Agama</th>
                        <th>Tempat Lahir</th>
                        <th>Tanggal Lahir</th>
                        <th>Gol Darah</th>
                        <th>Nik</th>
                        <th>Pendidikan</th>
                        <th>Jurusan</th>
                        <th>Lulusan</th>
                        <th>Tahun Lulus</th>
                        <th>Hp</th>
                        <th>Email</th>
                        <th>Aktif</th>
                        <th>Foto</th>
                        <th>Nikt</th>
                        <th>Status</th>
                        <th>Alamat</th>
                        <th>Ket</th>
                        <th>Actions</th>
                    </tr>
					<?php foreach($pendaftaran as $p) { ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo $p['id_user']; ?></td>
                            <td><?php echo $p['nama']; ?></td>
                            <td><?php echo $p['jk']; ?></td>
                            <td><?php echo $p['agama']; ?></td>
                            <td><?php echo $p['tempat_lahir']; ?></td>
                            <td><?php echo $p['tanggal_lahir']; ?></td>
                            <td><?php echo $p['gol_darah']; ?></td>
                            <td><?php echo $p['nik']; ?></td>
                            <td><?php echo $p['pendidikan']; ?></td>
                            <td><?php echo $p['jurusan']; ?></td>
                            <td><?php echo $p['lulusan']; ?></td>
                            <td><?php echo $p['tahun_lulus']; ?></td>
                            <td><?php echo $p['hp']; ?></td>
                            <td><?php echo $p['email']; ?></td>
                            <td><?php echo $p['aktif']; ?></td>
                            <td><?php echo $p['foto']; ?></td>
                            <td><?php echo $p['nikt']; ?></td>
                            <td><?php echo $p['status']; ?></td>
                            <td><?php echo $p['alamat']; ?></td>
                            <td><?php echo $p['ket']; ?></td>
                            <td>
                                <a href="<?php echo site_url('pendaftaran/edit/' . $p['id']); ?>"
                                   class="btn btn-info btn-xs"><span class="fa fa-pencil"></span> Edit</a> <a
                                        href="<?php echo site_url('pendaftaran/remove/' . $p['id']); ?>"
                                        class="btn btn-danger btn-xs"><span class="fa fa-trash"></span> Delete</a>
                            </td>
                        </tr>
					<?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
