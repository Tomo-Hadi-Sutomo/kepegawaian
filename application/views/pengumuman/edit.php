<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Pengumuman Edit</h3>
            </div>
			<?php echo form_open('pengumuman/edit/' . $pengumuman['id']); ?>
            <div class="box-body">
                <div class="row clearfix">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="checkbox" name="diterima"
                                   value="1" <?php echo($pengumuman['diterima'] == 1 ? 'checked="checked"' : ''); ?>
                                   id='diterima'/> <label for="diterima" class="control-label">Diterima</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="checkbox" name="tampil"
                                   value="1" <?php echo($pengumuman['tampil'] == 1 ? 'checked="checked"' : ''); ?>
                                   id='tampil'/> <label for="tampil" class="control-label">Tampil</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="id_pendaftar" class="control-label">Id Pendaftar</label>
                        <div class="form-group">
                            <input type="text" name="id_pendaftar"
                                   value="<?php echo($this->input->post('id_pendaftar') ? $this->input->post('id_pendaftar') : $pengumuman['id_pendaftar']); ?>"
                                   class="form-control" id="id_pendaftar"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nama" class="control-label">Nama</label>
                        <div class="form-group">
                            <input type="text" name="nama"
                                   value="<?php echo($this->input->post('nama') ? $this->input->post('nama') : $pengumuman['nama']); ?>"
                                   class="form-control" id="nama"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nilai_berkas" class="control-label">Nilai Berkas</label>
                        <div class="form-group">
                            <input type="text" name="nilai_berkas"
                                   value="<?php echo($this->input->post('nilai_berkas') ? $this->input->post('nilai_berkas') : $pengumuman['nilai_berkas']); ?>"
                                   class="form-control" id="nilai_berkas"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nilai_pengetahuan" class="control-label">Nilai Pengetahuan</label>
                        <div class="form-group">
                            <input type="text" name="nilai_pengetahuan"
                                   value="<?php echo($this->input->post('nilai_pengetahuan') ? $this->input->post('nilai_pengetahuan') : $pengumuman['nilai_pengetahuan']); ?>"
                                   class="form-control" id="nilai_pengetahuan"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nilai_psikotes" class="control-label">Nilai Psikotes</label>
                        <div class="form-group">
                            <input type="text" name="nilai_psikotes"
                                   value="<?php echo($this->input->post('nilai_psikotes') ? $this->input->post('nilai_psikotes') : $pengumuman['nilai_psikotes']); ?>"
                                   class="form-control" id="nilai_psikotes"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="alamat" class="control-label">Alamat</label>
                        <div class="form-group">
                            <textarea name="alamat" class="form-control"
                                      id="alamat"><?php echo($this->input->post('alamat') ? $this->input->post('alamat') : $pengumuman['alamat']); ?></textarea>
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