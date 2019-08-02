<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Psikotes Listing</h3>
                <div class="box-tools">
                    <a href="<?php echo site_url('psikote/add'); ?>" class="btn btn-success btn-sm">Add</a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <tr>
                        <th>ID</th>
                        <th>A</th>
                        <th>B</th>
                        <th>C</th>
                        <th>D</th>
                        <th>E</th>
                        <th>Kunci</th>
                        <th>Soal</th>
                        <th>Actions</th>
                    </tr>
					<?php foreach($psikotes as $p) { ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo $p['a']; ?></td>
                            <td><?php echo $p['b']; ?></td>
                            <td><?php echo $p['c']; ?></td>
                            <td><?php echo $p['d']; ?></td>
                            <td><?php echo $p['e']; ?></td>
                            <td><?php echo $p['kunci']; ?></td>
                            <td><?php echo $p['soal']; ?></td>
                            <td>
                                <a href="<?php echo site_url('psikote/edit/' . $p['id']); ?>"
                                   class="btn btn-info btn-xs"><span class="fa fa-pencil"></span> Edit</a> <a
                                        href="<?php echo site_url('psikote/remove/' . $p['id']); ?>"
                                        class="btn btn-danger btn-xs"><span class="fa fa-trash"></span> Delete</a>
                            </td>
                        </tr>
					<?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
