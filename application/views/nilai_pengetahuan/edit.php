<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Nilai Pengetahuan Edit</h3>
            </div>
			<?php echo form_open('nilai_pengetahuan/edit/' . $nilai_pengetahuan['id']); ?>
            <div class="box-body">
                <div class="row clearfix">
                    <div class="col-md-6">
                        <label for="id_pendaftar" class="control-label">Id Pendaftar</label>
                        <div class="form-group">
                            <input type="text" name="id_pendaftar"
                                   value="<?php echo($this->input->post('id_pendaftar') ? $this->input->post('id_pendaftar') : $nilai_pengetahuan['id_pendaftar']); ?>"
                                   class="form-control" id="id_pendaftar"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nilai" class="control-label">Nilai</label>
                        <div class="form-group">
                            <input type="text" name="nilai"
                                   value="<?php echo($this->input->post('nilai') ? $this->input->post('nilai') : $nilai_pengetahuan['nilai']); ?>"
                                   class="form-control" id="nilai"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-check"></i> Save
                </button>
            </div>
			<?php echo form_close(); ?>
        </div>
    </div>
</div>