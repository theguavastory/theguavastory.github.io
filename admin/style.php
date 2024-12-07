<style>
 body {
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #006769;
            color: #fff;
            padding: 20px;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 1100;
        }
        .sidebar img {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px 0;
            display: flex;
            align-items: center;
        }
        .sidebar a:hover {
            background-color: #1272d3;
            text-decoration: none;
        }
        .sidebar a i {
            margin-right: 10px;
        }
                .close-sidebar-btn {
            background: none;
            border: none;
            color: white; /* Adjust based on your sidebar theme */
            font-size: 1.5rem;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            z-index: 1000;
        }

        .close-sidebar-btn:hover {
            color: #ccc; /* Optional: Add a hover effect */
        }
        .logout-btn {
            margin-top: auto;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease;
        }
        .navbar-custom {
            background-color: #007bff;
            width: 100%;
            position: fixed;
            top: 0;
            left: 250px;
            z-index: 2000;
            padding-right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: left 0.3s ease;
        }
            .navbar-brand {
        display: flex;
        align-items: center;
        font-size: 1.25rem;
    }

    .navbar-toggler i {
        margin-right: 0.5rem; /* Space between the icon and text */
    }
                .navbar-toggler {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: inherit;
            cursor: pointer;
        }
        .navbar-custom .navbar-brand {
            color: #fff;
        }
        .search-bar {
            margin-top: 80px;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex: 1;
        }
        .dashboard {
            margin-top: 20px;
            
        }
        .chart-container {
            position: relative;
            height: 380px;
            width: 100%;
        }
        @media (max-width: 768px) {

    .content {
        margin-left: 0;
        transition: margin-left 0.3s ease;
    }
    .navbar-custom {
        left: 0;
        width: 100%;
    }
    
}
/* Show the close button on small screens (below 768px) */
@media (max-width: 768px) {
    .close-sidebar-btn {
        display: block;
    }
}

/* Hide the close button on larger screens (768px and above) */
@media (min-width: 768px) {
    .close-sidebar-btn {
        display: none;
    }
}

        .user-info {
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Align to the right */
            margin-right: 450px; /* Adjust as needed */
            position: flex; /* Ensure correct placement */
        }


        .user-info .badge {
            background-color: #28a745;
            margin-right: 5px; /* Adjust margin as needed */
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.9em;
            color: #fff;
        }

        .user-info .username {
            color: #fff;
            font-family:Arial, Helvetica, sans-serif ;
        }
        .notification-count {
        display: none; /* Initially hidden */
        background-color: #00999c;
        color: #fff;
        padding: 5px 10px;
        border-radius: 50%;
        font-size: 0.8em;
        position: absolute;
        top: 360px;
        right: 41px;
    }
    

    /* Display the notification count if it's greater than 0 */
    <?php if ($notification_count > 0): ?>
    .notification-count {
        display: inline-block;
    }
    <?php endif; ?>

    /* Define hover style for the notification button */
    .sidebar a:hover .notification-count {
        display: inline-block; /* Show when the notification button is hovered */
    }
    #map {
    height: 400px; /* Fixed height */
    width: 100%;   /* Make sure it fills its container */
        }
        .card {
            margin-bottom: 20px; /* Space out the cards */
        }
        .btn i {
    margin-right: 5px; /* Add space between icon and text */
    font-size: 1.2em; /* Adjust icon size */
}
.map-container {
    position: relative; /* Required for the absolutely positioned button */
}

.map-container .btn {
    position: absolute; /* Absolute positioning within the container */
    top: 10px; /* Adjust the top position */
    right: 10px; /* Adjust the right position */
    z-index: 1000; /* Ensure the button is on top of other elements */
}

.message-count {
        display: none;
        background-color: #e0ae07;
        color: #fff;
        padding: 5px 10px;
        border-radius: 50%;
        font-size: 0.8em;
        position: absolute;
        top: 320px;
        right: 81px;
    }
    
            /* Show message count if greater than 0 */
        <?php if ($unread_message_count > 0): ?> 
        .message-count {
            display: inline-block;
        }
        <?php endif; ?>
        .messages-container {
            margin-top: 20px;
        }

        ./* Inbox Container */
.inbox {
    width: 85%;
    background-color: #f9f9f9;
    height: 83%;
    overflow-y: auto;
    position: fixed;
    top: 10%; /* Adjusted top for better placement */
    transition: left 0.3s ease;
    z-index: 1000;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    padding: 10px;
}

