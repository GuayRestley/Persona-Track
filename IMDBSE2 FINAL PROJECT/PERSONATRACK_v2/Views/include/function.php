
<?php
// includes/functions.php

// Redirect to another page
function redirect($page) {
    if (!headers_sent()) {
        header("Location: " . BASE_URL . $page);
        exit;
    } else {
        echo "<script>window.location.href='" . BASE_URL . $page . "';</script>";
        exit;
    }
}

// Set flash message
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

// Get and clear flash message
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Display flash message HTML
function display_flash_message() {
    $flash = get_flash_message();
    if ($flash) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        $class = $alertClass[$flash['type']] ?? 'alert-info';
        
        return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($flash['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }
    return '';
}

// Format date
function format_date($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date == '0000-00-00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

// Format datetime
function format_datetime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

// Calculate age from birth date
function calculate_age($birth_date) {
    $dob = new DateTime($birth_date);
    $now = new DateTime();
    return $now->diff($dob)->y;
}

// Pagination helper
function paginate($total_records, $current_page = 1, $records_per_page = RECORDS_PER_PAGE) {
    $total_pages = ceil($total_records / $records_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_records' => $total_records,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'records_per_page' => $records_per_page,
        'offset' => $offset,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

// Generate pagination HTML
function pagination_html($pagination, $base_url) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    $prev_class = $pagination['has_prev'] ? '' : 'disabled';
    $prev_page = max(1, $pagination['current_page'] - 1);
    $html .= '<li class="page-item ' . $prev_class . '">';
    $html .= '<a class="page-link" href="' . $base_url . '?page=' . $prev_page . '">Previous</a>';
    $html .= '</li>';
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active_class = ($i == $pagination['current_page']) ? 'active' : '';
        $html .= '<li class="page-item ' . $active_class . '">';
        $html .= '<a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Next button
    $next_class = $pagination['has_next'] ? '' : 'disabled';
    $next_page = min($pagination['total_pages'], $pagination['current_page'] + 1);
    $html .= '<li class="page-item ' . $next_class . '">';
    $html .= '<a class="page-link" href="' . $base_url . '?page=' . $next_page . '">Next</a>';
    $html .= '</li>';
    
    $html .= '</ul></nav>';
    return $html;
}

// Get request parameter
function get_param($key, $default = null) {
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

// Get POST parameter
function post_param($key, $default = null) {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

// Debug function
function debug($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($die) die();
}

// Generate dropdown options
function generate_options($items, $value_field, $text_field, $selected = null) {
    $html = '<option value="">-- Select --</option>';
    foreach ($items as $item) {
        $selected_attr = ($item[$value_field] == $selected) ? 'selected' : '';
        $html .= '<option value="' . htmlspecialchars($item[$value_field]) . '" ' . $selected_attr . '>';
        $html .= htmlspecialchars($item[$text_field]);
        $html .= '</option>';
    }
    return $html;
}

// Status badge
function status_badge($status) {
    $class = ($status == 'Active') ? 'bg-success' : 'bg-danger';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
}

// Get full name
function get_full_name($first_name, $last_name) {
    return trim($first_name . ' ' . $last_name);
}
