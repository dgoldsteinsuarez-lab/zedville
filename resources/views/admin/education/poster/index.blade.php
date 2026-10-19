@extends('layouts.admin')

@section('title', 'Room Poster')

@section('content')
<style>
    .upload-box:hover {
    background: #f8f8f8;
    border-color: #999 !important;
}

.image-upload-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.image-upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    cursor: pointer;
}

.image-upload-overlay i {
    font-size: 2.5rem;
    color: white;
    margin-bottom: 0.5rem;
}

.image-upload-overlay p {
    color: white;
    margin: 0;
    font-size: 0.9rem;
}

</style>
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Room Poster</h1>
</div>
<div class="row">
    <div class="themeCard card card-custom card-stretch gutter-b mab-15">
        <div class="card-body pall-30">
            <div class="tableHeadAdvance themeForm">
                <div class="dataTableAdvan">
                    <div class="tableHeadAdvance mab-15">
                        <div class="row gap-2 justify-content-between">
                            <div class="form-group gap-2 d-flex justify-content-between">
                                <div class="rightpanel d-flex gap-2  ms-auto">
                                    @if($posters->count() >= 13)
                                        <button class="themeBtnGray theme-text-dark" disabled 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            data-bs-title="13 poster upload done" 
                                            style="opacity: 0.6; cursor: not-allowed;">
                                            Add Poster
                                        </button>
                                    @else
                                        <button class="themeBtnGray theme-text-dark" data-bs-toggle="modal"
                                            data-bs-target="#addmodel">Add Poster</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="themeTable">
                <div class="table-responsive">
                    @if($posters->isEmpty())
                        <p>No Poster</p>
                    @else
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th class="rightText">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $i=0;
                            @endphp
                            @foreach ($posters as $list)
                            @php
                            $i++;
                            @endphp
                            <tr>
                                <td valign="middle"><a class="theme-text-dark">{{$i}}</a></td>
                                <td valign="middle">{{ $list->poster_name}}</td>
                                <td valign="middle"><a href="{{ asset('public/uploads/room_poster/' . $list->poster_image) }}" target="_blank">
                                <img src="{{ asset('public/uploads/room_poster/' . $list->poster_image) }}" 
                                    alt="Poster" 
                                    width="80" 
                                    style="border-radius: 6px;">
                                    </a>
                                </td>
                                <td valign="middle">
                                    <div class="actionBtns d-flex align-items-center justify-content-end">
                                        <a href="#" class="tableActionBtn me-3 edit-role-btn" data-bs-toggle="modal" data-bs-target="#editmodel" data-id="{{ $list->id }}" data-poster_name="{{ $list->poster_name }}" data-poster_image="{{ $list->poster_image }}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                                                <path d="M16 4.99994L19 7.99994" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M4.50001 16.4994L18.4999 2.49994L21.5004 5.49994L7.50041 19.4999L3 20.9995L4.50001 16.4994Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M5 16.9999L7 18.9999" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </a>    
                                        
                                        <a href="#" class="deleteBtn" data-bs-toggle="modal" data-bs-target="#confirmModal" data-url="{{ url('admin/education/deletePoster', $list->id) }}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                                                <path d="M4 5.5H20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M9 2.5H15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M6 8.5H18V20C18 20.8285 17.3285 21.5 16.5 21.5H7.5C6.67155 21.5 6 20.8285 6 20V8.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add Poster -->
