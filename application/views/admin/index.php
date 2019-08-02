<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Admin Listing</h3>
                <div class="box-tools">
                    <a href="<?php echo site_url('admin/add'); ?>" class="btn btn-success btn-sm">Add</a>
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
                        <th>Ttl</th>
                        <th>Jabatan</th>
                        <th>Gol Darah</th>
                        <th>Hp</th>
                        <th>Email</th>
                        <th>Foto</th>
                        <th>Alamat</th>
                        <th>Ket</th>
                        <th>Actions</th>
                    </tr>
					<?php foreach($admin as $a) { ?>
                        <tr>
                            <td><?php echo $a['id']; ?></td>
                            <td><?php echo $a['id_user']; ?></td>
                            <td><?php echo $a['nama']; ?></td>
                            <td><?php echo $a['jk']; ?></td>
                            <td><?php echo $a['agama']; ?></td>
                            <td><?php echo $a['ttl']; ?></td>
                            <td><?php echo $a['jabatan']; ?></td>
                            <td><?php echo $a['gol_darah']; ?></td>
                            <td><?php echo $a['hp']; ?></td>
                            <td><?php echo $a['email']; ?></td>
                            <td><?php echo $a['foto']; ?></td>
                            <td><?php echo $a['alamat']; ?></td>
                            <td><?php echo $a['ket']; ?></td>
                            <td>
                                <a href="<?php echo site_url('admin/edit/' . $a['id']); ?>" class="btn btn-info btn-xs"><span
                                            class="fa fa-pencil"></span> Edit</a> <a
                                        href="<?php echo site_url('admin/remove/' . $a['id']); ?>"
                                        class="btn btn-danger btn-xs"><span class="fa fa-trash"></span> Delete</a>
                            </td>
                        </tr>
					<?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
