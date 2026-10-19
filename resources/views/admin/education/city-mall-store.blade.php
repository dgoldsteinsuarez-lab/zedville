@extends('layouts.admin')

@section('title', 'City Mall Store')

@section('content')

    @push('styles')
        <style>
            :root {
                --primary-teal: #00c4b4;
                --primary-dark: #009e91;
                --border: #e5e7eb;
                --danger: #ff7675;
            }

            .upload-card {
                background: #fff;
                border-radius: 16px;
                border: 1px solid var(--border);
                box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
                overflow: hidden;
            }

            .card-header {
                padding: 16px 20px;
                border-bottom: 1px solid var(--border);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                min-width: 700px;
            }

            th,
            td {
                padding: 12px;
                border-bottom: 1px solid #f1f1f1;
            }

            th {
                background: #f9fafb;
                font-size: 12px;
                text-transform: uppercase;
            }

            .grid-input {
                width: 100%;
                padding: 8px 10px;
                border: 1px solid var(--border);
                border-radius: 6px;
            }

            .file-btn {
                display: inline-block;
                padding: 6px 10px;
                border: 1px dashed var(--primary-teal);
                border-radius: 6px;
                cursor: pointer;
                font-size: 12px;
                color: var(--primary-dark);
            }

            .btn {
                padding: 8px 16px;
                border-radius: 8px;
                border: none;
                cursor: pointer;
                font-weight: 600;
            }

            .btn-primary {
                background: var(--primary-teal);
                color: #fff;
            }

            .btn-secondary {
                background: #fff;
                border: 1px solid var(--border);
                color: #333;
            }

            .btn-icon {
                /*background:none;
            border:none;
            cursor:pointer;
            font-size:16px;*/
                /* padding: 6px; */
                border-radius: 6px;
                border: none;
                cursor: pointer;
                background: transparent;
                color: var(--text-muted);
                font-size: 16px;
            }

            /*.btn-icon.delete{color:var(--danger);}*/

            #ajaxLoader {
                position: fixed;
                inset: 0;
                background: rgba(255, 255, 255, .7);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 9999;
            }

            #ajaxLoader.show {
                display: flex;
            }
        </style>
    @endpush

    {{-- LOADER --}}
    <div id="ajaxLoader">
        <strong>Processing…</strong>
    </div>

    {{-- ================= LIST VIEW ================= --}}
    <div id="productListView">

        <div class="upload-card">
            <div class="card-header">
                <h3>Store List</h3>
                <button class="themeBtnGray theme-text-dark" onclick="showAdd()">+ Add Store</button>
            </div>

            {{-- SEARCH --}}
            <div style="padding:12px 20px;">
                <input type="text" id="searchInput" class="grid-input" placeholder="Search store..."
                    onkeyup="applySearch()">
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Store Name</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="storeList"></tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div style="padding:15px; display:flex; justify-content:flex-end; gap:10px; align-items:center;">
                <button class="btn btn-secondary" onclick="prevPage()">Prev</button>
                <span id="pageInfo"></span>
                <button class="btn btn-secondary" onclick="nextPage()">Next</button>
            </div>
        </div>
    </div>

    {{-- ================= ADD VIEW ================= --}}
    <div id="addView" style="display:none">

        <div class="upload-card">
            <div class="card-header">
                <h3>Add Stores</h3>
                <button class="btn btn-secondary" onclick="showList()">← Back</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Store Name</th>
                            <th>Image</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="addTable"></tbody>
                </table>
            </div>

            <div style="padding:15px; text-align:right">
                <button class="themeBtnGray theme-text-dark" onclick="addRow()">+ Add Row</button>
                <button class="secondaryBtn" onclick="submitStores()">Save</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        /* VIEW SWITCH */
        function showAdd() {
            productListView.style.display = 'none';
            addView.style.display = 'block';
        }
        function showList() {
            addView.style.display = 'none';
            productListView.style.display = 'block';
            loadStores();
        }

        /* ADD ROW */
        function addRow() {
            addTable.insertAdjacentHTML('beforeend', `
            <tr>
                <td><input class="grid-input" type="text"></td>
                <td>
                    <label class="file-btn">
                        <span class="file-text">Choose Image</span>
                        <input type="file" accept="image/*" hidden onchange="updateFileLabel(this)">
                    </label>
                </td>
                <td>
                    <button class="btn-icon delete" onclick="this.closest('tr').remove()">✖</button>
                </td>
            </tr>
        `);
        }


        /* SUBMIT */
        function submitStores() {
            let fd = new FormData();

            document.querySelectorAll('#addTable tr').forEach((tr, i) => {
                const nameInput = tr.querySelector('.grid-input');
                const fileInput = tr.querySelector('input[type="file"]');

                if (!nameInput || !nameInput.value.trim()) return;

                fd.append(`stores[${i}][store_name]`, nameInput.value.trim());

                if (fileInput && fileInput.files.length > 0) {
                    fd.append(`stores[${i}][store_image]`, fileInput.files[0]);
                }
            });

            loader(true);

            fetch("{{ url('admin/education/create-city-mall-store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: fd
            })
                .then(res => res.json())
                .then(() => {
                    loader(false);
                    showList();
                })
                .catch(() => loader(false));
        }


        /* DATA */
        let allStores = [], filteredStores = [], currentPage = 1, perPage = 10;

        /* LOAD LIST */
        function loadStores() {
            fetch("{{ url('admin/education/store-list') }}")
                .then(r => r.json())
                .then(d => {
                    allStores = d;
                    filteredStores = d;
                    currentPage = 1;
                    renderStores();
                });
        }

        /* RENDER */
        function renderStores() {
            storeList.innerHTML = '';
            const start = (currentPage - 1) * perPage;
            const pageItems = filteredStores.slice(start, start + perPage);

            pageItems.forEach(s => {
                storeList.insertAdjacentHTML('beforeend', `
                <tr data-id="${s.id}">
                    <td>${s.id}</td>
                    <td><input class="grid-input" value="${s.store_name}" disabled></td>
                    <td>${s.store_image ? `<img class='mr-2' src="{{ url('public/uploads/stores') }}/${s.store_image}" width="40">` : ''}
                    <label class="file-btn">
                        <span class="file-text">Choose Image</span>
                        <input type="file" accept="image/*" hidden onchange="updateFileLabel(this)">
                    </label></td>
                    <td>
                        <button class="btn-icon" onclick="editRow(this)">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                                                        <path d="M16 4.99994L19 7.99994" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M4.50001 16.4994L18.4999 2.49994L21.5004 5.49994L7.50041 19.4999L3 20.9995L4.50001 16.4994Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M5 16.9999L7 18.9999" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                    </button>
                        <button class="btn-icon delete" onclick="deleteRow(this)">
                         <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                                                        <path d="M4 5.5H20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M9 2.5H15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6 8.5H18V20C18 20.8285 17.3285 21.5 16.5 21.5H7.5C6.67155 21.5 6 20.8285 6 20V8.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                                    </svg>
                        </button>
                    </td>
                </tr>
            `);
            });

            pageInfo.innerText = `Page ${currentPage} of ${Math.ceil(filteredStores.length / perPage) || 1}`;
        }

        /* PAGINATION */
        function nextPage() { if (currentPage * perPage < filteredStores.length) { currentPage++; renderStores(); } }
        function prevPage() { if (currentPage > 1) { currentPage--; renderStores(); } }

        /* SEARCH */
        function applySearch() {
            const q = searchInput.value.toLowerCase();
            filteredStores = allStores.filter(s => s.store_name.toLowerCase().includes(q));
            currentPage = 1;
            renderStores();
        }

        /* EDIT */
        function editRow(btn) {
            let tr = btn.closest('tr');
            let input = tr.querySelector('input');
            input.disabled = false;
            btn.innerHTML = '<svg width="24" height="24" viewBox="0 0 640 512" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 237.3C496 233.1 494.3 229 491.3 226L416 150.6L416 240C416 257.7 401.7 272 384 272L224 272C206.3 272 192 257.7 192 240L192 144L160 144zM240 144L240 224L368 224L368 144L240 144zM96 160C96 124.7 124.7 96 160 96L402.7 96C419.7 96 436 102.7 448 114.7L525.3 192C537.3 204 544 220.3 544 237.3L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM256 384C256 348.7 284.7 320 320 320C355.3 320 384 348.7 384 384C384 419.3 355.3 448 320 448C284.7 448 256 419.3 256 384z" fill="currentColor"/></svg>';
            btn.onclick = () => saveRow(tr);
        }
        function saveRow(tr) {
            let fd = new FormData();

            const nameInput = tr.querySelector('.grid-input');
            const fileInput = tr.querySelector('input[type="file"]');

            fd.append('store_name', nameInput.value);

            if (fileInput && fileInput.files.length > 0) {
                fd.append('store_image', fileInput.files[0]);
            }

            loader(true);

            fetch(`/admin/education/store-update/${tr.dataset.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: fd
            })
                .then(() => {
                    loader(false);
                    loadStores();
                });
        }


    /* DELETE */
    /*function deleteRow(btn){
        if(!confirm('Delete store?'))return;
        let tr=btn.closest('tr');
        loader(true);
        fetch(`/admin/education/store-delete/${tr.dataset.id}`,{
            method:'DELETE',
            headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
        }).then(() => { loader(false); tr.remove(); });
    }*/
        function deleteRow(btn) {

            let tr = btn.closest('tr');
            let randomNumber = Math.floor(1000 + Math.random() * 9000);

            Swal.fire({
                title: 'Delete Store?',
                html: `
                <p>This action cannot be undone.</p>
                <p>Please enter the following number to confirm:</p>

                <h2 style="color:#dc3545;font-weight:bold;">
                    ${randomNumber}
                </h2>

                <input type="text"
                       id="confirmCode"
                       class="swal2-input"
                       placeholder="Enter the number">
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',

                preConfirm: () => {

                    let enteredCode =
                        document.getElementById('confirmCode').value;

                    if (enteredCode != randomNumber) {
                        Swal.showValidationMessage(
                            'Confirmation number does not match'
                        );
                        return false;
                    }

                    return true;
                }

            }).then((result) => {

                if (result.isConfirmed) {

                    loader(true);

                    fetch(`/admin/education/store-delete/${tr.dataset.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(() => {
                            loader(false);
                            tr.remove();

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Store deleted successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        });
                }
            });
        }
        function updateFileLabel(input) {
            const label = input.closest('.file-btn');
            const span = label.querySelector('.file-text');

            if (input.files && input.files[0]) {
                let name = input.files[0].name;
                if (name.length > 14) name = name.substring(0, 12) + '..';
                span.textContent = name;
                label.style.background = "#d1fae5";
                label.style.borderColor = "#10b981";
            }
        }

        function loader(s) { ajaxLoader.classList.toggle('show', s); }

        /* INIT */
        addRow(); addRow();
        showList();
    </script>
@endpush