.inbox.show {
    left: 0; /* Show when toggled */
}

.toggle-inbox-btn {
    margin: 10px;
}
/* For small devices */
@media (max-width: 768px) {
    .inbox {
        width: 100%;
        height: 100vh;
        top: 0;
    }
}
/* General styling for message container */
.message-container {
    display: flex;
    flex-direction: column;
    margin-bottom: 10px;
}

/* Styling for sent messages */
.sent .message-bubble {
    background-color: #007bff;
    color: white;
    align-self: flex-end; /* Align to the right */
    border-radius: 12px;
    padding: 8px 12px;
    max-width: 75%; /* Adjust width as needed */
    word-wrap: break-word;
}

/* Styling for received messages */
.received .message-bubble {
    background-color: #f0f0f0;
    color: black;
    align-self: flex-start; /* Align to the left */
    border-radius: 12px;
    padding: 8px 12px;
    max-width: 75%;
    word-wrap: break-word;
}

/* Styling for message time */
.message-time {
    font-size: 12px;
    color: #888;
    margin-top: 5px;
}
/* Conversation Items */
.conversation-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background-color: #fff;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: box-shadow 0.3s ease;
}

.conversation-item:hover {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}

/* Name Styling for Sender */
.conversation-item .sender-name {
    background-color: #007bff;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.9em;
    font-weight: bold;
    white-space: nowrap;
    text-align: center;
}

