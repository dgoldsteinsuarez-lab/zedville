@extends('layouts.admin')

@section('title', 'Supermarket Product Management')

@section('content')
    @push('styles')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

            :root {
                --primary-teal: #00c4b4;
                --primary-dark: #009e91;
                --accent-yellow: #ffeaa7;
                --text-dark: #2d3436;
                --text-muted: #636e72;
                --bg-body: #f8f9fa;
                --bg-sidebar: #f0fdfa;
                --border-color: #e2e8f0;
                --danger: #ff7675;
            }



            /* --- BULK UPLOAD CARD --- */
            .upload-card {
                background: white;
                border-radius: 20px;
                padding: 0;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
                border: 1px solid var(--border-color);
                overflow: hidden;
            }

            .card-header {
                padding: 20px 25px;
                border-bottom: 1px solid var(--border-color);
                background: #fcfcfc;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .action-buttons {
                display: flex;
                gap: 10px;
            }

            /* --- TABLE STYLES --- */
            .table-container {
                width: 100%;
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                min-width: 900px;
            }

            th {
                background: #f8f9fa;
                text-align: left;
                padding: 15px 20px;
                font-size: 12px;
                font-weight: 700;
                color: var(--text-muted);
                text-transform: uppercase;
                border-bottom: 1px solid var(--border-color);
            }

            td {
                padding: 10px 15px;
                border-bottom: 1px solid #f1f2f6;
                vertical-align: middle;
            }

            /* --- INPUT STYLES WITHIN TABLE --- */
            .grid-input {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #e0e0e0;
                border-radius: 6px;
                font-size: 13px;
                transition: 0.2s;
                background: #fff;
            }

            .grid-input:focus {
                border-color: var(--primary-teal);
                outline: none;
                box-shadow: 0 0 0 3px rgba(0, 196, 180, 0.1);
            }

            .grid-select {
                width: 100%;
                padding: 8px;
                border-radius: 6px;
                border: 1px solid #e0e0e0;
                background: white;
                font-size: 13px;
                cursor: pointer;
            }

            .file-upload-wrapper {
                position: relative;
                overflow: hidden;
                display: inline-block;
            }

            .file-btn {
                border: 1px dashed var(--primary-teal);
                color: var(--primary-dark);
                background: #f0fdfa;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                text-align: center;
            }

            .file-btn:hover {
                background: #e0f2f1;
            }

            /* --- BUTTONS --- */
            .btn {
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 14px;
                cursor: pointer;
                border: none;
                transition: 0.2s;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .btn-primary {
                background: var(--primary-teal);
                color: white;
                box-shadow: 0 4px 10px rgba(0, 196, 180, 0.3);
            }

            .btn-primary:hover {
                background: var(--primary-dark);
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: white;
                border: 1px solid var(--border-color);
                color: var(--text-dark);
            }

            .btn-secondary:hover {
                background: #f8f9fa;
            }

            .btn-icon {
                padding: 6px;
                border-radius: 6px;
                border: none;
                cursor: pointer;
                background: transparent;
                color: var(--text-muted);
                font-size: 16px;
            }

            .btn-icon.delete:hover {
                background: #fee2e2;
                color: var(--danger);
            }

            /* --- SUCCESS MODAL --- */
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);

                /* IMPORTANT */
                z-index: 9999;

                display: none;
                justify-content: center;
                align-items: center;
            }

            .overlay.show {
                display: flex;
                animation: fadeIn 0.3s;
            }

            .modal {
                background: white;
                padding: 30px;
                border-radius: 16px;
                text-align: center;
                max-width: 400px;
                width: 90%;
                border-top: 6px solid var(--primary-teal);

                /* IMPORTANT */
                position: relative;
                z-index: 10000;

                /* OPTIONAL but recommended */
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            /* --- AJAX LOADER --- */
            #ajaxLoader {
                position: fixed;
                inset: 0;
                background: rgba(255, 255, 255, 0.75);
                backdrop-filter: blur(2px);
                z-index: 99998;

                display: none;
                align-items: center;
                justify-content: center;
            }

            #ajaxLoader.show {
                display: flex;
            }

            .loader-box {
                background: #fff;
                padding: 30px 40px;
                border-radius: 14px;
                text-align: center;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .spinner {
                width: 42px;
                height: 42px;
                border: 4px solid #e5e7eb;
                border-top-color: var(--primary-teal);
                border-radius: 50%;
                margin: 0 auto 15px;
                animation: spin 0.8s linear infinite;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>
    @endpush
    {{-- ================= OVERLAYS (UNCHANGED) ================= --}}
    <div class="overlay" id="successOverlay">
        <div class="modal1"
            style="background:#fff;padding:30px;border-radius:16px;text-align:center;max-width:400px;width:90%;">
            <h2 style="color:var(--primary-dark)">Upload Successful!</h2>
            <p>Your supermarket products have been added to the database.</p>
            <button class="btn btn-primary" onclick="closeModal()">Return to List</button>
        </div>
    </div>

    <div class="overlay" id="ajaxLoader">
        <div class="loader-box">
            <div class="spinner"></div>
            <p>Processing…</p>
        </div>
    </div>

    <div class="adminHeading">
        <h1>Supermarket Product Management</h1>
        <p>Manage products – add, edit, delete.</p>
    </div>

    {{-- ================= PRODUCT LIST VIEW ================= --}}
    <div id="productListView">

        <div class="upload-card mab-20">
            <div class="card-header">
                <h3>Product List</h3>
                <button class="btn btn-primary" onclick="showAddProduct()">+ Add Product</button>
            </div>

            {{-- SEARCH --}}
            <div style="padding:15px;">
                <input type="text" id="searchInput" class="grid-input" placeholder="Search product…"
                    onkeyup="filterProducts()">
            </div>

            {{-- TABLE --}}
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Supermarket Name</th>
                            <th style="display:none">Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productListBody"></tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div style="padding:15px; display:flex; justify-content:flex-end; align-items:center; gap:10px;">
                <button class="btn btn-secondary" onclick="prevPage()">Prev</button>
                <span id="pageInfo" style="margin:0 10px;"></span>
                <button class="btn btn-secondary" onclick="nextPage()">Next</button>
            </div>
        </div>
    </div>

    {{-- ================= ADD PRODUCT VIEW ================= --}}
    <div id="addProductView" style="display:none;">

        <div class="upload-card">
            <div class="card-header">
                <h3>Add Products</h3>
                <button class="btn btn-secondary" onclick="showProductList()">← Back</button>
            </div>

            {{-- EXISTING ADD PRODUCT TABLE (UNCHANGED) --}}
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Supermarket Name</th>
                            <th style="display:none">Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Image</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

            <div style="padding:15px; display:flex; justify-content:flex-end; gap:10px;">
                <button class="btn btn-secondary" onclick="addEmptyRow()">+ Add Row</button>
                <button class="btn btn-primary" onclick="submitBatch()">Publish Products</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const stores = @json($stores);
        /* ================= VIEW SWITCH ================= */
        function showAddProduct() {
            document.getElementById('productListView').style.display = 'none';
            document.getElementById('addProductView').style.display = 'block';
            // ✅ CLEAR OLD ROWS (VERY IMPORTANT)
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            // ✅ ADD FRESH EMPTY ROWS
            addEmptyRow();
            addEmptyRow();
            addEmptyRow();
        }
        function showProductList() {
            document.getElementById('addProductView').style.display = 'none';
            document.getElementById('productListView').style.display = 'block';
            loadProductList();
        }

        /* ================= ADD PRODUCT (UNCHANGED LOGIC) ================= */
        let nextId = 101;
        function addEmptyRow() {
            const tr = document.createElement('tr');
            // Create options from store array
            let storeOptions = '';
            stores.forEach(store => {
                //storeOptions += `<option value="${store.id}">${store.store_name}</option>`;
                storeOptions += `<option>${store.name}</option>`;
            });
            tr.innerHTML = `

            <td><input class="grid-input"></td>
            <td><select class="grid-select">${storeOptions}</select></td>
            <td  style="display:none"><select class="grid-select"><option>Needs</option><option>Wants</option></select></td>
            <td><input type="number" class="grid-input"></td>
            <td><input type="number" class="grid-input"></td>
            <td><label class="file-btn mr-1">
                            <span class="file-text">Choose File</span>
                            <input type="file" accept="image/*" style="display: none;" onchange="updateFileLabel(this)">
                        </label></td>
            <td><button class="btn-icon delete" onclick="this.closest('tr').remove()">×</button></td>
        `;
            document.getElementById('tableBody').appendChild(tr);
        }

        /* ================= SUBMIT BATCH ================= */
        function submitBatch() {
            const rows = document.querySelectorAll('#tableBody tr');
            let fd = new FormData();

            rows.forEach((r, i) => {
                const nameInput = r.querySelector('.grid-input');
                const selects = r.querySelectorAll('select');
                const numbers = r.querySelectorAll('input[type="number"]');
                const fileInput = r.querySelector('input[type="file"]');

                if (!nameInput || !nameInput.value.trim()) return;

                const price = numbers[0] ?? { value: 0 };
                const quantity = numbers[1] ?? { value: 0 };

                fd.append(`products[${i}][product_name]`, nameInput.value);
                fd.append(`products[${i}][type]`, selects[0].value);
                fd.append(`products[${i}][category]`, selects[1].value);
                fd.append(`products[${i}][price]`, price.value || 0);
                fd.append(`products[${i}][quantity]`, quantity.value || 0);

                if (fileInput && fileInput.files.length > 0) {
                    fd.append(`products[${i}][image]`, fileInput.files[0]);
                }
            });

            showLoader();

            fetch("{{ url('admin/education/supermarket-store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: fd
            })
                .then(r => r.json())
                .then(d => {
                    hideLoader();
                    if (d.success) {
                        document.getElementById('successOverlay').classList.add('show');
                        showProductList();
                    }
                });
        }



        /* ================= LIST / SEARCH / PAGINATION ================= */
        let allProducts = [], currentPage = 1, perPage = 20;

        function loadProductList() {
            fetch("{{ url('admin/education/supermarket-list') }}")
                .then(r => r.json()).then(d => {
                    allProducts = d;
                    currentPage = 1;
                    renderProducts();
                });
        }

        function renderProducts() {
            const tbody = document.getElementById('productListBody');
            tbody.innerHTML = '';

            const start = (currentPage - 1) * perPage;
            const pageItems = allProducts.slice(start, start + perPage);

            pageItems.forEach(p => {

                // Build store options dynamically
                let storeOptions = '';
                stores.forEach(store => {
                    storeOptions += `
                    <option ${p.type === store.name ? 'selected' : ''}>
                        ${store.name}
                    </option>`;
                });

                tbody.innerHTML += `
            <tr data-id="${p.id}">
                <td>${p.id}</td>

                <td>
                    <input class="grid-input" value="${p.product_name}" disabled>
                </td>

                <td>
                    <select class="grid-select" disabled>
                        ${storeOptions}
                    </select>
                </td>

                <td style="display:none">
                    <select class="grid-select" disabled>
                        <option ${p.category == 'Needs' ? 'selected' : ''}>Needs</option>
                        <option ${p.category == 'Wants' ? 'selected' : ''}>Wants</option>
                    </select>
                </td>

                <td>
                    <input  class="grid-input" value="${parseFloat(p.price).toFixed(2)}" disabled>
                </td>
                <td>
                    <input  class="grid-input" value="${p.quantity}" disabled>
                </td>

                <td>
                    ${p.image ? `
                        <img src="{{ url('public/uploads/supermarket') }}/${p.image}" width="40">
                    ` : ''}
                    <label class="file-btn mr-1">
                        <span class="file-text">Choose File</span>
                        <input type="file" accept="image/*" style="display:none" onchange="updateFileLabel(this)">
                    </label>
                </td>

                <td>
                    <button class="btn-icon" onclick="editRow(this)">
                     <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                                                        <path d="M16 4.99994L19 7.99994" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M4.50001 16.4994L18.4999 2.49994L21.5004 5.49994L7.50041 19.4999L3 20.9995L4.50001 16.4994Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M5 16.9999L7 18.9999" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                    </button>
                    <button class="btn-icon delete" onclick="deleteProduct(this)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                                                        <path d="M4 5.5H20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M9 2.5H15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M6 8.5H18V20C18 20.8285 17.3285 21.5 16.5 21.5H7.5C6.67155 21.5 6 20.8285 6 20V8.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                                    </svg>
                    </button>
                </td>
            </tr>`;
            });

            document.getElementById('pageInfo').innerText =
                `Page ${currentPage} of ${Math.ceil(allProducts.length / perPage)}`;
        }


        function nextPage() { if (currentPage * perPage < allProducts.length) { currentPage++; renderProducts(); } }
        function prevPage() { if (currentPage > 1) { currentPage--; renderProducts(); } }

        function filterProducts() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            allProducts = allProducts.filter(p => p.product_name.toLowerCase().includes(q));
            currentPage = 1;
            renderProducts();
        }

        /* ================= EDIT / DELETE ================= */
        function editRow(btn) {
            const r = btn.closest('tr');
            r.querySelectorAll('input,select').forEach(e => e.disabled = false);
            btn.innerHTML = '<svg width="24" height="24" viewBox="0 0 640 512" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 237.3C496 233.1 494.3 229 491.3 226L416 150.6L416 240C416 257.7 401.7 272 384 272L224 272C206.3 272 192 257.7 192 240L192 144L160 144zM240 144L240 224L368 224L368 144L240 144zM96 160C96 124.7 124.7 96 160 96L402.7 96C419.7 96 436 102.7 448 114.7L525.3 192C537.3 204 544 220.3 544 237.3L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM256 384C256 348.7 284.7 320 320 320C355.3 320 384 348.7 384 384C384 419.3 355.3 448 320 448C284.7 448 256 419.3 256 384z" fill="currentColor"/></svg>';
            btn.onclick = () => saveRow(r);
        }
        function saveRow(r) {
            const id = r.dataset.id;

            const inputs = r.querySelectorAll('.grid-input');
            const selects = r.querySelectorAll('select');
            const fileInput = r.querySelector('input[type="file"]');

            const nameInput = inputs[0]; // product name
            const priceInput = inputs[1]; // price (20.00)
            const quantityInput = inputs[2]; // price (20.00)

            let fd = new FormData();
            fd.append('product_name', nameInput.value);
            fd.append('type', selects[0].value);
            fd.append('category', selects[1].value);
            fd.append('price', priceInput.value);
            fd.append('quantity', quantityInput.value);

            if (fileInput && fileInput.files.length > 0) {
                fd.append('image', fileInput.files[0]);
            }

            showLoader();

            fetch(`/admin/education/supermarket-update/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: fd
            })
                .then(() => {
                    hideLoader();
                    loadProductList();
                });
        }



    /*function deleteProduct(btn){
        if(!confirm('Delete product?')) return;
        const r=btn.closest('tr');
        showLoader();
        fetch(`/admin/education/supermarket-delete/${r.dataset.id}`,{
            method:'DELETE',
            headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
        }).then(() => { hideLoader(); r.remove(); });
    }*/
        function deleteProduct(btn) {

            const r = btn.closest('tr');
            const randomNumber = Math.floor(1000 + Math.random() * 9000);

            Swal.fire({
                title: 'Delete Product?',
                html: `
                <p>This action cannot be undone.</p>
                <p>Please enter the following number to confirm deletion:</p>

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

                    const enteredCode =
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

                    showLoader();

                    fetch(`/admin/education/supermarket-delete/${r.dataset.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(() => {
                            hideLoader();
                            r.remove();

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Product deleted successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        })
                        .catch(() => {
                            hideLoader();

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to delete product.'
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

        /* ================= HELPERS ================= */
        function showLoader() { document.getElementById('ajaxLoader').classList.add('show'); }
        function hideLoader() { document.getElementById('ajaxLoader').classList.remove('show'); }
        function closeModal() { document.getElementById('successOverlay').classList.remove('show'); }

        /* INIT */
        addEmptyRow(); addEmptyRow(); addEmptyRow();
        showProductList();
    </script>
@endpush