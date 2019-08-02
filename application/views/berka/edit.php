<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Berka Edit</h3>
            </div>
			<?php echo form_open('berka/edit/' . $berka['id']); ?>
            <div class="box-body">
                <div class="row clearfix">
                    <div class="col-md-6">
                        <label for="id_pendaftar" class="control-label">Id Pendaftar</label>
                        <div class="form-group">
                            <input type="text" name="id_pendaftar"
                                   value="<?php echo($this->input->post('id_pendaftar') ? $this->input->post('id_pendaftar') : $berka['id_pendaftar']); ?>"
                                   class="form-control" id="id_pendaftar"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="foto" class="control-label">Foto</label>
                        <div class="form-group">
                            <input type="text" name="foto"
                                   value="<?php echo($this->input->post('foto') ? $this->input->post('foto') : $berka['foto']); ?>"
                                   class="form-control" id="foto"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="cv" class="control-label">Cv</label>
                        <div class="form-group">
                            <input type="text" name="cv"
                                   value="<?php echo($this->input->post('cv') ? $this->input->post('cv') : $berka['cv']); ?>"
                                   class="form-control" id="cv"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="ijazah_smu" class="control-label">Ijazah Smu</label>
                        <div class="form-group">
                            <input type="text" name="ijazah_smu"
                                   value="<?php echo($this->input->post('ijazah_smu') ? $this->input->post('ijazah_smu') : $berka['ijazah_smu']); ?>"
                                   class="form-control" id="ijazah_smu"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="ijazah_pt" class="control-label">Ijazah Pt</label>
                        <div class="form-group">
                            <input type="text" name="ijazah_pt"
                                   value="<?php echo($this->input->post('ijazah_pt') ? $this->input->post('ijazah_pt') : $berka['ijazah_pt']); ?>"
                                   class="form-control" id="ijazah_pt"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="transkrip_smu" class="control-label">Transkrip Smu</label>
                        <div class="form-group">
                            <input type="text" name="transkrip_smu"
                                   value="<?php echo($this->input->post('transkrip_smu') ? $this->input->post('transkrip_smu') : $berka['transkrip_smu']); ?>"
                                   class="form-control" id="transkrip_smu"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="transkrip_pt" class="control-label">Transkrip Pt</label>
                        <div class="form-group">
                            <input type="text" name="transkrip_pt"
                                   value="<?php echo($this->input->post('transkrip_pt') ? $this->input->post('transkrip_pt') : $berka['transkrip_pt']); ?>"
                                   class="form-control" id="transkrip_pt"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="sertifikat1" class="control-label">Sertifikat1</label>
                        <div class="form-group">
                            <input type="text" name="sertifikat1"
                                   value="<?php echo($this->input->post('sertifikat1') ? $this->input->post('sertifikat1') : $berka['sertifikat1']); ?>"
                                   class="form-control" id="sertifikat1"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="sertifikat2" class="control-label">Sertifikat2</label>
                        <div class="form-group">
                            <input type="text" name="sertifikat2"
                                   value="<?php echo($this->input->post('sertifikat2') ? $this->input->post('sertifikat2') : $berka['sertifikat2']); ?>"
                                   class="form-control" id="sertifikat2"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="sertifikat3" class="control-label">Sertifikat3</label>
                        <div class="form-group">
                            <input type="text" name="sertifikat3"
                                   value="<?php echo($this->input->post('sertifikat3') ? $this->input->post('sertifikat3') : $berka['sertifikat3']); ?>"
                                   class="form-control" id="sertifikat3"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="sertifikat4" class="control-label">Sertifikat4</label>
                        <div class="form-group">
                            <input type="text" name="sertifikat4"
                                   value="<?php echo($this->input->post('sertifikat4') ? $this->input->post('sertifikat4') : $berka['sertifikat4']); ?>"
                                   class="form-control" id="sertifikat4"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="sertifikat5" class="control-label">Sertifikat5</label>
                        <div class="form-group">
                            <input type="text" name="sertifikat5"
                                   value="<?php echo($this->input->post('sertifikat5') ? $this->input->post('sertifikat5') : $berka['sertifikat5']); ?>"
                                   class="form-control" id="sertifikat5"/>
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