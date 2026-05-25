<div class="wrap devxpert-admin">
    <div class="devxpert-admin-header">
        <div>
            <p class="devxpert-admin-eyebrow">Audience Growth</p>
            <h1 class="devxpert-admin-title">Newsletter Subscribers</h1>
            <p class="devxpert-admin-subtitle">Monitor newsletter growth, review subscription sources, and export the current audience list for campaigns.</p>
        </div>
        <div class="devxpert-admin-actions">
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=devxpert-chatbot-newsletter&action=export'), 'devxpert_export_newsletter')); ?>" class="button button-primary">Export to CSV</a>
        </div>
    </div>
    
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'devxpert_newsletter_subscribers';
    
    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['subscriber_id'])) {
        check_admin_referer('delete_subscriber_' . $_GET['subscriber_id']);
        $wpdb->delete($table_name, array('id' => intval($_GET['subscriber_id'])), array('%d'));
        echo '<div class="notice notice-success"><p>Subscriber deleted successfully.</p></div>';
    }
    
    // Handle bulk delete
    if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && !empty($_POST['subscriber_ids'])) {
        check_admin_referer('devxpert_bulk_newsletter');
        $subscriber_ids = array_map('intval', $_POST['subscriber_ids']);
        foreach ($subscriber_ids as $subscriber_id) {
            $wpdb->delete($table_name, array('id' => $subscriber_id), array('%d'));
        }
        echo '<div class="notice notice-success"><p>' . count($subscriber_ids) . ' subscriber(s) deleted successfully.</p></div>';
    }
    
    // Handle export
    if (isset($_GET['action']) && $_GET['action'] === 'export') {
        check_admin_referer('devxpert_export_newsletter');
        
        $subscribers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY subscribed_at DESC");
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="devxpert-newsletter-subscribers-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Email', 'Name', 'Subscribed Date', 'IP Address', 'Page URL'));
        
        foreach ($subscribers as $sub) {
            fputcsv($output, array(
                $sub->email,
                $sub->name,
                $sub->subscribed_at,
                $sub->ip_address,
                $sub->page_url
            ));
        }
        
        fclose($output);
        exit;
    }
    
    // Get subscribers with pagination
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $total_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_subscribers / $per_page);
    
    $subscribers = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY subscribed_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
    ?>
    
    <div class="devxpert-stat-grid">
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">Total Subscribers</p>
            <p class="devxpert-stat-value"><?php echo number_format($total_subscribers); ?></p>
            <p class="devxpert-stat-note">Current size of the stored newsletter audience.</p>
        </div>
        
        <?php
        $today_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE DATE(subscribed_at) = CURDATE()");
        ?>
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">Today</p>
            <p class="devxpert-stat-value"><?php echo number_format($today_subscribers); ?></p>
            <p class="devxpert-stat-note">New subscribers collected on the current server date.</p>
        </div>
        
        <?php
        $week_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        ?>
        <div class="devxpert-stat-card">
            <p class="devxpert-stat-label">This Week</p>
            <p class="devxpert-stat-value"><?php echo number_format($week_subscribers); ?></p>
            <p class="devxpert-stat-note">Weekly signup pace from the popup and current offer.</p>
        </div>
    </div>
    
    <?php if (empty($subscribers)): ?>
        <div class="notice notice-info">
            <p>No newsletter subscribers yet. Enable the newsletter popup in <a href="<?php echo admin_url('admin.php?page=devxpert-chatbot'); ?>">Settings</a>.</p>
        </div>
    <?php else: ?>
        
        <div class="devxpert-admin-panel">
        <form method="post" action="">
            <?php wp_nonce_field('devxpert_bulk_newsletter'); ?>
            <input type="hidden" name="action" value="bulk_delete">
            
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <button type="submit" class="button action" onclick="return confirm('Are you sure you want to delete selected subscribers?');">Delete Selected</button>
                </div>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo number_format($total_subscribers); ?> items</span>
                    <?php if ($total_pages > 1): ?>
                        <span class="pagination-links">
                            <?php if ($page > 1): ?>
                                <a class="prev-page button" href="?page=devxpert-chatbot-newsletter&paged=<?php echo ($page - 1); ?>">‹</a>
                            <?php endif; ?>
                            <span class="paging-input">
                                <span class="current-page"><?php echo $page; ?></span> of 
                                <span class="total-pages"><?php echo $total_pages; ?></span>
                            </span>
                            <?php if ($page < $total_pages): ?>
                                <a class="next-page button" href="?page=devxpert-chatbot-newsletter&paged=<?php echo ($page + 1); ?>">›</a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped devxpert-admin-table">
                <thead>
                    <tr>
                        <td class="check-column"><input type="checkbox" id="cb-select-all"></td>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Subscribed Date</th>
                        <th>Page URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="subscriber_ids[]" value="<?php echo esc_attr($subscriber->id); ?>">
                            </th>
                            <td><strong><?php echo esc_html($subscriber->email); ?></strong></td>
                            <td><?php echo esc_html($subscriber->name ?: '—'); ?></td>
                            <td><?php echo esc_html(date('M j, Y g:i A', strtotime($subscriber->subscribed_at))); ?></td>
                            <td>
                                <?php if ($subscriber->page_url): ?>
                                    <a href="<?php echo esc_url($subscriber->page_url); ?>" target="_blank" title="<?php echo esc_attr($subscriber->page_url); ?>">
                                        <?php echo esc_html(wp_trim_words($subscriber->page_url, 5, '...')); ?>
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="mailto:<?php echo esc_attr($subscriber->email); ?>" class="button button-small">Email</a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=devxpert-chatbot-newsletter&action=delete&subscriber_id=' . $subscriber->id), 'delete_subscriber_' . $subscriber->id); ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('Are you sure you want to delete this subscriber?');">Delete</a>
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
                            <a class="prev-page button" href="?page=devxpert-chatbot-newsletter&paged=<?php echo ($page - 1); ?>">‹</a>
                        <?php endif; ?>
                        <span class="paging-input">
                            <span class="current-page"><?php echo $page; ?></span> of 
                            <span class="total-pages"><?php echo $total_pages; ?></span>
                        </span>
                        <?php if ($page < $total_pages): ?>
                            <a class="next-page button" href="?page=devxpert-chatbot-newsletter&paged=<?php echo ($page + 1); ?>">›</a>
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
        $('input[name="subscriber_ids[]"]').prop('checked', this.checked);
    });
});
</script>

<style>
.button-primary {
    background: var(--dx-accent);
    border-color: var(--dx-accent);
}

.button-primary:hover {
    background: #d4b670;
    border-color: #d4b670;
}
</style>
