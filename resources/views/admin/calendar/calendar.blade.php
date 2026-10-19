@extends('layouts.admin')

@section('title', 'Calendar')

@section('content')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
:root {
    --primary-color: #6b7280;
    --primary-dark: #4b5563;
}

.calendar-container {
    /* background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px; */
}

.fc {
    font-family: 'Arial', sans-serif;
}

.fc-button-primary {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
}

.fc-button-primary:hover {
    background-color: var(--primary-dark) !important;
    border-color: var(--primary-dark) !important;
}

.fc-button-primary:not(:disabled).fc-button-active {
    background-color: var(--primary-dark) !important;
}

.fc-event {
    cursor: pointer;
    border: none;
    padding: 2px 4px;
    border-radius: 4px;
    position: relative;
}

.fc-event::before {
    content: '';
    position: absolute;
    left: 4px;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: inherit;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 0 3px rgba(0,0,0,0.3);
}

.fc-event .fc-event-title {
    padding-left: 16px;
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
}

.btn-primary {
    background: var(--primary-color);
    color: #fff;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-secondary {
    background: #6b7280;
    color: #fff;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-danger {
    background: #ef4444;
    color: #fff;
}

.btn-danger:hover {
    background: #dc2626;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 30px;
    height: 30px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.event-detail {
    margin-bottom: 12px;
}

.event-detail strong {
    display: block;
    margin-bottom: 4px;
    color: #6b7280;
    font-size: 12px;
    text-transform: uppercase;
}

/* Class Filter Styles */
.form-group.checkbox-group {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
}

.form-group.checkbox-group input[type="checkbox"] {
    width: auto;
    margin-right: 8px;
    cursor: pointer;
}

.form-group.checkbox-group label {
    margin-bottom: 0;
    cursor: pointer;
}

/* Hide the placeholder button while it's being replaced with dropdown */
.fc-classFilter-button {
    display: none !important;
}
</style>
@endpush

<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Calendar</h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Add/Edit Activity Modal -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Activity</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="eventForm">
                <input type="hidden" id="eventId">
                
                <div class="form-group">
                    <label for="eventTitle">Activity Title *</label>
                    <input type="text" id="eventTitle" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="activitys">Activity *</label>
                    <select id="activitys" class="form-control" required>
                        <option value="">Select Activity</option>
                        @foreach($activitiesTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="activityType">Activity Type *</label>
                    <select id="activityType" class="form-control" required>
                        <option value="">Select Activity Type</option>
                        @foreach($activities as $act)
                        <option value="{{ $act->id }}">{{ $act->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="eventClass">Class *</label>
                    <select id="eventClass" class="form-control" required>
                        <option value="">Select Class</option>
                        <option value="Class 1">Class 1</option>
                        <option value="Class 2">Class 2</option>
                        <option value="Class 3">Class 3</option>
                        <option value="Class 4">Class 4</option>
                        <option value="Class 5">Class 5</option>
                        <option value="Class 6">Class 6</option>
                        <option value="Class 7">Class 7</option>
                        <option value="Class 8">Class 8</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="eventPosition">Position *</label>
                    <select id="eventPosition" class="form-control" required>
                        <option value="">Select Position</option>
                        <option value="Require">Require</option>
                        <option value="Optional">Optional</option>
                    </select>
                </div>

                <div class="form-group checkbox-group" style="display:none">
                    <input type="checkbox" id="repeatActivity" checked />
                    <label for="repeatActivity">Repeat activity Monthly</label>
                </div>

                <div class="form-group">
                    <label for="eventStart">Start Date *</label>
                    <input type="date" id="eventStart" class="form-control" required>
                </div>

                <div class="form-group" id="endDateGroup" style="display: none;">
                    <label for="eventEnd">End Date *</label>
                    <input type="date" id="eventEnd" class="form-control">
                </div>

                <div class="form-group">
                    <label for="eventDescription">Description</label>
                    <textarea id="eventDescription" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="eventColor">Color</label>
                    <input type="color" id="eventColor" class="form-control" value="#6b7280">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveEventAPI()">Save Activity</button>
        </div>
    </div>
</div>

<!-- View Activity Modal -->
<div class="modal-overlay" id="viewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Activity Details</h3>
            <button class="close-btn" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body" id="eventDetails">
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="deleteEventAPI()">Delete</button>
            <button class="btn btn-primary" onclick="editEvent()">Edit</button>
            <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

let calendar;
let currentEvent = null;
let events = [];
let currentClass = 'all'; // Default to show all classes
let classList = []; // Store classes from API

// API Configuration
const API_BASE_URL = '{{ url('/api/admin/calendar-events') }}';
const CLASS_LIST_API_URL = '{{ url('/admin/class-list') }}';

// Clear any old localStorage data from previous implementation
localStorage.removeItem('calendarEvents');

// Fetch Classes from API
async function fetchClasses() {
    try {
        const response = await fetch(CLASS_LIST_API_URL, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load classes');
        }

        const result = await response.json();
        classList = result.data || result;
        populateClassDropdowns();
    } catch (error) {
        console.error('Error fetching classes:', error);
        // Fallback to default classes if API fails
        classList = [
            { id: 1, name: 'Class 1' },
            { id: 2, name: 'Class 2' },
            { id: 3, name: 'Class 3' },
            { id: 4, name: 'Class 4' },
            { id: 5, name: 'Class 5' },
            { id: 6, name: 'Class 6' },
            { id: 7, name: 'Class 7' },
            { id: 8, name: 'Class 8' }
        ];
        populateClassDropdowns();
    }
}

// Populate class dropdowns with fetched data
function populateClassDropdowns() {
    // Populate event form class dropdown
    const eventClassSelect = document.getElementById('eventClass');
    if (eventClassSelect) {
        eventClassSelect.innerHTML = '<option value="">Select Class</option>';
        classList.forEach(cls => {
            const option = document.createElement('option');
            option.value = cls.id;
            option.textContent = cls.name;
            eventClassSelect.appendChild(option);
        });
    }
}

// Load events from localStorage or dummy data (NOT USED - Using API instead)
// function loadEvents() {
//     const stored = localStorage.getItem('calendarEvents');
//     if (stored) {
//         events = JSON.parse(stored);
//     } else {
//         // Load dummy data on first load
//         events = dummyEvents;
//         saveEvents();
//     }
//     return events;
// }

// Save events to localStorage (NOT USED - Using API instead)
// function saveEvents() {
//     localStorage.setItem('calendarEvents', JSON.stringify(events));
// }

// Initialize Calendar
document.addEventListener('DOMContentLoaded', async function() {
    // Fetch classes first
    await fetchClasses();
    
    const calendarEl = document.getElementById('calendar');
    
    // Add event listener for repeat activity checkbox
    document.getElementById('repeatActivity').addEventListener('change', function() {
        const endDateGroup = document.getElementById('endDateGroup');
        const endDateInput = document.getElementById('eventEnd');
        if (this.checked) {
            endDateGroup.style.display = 'block';
            endDateInput.required = true;
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.required = false;
            endDateInput.value = '';
        }
    });
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today classFilter',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        customButtons: {
            classFilter: {
                text: '',
            }
        },
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        
        // Load events from API
        events: async function (info, successCallback, failureCallback) {
            try {
                const url = currentClass === 'all' 
                    ? API_BASE_URL 
                    : `${API_BASE_URL}?class=${encodeURIComponent(currentClass)}`;
                    
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.content
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load events');
                }

                const data = await response.json();
                successCallback(data);
            } catch (error) {
                console.error('Error fetching events:', error);
                failureCallback(error);
            }
        },
        
        // Apply custom styling after event is mounted
        eventDidMount: function(info) {
            const color = info.event.backgroundColor || '#6b7280';
            const textColor = getContrastColor(color);
            
            // Apply background color and text color
            info.el.style.backgroundColor = color;
            info.el.style.borderColor = color;
            
            // Set text color for better contrast
            const titleEl = info.el.querySelector('.fc-event-title, .fc-event-main, .fc-list-event-title');
            if (titleEl) {
                titleEl.style.color = textColor;
            }
            
            // Also set color for all text elements within the event
            info.el.style.color = textColor;
        },

        // Click on empty date to add event
        select: function(info) {
            openModal(info.startStr, info.endStr);
            calendar.unselect();
        },
        
        // Click on event to view
        eventClick: function(info) {
            currentEvent = info.event;
            showEventDetails(info.event);
        },
        
        // Drag and drop to reschedule
        eventDrop: function(info) {
            updateEventTime(info.event);
        },
        
        // Resize event
        eventResize: function(info) {
            updateEventTime(info.event);
        }
    });
    
    calendar.render();
    
    // Add class filter dropdown to toolbar after today button
    setTimeout(() => {
        const filterButton = document.querySelector('.fc-classFilter-button');
        if (filterButton) {
            const select = document.createElement('select');
            select.id = 'classFilter';
            select.className = 'fc-button fc-button-primary';
            select.style.height = 'auto';
            select.style.padding = '6px 10px';
            select.style.fontSize = '14px';
            select.style.setProperty('background-color', '#fff', 'important');
            select.style.setProperty('color', '#333', 'important');
            select.style.setProperty('border', '1px solid #d1d5db', 'important');
            select.style.borderRadius = '4px';
            select.style.cursor = 'pointer';
            
            // Build options from fetched class list
            let optionsHTML = '<option value="all">All Classes</option>';
            classList.forEach(cls => {
                const className = cls.name;
                optionsHTML += `<option value="${cls.id}">${className}</option>`;
            });
            select.innerHTML = optionsHTML;
            
            select.addEventListener('change', function() {
                currentClass = this.value;
                calendar.refetchEvents();
            });
            
            // Replace the button with the dropdown
            filterButton.style.display = 'inline-block';
            filterButton.parentNode.replaceChild(select, filterButton);
        }
    }, 50);
});

