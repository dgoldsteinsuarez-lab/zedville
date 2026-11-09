@extends('layouts.profile')

@section('title', 'Mailbox')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('mailbox.title') }}</h1>
</div>
<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="themeTabspills">
        <div class="w-full">
            <!-- Tabs Header -->
            <div class="flex menus overborderleftright border-b border-[#D2DDDB]">
                <button class="tabitems tab-button inactive" data-tab="tab1">{{ __('mailbox.primary') }} @if($primaryMailboxcount > 0)<span class="numbers" id="primaryUnreadBadge">{{$primaryMailboxcount}}</span>@endif</button>
                <button class="tabitems tab-button inactive" data-tab="tab2">{{ __('mailbox.starred') }}</button>
                <button class="tabitems tab-button inactive" data-tab="tab3">{{ __('mailbox.deleted') }}</button>
            </div>
            <!-- Tabs Content -->
            <div class="tailCard w-full mt-4">
                @foreach (['primary' => $primaryMailbox, 'starred' => $starredMailbox, 'deleted' => $deletedMailbox] as $tabName => $mailboxList)
                @php
                    $tabId = $tabName === 'primary' ? 'tab1' : ($tabName === 'starred' ? 'tab2' : 'tab3');
                    $mailContentId = $tabName . '-mail-content';
                    $firstMailVar = 'first' . ucfirst($tabName) . 'Mail';
                    $firstMail = $$firstMailVar;
                    $partialName = $tabName === 'primary' ? 'mail' : ($tabName === 'starred' ? 'startedmail' : 'deletededmail');
                @endphp
                <div id="{{ $tabId }}" class="tab-content hidden">
                    <div class="flex flex-col">
                        <div class="flex-1 flex">
                            <main class="flex gap-8 flex-1 mailboxMain">
                                <div class="sticky top-0 flex-grow max-w-xs w-full flex flex-col h-[calc(100vh-180px)]">
                                    <div class="overflow-y-auto mailboxLists">
                                        @foreach ($mailboxList as $mail)
                                        @php 
                                            $shortId = base64_encode($mail->id);
                                            $isFirst = $loop->first;
                                            $isUnread = !$isFirst && $mail->read == 0;
                                        @endphp

                                        <div
                                            data-mail-id="{{ $shortId }}"
                                            class="mailItems mail-item pointer
                                                {{ $isFirst ? 'active' : '' }}
                                                {{ $mail->read == 0 ? 'unread' : 'read' }}"
                                            onclick="selectMail('{{ $shortId }}', '{{ $tabName }}')"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">{{ $mail->subject }}</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">{{ Str::limit(strip_tags($mail->content), 100) }}</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">{{ $mail->created_at->isoFormat('LT') }}</span>
                                                    <span class="date block">{{ $mail->created_at->isoFormat('D MMM') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    </div>
                                </div>
                                <div class="flex-1 w-0 flex flex-col">
                                    <div class="overflow-y-auto">
                                        <div id="{{ $mailContentId }}">
                                            @include('profile.' . $partialName, ['mail' => $firstMail])
                                        </div>
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<style>
.mailboxMain .mailboxLists .mailItems.active, .mailboxMain .mailboxLists .mailItems:hover {
    background-color: #00A47D;
    border: 1px solid #016950;
        color: #fff !important;
}
.mailboxMain .mailboxLists .mailItems.active .text-black,  .mailboxMain .mailboxLists .mailItems:hover .text-black,
.mailboxMain .mailboxLists .mailItems.active  .text-xs, .mailboxMain .mailboxLists .mailItems:hover  .text-xs{
    color: #fff !important;
}

/* ── SweetAlert popups aligned to the app design system ── */
.swal2-popup {
    border-radius: 12px;
    border: 1px solid #D2DDDB;
    font-family: 'Open Sans', sans-serif;
    padding: 2rem 1.75rem 1.75rem;
}
.swal2-title {
    font-family: 'Manrope', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: #222;
}
.swal2-html-container {
    font-size: 14px;
    color: #5C5C5C;
    line-height: 1.6;
}
.swal2-html-container h2 {
    font-family: 'Manrope', sans-serif;
}
.swal2-input {
    height: 44px;
    font-size: 14px;
    color: #222;
    border: 1px solid #D2DDDB;
    border-radius: 8px;
    box-shadow: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.swal2-input:focus {
    border-color: #00A47D;
    box-shadow: 0 0 0 3px rgba(0, 164, 125, 0.15);
}
.swal2-actions {
    gap: 12px;
}
/* Base button shape = app pill buttons */
.swal2-styled {
    border-radius: 30px !important;
    font-weight: 600 !important;
    font-family: 'Open Sans', sans-serif;
    padding: 0.65rem 1.6rem !important;
    box-shadow: none !important;
    transition: 0.2s !important;
}
.swal2-styled:focus {
    box-shadow: 0 0 0 3px rgba(0, 164, 125, 0.25) !important;
}
/* Confirm — green primary when no explicit colour was set */
.swal2-styled.swal2-confirm:not([style*="background"]) {
    background-color: #00A47D !important;
    border: 1px solid #016950 !important;
    box-shadow: 0 3px 0 #016950 !important;
    color: #fff !important;
}
.swal2-styled.swal2-confirm:not([style*="background"]):hover {
    background-color: #016950 !important;
    transform: translateY(3px);
    box-shadow: 0 1px 0 #016950 !important;
}
/* Confirm for a destructive action (inline confirmButtonColor: #dc3545) —
   keep it red, just give it the app's pill + pressed-edge treatment */
.swal2-styled.swal2-confirm[style*="background"] {
    border: 1px solid #A61B29 !important;
    box-shadow: 0 3px 0 #A61B29 !important;
    color: #fff !important;
}
.swal2-styled.swal2-confirm[style*="background"]:hover {
    transform: translateY(3px);
    box-shadow: 0 1px 0 #A61B29 !important;
}
/* Cancel = neutral outline pill */
.swal2-styled.swal2-cancel {
    background-color: #fff !important;
    border: 1px solid #D2DDDB !important;
    color: #5C5C5C !important;
}
.swal2-styled.swal2-cancel:hover {
    background-color: #F2F2F2 !important;
    color: #222 !important;
}
.swal2-icon.swal2-success .swal2-success-ring {
    border-color: rgba(0, 164, 125, 0.3);
}
.swal2-icon.swal2-success [class^='swal2-success-line'] {
    background-color: #00A47D;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Texts in the student's language (lang/<language>/mailbox.php).
const MB_T = {{ \Illuminate\Support\Js::from(__('mailbox.js')) }};
const showMailRouteTemplate = "{{ route('profile.showMail', ['encryptedId' => '__ID__']) }}";
const csrfToken = "{{ csrf_token() }}";

/*function selectMail(encryptedId, tabName) {
    const newHash = `#${tabName}/${encryptedId}`;
    location.hash = newHash;
    fetchMailContent(encryptedId, tabName);
    
   // 2️⃣ Find all mail items
    const allMailItems = document.querySelectorAll('.mail-item');

    // 3️⃣ Remove 'active' class from all
    allMailItems.forEach(item => item.classList.remove('active'));

    // 4️⃣ Find the clicked mail item
    const mailItem = document.querySelector(`[data-mail-id="${encryptedId}"]`);

    if (mailItem) {
        // Remove 'unread' class if present
        mailItem.classList.remove('unread');

        // Add 'read' and 'active' classes
        mailItem.classList.add('read', 'active');
        const badge = document.getElementById('primaryUnreadBadge');

        if (badge && mailItem.classList.contains('unread')) {

            let count = parseInt(badge.innerText);

            if (count > 1) {

                badge.innerText = count - 1;

            } else {

                badge.style.display = 'none';

            }

        }
    }
}*/
function selectMail(encryptedId, tabName) {

    const newHash = `#${tabName}/${encryptedId}`;
    location.hash = newHash;

    // Find clicked mail
    const mailItem = document.querySelector(`[data-mail-id="${encryptedId}"]`);

    // Check BEFORE removing the class
    const wasUnread = mailItem && mailItem.classList.contains('unread');

    fetchMailContent(encryptedId, tabName);

    // Remove active from all
    document.querySelectorAll('.mail-item').forEach(item => {
        item.classList.remove('active');
    });

    if (mailItem) {

        mailItem.classList.remove('unread');
        mailItem.classList.add('read', 'active');

        // Update mailbox badge
        if (wasUnread) {

            const badge = document.getElementById('primaryUnreadBadge');

            if (badge) {

                let count = parseInt(badge.innerText) || 0;

                count--;

                if (count > 0) {

                    badge.innerText = count;

                } else {

                    badge.style.display = 'none';

                }

            }

            // Update sidebar badge
            const sidebarBadge = document.getElementById('sidebarUnreadBadge');

            if (sidebarBadge) {

                let count = parseInt(sidebarBadge.innerText) || 0;

                count--;

                if (count > 0) {

                    sidebarBadge.innerText = count;

                } else {

                    sidebarBadge.style.display = 'none';

                }

            }

        }

    }

}
function fetchMailContent(encryptedId, tabName) {
    const url = showMailRouteTemplate.replace('__ID__', encodeURIComponent(encryptedId));
    fetch(url)
        .then(res => res.json())
        /*.then(data => {
            const containerId = tabName + '-mail-content';
            document.getElementById(containerId).innerHTML = data.html;
            setActiveTab(tabName);
        });*/
        .then(data => {

        const containerId = tabName + '-mail-content';

        document.getElementById(containerId).innerHTML = data.html;

        setActiveTab(tabName);

        // Update unread badge
        const badge = document.getElementById('primaryUnreadBadge');

        if (badge && data.unreadCount !== undefined) {

            if (data.unreadCount > 0) {

                badge.innerText = data.unreadCount;
                badge.style.display = 'inline-flex';

            } else {

                badge.style.display = 'none';

            }

        }

    });
}

function setActiveTab(tabName) {
    const tabIdMap = {
        primary: 'tab1',
        starred: 'tab2',
        deleted: 'tab3'
    };
    const tabId = tabIdMap[tabName] || 'tab1';

    // 1. Tab Content Control
    document.querySelectorAll('.tab-content').forEach(tabContent => {
        if (tabContent.id === tabId) {
            tabContent.classList.remove('hidden');
            tabContent.classList.add('active');
        } else {
            tabContent.classList.add('hidden');
            tabContent.classList.remove('active');
        }
    });

    // 2. Tab Button Control
    document.querySelectorAll('.tab-button').forEach(btn => {
        if (btn.getAttribute('data-tab') === tabId) {
            btn.classList.remove('inactive');
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
            btn.classList.add('inactive');
        }
    });
}

function loadMailFromHash() {
    const hash = location.hash;
    if (hash.startsWith('#')) {
        const parts = hash.substring(1).split('/');
        if (parts.length === 2) {
            const tabName = parts[0];
            const encryptedId = parts[1];
            setActiveTab(tabName);
            fetchMailContent(encryptedId, tabName);
        } else {
            setActiveTab('primary');
        }
    } else {
        setActiveTab('primary');
    }
}

window.addEventListener('load', loadMailFromHash);
window.addEventListener('hashchange', loadMailFromHash);

// Print and status update remain the same
function printDiv(divId) {
    const divContent = document.getElementById(divId).innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>' + MB_T.print_title + '</title></head><body>');
    printWindow.document.write(divContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

/*function updateemailstatus(mailId, type) {
    $.ajax({
        url: '{{ route('profile.mailbox.updateStatus') }}',
        type: 'POST',
        data: {
            mail_id: mailId,
            status_type: type,
            _token: csrfToken
        },
        success: function(response) {
            console.log('Status updated:', response.message);
            location.reload(); // Simplified: reload page for now
        },
        error: function(xhr) {
            console.error('Failed to update status:', xhr.responseText);
        }
    });
}*/
function updateemailstatus(mailId, type) {

    if (type === 'deleted') {

        const randomNumber = Math.floor(1000 + Math.random() * 9000);

        Swal.fire({
            title: MB_T.delete_title,
            html: `
                <p>${MB_T.cannot_undo}</p>
                <p>${MB_T.enter_number}</p>

                <h2 style="color:#dc3545;font-weight:bold;">
                    ${randomNumber}
                </h2>

                <input type="text"
                       id="confirmCode"
                       class="swal2-input"
                       placeholder="${MB_T.number_placeholder}">
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: MB_T.yes_delete,
            cancelButtonText: MB_T.cancel,
            confirmButtonColor: '#dc3545',

            preConfirm: () => {

                const enteredCode = document.getElementById('confirmCode').value;

                if (enteredCode != randomNumber) {
                    Swal.showValidationMessage(
                        MB_T.mismatch
                    );
                    return false;
                }

                return true;
            }

        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: '{{ route('profile.mailbox.updateStatus') }}',
                    type: 'POST',
                    data: {
                        mail_id: mailId,
                        status_type: type,
                        _token: csrfToken
                    },
                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: MB_T.deleted,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        console.error('Failed to update status:', xhr.responseText);

                        Swal.fire({
                            icon: 'error',
                            title: MB_T.error,
                            text: MB_T.delete_failed
                        });
                    }
                });
            }
        });

        return;
    }

    // For star/unstar/read actions
    $.ajax({
        url: '{{ route('profile.mailbox.updateStatus') }}',
        type: 'POST',
        data: {
            mail_id: mailId,
            status_type: type,
            _token: csrfToken
        },
        success: function(response) {
            console.log('Status updated:', response.message);
            location.reload();
        },
        error: function(xhr) {
            console.error('Failed to update status:', xhr.responseText);
        }
    });
}

</script>
@endsection