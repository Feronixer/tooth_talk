<?php
// user_management.php
session_start(); 

$activeSection = isset($_GET['section']) && $_GET['section'] === 'staff' ? 'staff' : 'patient';
$pageTitle = ($activeSection === 'staff' ? "Staff" : "Patient") . " Management - ToothTalk";

$adminName = $_SESSION['admin_name'] ?? "Dr. Justine Valera";
$adminRole = $_SESSION['admin_role'] ?? "Administrator";

if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME']; 
    $baseUrlPath = str_replace(basename($scriptName), '', $scriptName);
    define('BASE_URL', rtrim($baseUrlPath, '/') . '/');
}
$isUserManagementActive = ($activeSection === 'staff' || $activeSection === 'patient');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }

        .sidebar { width: 280px; transition: transform 0.3s ease-in-out; }
        .main-content-area { margin-left: 280px; transition: margin-left 0.3s ease-in-out; width: calc(100% - 280px); }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); position: fixed; height: 100%; z-index: 40; }
            .sidebar.open { transform: translateX(0); }
            .main-content-area { margin-left: 0; width: 100%; }
            .overlay.open { display: block; }
        }

        .nav-link { display: flex; align-items: center; padding: 0.75rem 1.5rem; color: #e2e8f0; font-weight: 500; border-radius: 0.375rem; transition: background-color 0.2s ease, color 0.2s ease; }
        .nav-link:hover, .nav-link.active-main { background-color: #0f766e; color: #ffffff; }
        .nav-link.active-main { background-color: #14b8a6; }
        .nav-link i.fa-solid, .nav-link i.fas { margin-right: 0.75rem; width: 1.25rem; text-align: center; }
        
        .submenu-link { padding-left: 3.5rem !important; }
        .submenu-link.active-sub { color: #ccfbf1; font-weight: 600; background-color: #0d9488 !important; }
        .submenu-link:not(.active-sub):hover { background-color: #0f766e; color: #ffffff; }

        .action-btn { padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; transition: background-color 0.2s; margin-right: 0.25rem; }
        .action-btn:last-child { margin-right: 0; }
        .view-btn { background-color: #3b82f6; color: white; } .view-btn:hover { background-color: #2563eb; }
        .edit-btn { background-color: #10b981; color: white; } .edit-btn:hover { background-color: #059669; }
        .delete-btn { background-color: #ef4444; color: white; } .delete-btn:hover { background-color: #dc2626; }
        .add-btn { background-color: #06b6d4; color: white; padding: 0.625rem 1.25rem; } .add-btn:hover { background-color: #0891b2; }

        .status-badge { padding: 0.25em 0.6em; font-size: 0.75em; font-weight: 600; border-radius: 9999px; text-transform: capitalize; }
        .status-pending { background-color: #fef3c7; color: #d97706; } 
        .status-confirmed { background-color: #dcfce7; color: #16a34a; }
        .status-completed { background-color: #dbeafe; color: #2563eb; } 
        .status-rescheduled { background-color: #ffedd5; color: #f97316; } 
        .status-cancelled { background-color: #e5e7eb; color: #4b5563; } 

        .table-container { overflow-x: auto; }
        th, td { white-space: nowrap; padding: 0.75rem 1rem; }
        th.sortable { cursor: pointer; }
        th.sortable .sort-icon { margin-left: 5px; color: #9ca3af; }
        th.sortable .sort-icon.active { color: #14b8a6; }
        
        .modal { z-index: 50; } 
        .modal-content { max-height: 90vh; overflow-y: auto; padding: 1.5rem; }
        .form-input, .form-select, .form-textarea { 
            @apply w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 sm:text-sm transition-shadow duration-150 ease-in-out;
        }
        .form-label { @apply block text-sm font-medium text-gray-700 mb-1.5; }
        .form-group { @apply mb-5; }

        .modal-header { @apply pb-4 border-b border-gray-200 mb-6; }
        .modal-footer { @apply pt-6 border-t border-gray-200; }

    </style>
</head>
<body>
    <div class="flex h-screen">
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden overlay"></div>
        <aside id="sidebar" class="sidebar fixed top-0 left-0 h-full bg-teal-800 text-slate-200 flex flex-col shadow-lg z-40">
            <div class="p-6 border-b border-teal-700">
                <div class="flex items-center justify-center">
                    <img src="https://placehold.co/40x40/ffffff/14b8a6?text=TT" alt="ToothTalk Logo" class="h-10 w-10 mr-3 rounded-full">
                    <h1 class="text-xl font-bold text-white">ToothTalk</h1>
                </div>
                <p class="text-center text-sm text-teal-300 mt-1">JValera Dental Clinic</p>
            </div>
            <div class="p-6 border-b border-teal-700">
                 <div class="flex items-center">
                    <img src="https://placehold.co/40x40/e0e0e0/333333?text=A" alt="Admin" class="h-10 w-10 rounded-full mr-3">
                    <div>
                        <p class="font-semibold text-white"><?php echo htmlspecialchars($adminName); ?></p>
                        <p class="text-xs text-teal-300"><?php echo htmlspecialchars($adminRole); ?></p>
                    </div>
                </div>
            </div>
            <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="<?php echo BASE_URL; ?>schedule_management.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="<?php echo BASE_URL; ?>form_management.php" class="nav-link"><i class="fas fa-file-invoice"></i> Forms</a>
                <div>
                    <button onclick="toggleSubmenu('userSubmenu')" class="nav-link w-full text-left <?php echo $isUserManagementActive ? 'active-main' : ''; ?>">
                        <i class="fas fa-users"></i> User Management <i class="fas fa-chevron-down ml-auto transform transition-transform duration-200 <?php echo $isUserManagementActive ? 'rotate-180' : ''; ?>" id="userSubmenuIcon"></i>
                    </button>
                    <div id="userSubmenu" class="pl-4 mt-1 space-y-1 <?php echo $isUserManagementActive ? '' : 'hidden'; ?>">
                        <a href="<?php echo BASE_URL; ?>user_management.php?section=staff" class="nav-link submenu-link <?php echo ($activeSection === 'staff') ? 'active-sub' : ''; ?>"><i class="fas fa-user-tie"></i> Staff</a>
                        <a href="<?php echo BASE_URL; ?>user_management.php?section=patient" class="nav-link submenu-link <?php echo ($activeSection === 'patient') ? 'active-sub' : ''; ?>"><i class="fas fa-user-injured"></i> Patient</a>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>content_management.php" class="nav-link"><i class="fas fa-cogs"></i> Content</a>
                <a href="<?php echo BASE_URL; ?>service_rating.php" class="nav-link"><i class="fas fa-star"></i> Ratings</a>
                <a href="<?php echo BASE_URL; ?>logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <div id="mainContentArea" class="main-content-area flex-1 flex flex-col overflow-y-auto">
            <header class="bg-white shadow-md p-4 flex items-center justify-between md:justify-end sticky top-0 z-20">
                 <button id="hamburgerBtn" class="md:hidden text-gray-600 hover:text-gray-800 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="text-gray-600">Today: <?php echo date("D, M j, Y"); ?></div>
            </header>

            <main class="flex-1 p-6">
                <div id="staffManagementSection" class="<?php echo $activeSection === 'staff' ? '' : 'hidden'; ?>">
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h1 class="text-2xl font-semibold text-gray-700 mb-6">Staff List</h1>
                        <div class="flex flex-wrap items-center justify-between mb-4 gap-4">
                            <div class="flex items-center space-x-2">
                                <label for="staffShowEntries" class="text-sm text-gray-600">Show</label>
                                <select id="staffShowEntries" class="border border-gray-300 rounded-md p-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="5">5</option><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option>
                                </select>
                                <span class="text-sm text-gray-600">entries</span>
                            </div>
                            <input type="text" id="staffSearchInput" placeholder="Search staff by username or name..." class="border border-gray-300 rounded-md p-2 text-sm focus:ring-teal-500 focus:border-teal-500 w-full sm:w-auto">
                            <button id="openAddStaffModalBtn" class="action-btn add-btn"><i class="fas fa-plus mr-2"></i>Add Staff</button>
                        </div>
                        <div class="table-container rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="id">ID <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="username">Username <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="name">Name <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="role">Role <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="email">Email <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="mobile">Mobile <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="created_at">Created <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="staffTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                        <div id="staffLoadingMessage" class="text-center py-4 text-gray-500" style="display: none;">Loading staff...</div>
                        <div id="staffNoResultsMessage" class="text-center py-4 text-gray-500" style="display: none;">No staff found.</div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="text-sm text-gray-700" id="staffPaginationInfo"></div>
                            <div class="inline-flex -space-x-px" id="staffPaginationControls"></div>
                        </div>
                    </div>
                </div>

                <div id="patientManagementSection" class="<?php echo $activeSection === 'patient' ? '' : 'hidden'; ?>">
                     <div class="bg-white shadow-lg rounded-lg p-6">
                        <h1 class="text-2xl font-semibold text-gray-700 mb-6">Patient List</h1>
                        <div class="flex flex-wrap items-center justify-between mb-4 gap-4">
                            <div class="flex items-center space-x-2">
                                <label for="patientShowEntries" class="text-sm text-gray-600">Show</label>
                                <select id="patientShowEntries" class="border border-gray-300 rounded-md p-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="5">5</option><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option>
                                </select>
                                <span class="text-sm text-gray-600">entries</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-4">
                                <select id="patientFilterStatus" class="border border-gray-300 rounded-md p-2 text-sm focus:ring-teal-500 focus:border-teal-500">
                                    <option value="">All Statuses</option><option value="Pending">Pending</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Rescheduled">Rescheduled</option><option value="Cancelled">Cancelled</option>
                                </select>
                                <input type="text" id="patientSearchInput" placeholder="Search by Patient No. or Name..." class="border border-gray-300 rounded-md p-2 text-sm focus:ring-teal-500 focus:border-teal-500 w-full sm:w-auto">
                            </div>
                            <button id="openAddPatientModalBtn" class="action-btn add-btn"><i class="fas fa-plus mr-2"></i>Add Patient</button>
                        </div>
                        <div class="table-container rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="patient_no">Patient No. <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="name">Name <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="appointment_date">Date <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="appointment_time">Time <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="treatment">Treatment <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="patient_type">Type <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable" data-column="status">Status <span class="sort-icon fas fa-sort"></span></th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="patientTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                        <div id="patientLoadingMessage" class="text-center py-4 text-gray-500" style="display: none;">Loading patients...</div>
                        <div id="patientNoResultsMessage" class="text-center py-4 text-gray-500" style="display: none;">No patients found.</div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="text-sm text-gray-700" id="patientPaginationInfo"></div>
                            <div class="inline-flex -space-x-px" id="patientPaginationControls"></div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="viewModal" class="fixed modal inset-0 overflow-y-auto hidden" aria-labelledby="viewModalTitleText" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-6 pt-5 pb-4 sm:p-8 sm:pb-6 modal-content">
                    <div class="sm:flex sm:items-start">
                        <div id="viewModalIconContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-teal-100 sm:mx-0 sm:h-10 sm:w-10"></div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900" id="viewModalTitleText">Details</h3>
                            <div class="mt-4 space-y-3 text-sm" id="viewModalDetails"></div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse modal-footer">
                    <button type="button" id="closeViewModalBtn" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="staffFormModal" class="fixed modal inset-0 overflow-y-auto hidden" aria-labelledby="staffModalTitle" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <form id="staffForm">
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-8 sm:pb-6 modal-content">
                        <div class="modal-header">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900" id="staffModalTitleText">Add Staff</h3>
                        </div>
                        <input type="hidden" id="staffId" name="id">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                            <div class="form-group"><label for="staffUsername" class="form-label">Username <span class="text-red-500">*</span></label><input type="text" name="username" id="staffUsername" class="form-input" required></div>
                            <div class="form-group"><label for="staffName" class="form-label">Full Name <span class="text-red-500">*</span></label><input type="text" name="name" id="staffName" class="form-input" required></div>
                            <div class="form-group"><label for="staffEmail" class="form-label">Email Address <span class="text-red-500">*</span></label><input type="email" name="email" id="staffEmail" class="form-input" required></div>
                            <div class="form-group"><label for="staffMobile" class="form-label">Mobile Number</label><input type="tel" name="mobile" id="staffMobile" class="form-input"></div>
                            <div class="form-group">
                                <label for="staffRole" class="form-label">Role <span class="text-red-500">*</span></label>
                                <select name="role" id="staffRole" class="form-select" required>
                                    <option value="Staff">Staff</option><option value="Admin">Admin</option><option value="Dentist">Dentist</option><option value="Nurse">Nurse</option><option value="Receptionist">Receptionist</option><option value="IT Support">IT Support</option><option value="Accountant">Accountant</option><option value="Manager">Manager</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="staffPassword" class="form-label">Password <span id="staffPasswordRequired" class="text-red-500">*</span></label>
                                <input type="password" name="password" id="staffPassword" class="form-input" autocomplete="new-password">
                                <p class="text-xs text-gray-500 mt-1" id="staffPasswordHelp">Leave blank to keep current password (for edit).</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse modal-footer">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                        <button type="button" id="closeStaffFormModalBtn" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="patientFormModal" class="fixed modal inset-0 overflow-y-auto hidden" aria-labelledby="patientModalTitle" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <form id="patientForm">
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-8 sm:pb-6 modal-content">
                        <div class="modal-header">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900" id="patientModalTitleText">Add Patient</h3>
                        </div>
                        <input type="hidden" id="patientId" name="id">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5">
                            <div class="form-group"><label for="patientNo" class="form-label">Patient Number <span class="text-red-500">*</span></label><input type="text" name="patient_no" id="patientNo" class="form-input" required></div>
                            <div class="form-group md:col-span-2"><label for="patientName" class="form-label">Full Name <span class="text-red-500">*</span></label><input type="text" name="name" id="patientName" class="form-input" required></div>
                            
                            <div class="form-group"><label for="patientBirthDate" class="form-label">Birthdate</label><input type="date" name="birth_date" id="patientBirthDate" class="form-input"></div>
                            <div class="form-group"><label for="patientPhone" class="form-label">Contact No.</label><input type="tel" name="phone" id="patientPhone" class="form-input"></div>
                            <div class="form-group"><label for="patientEmail" class="form-label">Email Address</label><input type="email" name="email" id="patientEmail" class="form-input"></div>
                            
                            <div class="form-group md:col-span-3"><label for="patientAddress" class="form-label">Address</label><textarea name="address" id="patientAddress" rows="2" class="form-textarea"></textarea></div>
                            
                            <div class="md:col-span-3 my-3"><hr></div>
                            
                            <div class="form-group"><label for="patientAppointmentDate" class="form-label">Appointment Date <span class="text-red-500">*</span></label><input type="date" name="appointment_date" id="patientAppointmentDate" class="form-input" required></div>
                            <div class="form-group"><label for="patientAppointmentTime" class="form-label">Appointment Time <span class="text-red-500">*</span></label><input type="time" name="appointment_time" id="patientAppointmentTime" class="form-input" required></div>
                            <div class="form-group md:col-span-1"><label for="patientTreatment" class="form-label">Treatment/Service <span class="text-red-500">*</span></label><input type="text" name="treatment" id="patientTreatment" class="form-input" required></div>

                            <div class="form-group">
                                <label for="patientType" class="form-label">Patient Type <span class="text-red-500">*</span></label>
                                <select name="patient_type" id="patientType" class="form-select" required><option value="New">New</option><option value="Old">Old</option></select>
                            </div>
                            <div class="form-group">
                                <label for="patientStatus" class="form-label">Status <span class="text-red-500">*</span></label>
                                <select name="status" id="patientStatus" class="form-select" required><option value="Pending">Pending</option><option value="Confirmed">Confirmed</option><option value="Completed">Completed</option><option value="Rescheduled">Rescheduled</option><option value="Cancelled">Cancelled</option></select>
                            </div>
                             <div class="form-group md:col-span-3"><label for="patientReason" class="form-label">Reason (for Reschedule/Cancel)</label><input type="text" name="reason" id="patientReason" class="form-input"></div>
                             <div class="form-group md:col-span-3"><label for="patientNotes" class="form-label">Notes</label><textarea name="notes" id="patientNotes" rows="2" class="form-textarea"></textarea></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse modal-footer">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:ml-3 sm:w-auto sm:text-sm">Save Patient</button>
                        <button type="button" id="closePatientFormModalBtn" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
// API_BASE_URL, escapeHtml, formatDate, formatTime, formatDateTime (same as previous version)
const API_BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>/api/';
function escapeHtml(unsafe) { if (unsafe === null || typeof unsafe === 'undefined') return ''; return String(unsafe).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");}
function formatDate(dateString) { if (!dateString || dateString === '0000-00-00') return 'N/A'; try { const date = new Date(dateString); if (isNaN(date.getTime())) return 'Invalid Date'; return date.toLocaleDateString('en-CA'); } catch (e) { return dateString; } }
function formatTime(timeString) { if (!timeString) return 'N/A'; try { const parts = String(timeString).split(':'); if(parts.length < 2) return timeString; const hours = parseInt(parts[0], 10); const minutes = parseInt(parts[1], 10); const date = new Date(); date.setHours(hours, minutes, 0); if (isNaN(date.getTime())) return 'Invalid Time'; return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }); } catch (e) { return timeString; } }
function formatDateTime(dateTimeString) { if (!dateTimeString) return 'N/A'; try { const date = new Date(dateTimeString); if (isNaN(date.getTime())) return 'Invalid DateTime'; return date.toLocaleDateString('en-CA') + ' ' + date.toLocaleTimeString('en-GB', { hour12: false }); } catch (e) { return dateTimeString; } }

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const viewModal = document.getElementById('viewModal');
    const closeViewModalBtn = document.getElementById('closeViewModalBtn');

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); sidebarOverlay.classList.toggle('hidden'); });
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', () => { sidebar.classList.remove('open'); sidebarOverlay.classList.add('hidden'); });
    if (closeViewModalBtn) closeViewModalBtn.addEventListener('click', () => viewModal.classList.add('hidden'));
    if (viewModal) viewModal.addEventListener('click', (event) => { if (event.target === viewModal) viewModal.classList.add('hidden'); });

    const activeSection = '<?php echo $activeSection; ?>';
    if (activeSection === 'staff') initializeStaffManagement();
    else if (activeSection === 'patient') initializePatientManagement();
    
    const userSubmenuButton = document.querySelector('button[onclick="toggleSubmenu(\'userSubmenu\')"]');
    const isUserManagementActivePHP = <?php echo json_encode($isUserManagementActive); ?>;
    if (userSubmenuButton && isUserManagementActivePHP) {
        const submenu = document.getElementById('userSubmenu');
        const icon = document.getElementById('userSubmenuIcon');
        if (submenu && icon) { 
            submenu.classList.remove('hidden');
            icon.classList.add('rotate-180'); 
        }
    }

    const staffFormModal = document.getElementById('staffFormModal');
    const openAddStaffModalBtn = document.getElementById('openAddStaffModalBtn');
    const closeStaffFormModalBtn = document.getElementById('closeStaffFormModalBtn');
    const staffForm = document.getElementById('staffForm');

    if(openAddStaffModalBtn) openAddStaffModalBtn.addEventListener('click', () => openStaffModal());
    if(closeStaffFormModalBtn) closeStaffFormModalBtn.addEventListener('click', () => staffFormModal.classList.add('hidden'));
    if(staffForm) staffForm.addEventListener('submit', handleStaffFormSubmit);

    const patientFormModal = document.getElementById('patientFormModal');
    const openAddPatientModalBtn = document.getElementById('openAddPatientModalBtn');
    const closePatientFormModalBtn = document.getElementById('closePatientFormModalBtn');
    const patientForm = document.getElementById('patientForm');

    if(openAddPatientModalBtn) openAddPatientModalBtn.addEventListener('click', () => openPatientModal());
    if(closePatientFormModalBtn) closePatientFormModalBtn.addEventListener('click', () => patientFormModal.classList.add('hidden'));
    if(patientForm) patientForm.addEventListener('submit', handlePatientFormSubmit);
});

function toggleSubmenu(submenuId) { const submenu = document.getElementById(submenuId); const icon = document.getElementById(submenuId + 'Icon'); if (submenu) { submenu.classList.toggle('hidden'); icon.classList.toggle('rotate-180'); } }

async function handleFetchError(response, context) {
    let errorDisplayMessage = `Error during ${context}. Status: ${response.status}.`;
    let rawResponseForConsole = "";
    try {
        rawResponseForConsole = await response.text(); 
        try {
            const errorData = JSON.parse(rawResponseForConsole); 
            if (errorData && errorData.error) {
                errorDisplayMessage = `Error: ${errorData.error}`;
                if (errorData.details) errorDisplayMessage += ` Details: ${errorData.details}`;
                if(errorData.debug_sql) console.warn("SQL Debug Info:", errorData.debug_sql);
            } else if (errorData) { errorDisplayMessage += " Unexpected JSON response."; }
        } catch (e) { errorDisplayMessage += ` Server response: ${rawResponseForConsole.substring(0, 200)}...`; }
    } catch (textError) { errorDisplayMessage += ` Could not read server response.`; }
    console.error(`Full error context for ${context}:`, response, "Raw response text:", rawResponseForConsole);
    return errorDisplayMessage;
}

// --- Staff Management (CRUD) ---
let staffDataCache = []; let staffCurrentPage = 1; let staffEntriesPerPage = 10; let staffCurrentSearchTerm = '';
let staffSortBy = 'id'; let staffSortDir = 'DESC'; 

function initializeStaffManagement() { 
    const staffShowEntriesSelect = document.getElementById('staffShowEntries');
    const staffSearchInput = document.getElementById('staffSearchInput');
    const staffHeaders = document.querySelectorAll('#staffManagementSection th.sortable');

    staffEntriesPerPage = parseInt(staffShowEntriesSelect.value);
    staffShowEntriesSelect.addEventListener('change', () => { staffEntriesPerPage = parseInt(staffShowEntriesSelect.value); staffCurrentPage = 1; fetchStaffData(); });
    staffSearchInput.addEventListener('input', () => { staffCurrentSearchTerm = staffSearchInput.value; staffCurrentPage = 1; clearTimeout(staffSearchInput.timer); staffSearchInput.timer = setTimeout(fetchStaffData, 300); });
    
    staffHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.column;
            if (staffSortBy === column) staffSortDir = staffSortDir === 'ASC' ? 'DESC' : 'ASC';
            else { staffSortBy = column; staffSortDir = 'ASC'; }
            updateSortIcons(staffHeaders, staffSortBy, staffSortDir); fetchStaffData();
        });
    });
    updateSortIcons(staffHeaders, staffSortBy, staffSortDir); fetchStaffData();
}
async function fetchStaffData() { 
    const loadingMsg = document.getElementById('staffLoadingMessage'); const noResultsMsg = document.getElementById('staffNoResultsMessage'); const tableBody = document.getElementById('staffTableBody');
    if (!loadingMsg || !noResultsMsg || !tableBody) return; 
    loadingMsg.style.display = 'block'; noResultsMsg.style.display = 'none'; tableBody.innerHTML = '';
    const params = new URLSearchParams({ page: staffCurrentPage, limit: staffEntriesPerPage, search: staffCurrentSearchTerm, sort_by: staffSortBy, sort_dir: staffSortDir });
    try {
        const response = await fetch(`${API_BASE_URL}fetch_staff.php?${params.toString()}`);
        if (!response.ok) { const errorMsg = await handleFetchError(response, "fetching staff"); throw new Error(errorMsg); }
        const data = await response.json(); 
        if (data.error) { throw new Error(`API Error: ${data.error}${data.details ? ' - ' + data.details : ''}`); }
        staffDataCache = data.staff; renderStaffTable(data.staff);
        renderPagination('staff', data.total, data.staff.length, staffCurrentPage, staffEntriesPerPage, fetchStaffData);
        if (data.staff.length === 0 && !staffCurrentSearchTerm) noResultsMsg.textContent = 'No staff members found.';
        else if (data.staff.length === 0 && staffCurrentSearchTerm) noResultsMsg.textContent = `No staff members found for "${escapeHtml(staffCurrentSearchTerm)}".`;
        if (data.staff.length === 0) noResultsMsg.style.display = 'block';

    } catch (error) { 
        console.error('Error in fetchStaffData:', error); 
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-red-500">${escapeHtml(error.message)}</td></tr>`;
    } finally { loadingMsg.style.display = 'none'; }
}
function renderStaffTable(staffList) { 
    const tableBody = document.getElementById('staffTableBody'); tableBody.innerHTML = ''; if (!staffList || staffList.length === 0) return;
    staffList.forEach(staff => {
        const row = tableBody.insertRow();
        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-gray-700">${staff.id}</td>
            <td class="px-4 py-3 text-sm text-gray-900 font-medium">${escapeHtml(staff.username)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(staff.name)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(staff.role)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(staff.email)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(staff.mobile) || 'N/A'}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${formatDateTime(staff.created_at)}</td>
            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                <button class="action-btn view-btn" onclick="viewStaffDetails(${staff.id})"><i class="fas fa-eye sm:mr-1"></i><span class="hidden sm:inline">View</span></button>
                <button class="action-btn edit-btn" onclick="openStaffModal(${staff.id}, true)"><i class="fas fa-edit sm:mr-1"></i><span class="hidden sm:inline">Edit</span></button>
                <button class="action-btn delete-btn" onclick="deleteStaff(${staff.id}, '${escapeHtml(staff.name)}')"><i class="fas fa-trash sm:mr-1"></i><span class="hidden sm:inline">Delete</span></button>
            </td>`;
    });
}
function openStaffModal(staffId = null, isEdit = false) {
    const modal = document.getElementById('staffFormModal'); const form = document.getElementById('staffForm');
    const title = document.getElementById('staffModalTitleText'); const passwordHelp = document.getElementById('staffPasswordHelp');
    const passwordRequired = document.getElementById('staffPasswordRequired'); const staffPasswordInput = document.getElementById('staffPassword');
    form.reset(); document.getElementById('staffId').value = '';
    if (isEdit && staffId) {
        title.textContent = 'Edit Staff'; passwordHelp.style.display = 'block'; passwordRequired.style.display = 'none'; staffPasswordInput.required = false;
        const staff = staffDataCache.find(s => s.id == staffId);
        if (staff) {
            document.getElementById('staffId').value = staff.id; document.getElementById('staffUsername').value = staff.username;
            document.getElementById('staffName').value = staff.name; document.getElementById('staffEmail').value = staff.email;
            document.getElementById('staffMobile').value = staff.mobile || ''; document.getElementById('staffRole').value = staff.role;
        } else { alert('Staff member not found for editing.'); return; }
    } else {
        title.textContent = 'Add New Staff'; passwordHelp.style.display = 'none'; passwordRequired.style.display = 'inline'; staffPasswordInput.required = true;
    }
    modal.classList.remove('hidden');
}
async function handleStaffFormSubmit(event) {
    event.preventDefault(); const form = event.target; const formData = new FormData(form);
    const staffId = formData.get('id'); const isEdit = !!staffId;
    const apiUrl = isEdit ? `${API_BASE_URL}edit_staff.php` : `${API_BASE_URL}add_staff.php`;
    if (isEdit && !formData.get('password')) formData.delete('password');
    const data = Object.fromEntries(formData.entries());
    try {
        const response = await fetch(apiUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        if (!response.ok) { const errorMsg = await handleFetchError(response, isEdit ? "editing staff" : "adding staff"); throw new Error(errorMsg); }
        const result = await response.json();
        if (result.success) {
            alert(result.message); document.getElementById('staffFormModal').classList.add('hidden'); fetchStaffData(); 
        } else { alert(`Error: ${result.message || 'Unknown error from server.'}`); }
    } catch (error) { alert(`Submission failed: ${error.message}`); console.error('Staff form error:', error); }
}
function viewStaffDetails(staffId) { 
    const staff = staffDataCache.find(s => s.id == staffId); if (!staff) return;
    document.getElementById('viewModalTitleText').textContent = `Staff Details: ${escapeHtml(staff.name)}`;
    document.getElementById('viewModalIconContainer').innerHTML = `<i class="fas fa-user-tie text-teal-600 text-2xl"></i>`;
    document.getElementById('viewModalDetails').innerHTML = `<p><strong>ID:</strong> ${staff.id}</p> <p><strong>Username:</strong> ${escapeHtml(staff.username)}</p> <p><strong>Name:</strong> ${escapeHtml(staff.name)}</p> <p><strong>Role:</strong> ${escapeHtml(staff.role)}</p> <p><strong>Email:</strong> ${escapeHtml(staff.email)}</p> <p><strong>Mobile:</strong> ${escapeHtml(staff.mobile) || 'N/A'}</p> <p><strong>Created At:</strong> ${formatDateTime(staff.created_at)}</p>`;
    document.getElementById('viewModal').classList.remove('hidden');
}
async function deleteStaff(staffId, staffName) { 
    if (!confirm(`Delete staff "${staffName}"?`)) return;
    try {
        const response = await fetch(`${API_BASE_URL}delete_staff.php`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: staffId }) });
        if (!response.ok) { const errorMsg = await handleFetchError(response, "deleting staff"); throw new Error(errorMsg); }
        const result = await response.json();
        if (result.success) { alert(result.message); fetchStaffData(); } else { throw new Error(result.message || 'Unknown error'); }
    } catch (error) { alert(`Error: ${error.message}`); console.error(error); }
}

// --- Patient Management (CRUD) ---
let patientDataCache = []; let patientCurrentPage = 1; let patientEntriesPerPage = 10;
let patientCurrentSearchTerm = ''; let patientCurrentFilterStatus = '';
let patientSortBy = 'appointment_date'; let patientSortDir = 'DESC';

function initializePatientManagement() { 
    const patientShowEntriesSelect = document.getElementById('patientShowEntries');
    const patientFilterStatusSelect = document.getElementById('patientFilterStatus');
    const patientSearchInput = document.getElementById('patientSearchInput');
    const patientHeaders = document.querySelectorAll('#patientManagementSection th.sortable');

    patientEntriesPerPage = parseInt(patientShowEntriesSelect.value);
    patientShowEntriesSelect.addEventListener('change', () => { patientEntriesPerPage = parseInt(patientShowEntriesSelect.value); patientCurrentPage = 1; fetchPatientData(); });
    patientFilterStatusSelect.addEventListener('change', () => { patientCurrentFilterStatus = patientFilterStatusSelect.value; patientCurrentPage = 1; fetchPatientData(); });
    patientSearchInput.addEventListener('input', () => { patientCurrentSearchTerm = patientSearchInput.value; patientCurrentPage = 1; clearTimeout(patientSearchInput.timer); patientSearchInput.timer = setTimeout(fetchPatientData, 300); });
    
    patientHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.column;
            if (patientSortBy === column) patientSortDir = patientSortDir === 'ASC' ? 'DESC' : 'ASC';
            else { patientSortBy = column; patientSortDir = 'ASC'; }
            updateSortIcons(patientHeaders, patientSortBy, patientSortDir); fetchPatientData();
        });
    });
    updateSortIcons(patientHeaders, patientSortBy, patientSortDir); fetchPatientData();
}
async function fetchPatientData() { 
    const loadingMsg = document.getElementById('patientLoadingMessage'); const noResultsMsg = document.getElementById('patientNoResultsMessage'); const tableBody = document.getElementById('patientTableBody');
    if (!loadingMsg || !noResultsMsg || !tableBody) return; 
    loadingMsg.style.display = 'block'; noResultsMsg.style.display = 'none'; tableBody.innerHTML = '';
    const params = new URLSearchParams({ page: patientCurrentPage, limit: patientEntriesPerPage, search: patientCurrentSearchTerm, status: patientCurrentFilterStatus, sort_by: patientSortBy, sort_dir: patientSortDir });
    try {
        const response = await fetch(`${API_BASE_URL}fetch_patients.php?${params.toString()}`);
        if (!response.ok) { const errorMsg = await handleFetchError(response, "fetching patients"); throw new Error(errorMsg); }
        const data = await response.json(); 
        if (data.error) { throw new Error(`API Error: ${data.error}${data.details ? ' - ' + data.details : ''}`); }
        patientDataCache = data.patients; renderPatientTable(data.patients);
        renderPagination('patient', data.total, data.patients.length, patientCurrentPage, patientEntriesPerPage, fetchPatientData);
        if (data.patients.length === 0 && !patientCurrentSearchTerm && !patientCurrentFilterStatus) noResultsMsg.textContent = 'No patients found.';
        else if (data.patients.length === 0) noResultsMsg.textContent = 'No patients found matching your criteria.';
        if (data.patients.length === 0) noResultsMsg.style.display = 'block';

    } catch (error) { 
        console.error('Error in fetchPatientData:', error); 
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-red-500">${escapeHtml(error.message)}</td></tr>`;
    } finally { loadingMsg.style.display = 'none'; }
}
function renderPatientTable(patientList) { 
    const tableBody = document.getElementById('patientTableBody'); tableBody.innerHTML = ''; if (!patientList || patientList.length === 0) return;
    patientList.forEach(patient => {
        const row = tableBody.insertRow();
        const statusClass = `status-${patient.status ? patient.status.toLowerCase().replace(/\s+/g, '-') : 'unknown'}`;
        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(patient.patient_no)}</td>
            <td class="px-4 py-3 text-sm text-gray-900 font-medium">${escapeHtml(patient.name)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${formatDate(patient.appointment_date)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${formatTime(patient.appointment_time)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(patient.treatment)}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${escapeHtml(patient.patient_type)}</td>
            <td class="px-4 py-3 text-sm text-gray-700"><span class="status-badge ${statusClass}">${escapeHtml(patient.status)}</span></td>
            <td class="px-4 py-3 text-sm text-gray-700 truncate max-w-xs" title="${escapeHtml(patient.reason)}">${escapeHtml(patient.reason) || 'N/A'}</td>
            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                <button class="action-btn view-btn" onclick="viewPatientDetails(${patient.id})"><i class="fas fa-eye sm:mr-1"></i><span class="hidden sm:inline">View</span></button>
                <button class="action-btn edit-btn" onclick="openPatientModal(${patient.id}, true)"><i class="fas fa-edit sm:mr-1"></i><span class="hidden sm:inline">Edit</span></button>
                <button class="action-btn delete-btn" onclick="deletePatient(${patient.id}, '${escapeHtml(patient.name)}')"><i class="fas fa-trash sm:mr-1"></i><span class="hidden sm:inline">Delete</span></button>
            </td>`;
    });
}
function openPatientModal(patientId = null, isEdit = false) {
    const modal = document.getElementById('patientFormModal'); const form = document.getElementById('patientForm');
    const title = document.getElementById('patientModalTitleText'); form.reset(); document.getElementById('patientId').value = '';
    if (isEdit && patientId) {
        title.textContent = 'Edit Patient Record';
        const patient = patientDataCache.find(p => p.id == patientId);
        if (patient) {
            document.getElementById('patientId').value = patient.id;
            document.getElementById('patientNo').value = patient.patient_no;
            document.getElementById('patientName').value = patient.name;
            document.getElementById('patientBirthDate').value = patient.birth_date ? patient.birth_date.split('T')[0] : '';
            document.getElementById('patientPhone').value = patient.phone || '';
            document.getElementById('patientEmail').value = patient.email || '';
            document.getElementById('patientAddress').value = patient.address || '';
            document.getElementById('patientAppointmentDate').value = patient.appointment_date ? patient.appointment_date.split('T')[0] : '';
            document.getElementById('patientAppointmentTime').value = patient.appointment_time || '';
            document.getElementById('patientTreatment').value = patient.treatment;
            document.getElementById('patientType').value = patient.patient_type;
            document.getElementById('patientStatus').value = patient.status;
            document.getElementById('patientReason').value = patient.reason || '';
            document.getElementById('patientNotes').value = patient.notes || '';
        } else { alert('Patient record not found for editing.'); return; }
    } else { title.textContent = 'Add New Patient Record'; }
    modal.classList.remove('hidden');
}
async function handlePatientFormSubmit(event) {
    event.preventDefault(); const form = event.target; const formData = new FormData(form);
    const patientId = formData.get('id'); const isEdit = !!patientId;
    const apiUrl = isEdit ? `${API_BASE_URL}edit_patient.php` : `${API_BASE_URL}add_patient.php`;
    const data = Object.fromEntries(formData.entries());
    try {
        const response = await fetch(apiUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        if (!response.ok) { const errorMsg = await handleFetchError(response, isEdit ? "editing patient" : "adding patient"); throw new Error(errorMsg); }
        const result = await response.json();
        if (result.success) {
            alert(result.message); document.getElementById('patientFormModal').classList.add('hidden'); fetchPatientData(); 
        } else { alert(`Error: ${result.message || 'Unknown error from server.'}`); }
    } catch (error) { alert(`Submission failed: ${error.message}`); console.error('Patient form error:', error); }
}
function viewPatientDetails(patientId) { 
    const patient = patientDataCache.find(p => p.id == patientId); if (!patient) return;
    document.getElementById('viewModalTitleText').textContent = `Patient Details: ${escapeHtml(patient.name)}`;
    document.getElementById('viewModalIconContainer').innerHTML = `<i class="fas fa-user-injured text-teal-600 text-2xl"></i>`;
    document.getElementById('viewModalDetails').innerHTML = `
        <p><strong>Patient No:</strong> ${escapeHtml(patient.patient_no)}</p> <p><strong>Name:</strong> ${escapeHtml(patient.name)}</p>
        <p><strong>Email:</strong> ${escapeHtml(patient.email || 'N/A')}</p> <p><strong>Phone:</strong> ${escapeHtml(patient.phone || 'N/A')}</p>
        <p><strong>Address:</strong> ${escapeHtml(patient.address || 'N/A')}</p> <p><strong>Birth Date:</strong> ${formatDate(patient.birth_date)}</p>
        <hr class="my-2">
        <p><strong>Appointment Date:</strong> ${formatDate(patient.appointment_date)}</p> <p><strong>Time:</strong> ${formatTime(patient.appointment_time)}</p>
        <p><strong>Treatment:</strong> ${escapeHtml(patient.treatment)}</p> <p><strong>Type:</strong> ${escapeHtml(patient.patient_type)}</p>
        <p><strong>Status:</strong> <span class="status-badge status-${patient.status ? patient.status.toLowerCase().replace(/\s+/g, '-') : 'unknown'}">${escapeHtml(patient.status)}</span></p>
        <p><strong>Reason:</strong> ${escapeHtml(patient.reason || 'N/A')}</p> <p><strong>Notes:</strong> ${escapeHtml(patient.notes || 'N/A')}</p>
        <p><strong>Registered At:</strong> ${formatDateTime(patient.created_at)}</p>`;
    document.getElementById('viewModal').classList.remove('hidden');
}
async function deletePatient(patientId, patientName) { 
    if (!confirm(`Delete patient "${patientName}"?`)) return;
    try {
        const response = await fetch(`${API_BASE_URL}delete_patient.php`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: patientId }) });
        if (!response.ok) { const errorMsg = await handleFetchError(response, "deleting patient"); throw new Error(errorMsg); }
        const result = await response.json();
        if (result.success) { alert(result.message); fetchPatientData(); } else { throw new Error(result.message || 'Unknown error'); }
    } catch (error) { alert(`Error: ${error.message}`); console.error(error); }
}

function updateSortIcons(headersNodeList, currentSortBy, currentSortDir) { 
    headersNodeList.forEach(header => {
        const icon = header.querySelector('.sort-icon');
        if (icon) { 
            if (header.dataset.column === currentSortBy) {
                icon.classList.remove('fa-sort', 'fa-sort-up', 'fa-sort-down');
                icon.classList.add(currentSortDir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down');
                icon.classList.add('active');
            } else {
                icon.classList.remove('fa-sort-up', 'fa-sort-down', 'active');
                icon.classList.add('fa-sort');
            }
        }
    });
}
function renderPagination(type, totalItems, itemsOnPage, currentPage, entriesPerPage, fetchDataFunction) { 
    const paginationControls = document.getElementById(`${type}PaginationControls`); const paginationInfo = document.getElementById(`${type}PaginationInfo`);
    if (!paginationControls || !paginationInfo) return; 
    const totalPages = Math.ceil(totalItems / entriesPerPage); paginationControls.innerHTML = '';
    if (totalItems === 0) { paginationInfo.innerHTML = `Showing 0 to 0 of 0 entries`; return; }
    if (totalPages <= 1 && totalItems > 0) { paginationInfo.innerHTML = `Showing 1 to ${itemsOnPage} of ${totalItems} entries`; return; }
    const createPageButton = (text, pageNum, isDisabled = false, isCurrent = false, isIcon = false) => {
        const button = document.createElement('button'); button.innerHTML = text; 
        button.className = `relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50`;
        if (isIcon) button.className += ' px-2'; 
        if (isCurrent) button.className = `relative inline-flex items-center px-3 py-2 border border-teal-500 bg-teal-50 text-sm font-medium text-teal-600 z-10`;
        if (isDisabled) { button.disabled = true; button.classList.add('opacity-50', 'cursor-not-allowed'); } 
        else { button.addEventListener('click', () => { if (type === 'staff') staffCurrentPage = pageNum; else if (type === 'patient') patientCurrentPage = pageNum; fetchDataFunction(); }); }
        return button;
    };
    paginationControls.appendChild(createPageButton('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1, false, true));
    let addedEllipsisStart = false; let addedEllipsisEnd = false;
    for (let i = 1; i <= totalPages; i++) {
        const showPage = totalPages <= 7 || (i <= 2) || (i >= totalPages - 1) || (i >= currentPage - 1 && i <= currentPage + 1); 
        if (showPage) {
            paginationControls.appendChild(createPageButton(i, i, false, currentPage === i));
            if (i < currentPage - 1 && addedEllipsisStart) addedEllipsisStart = false; 
            if (i > currentPage + 1 && addedEllipsisEnd) addedEllipsisEnd = false; 
        } else {
            if (i < currentPage && !addedEllipsisStart) { const ellipsis = document.createElement('span'); ellipsis.textContent = '...'; ellipsis.className = 'relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700'; paginationControls.appendChild(ellipsis); addedEllipsisStart = true; } 
            else if (i > currentPage && !addedEllipsisEnd) { const ellipsis = document.createElement('span'); ellipsis.textContent = '...'; ellipsis.className = 'relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700'; paginationControls.appendChild(ellipsis); addedEllipsisEnd = true; }
        }
    }
    paginationControls.appendChild(createPageButton('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages, false, true));
    const startEntry = totalItems > 0 ? (currentPage - 1) * entriesPerPage + 1 : 0;
    const endEntry = totalItems > 0 ? Math.min(startEntry + itemsOnPage - 1, totalItems) : 0;
    paginationInfo.innerHTML = `Showing ${startEntry} to ${endEntry} of ${totalItems} entries`;
}

</script>
</body>
</html>
