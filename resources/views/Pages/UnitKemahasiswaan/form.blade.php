    <div class="fixed-plugin">
        <div class="card shadow-lg ">
            <div class="card-header pb-0 pt-3 ">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Tambah Data</h5>
                    <p>Unit Kemahasiswaan</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close">x</i>
                    </button>
                </div>
                <!-- End Toggle Button -->
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0">
                <form action="">
                    <label>Unit Kemahasiswaan</label>
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Nama Unit Kemahasiswaan" name="name"
                            id="name">
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <label>No Hp</label>
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Nama Unit Kemahasiswaan" name="no_hp"
                            id="no_hp">
                        <div class="invalid-feedback" id="no_hpError"></div>
                    </div>
                    <label>Image</label>
                    <div class="mb-3">
                        <input type="file" class="form-control" placeholder="Nama Unit Kemahasiswaan" name="image"
                            id="image">
                        <div class="invalid-feedback" id="imageError"></div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status" name="status" checked="">
                        <label class="form-check-label" >Status</label>
                    </div>
                    <div class="d-flex justify-content-end">
                    @include('Component.button',[
                            'class' => 'bg-gradient-info mt-4 mb-0',
                            'label' => 'Tambah',
                            'id' => 'btn-tambah',
                        ])
                    </div>
                       
                </form>
            </div>
        </div>
    </div>
