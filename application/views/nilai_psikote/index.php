<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Nilai Psikotes Listing</h3>
                <div class="box-tools">
                    <a href="<?php echo site_url('nilai_psikote/add'); ?>" class="btn btn-success btn-sm">Add</a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <tr>
                        <th>ID</th>
                        <th>Id Pendaftar</th>
                        <th>Nilai</th>
                        <th>Actions</th>
                    </tr>
					<?php foreach($nilai_psikotes as $n) { ?>
                        <tr>
                            <td><?php echo $n['id']; ?></td>
                            <td><?php echo $n['id_pendaftar']; ?></td>
                            <td><?php echo $n['nilai']; ?></td>
                            <td>
                                <a href="<?php echo site_url('nilai_psikote/edit/' . $n['id']); ?>"
                                   class="btn btn-info btn-xs"><span class="fa fa-pencil"></span> Edit</a> <a
                                        href="<?php echo site_url('nilai_psikote/remove/' . $n['id']); ?>"
                                        class="btn btn-danger btn-xs"><span class="fa fa-trash"></span> Delete</a>
                            </td>
                        </tr>
					<?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