// Open Add/Edit Modal
function openModal(start = null, end = null) {
    document.getElementById('modalTitle').textContent = 'Add Activity';
    document.getElementById('eventForm').reset();
    document.getElementById('eventId').value = '';
    
    // Hide end date field by default
    document.getElementById('endDateGroup').style.display = 'block';
    document.getElementById('eventEnd').required = false;
    
    if (start) {
        // Extract just the date part from the ISO string
        const startDate = new Date(start);
        const dateStr = startDate.toISOString().split('T')[0];
        document.getElementById('eventStart').value = dateStr;
    }
    
    document.getElementById('eventModal').classList.add('show');
}

// Close Modal
function closeModal() {
    document.getElementById('eventModal').classList.remove('show');
}

// Save Event (Local) - NOT USED, using API version instead
// function saveEvent() {
//     const id = document.getElementById('eventId').value;
//     const title = document.getElementById('eventTitle').value.trim();
//     const start = document.getElementById('eventStart').value;
//     const end = document.getElementById('eventEnd').value;
//     const description = document.getElementById('eventDescription').value.trim();
//     const color = document.getElementById('eventColor').value;
//     
//     if (!title || !start || !end) {
//         alert('Please fill in all required fields');
//         return;
//     }
//     
//     if (new Date(end) < new Date(start)) {
//         alert('End date must be after start date');
//         return;
//     }
//     
//     const eventData = {
//         id: id || Date.now().toString(),
//         title: title,
//         start: start,
//         end: end,
//         description: description,
//         backgroundColor: color,
//         borderColor: color
//     };
//     
//     if (id) {
//         // Update existing event
//         const index = events.findIndex(e => e.id === id);
//         if (index !== -1) {
//             events[index] = eventData;
//             const calEvent = calendar.getEventById(id);
//             if (calEvent) {
//                 calEvent.setProp('title', title);
//                 calEvent.setStart(start);
//                 calEvent.setEnd(end);
//                 calEvent.setProp('backgroundColor', color);
//                 calEvent.setProp('borderColor', color);
//                 calEvent.setExtendedProp('description', description);
//             }
//         }
//     } else {
//         // Add new event
//         events.push(eventData);
//         calendar.addEvent(eventData);
//     }
//     
//     saveEvents();
//     closeModal();
// }

