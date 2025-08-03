<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contacts') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="text-gray-900">
                    <div class="max-w-8xl mx-auto py-10 sm:px-6 lg:px-8">

                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-3xl font-bold text-gray-900">Contacts</h1>
                            <div class="flex space-x-2">
                                <button id="openMergeModalBtn" class="inline-flex items-center px-2 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Merge Contact
                                </button>
                                <button id="openAddContactModal" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Add Contact
                                </button>
                            </div>
                        </div>

                        <form id="filterForm" class="mb-6 flex flex-wrap gap-4">
                            <input type="text" name="search" placeholder="Search Name, Email or Phone" value="{{ request('search') }}" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 flex-grow min-w-[200px]" />
                            <input type="text" name="name" placeholder="Name" value="{{ request('name') }}" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 flex-grow min-w-[200px]" />
                            <input type="text" name="email" placeholder="Email" value="{{ request('email') }}" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 flex-grow min-w-[200px]" />
                            <input type="text" name="phone" placeholder="Phone" value="{{ request('phone') }}" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 flex-grow min-w-[200px]" />
                            <select name="gender" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 w-40">
                                <option value="">Select Gender</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <select name="status" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 w-40">
                                <option value="">Select Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <input type="text" name="custom_field" placeholder="Custom Field" value="{{ request('custom_field') }}" class="rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 flex-grow min-w-[200px]" />
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Filter
                            </button>
                        </form>

                        <div id="contactsTableContainer">
                            @include('contacts._table')
                        </div>

                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script>
                            $(document).ready(function() {
                                var mergeContactId = null;
                                var contactsList = @json($contacts);
                                
                                // Contact Modal Functions
                                const contactModal = $('#contactModal');
                                const contactModalTitle = $('#contactModalTitle');
                                const contactModalContent = $('#contactModalContent');
                                const closeContactModal = $('#closeContactModal');
                                
                                // Open Add Contact Modal
                                $('#openAddContactModal').off('click').on('click', function() {
                                    contactModalTitle.text('Add Contact');
                                    contactModalContent.html('<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div></div>');
                                    contactModal.removeClass('hidden').addClass('flex');
                                    
                                    $.get('{{ route("contacts.create") }}', function(data) {
                                        contactModalContent.html(data);
                                        // Reinitialize any JavaScript needed for the form
                                        initializeContactForm();
                                    }).fail(function() {
                                        contactModalContent.html('<div class="text-red-500">Error loading form. Please try again.</div>');
                                    });
                                });
                                
                                // Open Edit Contact Modal
                                $(document).on('click', '.edit-contact', function(e) {
                                    e.preventDefault();
                                    const contactId = $(this).data('contact-id');
                                    const contactName = $(this).data('contact-name');
                                    
                                    contactModalTitle.text('Edit Contact: ' + contactName);
                                    contactModalContent.html('<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div></div>');
                                    contactModal.removeClass('hidden').addClass('flex');
                                    
                                    $.get('/contacts/' + contactId + '/edit', function(data) {
                                        contactModalContent.html(data);
                                        // Reinitialize any JavaScript needed for the form
                                        initializeContactForm();
                                    }).fail(function() {
                                        contactModalContent.html('<div class="text-red-500">Error loading form. Please try again.</div>');
                                    });
                                });
                                
                                // Open View Contact Modal
                                $(document).on('click', '.view-contact', function(e) {
                                    e.preventDefault();
                                    const contactId = $(this).data('contact-id');
                                    const contactName = $(this).data('contact-name');
                                    
                                    contactModalTitle.text('View Contact: ' + contactName);
                                    contactModalContent.html('<div class="flex justify-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div></div>');
                                    contactModal.removeClass('hidden').addClass('flex');
                                    
                                    $.get('/contacts/' + contactId, function(data) {
                                        contactModalContent.html(data);
                                    }).fail(function() {
                                        contactModalContent.html('<div class="text-red-500">Error loading contact details. Please try again.</div>');
                                    });
                                });
                                
                                // Close Contact Modal
                                closeContactModal.off('click').on('click', function() {
                                    contactModal.addClass('hidden').removeClass('flex');
                                });
                                
                                // Close modal when clicking outside
                                $(window).on('click', function(e) {
                                    if (e.target === contactModal[0]) {
                                        contactModal.addClass('hidden').removeClass('flex');
                                    }
                                });
                                
                                // Initialize contact form functionality
                                function initializeContactForm() {
                                    // Initialize custom field index based on existing fields
                                    let customFieldIndex = $('#customFieldsContainer .custom-field').length;

                                    // Add event listener for the "Add Custom Field" button
                                    $('#addCustomFieldBtn').off('click').on('click', function () {
                                        const container = $('#customFieldsContainer');
                                        const newField = $(`
                                            <div class="custom-field mb-4">
                                                <input type="text" name="custom_fields[${customFieldIndex}][field_name]" placeholder="Field Name" class="mb-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" required>
                                                <input type="text" name="custom_fields[${customFieldIndex}][field_value]" placeholder="Field Value" class="block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
                                                <button type="button" class="mt-1 inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 removeCustomField">Remove</button>
                                            </div>
                                        `);
                                        container.append(newField);
                                        customFieldIndex++;

                                        // Add event listener to the remove button of the new field
                                        newField.find('.removeCustomField').off('click').on('click', function () {
                                            newField.remove();
                                            // Re-index all remaining fields to ensure proper indexing
                                            reindexCustomFields();
                                        });
                                    });

                                    // Add event listeners to existing remove buttons
                                    $('.removeCustomField').off('click').on('click', function () {
                                        $(this).parent().remove();
                                        // Re-index all remaining fields to ensure proper indexing
                                        reindexCustomFields();
                                    });

                                    // Function to re-index all custom fields to ensure proper indexing
                                    function reindexCustomFields() {
                                        const customFields = $('#customFieldsContainer .custom-field');
                                        customFields.each(function (index) {
                                            const fieldNameInput = $(this).find('input[name*="[field_name]"]');
                                            const fieldValueInput = $(this).find('input[name*="[field_value]"]');
                                            
                                            if (fieldNameInput.length) {
                                                fieldNameInput.attr('name', `custom_fields[${index}][field_name]`);
                                            }
                                            if (fieldValueInput.length) {
                                                fieldValueInput.attr('name', `custom_fields[${index}][field_value]`);
                                            }
                                        });
                                        
                                        // Update the global index to the number of remaining fields
                                        customFieldIndex = customFields.length;
                                    }

                                    // AJAX form submission
                                    $('#contactForm').off('submit').on('submit', function(e) {
                                        e.preventDefault();
                                        const form = $(this);
                                        const url = form.attr('action');
                                        const method = form.find('input[name="_method"]').val() || form.attr('method') || 'POST';
                                        
                                        // Show loader
                                        const formMessage = $('#formMessage');
                                        formMessage.removeClass('hidden');
                                        formMessage.text('Processing...');
                                        formMessage.removeClass('bg-green-100 text-green-800 bg-red-100 text-red-800');
                                        formMessage.addClass('bg-blue-100 text-blue-800');
                                        
                                        // Create FormData object properly
                                        const formData = new FormData(form[0]);
                                        
                                        $.ajaxSetup({
                                            headers: {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                            }
                                        });
                                        
                                        // Handle Laravel method spoofing for PUT/PATCH requests
                                        if (method.toUpperCase() === 'PUT' || method.toUpperCase() === 'PATCH') {
                                            formData.append('_method', method.toUpperCase());
                                        }
                                        
                                        $.ajax({
                                            url: url,
                                            type: 'POST',
                                            data: formData,
                                            processData: false,
                                            contentType: false,
                                            success: function(response) {
                                                formMessage.removeClass('bg-blue-100 text-blue-800').addClass('bg-green-100 text-green-800');
                                                formMessage.text(response.message || 'Contact saved successfully!');
                                                
                                                // Close the modal and refresh the contacts table without full page refresh
                                                setTimeout(function() {
                                                    $('#contactModal').addClass('hidden').removeClass('flex');
                                                    // Reload the contacts table via AJAX
                                                    $.get(window.location.href + '?component=table', function(data) {
                                                        $('#contactsTableContainer').html(data);
                                                    });
                                                }, 1000);
                                            },
                                            error: function(xhr) {
                                                formMessage.removeClass('bg-blue-100 text-blue-800').addClass('bg-red-100 text-red-800');
                                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                                    let errorMessages = '';
                                                    $.each(xhr.responseJSON.errors, function(key, value) {
                                                        errorMessages += value[0] + ' ';
                                                    });
                                                    formMessage.text(errorMessages);
                                                } else {
                                                    formMessage.text('An error occurred. Please try again.');
                                                }
                                            }
                                        });
                                    });
                                }

                                $('.merge-contact').off('click').on('click', function(e) {
                                    e.preventDefault();
                                    mergeContactId = $(this).data('contact-id');

                                    // Populate master contact select excluding the contact to merge
                                    var options = '<option value="">Select master contact</option>';
                                    contactsList.data.forEach(function(contact) {
                                        if(contact.id !== mergeContactId && contact.is_active) {
                                            options += '<option value="' + contact.id + '">' + contact.name + ' (' + contact.email + ')</option>';
                                        }
                                    });
                                    $('#masterContactSelect').html(options);

                                    $('#mergeModal').removeClass('hidden').addClass('flex');
                                });

                                $('#cancelMergeBtn').off('click').on('click', function() {
                                    $('#mergeModal').addClass('hidden').removeClass('flex');
                                    mergeContactId = null;
                                });

                                $('#confirmMergeBtn').off('click').on('click', function() {
                                    var masterContactId = $('#masterContactSelect').val();
                                    if(!masterContactId) {
                                        alert('Please select a master contact.');
                                        return;
                                    }

                                    // Confirm before merging
                                    // Remove browser alert and use custom modal instead
                                    $('#mergeModal').addClass('hidden').removeClass('flex');
                                    $('#mergeConfirmModal').removeClass('hidden').addClass('flex');
                                    return;
                                });

                                // New Merge Modal handlers
                                $('#openMergeModalBtn').off('click').on('click', function() {
                                    // Populate both dropdowns with contacts (only active contacts)
                                    var options = '<option value="">Select contact</option>';
                                    contactsList.data.forEach(function(contact) {
                                        if(contact.is_active) {
                                            options += '<option value="' + contact.id + '">' + contact.name + ' (' + contact.email + ')</option>';
                                        }
                                    });
                                    $('#secondaryContactSelect').html(options);
                                    $('#masterContactSelectNew').html(options);

                                    $('#newMergeModal').removeClass('hidden').addClass('flex');
                                });

                                $('#cancelNewMergeBtn').off('click').on('click', function() {
                                    $('#newMergeModal').addClass('hidden').removeClass('flex');
                                });

                                $('#confirmNewMergeBtn').off('click').on('click', function() {
                                    var secondaryId = $('#secondaryContactSelect').val();
                                    var masterId = $('#masterContactSelectNew').val();

                                    if(!secondaryId || !masterId) {
                                        alert('Please select both contacts to merge.');
                                        return;
                                    }
                                    if(secondaryId === masterId) {
                                        alert('Master contact cannot be the same as the contact to merge.');
                                        return;
                                    }

                                    // Set mergeContactId and open existing confirmation modal
                                    mergeContactId = secondaryId;
                                    $('#newMergeModal').addClass('hidden').removeClass('flex');
                                    $('#mergeConfirmModal').removeClass('hidden').addClass('flex');

                                    // Store masterId in a hidden input or variable for use in mergeConfirmBtn handler
                                    $('#mergeConfirmBtn').data('master-id', masterId);
                                });

                                // Custom merge confirmation modal buttons
                                $('#mergeConfirmCancelBtn').off('click').on('click', function() {
                                    $('#mergeConfirmModal').addClass('hidden').removeClass('flex');
                                    $('#mergeModal').removeClass('hidden').addClass('flex');
                                });

                                $('#mergeConfirmBtn').off('click').on('click', function() {
                                    var masterContactId = $(this).data('master-id') || $('#masterContactSelect').val();
                                    if(!masterContactId) {
                                        alert('Please select a master contact.');
                                        return;
                                    }

                                    // Hide the confirmation modal and show the loader
                                    $('#mergeConfirmModal').addClass('hidden').removeClass('flex');
                                    $('#mergeLoader').removeClass('hidden').addClass('flex');

                                    // Submit merge request via POST
                                    $.ajax({
                                        url: '/contacts/' + mergeContactId + '/merge',
                                        type: 'POST',
                                        data: {
                                            master_contact_id: masterContactId,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function(response) {
                                            // Hide the loader
                                            $('#mergeLoader').addClass('hidden').removeClass('flex');
                                            
                                            // Remove alert and show success message at bottom right
                                            var formMessage = $('#formMessage');
                                            if(formMessage.length === 0) {
                                                formMessage = $('<div id="formMessage" class="fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm bg-green-100 text-green-800"></div>');
                                                $('body').append(formMessage);
                                            }
                                            formMessage.text('Contacts merged successfully.');

                                            // After showing message, reload only the contacts table
                                            formMessage.show();
                                            setTimeout(function() {
                                                formMessage.hide();
                                                // Reload the contacts table via AJAX
                                                $.get(window.location.href + '?component=table', function(data) {
                                                    $('#contactsTableContainer').html(data);
                                                }).fail(function() {
                                                    // If AJAX fails, fallback to full page refresh
                                                    window.location.href = '{{ route("contacts.index") }}';
                                                });
                                            }, 3000);
                                        },
                                        error: function(xhr) {
                                            // Hide the loader and show error
                                            $('#mergeLoader').addClass('hidden').removeClass('flex');
                                            alert('Error merging contacts: ' + xhr.responseJSON.message || 'Unknown error');
                                        }
                                    }); 
                                });

                                // Delete confirmation and AJAX delete
                                $(document).on('click', '.delete-button', function(e) {
                                
                                    e.preventDefault();
                                    var form = $(this).closest('form');
                                    var contactId = form.find('button.delete-button').data('contact-id');
                                    
                                    $('#deleteContactId').val(contactId);
                                    $('#deleteConfirmModal').removeClass('hidden').addClass('flex');

                                    $('#deleteConfirmCancelBtn').off('click').on('click', function() {
                                        $('#deleteConfirmModal').addClass('hidden').removeClass('flex');
                                    });

                                    $('#deleteConfirmBtn').off('click').on('click', function() {
                                        var contactId = $('#deleteContactId').val();
                                        var form = $('#deleteForm-' + contactId);
                                        
                                        // Hide the confirmation modal and show the loader
                                        $('#deleteConfirmModal').addClass('hidden').removeClass('flex');
                                        $('#deleteLoader').removeClass('hidden').addClass('flex');
                                        
                                        // Add CSRF token to the form data
                                        const formData = form.serialize();
                                        const csrfToken = $('meta[name="csrf-token"]').attr('content');
                                        
                                        $.ajaxSetup({
                                            headers: {
                                                'X-CSRF-TOKEN': csrfToken
                                            }
                                        });
                                        
                                        $.ajax({
                                            url: form.attr('action'),
                                            type: 'POST',
                                            data: formData + '&_token=' + csrfToken,
                                            success: function(response) {
                                                // Hide the loader
                                                $('#deleteLoader').addClass('hidden').removeClass('flex');
                                                
                                                var formMessage = $('#formMessage');
                                                if(formMessage.length === 0) {
                                                    formMessage = $('<div id="formMessage" class="fixed bottom-5 right-5 max-w-xs p-4 rounded shadow-lg text-sm bg-green-100 text-green-800"></div>');
                                                    $('body').append(formMessage);
                                                }
                                                formMessage.text('Contact deleted successfully.');
                                                formMessage.show();
                                                setTimeout(function() {
                                                    formMessage.hide();
                                                    // Reload the contacts table via AJAX
                                                    $.get(window.location.href + '?component=table', function(data) {
                                                        $('#contactsTableContainer').html(data);
                                                    });
                                                }, 3000);
                                            },
                                            error: function(xhr) {
                                                // Hide the loader and show error
                                                $('#deleteLoader').addClass('hidden').removeClass('flex');
                                                alert('Error deleting contact: ' + xhr.responseJSON.message || 'Unknown error');
                                            }
                                        });
                                    });
                                });

                                // AJAX filter form submission
                                $('#filterForm').on('submit', function(e) {
                                    e.preventDefault();
                                    var form = $(this);

                                    // Validate gender select
                                    // var genderVal = form.find('select[name="gender"]').val();
                                    // if(genderVal === '') {
                                    //     alert('Please select a gender option.');
                                    //     return;
                                    // }

                                    $.ajax({
                                        url: form.attr('action') || window.location.href,
                                        type: 'GET',
                                        data: form.serialize() + '&component=table&page=1',
                                        success: function(data) {
                                            $('#contactsTableContainer').html(data);
                                        },
                                        error: function(xhr) {
                                            alert('Error filtering contacts: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
                                        }
                                    });
                                });

                                // Handle pagination links click
                                $(document).on('click', '#contactsTableContainer .pagination a', function(e) {
                                    e.preventDefault();
                                    var url = $(this).attr('href');
                                    
                                    // Get current filter values
                                    var filterData = $('#filterForm').serialize();
                                    
                                    // Append filter data to pagination URL
                                    if (filterData) {
                                        // Check if URL already has query parameters
                                        var separator = url.indexOf('?') !== -1 ? '&' : '?';
                                        url += separator + filterData;
                                    }
                                    
                                    $.ajax({
                                        url: url + '&component=table',
                                        type: 'GET',
                                        success: function(data) {
                                            $('#contactsTableContainer').html(data);
                                            
                                            // Re-bind event handlers for new pagination links
                                            bindPaginationHandlers();
                                        },
                                        error: function(xhr) {
                                            alert('Error loading page: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
                                        }
                                    });
                                });
                                
                                // Function to bind pagination handlers (for re-binding after AJAX updates)
                                function bindPaginationHandlers() {
                                    // Remove existing handlers to prevent duplicates
                                    $(document).off('click', '#contactsTableContainer .pagination a');
                                    
                                    // Re-bind pagination click handler
                                    $(document).on('click', '#contactsTableContainer .pagination a', function(e) {
                                        e.preventDefault();
                                        var url = $(this).attr('href');
                                        
                                        // Get current filter values
                                        var filterData = $('#filterForm').serialize();
                                        
                                        // Append filter data to pagination URL
                                        if (filterData) {
                                            // Check if URL already has query parameters
                                            var separator = url.indexOf('?') !== -1 ? '&' : '?';
                                            url += separator + filterData;
                                        }
                                        
                                        $.ajax({
                                            url: url + '&component=table',
                                            type: 'GET',
                                            success: function(data) {
                                                $('#contactsTableContainer').html(data);
                                                
                                                // Re-bind event handlers for new pagination links
                                                bindPaginationHandlers();
                                            },
                                            error: function(xhr) {
                                                alert('Error loading page: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
                                            }
                                        });
                                    });
                                }
                            });
                        </script>

                        <!-- Merge Modal -->
                        <div id="mergeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                            <div class="bg-white rounded-lg p-6 w-96">
                                <h2 class="text-lg font-semibold mb-4">Select Master Contact</h2>
                                <select id="masterContactSelect" class="w-full border border-gray-300 rounded-md p-2 mb-4">
                                    <!-- Options will be populated dynamically -->
                                </select>
                                <div class="flex justify-end space-x-4">
                                    <button id="cancelMergeBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                                    <button id="confirmMergeBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Merge</button>
                                </div>
                            </div>
                        </div>

                        <!-- New Merge Selection Modal -->
                        <div id="newMergeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                            <div class="bg-white rounded-lg p-6 w-96">
                                <h2 class="text-lg font-semibold mb-4">Select Contacts to Merge</h2>
                                <label for="secondaryContactSelect" class="block mb-1 font-medium">Contact to Merge</label>
                                <select id="secondaryContactSelect" class="w-full border border-gray-300 rounded-md p-2 mb-4">
                                    <option value="">Select contact</option>
                                </select>
                                <label for="masterContactSelectNew" class="block mb-1 font-medium">Master Contact</label>
                                <select id="masterContactSelectNew" class="w-full border border-gray-300 rounded-md p-2 mb-4">
                                    <option value="">Select master contact</option>
                                </select>
                                <div class="flex justify-end space-x-4">
                                    <button id="cancelNewMergeBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                                    <button id="confirmNewMergeBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Next</button>
                                </div>
                            </div>
                        </div>

                        <!-- Merge Confirmation Modal -->
                        <div id="mergeConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                            <div class="bg-white rounded-lg p-6 w-96">
                                <h2 class="text-lg font-semibold mb-4">Confirm Merge</h2>
                                <p>Are you sure you want to merge the selected contacts?</p>
                                <div class="flex justify-end space-x-4 mt-4">
                                    <button id="mergeConfirmCancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                                    <button id="mergeConfirmBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Confirm</button>
                                </div>
                            </div>
                        </div>
                        <!-- Delete Confirmation Modal -->
                        <input type="hidden" id="deleteContactId" value="">

                        <div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                            <div class="bg-white rounded-lg p-6 w-96">
                                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
                                <p>Are you sure you want to delete this contact?</p>
                                <div class="flex justify-end space-x-4 mt-4">
                                    <button id="deleteConfirmCancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                                    <button id="deleteConfirmBtn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                                </div>
                            </div>
                        </div>

                        <!-- Merge Loader -->
                        <div id="mergeLoader" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                            <div class="bg-white rounded-lg p-6 w-96 text-center">
                                <div class="flex justify-center mb-4">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                                </div>
                                <h2 class="text-lg font-semibold mb-2">Merging Contacts</h2>
                                <p>Please wait while we merge your contacts...</p>
                            </div>
                        </div>

                        <!-- Delete Loader -->
                        <div id="deleteLoader" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                            <div class="bg-white rounded-lg p-6 w-96 text-center">
                                <div class="flex justify-center mb-4">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                                </div>
                                <h2 class="text-lg font-semibold mb-2">Deleting Contact</h2>
                                <p>Please wait while we delete your contact...</p>
                            </div>
                        </div>

                        <!-- Contact Modal -->
                        <div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                            <div class="bg-white rounded-lg my-8 w-full max-w-4xl mx-4 max-h-[90vh] flex flex-col">
                                <div class="flex justify-between items-center border-b px-6 py-4">
                                    <h2 class="text-xl font-semibold" id="contactModalTitle">Contact</h2>
                                    <button id="closeContactModal" class="text-gray-500 hover:text-gray-700">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div id="contactModalContent" class="p-6 overflow-y-auto flex-grow"></div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