<section class="themeModal">
    <form action="{{ url('admin/education/storePoster') }}" method="post" enctype="multipart/form-data">
         @csrf
        <div class="modal fade" id="addmodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-460px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Add Poster</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pall-30">
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Name</label>
                                <input class="form-control" type="text" name="poster_name"  placeholder="Please Poster Name">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="image" class="form-label">Poster Image</label>

                                <div class="upload-box text-center p-4 border rounded-3" style="border: 2px dashed #d0d0d0; cursor: pointer;">
                                    <img id="previewImage" src="" alt="" style="display:none; max-width: 100%; border-radius: 8px;">
                                    <div id="placeholderText" class="text-muted">
                                        <i class="bi bi-upload fs-1"></i>
                                        <p class="mt-2 mb-0">Click to upload image</p>
                                    </div>
                                </div>

                                <input type="file" name="poster_image" id="imageUpload" accept="image/*" style="display:none;">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer text-center justify-content-center">
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="themeBtn text-white">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
<!-- Edit Poster -->
 <section class="themeModal">
    <form id="editRoleForm" method="post" enctype="multipart/form-data">
         @csrf
          @method('PUT')
        <div class="modal fade" id="editmodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-460px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Edit Poster</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pall-30">
                        <input type="hidden" name="poster_id" id="poster_id" />
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Name</label>
                                <input class="form-control" type="text" name="poster_name" id="poster_name"  placeholder="Please Enter Poster Name">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label class="form-label">Poster Image</label>

                                 <div class="upload-box-edit text-center p-4 border rounded-3" 
                                    style="border: 2px dashed #d0d0d0; cursor: pointer;">
                                    
                                   
                                    @if(!empty($poster->image))
                                        <div class="image-upload-wrapper">
                                            <img id="editPreviewImage" src="{{ asset('uploads/poster/' . $poster->image) }}" 
                                                alt="" style="max-width: 100%; border-radius: 8px;">
                                            <div class="image-upload-overlay">
                                                <i class="bi bi-upload"></i>
                                                <p>Click to change image</p>
                                            </div>
                                        </div>
                                        <div id="editPlaceholderText" class="text-muted" style="display:none;">
                                            <i class="bi bi-upload fs-1"></i>
                                            <p class="mt-2 mb-0">Click to upload image</p>
                                        </div>
                                    @else
                                        <div class="image-upload-wrapper" style="display:none;">
                                            <img id="editPreviewImage" src="" alt="" 
                                                style="max-width: 100%; border-radius: 8px;">
                                            <div class="image-upload-overlay">
                                                <i class="bi bi-upload"></i>
                                                <p>Click to change image</p>
                                            </div>
                                        </div>
                                        <div id="editPlaceholderText" class="text-muted">
                                            <i class="bi bi-upload fs-1"></i>
                                            <p class="mt-2 mb-0">Click to upload image</p>
                                        </div>
                                    @endif
                                </div>

                                <input type="file" name="poster_image" id="editImageUpload" accept="image/*" style="display:none;">
                            </div>
                        </div>

                    </div>
                    
                    <div class="modal-footer text-center justify-content-center">
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="themeBtn text-white">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
 <!-- Delete Grade / Class -->
  <section class="themeModal">
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-350px">
            <form id="modalDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="delete_code" name="delete_code">
                <div class="modal-content">
                    <div class="modal-body text-center pall-60">
                        <h4 class="fs-18 fw-700 mab-20">Are you sure?</h4>
                        <p>You won't be able to revert this!</p>
                        <div class="alert alert-warning">
                            Please enter this number to confirm deletion:
                            <h3 id="randomNumber" class="mt-2"></h3>
                        </div>

                        <div class="mb-3">
                            <input type="text"
                                name="confirm_code"
                                class="form-control"
                                placeholder="Enter the number shown above"
                                required>
                        </div>
                        <div class="text-center mat-40">
                            
                            <button type="submit" class="themeBtn text-white">Yes Delete it!</button>
                            <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
// Initialize Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>

<script>
document.querySelector('.upload-box').addEventListener('click', function () {
    document.getElementById('imageUpload').click();
});

document.getElementById('imageUpload').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            document.getElementById('previewImage').style.display = 'block';
            document.getElementById('previewImage').src = event.target.result;
            document.getElementById('placeholderText').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>
<!-- For Edit Modal -->
 <script>
document.querySelector('.upload-box-edit').addEventListener('click', function () {
    document.getElementById('editImageUpload').click();
});

document.getElementById('editImageUpload').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            const wrapper = document.querySelector('.image-upload-wrapper');
            const previewImage = document.getElementById('editPreviewImage');
            const placeholderText = document.getElementById('editPlaceholderText');
            
            wrapper.style.display = 'inline-block';
            previewImage.src = event.target.result;
            placeholderText.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>
<!-- Edit Poster Script -->
 <script>
$(document).on("click", ".edit-role-btn", function () {

    let id = $(this).data('id');
    let name = $(this).data('poster_name');
    let image = $(this).data('poster_image');

    // Fill fields
    $("#poster_id").val(id);
    $("#poster_name").val(name);

    // Correct FULL image path
    let imagePath = "{{ url('public/uploads/room_poster') }}/" + image;

    const wrapper = $('.image-upload-wrapper');
    const previewImage = $('#editPreviewImage');
    const placeholderText = $('#editPlaceholderText');

    if (image && image !== "") {
        wrapper.show();
        previewImage.attr("src", imagePath);
        placeholderText.hide();
    } else {
        wrapper.hide();
        placeholderText.show();
    }

    // Correct form action using FULL BASE URL
    $("#editRoleForm").attr("action", "{{ url('admin/education/updatePoster') }}/" + id);
});

//Delete security
    $('.deleteBtn').on('click', function() {
    let randomNumber = Math.floor(1000 + Math.random() * 9000);

    $('#randomNumber').text(randomNumber);
    $('#delete_code').val(randomNumber);
});
</script>

@endpush