// Save Activity with API - ACTIVE VERSION
async function saveEventAPI() {
    const id = document.getElementById('eventId').value;
    const title = document.getElementById('eventTitle').value.trim();
    const activitys = document.getElementById('activitys').value;
    const activityType = document.getElementById('activityType').value;
    const eventClass = document.getElementById('eventClass').value;
    const position = document.getElementById('eventPosition').value;
    const repeatActivity = document.getElementById('repeatActivity').checked;
    const start = document.getElementById('eventStart').value;
    const end = document.getElementById('eventEnd').value;
    const description = document.getElementById('eventDescription').value.trim();
    const color = document.getElementById('eventColor').value;
    
    if (!title || !eventClass || !position || !start) {
        alert('Please fill in all required fields');
        return;
    }

    if (repeatActivity && !end) {
        alert('End date is required when repeat activity is checked');
        return;
    }

    if (repeatActivity && new Date(end) < new Date(start)) {
        alert('End date must be after start date');
        return;
    }

    // Validate maximum 12 activities for repeat activity
    if (repeatActivity) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        let monthCount = 0;
        let currentDate = new Date(startDate);
        
        while (currentDate <= endDate) {
            monthCount++;
            currentDate.setMonth(currentDate.getMonth() + 1);
        }
        
        if (monthCount > 12) {
            alert('Maximum 12 activities can be created. Please select an end date within 12 months from start date.');
            return;
        }
    }

    try {
        if (id) {
            // UPDATE - Single update for existing activity
            const eventData = {
                title,
                activitys,
                activityType,
                class: eventClass,
                position,
                repeat_activity: false,
                start: start,
                end: end,
                description,
                backgroundColor: color,
                borderColor: color
            };

            const response = await fetch(`${API_BASE_URL}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(eventData)
            });

            if (!response.ok) throw new Error('Failed to save activity');

            calendar.refetchEvents();
            closeModal();
            alert('Activity updated successfully!');
        } else {
            // CREATE - Multiple creates if repeat activity is checked
            if (repeatActivity) {
                // Generate all monthly dates between start and end
                const startDate = new Date(start);
                const endDate = new Date(end);
                const dates = [];

                let currentDate = new Date(startDate);
                while (currentDate <= endDate) {
                    dates.push(new Date(currentDate));
                    // Move to next month, same day
                    currentDate.setMonth(currentDate.getMonth() + 1);
                }

                // Create activity for each month
                let successCount = 0;
                let failCount = 0;
                //New End date add
                const originalStart = new Date(start);
                const originalEnd = new Date(end);

                // Calculate difference in days
                const diffTime = originalEnd - originalStart;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                //New End date add
                for (const date of dates) {
                    const newStart = new Date(date);
                    const newEnd = new Date(date);
                    newEnd.setDate(newEnd.getDate() + diffDays);
                    //const dateStr = date.toISOString().split('T')[0];
                    const eventData = {
                        title,
                        activitys,
                        activityType,
                        class: eventClass,
                        position,
                        repeat_activity: false,
                        //start: dateStr,
                        //end: dateStr,
                         start: newStart.toISOString().split('T')[0],
                         end: newEnd.toISOString().split('T')[0],
                        description,
                        backgroundColor: color,
                        borderColor: color
                    };

                    try {
                        const response = await fetch(API_BASE_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(eventData)
                        });

                        if (response.ok) {
                            successCount++;
                        } else {
                            failCount++;
                        }
                    } catch (error) {
                        console.error('Error saving activity for date:', dateStr, error);
                        failCount++;
                    }
                }

                calendar.refetchEvents();
                closeModal();
                
                if (failCount === 0) {
                    alert(`${successCount} activities created successfully!`);
                } else {
                    alert(`${successCount} activities created, ${failCount} failed. Please check and try again.`);
                }
            } else {
                // Single activity creation
                const eventData = {
                    title,
                    activitys,
                    activityType,
                    class: eventClass,
                    position,
                    repeat_activity: false,
                    start: start,
                    end: end,
                    description,
                    backgroundColor: color,
                    borderColor: color
                };

                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(eventData)
                });

                if (!response.ok) throw new Error('Failed to save activity');

                calendar.refetchEvents();
                closeModal();
                alert('Activity saved successfully!');
            }
        }
    } catch (error) {
        console.error('Error saving activity:', error);
        alert('Failed to save activity. Please try again.');
    }
}

// Show Activity Details
function showEventDetails(event) {

    // ✅ NO manipulation needed anymore
    let displayEnd = event.end || event.start;

    const details = `
        <div class="event-detail">
            <strong>Title</strong>
            <div>${event.title}</div>
        </div>

        ${event.extendedProps.class ? `
            <div class="event-detail">
                <strong>Class</strong>
                <div>${event.extendedProps.class}</div>
            </div>
        ` : ''}

        ${event.extendedProps.position ? `
            <div class="event-detail">
                <strong>Position</strong>
                <div>${event.extendedProps.position}</div>
            </div>
        ` : ''}

        <div class="event-detail">
            <strong>Start Date</strong>
            <div>${formatDate(event.start)}</div>
        </div>

        <div class="event-detail">
            <strong>End Date</strong>
            <div>${formatDate(displayEnd)}</div>
        </div>

        ${event.extendedProps.description ? `
            <div class="event-detail">
                <strong>Description</strong>
                <div>${event.extendedProps.description}</div>
            </div>
        ` : ''}
    `;
    
    document.getElementById('eventDetails').innerHTML = details;
    document.getElementById('viewModal').classList.add('show');
}

// Close View Modal
function closeViewModal() {
    document.getElementById('viewModal').classList.remove('show');
    currentEvent = null;
}

// Edit Activity
/*function editEvent() {
    if (!currentEvent) return;
    
    // Store event data before closing modal
    const eventToEdit = {
        id: currentEvent.id,
        title: currentEvent.title,
        activitys: currentEvent.extendedProps.activitys || '',
        activityType: currentEvent.extendedProps.activityType || '',
        class: currentEvent.extendedProps.class || '',
        position: currentEvent.extendedProps.position || '',
        repeat_activity: currentEvent.extendedProps.repeat_activity || false,
        start: currentEvent.start,
        end: currentEvent.end || currentEvent.start,
        description: currentEvent.extendedProps.description || '',
        backgroundColor: currentEvent.backgroundColor || '#00c4b4'
    };
    
    closeViewModal();
    
    // Use stored data to populate form
    document.getElementById('modalTitle').textContent = 'Edit Activity';
    document.getElementById('eventId').value = eventToEdit.id;
    document.getElementById('eventTitle').value = eventToEdit.title;
    //document.getElementById('activitys').value = eventToEdit.activitys;
    //document.getElementById('activityType').value = eventToEdit.activityType;
    setSelectValue('activitys', eventToEdit.activitys);
    setSelectValue('activityType', eventToEdit.activityType);
    //document.getElementById('eventClass').value = eventToEdit.class;
    setSelectValue('eventClass', eventToEdit.eventClass);
    document.getElementById('eventPosition').value = eventToEdit.position;
    document.getElementById('repeatActivity').checked = eventToEdit.repeat_activity;
    
    // Show/hide end date based on repeat activity
    const endDateGroup = document.getElementById('endDateGroup');
    const endDateInput = document.getElementById('eventEnd');
    if (eventToEdit.repeat_activity) {
        endDateGroup.style.display = 'block';
        endDateInput.required = true;
    } else {
        endDateGroup.style.display = 'block';
        endDateInput.required = false;
    }
    
    document.getElementById('eventStart').value = formatDateOnly(eventToEdit.start);
    document.getElementById('eventEnd').value = formatDateOnly(eventToEdit.end);
    document.getElementById('eventDescription').value = eventToEdit.description;
    document.getElementById('eventColor').value = eventToEdit.backgroundColor;
    
    document.getElementById('eventModal').classList.add('show');
    //console.log(eventToEdit.activitys, eventToEdit.activityType);
}*/
function editEvent() {
    if (!currentEvent) return;

    // ✅ Fix end date properly
    let eventEnd = currentEvent.end;

    // If end is null OR same-day event → use start
    if (!eventEnd) {
        eventEnd = currentEvent.start;
    } else {
        // ✅ Fix FullCalendar exclusive end date issue
        eventEnd = new Date(eventEnd);
        //eventEnd.setDate(eventEnd.getDate() - 1);
        eventEnd.setDate(eventEnd.getDate());
    }

    const eventToEdit = {
        id: currentEvent.id,
        title: currentEvent.title,
        activitys: currentEvent.extendedProps.activitys || '',
        activityType: currentEvent.extendedProps.activityType || '',
        class: currentEvent.extendedProps.classId || currentEvent.extendedProps.class || '',
        position: currentEvent.extendedProps.position || '',
        repeat_activity: currentEvent.extendedProps.repeat_activity || false,
        start: currentEvent.start,
        end: eventEnd,
        description: currentEvent.extendedProps.description || '',
        backgroundColor: currentEvent.backgroundColor || '#00c4b4',
        
    };
console.log(currentEvent.extendedProps);
    closeViewModal();

    // Populate form
    document.getElementById('modalTitle').textContent = 'Edit Activity';
    document.getElementById('eventId').value = eventToEdit.id;
    document.getElementById('eventTitle').value = eventToEdit.title;

    setSelectValue('activitys', eventToEdit.activitys);
    setSelectValue('activityType', eventToEdit.activityType);

    // ✅ FIXED HERE
    //setSelectValue('eventClass', eventToEdit.class);
    setSelectValue('eventClass', eventToEdit.class?.toString());

    document.getElementById('eventPosition').value = eventToEdit.position;
    //document.getElementById('repeatActivity').checked = eventToEdit.repeat_activity;
    document.getElementById('repeatActivity').checked = eventToEdit.repeat_activity == 1 || eventToEdit.repeat_activity === true;

    const endDateGroup = document.getElementById('endDateGroup');
    const endDateInput = document.getElementById('eventEnd');

    // ✅ Always show end date (better UX)
    endDateGroup.style.display = 'block';
    endDateInput.required = false;

    document.getElementById('eventStart').value = formatDateOnly(eventToEdit.start);
    document.getElementById('eventEnd').value = formatDateOnly(eventToEdit.end);

    document.getElementById('eventDescription').value = eventToEdit.description;
    document.getElementById('eventColor').value = eventToEdit.backgroundColor;

    document.getElementById('eventModal').classList.add('show');
}
function setSelectValue(selectId, value) {
    const select = document.getElementById(selectId);
    if (!select) return;

    value = value ? value.toString() : "";

    Array.from(select.options).forEach(option => {
        option.selected = (option.value === value);
    });
}

// Delete Event (Local) - NOT USED
// function deleteEvent() {
//     if (!currentEvent) return;
//     
//     if (!confirm('Are you sure you want to delete this event?')) return;
//     
//     events = events.filter(e => e.id !== currentEvent.id);
//     saveEvents();
//     currentEvent.remove();
//     closeViewModal();
// }

// Delete Activity with API
/*async function deleteEventAPI() {
    if (!currentEvent) return;

    if (!confirm('Are you sure you want to delete this activity?')) return;

    try {
        const response = await fetch(`${API_BASE_URL}/${currentEvent.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) throw new Error('Failed to delete activity');

        calendar.refetchEvents();
        closeViewModal();
        alert('Activity deleted successfully!');
    } catch (error) {
        console.error('Error deleting activity:', error);
        alert('Failed to delete activity. Please try again.');
    }
}*/
async function deleteEventAPI() {
    if (!currentEvent) return;

    // Close the details modal first
    closeViewModal();

    const deleteCode = Math.floor(1000 + Math.random() * 9000);

    const { value: enteredCode } = await Swal.fire({
        title: 'Delete Activity?',
        html: `
            <p>Enter this code to confirm deletion:</p>
            <h2 style="color:red">${deleteCode}</h2>
        `,
        input: 'text',
        inputPlaceholder: 'Enter confirmation code',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        inputValidator: (value) => {
            if (!value) {
                return 'Please enter the confirmation code';
            }

            if (value != deleteCode) {
                return 'Confirmation code does not match';
            }
        }
    });

    if (!enteredCode || enteredCode != deleteCode) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/${currentEvent.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) throw new Error('Failed to delete activity');

        calendar.refetchEvents();

        Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Activity deleted successfully.',
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to delete activity.'
        });
    }
}
// Update Event Time (after drag/resize) - Use API
function updateEventTime(event) {
    updateEventTimeAPI(event);
}

