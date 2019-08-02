<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Berkas Listing</h3>
                <div class="box-tools">
                    <a href="<?php echo site_url('berka/add'); ?>" class="btn btn-success btn-sm">Add</a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <tr>
                        <th>ID</th>
                        <th>Id Pendaftar</th>
                        <th>Foto</th>
                        <th>Cv</th>
                        <th>Ijazah Smu</th>
                        <th>Ijazah Pt</th>
                        <th>Transkrip Smu</th>
                        <th>Transkrip Pt</th>
                        <th>Sertifikat1</th>
                        <th>Sertifikat2</th>
                        <th>Sertifikat3</th>
                        <th>Sertifikat4</th>
                        <th>Sertifikat5</th>
                        <th>Actions</th>
                    </tr>
					<?php foreach($berkas as $b) { ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td><?php echo $b['id_pendaftar']; ?></td>
                            <td><?php echo $b['foto']; ?></td>
                            <td><?php echo $b['cv']; ?></td>
                            <td><?php echo $b['ijazah_smu']; ?></td>
                            <td><?php echo $b['ijazah_pt']; ?></td>
                            <td><?php echo $b['transkrip_smu']; ?></td>
                            <td><?php echo $b['transkrip_pt']; ?></td>
                            <td><?php echo $b['sertifikat1']; ?></td>
                            <td><?php echo $b['sertifikat2']; ?></td>
                            <td><?php echo $b['sertifikat3']; ?></td>
                            <td><?php echo $b['sertifikat4']; ?></td>
                            <td><?php echo $b['sertifikat5']; ?></td>
                            <td>
                                <a href="<?php echo site_url('berka/edit/' . $b['id']); ?>" class="btn btn-info btn-xs"><span
                                            class="fa fa-pencil"></span> Edit</a> <a
                                        href="<?php echo site_url('berka/remove/' . $b['id']); ?>"
                                        class="btn btn-danger btn-xs"><span class="fa fa-trash"></span> Delete</a>
                            </td>
                        </tr>
					<?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
