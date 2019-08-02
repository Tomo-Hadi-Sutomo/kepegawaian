<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Pendaftaran Edit</h3>
            </div>
			<?php echo form_open('pendaftaran/edit/' . $pendaftaran['id']); ?>
            <div class="box-body">
                <div class="row clearfix">
                    <div class="col-md-6">
                        <label for="id_user" class="control-label">Id User</label>
                        <div class="form-group">
                            <input type="text" name="id_user"
                                   value="<?php echo($this->input->post('id_user') ? $this->input->post('id_user') : $pendaftaran['id_user']); ?>"
                                   class="form-control" id="id_user"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nama" class="control-label">Nama</label>
                        <div class="form-group">
                            <input type="text" name="nama"
                                   value="<?php echo($this->input->post('nama') ? $this->input->post('nama') : $pendaftaran['nama']); ?>"
                                   class="form-control" id="nama"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="jk" class="control-label">Jk</label>
                        <div class="form-group">
                            <input type="text" name="jk"
                                   value="<?php echo($this->input->post('jk') ? $this->input->post('jk') : $pendaftaran['jk']); ?>"
                                   class="form-control" id="jk"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="agama" class="control-label">Agama</label>
                        <div class="form-group">
                            <input type="text" name="agama"
                                   value="<?php echo($this->input->post('agama') ? $this->input->post('agama') : $pendaftaran['agama']); ?>"
                                   class="form-control" id="agama"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tempat_lahir" class="control-label">Tempat Lahir</label>
                        <div class="form-group">
                            <input type="text" name="tempat_lahir"
                                   value="<?php echo($this->input->post('tempat_lahir') ? $this->input->post('tempat_lahir') : $pendaftaran['tempat_lahir']); ?>"
                                   class="form-control" id="tempat_lahir"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="control-label">Tanggal Lahir</label>
                        <div class="form-group">
                            <input type="text" name="tanggal_lahir"
                                   value="<?php echo($this->input->post('tanggal_lahir') ? $this->input->post('tanggal_lahir') : $pendaftaran['tanggal_lahir']); ?>"
                                   class="form-control" id="tanggal_lahir"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="gol_darah" class="control-label">Gol Darah</label>
                        <div class="form-group">
                            <input type="text" name="gol_darah"
                                   value="<?php echo($this->input->post('gol_darah') ? $this->input->post('gol_darah') : $pendaftaran['gol_darah']); ?>"
                                   class="form-control" id="gol_darah"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nik" class="control-label">Nik</label>
                        <div class="form-group">
                            <input type="text" name="nik"
                                   value="<?php echo($this->input->post('nik') ? $this->input->post('nik') : $pendaftaran['nik']); ?>"
                                   class="form-control" id="nik"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="pendidikan" class="control-label">Pendidikan</label>
                        <div class="form-group">
                            <input type="text" name="pendidikan"
                                   value="<?php echo($this->input->post('pendidikan') ? $this->input->post('pendidikan') : $pendaftaran['pendidikan']); ?>"
                                   class="form-control" id="pendidikan"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="jurusan" class="control-label">Jurusan</label>
                        <div class="form-group">
                            <input type="text" name="jurusan"
                                   value="<?php echo($this->input->post('jurusan') ? $this->input->post('jurusan') : $pendaftaran['jurusan']); ?>"
                                   class="form-control" id="jurusan"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="lulusan" class="control-label">Lulusan</label>
                        <div class="form-group">
                            <input type="text" name="lulusan"
                                   value="<?php echo($this->input->post('lulusan') ? $this->input->post('lulusan') : $pendaftaran['lulusan']); ?>"
                                   class="form-control" id="lulusan"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tahun_lulus" class="control-label">Tahun Lulus</label>
                        <div class="form-group">
                            <input type="text" name="tahun_lulus"
                                   value="<?php echo($this->input->post('tahun_lulus') ? $this->input->post('tahun_lulus') : $pendaftaran['tahun_lulus']); ?>"
                                   class="form-control" id="tahun_lulus"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="hp" class="control-label">Hp</label>
                        <div class="form-group">
                            <input type="text" name="hp"
                                   value="<?php echo($this->input->post('hp') ? $this->input->post('hp') : $pendaftaran['hp']); ?>"
                                   class="form-control" id="hp"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="control-label">Email</label>
                        <div class="form-group">
                            <input type="text" name="email"
                                   value="<?php echo($this->input->post('email') ? $this->input->post('email') : $pendaftaran['email']); ?>"
                                   class="form-control" id="email"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="aktif" class="control-label">Aktif</label>
                        <div class="form-group">
                            <input type="text" name="aktif"
                                   value="<?php echo($this->input->post('aktif') ? $this->input->post('aktif') : $pendaftaran['aktif']); ?>"
                                   class="form-control" id="aktif"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="foto" class="control-label">Foto</label>
                        <div class="form-group">
                            <input type="text" name="foto"
                                   value="<?php echo($this->input->post('foto') ? $this->input->post('foto') : $pendaftaran['foto']); ?>"
                                   class="form-control" id="foto"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nikt" class="control-label">Nikt</label>
                        <div class="form-group">
                            <input type="text" name="nikt"
                                   value="<?php echo($this->input->post('nikt') ? $this->input->post('nikt') : $pendaftaran['nikt']); ?>"
                                   class="form-control" id="nikt"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="control-label">Status</label>
                        <div class="form-group">
                            <input type="text" name="status"
                                   value="<?php echo($this->input->post('status') ? $this->input->post('status') : $pendaftaran['status']); ?>"
                                   class="form-control" id="status"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="alamat" class="control-label">Alamat</label>
                        <div class="form-group">
                            <textarea name="alamat" class="form-control"
                                      id="alamat"><?php echo($this->input->post('alamat') ? $this->input->post('alamat') : $pendaftaran['alamat']); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="ket" class="control-label">Ket</label>
                        <div class="form-group">
                            <textarea name="ket" class="form-control"
                                      id="ket"><?php echo($this->input->post('ket') ? $this->input->post('ket') : $pendaftaran['ket']); ?></textarea>
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