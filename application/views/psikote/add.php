<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Psikote Add</h3>
            </div>
			<?php echo form_open('psikote/add'); ?>
            <div class="box-body">
                <div class="row clearfix">
                    <div class="col-md-6">
                        <label for="a" class="control-label">A</label>
                        <div class="form-group">
                            <input type="text" name="a" value="<?php echo $this->input->post('a'); ?>"
                                   class="form-control" id="a"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="b" class="control-label">B</label>
                        <div class="form-group">
                            <input type="text" name="b" value="<?php echo $this->input->post('b'); ?>"
                                   class="form-control" id="b"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="c" class="control-label">C</label>
                        <div class="form-group">
                            <input type="text" name="c" value="<?php echo $this->input->post('c'); ?>"
                                   class="form-control" id="c"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="d" class="control-label">D</label>
                        <div class="form-group">
                            <input type="text" name="d" value="<?php echo $this->input->post('d'); ?>"
                                   class="form-control" id="d"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="e" class="control-label">E</label>
                        <div class="form-group">
                            <input type="text" name="e" value="<?php echo $this->input->post('e'); ?>"
                                   class="form-control" id="e"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="kunci" class="control-label">Kunci</label>
                        <div class="form-group">
                            <input type="text" name="kunci" value="<?php echo $this->input->post('kunci'); ?>"
                                   class="form-control" id="kunci"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="soal" class="control-label">Soal</label>
                        <div class="form-group">
                            <textarea name="soal" class="form-control"
                                      id="soal"><?php echo $this->input->post('soal'); ?></textarea>
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