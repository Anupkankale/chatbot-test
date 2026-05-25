<div class="wrap devxpert-admin">
    <div class="devxpert-admin-header">
        <div>
            <p class="devxpert-admin-eyebrow">Sales Pipeline</p>
            <h1 class="devxpert-admin-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p class="devxpert-admin-subtitle">Review captured leads, update outreach status, inspect conversation details, and export records for follow-up.</p>
        </div>
        <div class="devxpert-admin-actions">
            <a href="<?php echo esc_url(admin_url('admin-post.php?action=devxpert_export_leads&_wpnonce=' . wp_create_nonce('devxpert_export_leads'))); ?>" class="button button-primary">Export CSV</a>
        </div>
    </div>
    
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'devxpert_chatbot_leads';
    
    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['lead_id'])) {
        check_admin_referer('delete_lead_' . $_GET['lead_id']);
        $wpdb->delete($table_name, array('id' => intval($_GET['lead_id'])), array('%d'));
        echo '<div class="notice notice-success"><p>Lead deleted successfully.</p></div>';
    }
    
    // Handle bulk delete
    if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && !empty($_POST['lead_ids'])) {
        check_admin_referer('devxpert_bulk_action');
        $lead_ids = array_map('intval', $_POST['lead_ids']);
        foreach ($lead_ids as $lead_id) {
            $wpdb->delete($table_name, array('id' => $lead_id), array('%d'));
        }
        echo '<div class="notice notice-success"><p>' . count($lead_ids) . ' lead(s) deleted successfully.</p></div>';
    }
    
    // Get leads with pagination
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_leads / $per_page);
    
    $leads = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
    ?>
    
    <div class="devxpert-stat-grid">
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">Total Leads</p>
            <p class="devxpert-stat-value"><?php echo number_format($total_leads); ?></p>
            <p class="devxpert-stat-note">All submissions stored in the chatbot leads table.</p>
        </div>
        
        <?php
        $today_leads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE DATE(created_at) = CURDATE()");
        ?>
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">Today</p>
            <p class="devxpert-stat-value"><?php echo number_format($today_leads); ?></p>
            <p class="devxpert-stat-note">New leads added on the current server date.</p>
        </div>
        
        <?php
        $week_leads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        ?>
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">This Week</p>
            <p class="devxpert-stat-value"><?php echo number_format($week_leads); ?></p>
            <p class="devxpert-stat-note">Useful for tracking current demand and outreach volume.</p>
        </div>
    </div>
    
    <?php if (empty($leads)): ?>
        <div class="notice notice-info">
            <p>No leads have been submitted yet. When visitors complete the chatbot consultation, their information will appear here.</p>
        </div>
    <?php else: ?>
        
        <div class="devxpert-admin-panel">
        <form method="post" action="">
            <?php wp_nonce_field('devxpert_bulk_action'); ?>
            <input type="hidden" name="action" value="bulk_delete">

            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <button type="submit" class="button action" onclick="return confirm('Are you sure you want to delete selected leads?');">Delete Selected</button>
                </div>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo number_format($total_leads); ?> items</span>
                    <?php if ($total_pages > 1): ?>
                        <span class="pagination-links">
                            <?php if ($page > 1): ?>
                                <a class="prev-page button" href="?page=devxpert-chatbot-leads&paged=<?php echo ($page - 1); ?>">‹</a>
                            <?php endif; ?>
                            <span class="paging-input">
                                <span class="current-page"><?php echo $page; ?></span> of 
                                <span class="total-pages"><?php echo $total_pages; ?></span>
                            </span>
                            <?php if ($page < $total_pages): ?>
                                <a class="next-page button" href="?page=devxpert-chatbot-leads&paged=<?php echo ($page + 1); ?>">›</a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped devxpert-admin-table">
                <thead>
                    <tr>
                        <td class="check-column"><input type="checkbox" id="cb-select-all"></td>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Intent</th>
                        <th>Service Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="lead_ids[]" value="<?php echo esc_attr($lead->id); ?>">
                            </th>
                            <td><strong><?php echo esc_html($lead->name); ?></strong></td>
                            <td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td>
                            <td><?php echo esc_html($lead->company); ?></td>
                            <td><?php echo esc_html($lead->initial_intent); ?></td>
                            <td><?php echo esc_html($lead->service_type); ?></td>
                            <td>
                                <select class="devxpert-lead-status devxpert-admin-pill-select" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                    <?php
                                    $current_status = isset($lead->status) ? $lead->status : 'new';
                                    $statuses = array('new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'lost' => 'Lost');
                                    foreach ($statuses as $val => $label) {
                                        printf(
                                            '<option value="%s"%s>%s</option>',
                                            esc_attr($val),
                                            selected($current_status, $val, false),
                                            esc_html($label)
                                        );
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><?php echo esc_html(date('M j, Y g:i A', strtotime($lead->created_at))); ?></td>
                            <td class="devxpert-admin-inline">
                                <a href="#" class="button button-small view-lead-details" data-lead-id="<?php echo esc_attr($lead->id); ?>">View Details</a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=devxpert-chatbot-leads&action=delete&lead_id=' . $lead->id), 'delete_lead_' . $lead->id); ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                            </td>
                        </tr>
                        
                        <!-- Hidden details row -->
                        <tr id="lead-details-<?php echo esc_attr($lead->id); ?>" class="devxpert-admin-details" style="display: none;">
                            <td colspan="9" style="padding: 20px;">
                                <div class="devxpert-admin-details-grid">
                                    <div>
                                        <span class="devxpert-admin-detail-label">Full Name</span>
                                        <?php echo esc_html($lead->name); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Email</span>
                                        <a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Company</span>
                                        <?php echo esc_html($lead->company); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Location</span>
                                        <?php echo esc_html($lead->location); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Industry</span>
                                        <?php echo esc_html($lead->industry); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Platforms</span>
                                        <?php echo esc_html($lead->platforms); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Capabilities Gap</span>
                                        <?php echo esc_html($lead->capabilities); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Service Type</span>
                                        <?php echo esc_html($lead->service_type); ?>
                                    </div>
                                    <div style="grid-column: span 2;">
                                        <span class="devxpert-admin-detail-label">Pain Point</span>
                                        <?php echo nl2br(esc_html($lead->pain)); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Initial Intent</span>
                                        <?php echo esc_html($lead->initial_intent); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Lead Type</span>
                                        <?php echo esc_html($lead->lead_type); ?>
                                    </div>
                                    <div style="grid-column: span 2;">
                                        <span class="devxpert-admin-detail-label">Page URL</span>
                                        <a href="<?php echo esc_url($lead->page_url); ?>" target="_blank"><?php echo esc_html($lead->page_url); ?></a>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">IP Address</span>
                                        <?php echo esc_html($lead->ip_address); ?>
                                    </div>
                                    <div>
                                        <span class="devxpert-admin-detail-label">Submitted</span>
                                        <?php echo esc_html(date('F j, Y g:i A', strtotime($lead->created_at))); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        </div>
        
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php if ($total_pages > 1): ?>
                    <span class="pagination-links">
                        <?php if ($page > 1): ?>
                            <a class="prev-page button" href="?page=devxpert-chatbot-leads&paged=<?php echo ($page - 1); ?>">‹</a>
                        <?php endif; ?>
                        <span class="paging-input">
                            <span class="current-page"><?php echo $page; ?></span> of 
                            <span class="total-pages"><?php echo $total_pages; ?></span>
                        </span>
                        <?php if ($page < $total_pages): ?>
                            <a class="next-page button" href="?page=devxpert-chatbot-leads&paged=<?php echo ($page + 1); ?>">›</a>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox
    $('#cb-select-all').on('change', function() {
        $('input[name="lead_ids[]"]').prop('checked', this.checked);
    });
    
    // View lead details toggle
    $('.view-lead-details').on('click', function(e) {
        e.preventDefault();
        var leadId = $(this).data('lead-id');
        $('#lead-details-' + leadId).toggle();

        if ($('#lead-details-' + leadId).is(':visible')) {
            $(this).text('Hide Details');
        } else {
            $(this).text('View Details');
        }
    });

    // Lead status update via AJAX
    $(document).on('change', '.devxpert-lead-status', function() {
        var $select = $(this);
        var leadId  = $select.data('lead-id');
        var status  = $select.val();

        $.post(ajaxurl, {
            action:  'devxpert_update_lead_status',
            nonce:   '<?php echo wp_create_nonce('devxpert_chatbot_nonce'); ?>',
            lead_id: leadId,
            status:  status
        }, function(response) {
            if (response.success) {
                $select.css('border-color', '#46b450');
                setTimeout(function() { $select.css('border-color', ''); }, 1500);
            } else {
                alert('Failed to update status.');
                location.reload();
            }
        });
    });
});
</script>

<style>
.button.view-lead-details {
    background: var(--dx-accent);
    color: #000;
    border-color: var(--dx-accent);
}

.button.view-lead-details:hover {
    background: #d4b670;
    border-color: #d4b670;
}

.devxpert-lead-status {
    font-size: 12px;
    padding: 3px 6px;
    border-radius: 4px;
    border: 1px solid #ccc;
    transition: border-color 0.3s;
}

.devxpert-lead-status option[value="new"]       { color: #0073aa; }
.devxpert-lead-status option[value="contacted"] { color: #ff8c00; }
.devxpert-lead-status option[value="converted"] { color: #46b450; }
.devxpert-lead-status option[value="lost"]      { color: #dc3232; }
</style>