/* Name Highlight */
.conversation-item strong {
    font-size: 1.1em;
    color: #333;
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

/* Last Message Styling */
.last-message {
    font-size: 0.9em;
    color: #555;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px; /* Limit message width for better appearance */
}



/* Responsive Design */
@media (max-width: 768px) {
    .conversation-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .conversation-item .sender-name {
        align-self: flex-end;
        margin-top: 10px;
    }

    .last-message {
        max-width: 100%;
    }
}



.modal-content {
    padding: 15px;
}

/* Message Display Styling */
.message-display {
    max-height: 400px;
    overflow-y: auto;
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

/* Message Item Styling */
.message-item {
    display: flex;
    margin-bottom: 15px;
    align-items: flex-start;
}

/* Recipient's Messages (on the left) */
.message-item.received {
    justify-content: flex-start;
}

.message-item .message-bubble {
    max-width: 70%;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 5px;
    word-wrap: break-word;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Sender's Messages (on the right) */
.message-item.sent {
    justify-content: flex-end;
}

.message-bubble.sent {
    background-color: #007bff;
    color: white;
    text-align: left;
}

.message-bubble.received {
    background-color: #e4e6eb;
    color: black;
    text-align: left;
}

/* Message Timestamp */
.message-time {
    font-size: 0.8em;
    color: #666;
    margin-top: 5px;
    text-align: right;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .message-item .message-bubble {
        max-width: 100%;
    }
}

.message-input-footer {
    display: flex;
    gap: 10px;
}

.message-input-footer input {
    flex: 1;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.message-input-footer button {
    padding: 10px 15px;
}


        @media print {
    .no-print {
        display: none;
    }
    .actions-column {
        display: none;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
        page-break-inside: auto;
    }
    th {
        background-color: #f2f2f2;
    }
    .header {
        text-align: center;
        margin-bottom: 20px;
    }
    .header img {
        width: 80px; /* Adjust as needed */
        height: auto;
    }
    .header h6, .header h3, .header h4 {
        margin: 0;
    }
    /* Ensure no large gaps between table rows */
    tr {
        page-break-inside: avoid;
    }
}


@media (max-width: 576px) {
        body {
            flex-direction: column;
        }
        .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        margin-top: 15%;
        height: 100%;
        width: 250px;
            color: #fff;
            padding: 20px;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 1100;
    }
    .sidebar.show {
        transform: translateX(0);
    }
    /* Reorder the logout button to be under "My Messages" */
    .sidebar a.logout-btn {
        margin-top: 15px; /* Add margin for spacing */
    }

    .sidebar a.logout-btn {
        order: 10; /* Ensure logout button comes last */
    }
        .content {
            margin-left: 0;
            width: 100%;
            padding: 10px;
            padding-top: 120px;
        }
        .navbar-custom {
            left: 0;
            width: 100%;
            padding: 10px;
        }
        .navbar-custom .navbar-brand {
            font-size: 1em;
        }
        /* Hide user-info section on small devices */
    .user-info {
        display: none;
    }

    /* Reduce the size of the title on small devices */
    .navbar-custom .title {
        font-size: 0.9em; /* Adjust font size as needed */
    }
        .search-bar {
            margin: 10px 0;
           margin-top: 10px;
        }
        .chart-container {
            height: auto;
        }
        .btn {
            font-size: 0.9em;
        }
        .user-info .badge {
            margin-bottom: 5px;
        }
        #map {
            height: 250px;
        }
        .inbox {
            width: 100%;
            left: -100%;
            height: auto;
        }
        .inbox.show {
            left: 0;
        }
        .toggle-inbox-btn {
            margin: 5px;
        }
        .messages-container {
            margin-top: 20px;
        }
        .message-bubble {
            max-width: 90%;
        }
        .notification-count{
            top: 360px;
            right: 45px;
            font-size: 0.7em;
        }
        .message-count {
            top: 315px;
            right: 80px;
            font-size: 0.7em;
        }
        .card {
            margin-bottom: 10px;
        }
        .table-container {
        overflow-x: auto; /* Add horizontal scrolling for the table */
    }

    /* General table styling */
    .table {
        font-size: 0.8em; /* Reduce font size for better fit */
    }

    .table th, 
    .table td {
        white-space: nowrap; /* Prevent wrapping for content */
        padding: 5px; /* Reduce padding for smaller screens */
    }

    /* Show actions on larger screens, hide on smaller screens */
    .table .actions-column {
        display: table-cell; /* Ensure actions are shown on larger screens */
    }

    .table-responsive {
        overflow-x: auto; /* Enable horizontal scrolling */
    }

    .table-container:before {
        content: "Swipe horizontally to view more"; /* Add a hint for horizontal scrolling */
        display: block;
        text-align: center;
        font-size: 0.8em;
        color: #888;
        margin-bottom: 5px;
    }

    /* Adjust font size for table headers */
    .table thead th {
        font-size: 0.9em; /* Slightly larger font for headers */
    }

    .modal-dialog {
     margin-top: 70px; /* Adjust the value as needed */
            }

    .card .btn-warning {
        font-size: 0.75em; /* Slightly smaller font size for small screens */
        padding: 6px 12px; /* Reduce padding for a more compact button */
        margin-top: -10px; /* Move the button slightly higher */
    }

    .card-body {
        padding: 15px; /* Ensure the card content doesn't overflow on small screens */
    }

    .card-title {
        font-size: 1.2em; /* Adjust card title size for small screens */
    }

    .card-text {
        font-size: 0.9em; /* Adjust text size for better readability on small screens */
    }

    .col-lg-6, .col-md-4, .col-sm-12 {
        margin-bottom: 10px; /* Add margin for spacing between cards on small devices */
    }
    .d-flex .btn {
        font-size: 0.8em; /* Smaller font for small screens */
        padding: 4px 8px; /* Reduce padding for a compact look */
        margin-right: 2px; /* Adjust spacing between buttons */
    }

    .d-flex .btn i {
        font-size: 0.9em; /* Adjust icon size */
    }

    .btn-success {
        background-color: #28a745; /* Keep distinct button colors */
        border-color: #28a745;
    }

    .btn-info {
        background-color: #17a2b8; /* Keep distinct button colors */
        border-color: #17a2b8;
    }

    .d-flex {
        justify-content: center; /* Center align the buttons on small devices */
        margin-bottom: 10px; /* Add spacing below the button group */
         }
    }

    /* Media query for small screens */
@media (max-width: 768px) {
    .table {
        font-size: 0.75em; /* Further reduce font size on smaller screens */
    }

    .table th, 
    .table td {
        padding: 8px 5px; /* Adjust padding for small devices */
    }

    /* Enable horizontal scroll and ensure actions are visible */
    .table .actions-column {
        display: table-cell; /* Keep actions visible even on small devices */
    }

    .table-responsive {
        overflow-x: auto; /* Horizontal scroll for smaller screens */
    }

    /* Add horizontal scroll hint */
    .table-container:before {
        content: "Swipe horizontally to view more"; /* Add a hint for horizontal scrolling */
        display: block;
        text-align: center;
        font-size: 0.8em;
        color: #888;
        margin-bottom: 5px;
    }
}

    </style>