// Update Activity Time with API
async function updateEventTimeAPI(event) {
    try {
        const response = await fetch(`${API_BASE_URL}/${event.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.content
            },
            body: JSON.stringify({
                title: event.title,
                class: event.extendedProps?.class || '',
                position: event.extendedProps?.position || '',
                repeat_activity: event.extendedProps?.repeat_activity || false,
                start: event.start.toISOString().split('T')[0],
                end: event.end
                    ? event.end.toISOString().split('T')[0]
                    : event.start.toISOString().split('T')[0],
                description: event.extendedProps?.description || '',
                backgroundColor: event.backgroundColor,
                borderColor: event.borderColor
            })
        });

        if (!response.ok) {
            throw new Error('Failed to update activity');
        }

        console.log('Activity updated successfully');
    } catch (error) {
        console.error('Error updating activity:', error);
        alert('Failed to update activity time. Please try again.');

        // ⛔ revert drag/resize if API fails
        event.revert();
    }
}

// Format date for date input (YYYY-MM-DD)
function formatDateOnly(date) {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Format date for datetime-local input
function formatDateTimeLocal(date) {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Format date for display
function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Format date for display with time
function formatDateTime(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Get contrast color for text based on background
function getContrastColor(hexColor) {
    // Convert hex to RGB
    const r = parseInt(hexColor.substr(1, 2), 16);
    const g = parseInt(hexColor.substr(3, 2), 16);
    const b = parseInt(hexColor.substr(5, 2), 16);
    
    // Calculate luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    
    // Return white for dark backgrounds, black for light backgrounds
    return luminance > 0.5 ? '#000000' : '#ffffff';
}

// Close modals on outside click
document.getElementById('eventModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewModal();
});
</script>
@endpush