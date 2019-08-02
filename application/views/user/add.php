<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">User Add</h3>
            </div>
			<?php echo form_open('user/add'); ?>
            <div class="box-body">
                <div class="row clearfix">
                    <div class="col-md-6">
                        <label for="password" class="control-label">Password</label>
                        <div class="form-group">
                            <input type="password" name="password" value="<?php echo $this->input->post('password'); ?>"
                                   class="form-control" id="password"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="username" class="control-label">Username</label>
                        <div class="form-group">
                            <input type="text" name="username" value="<?php echo $this->input->post('username'); ?>"
                                   class="form-control" id="username"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="level" class="control-label">Level</label>
                        <div class="form-group">
                            <input type="text" name="level" value="<?php echo $this->input->post('level'); ?>"
                                   class="form-control" id="level"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="aktif" class="control-label">Aktif</label>
                        <div class="form-group">
                            <input type="text" name="aktif" value="<?php echo $this->input->post('aktif'); ?>"
                                   class="form-control" id="aktif"/>
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