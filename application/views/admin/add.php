<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Admin Add</h3>
            </div>
			<?php echo form_open('admin/add'); ?>
            <div class="box-body">
                <div class="row clearfix">
                    <div class="col-md-6">
                        <label for="id_user" class="control-label">Id User</label>
                        <div class="form-group">
                            <input type="text" name="id_user" value="<?php echo $this->input->post('id_user'); ?>"
                                   class="form-control" id="id_user"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nama" class="control-label">Nama</label>
                        <div class="form-group">
                            <input type="text" name="nama" value="<?php echo $this->input->post('nama'); ?>"
                                   class="form-control" id="nama"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="jk" class="control-label">Jk</label>
                        <div class="form-group">
                            <input type="text" name="jk" value="<?php echo $this->input->post('jk'); ?>"
                                   class="form-control" id="jk"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="agama" class="control-label">Agama</label>
                        <div class="form-group">
                            <input type="text" name="agama" value="<?php echo $this->input->post('agama'); ?>"
                                   class="form-control" id="agama"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="ttl" class="control-label">Ttl</label>
                        <div class="form-group">
                            <input type="text" name="ttl" value="<?php echo $this->input->post('ttl'); ?>"
                                   class="form-control" id="ttl"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="jabatan" class="control-label">Jabatan</label>
                        <div class="form-group">
                            <input type="text" name="jabatan" value="<?php echo $this->input->post('jabatan'); ?>"
                                   class="form-control" id="jabatan"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="gol_darah" class="control-label">Gol Darah</label>
                        <div class="form-group">
                            <input type="text" name="gol_darah" value="<?php echo $this->input->post('gol_darah'); ?>"
                                   class="form-control" id="gol_darah"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="hp" class="control-label">Hp</label>
                        <div class="form-group">
                            <input type="text" name="hp" value="<?php echo $this->input->post('hp'); ?>"
                                   class="form-control" id="hp"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="control-label">Email</label>
                        <div class="form-group">
                            <input type="text" name="email" value="<?php echo $this->input->post('email'); ?>"
                                   class="form-control" id="email"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="foto" class="control-label">Foto</label>
                        <div class="form-group">
                            <input type="text" name="foto" value="<?php echo $this->input->post('foto'); ?>"
                                   class="form-control" id="foto"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="alamat" class="control-label">Alamat</label>
                        <div class="form-group">
                            <textarea name="alamat" class="form-control"
                                      id="alamat"><?php echo $this->input->post('alamat'); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="ket" class="control-label">Ket</label>
                        <div class="form-group">
                            <textarea name="ket" class="form-control"
                                      id="ket"><?php echo $this->input->post('ket'); ?></textarea>
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