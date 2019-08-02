<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Pengumuman Berkas Listing</h3>
                <div class="box-tools">
                    <a href="<?php echo site_url('pengumuman_berka/add'); ?>" class="btn btn-success btn-sm">Add</a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <tr>
                        <th>ID</th>
                        <th>Lanjut</th>
                        <th>Tampil</th>
                        <th>Id Pendaftar</th>
                        <th>Nama</th>
                        <th>Nilai Berkas</th>
                        <th>Alamat</th>
                        <th>Actions</th>
                    </tr>
					<?php foreach($pengumuman_berkas as $p) { ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo $p['lanjut']; ?></td>
                            <td><?php echo $p['tampil']; ?></td>
                            <td><?php echo $p['id_pendaftar']; ?></td>
                            <td><?php echo $p['nama']; ?></td>
                            <td><?php echo $p['nilai_berkas']; ?></td>
                            <td><?php echo $p['alamat']; ?></td>
                            <td>
                                <a href="<?php echo site_url('pengumuman_berka/edit/' . $p['id']); ?>"
                                   class="btn btn-info btn-xs"><span class="fa fa-pencil"></span> Edit</a> <a
                                        href="<?php echo site_url('pengumuman_berka/remove/' . $p['id']); ?>"
                                        class="btn btn-danger btn-xs"><span class="fa fa-trash"></span> Delete</a>
                            </td>
                        </tr>
					<?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
