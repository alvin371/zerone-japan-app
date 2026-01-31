<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/BaseController.php';

/**
 * Cronjob Log Controller
 *
 * Displays and manages cronjob execution logs
 */
class Cronjob_log extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('mymodel');
        $this->load->library('template');
        $this->load->helper('url');
        $this->load->library('session');

        // Set public methods (no permission required)
        $this->set_public_methods([]);

        // Override method-to-action mapping if needed
        $this->set_method_permissions([
            'clear_logs' => 'delete'
        ]);
    }

    /**
     * Main listing page
     */
    public function index()
    {
        $data['title'] = 'Cronjob Logs - ' . $this->template->title();

        // Get filter parameters
        $job_name = $this->input->get('job_name');
        $status = $this->input->get('status');
        $job_type = $this->input->get('job_type');
        $trigger_source = $this->input->get('trigger_source');
        $start_date = $this->input->get('start_date') ?: date('Y-m-d', strtotime('-7 days'));
        $until_date = $this->input->get('until_date') ?: date('Y-m-d');

        $data['job_name'] = $job_name;
        $data['status'] = $status;
        $data['job_type'] = $job_type;
        $data['trigger_source'] = $trigger_source;
        $data['start_date'] = $start_date;
        $data['until_date'] = $until_date;

        // Get distinct job names for dropdown
        $data['job_names'] = $this->mymodel->selectWithQuery("
            SELECT DISTINCT job_name FROM cronjob_logs ORDER BY job_name ASC
        ");

        // Get distinct job types for dropdown
        $data['job_types'] = $this->mymodel->selectWithQuery("
            SELECT DISTINCT job_type FROM cronjob_logs ORDER BY job_type ASC
        ");

        // Pagination
        $limit = 30;
        $current_page = $this->input->get('page') ? intval($this->input->get('page')) : 1;
        if ($current_page < 1) {
            $current_page = 1;
        }
        $offset = ($current_page - 1) * $limit;

        // Build query conditions
        $where = "DATE(started_at) >= '" . $this->db->escape_str($start_date) . "'
                  AND DATE(started_at) <= '" . $this->db->escape_str($until_date) . "'";

        if ($job_name) {
            $where .= " AND job_name = '" . $this->db->escape_str($job_name) . "'";
        }
        if ($status) {
            $where .= " AND status = '" . $this->db->escape_str($status) . "'";
        }
        if ($job_type) {
            $where .= " AND job_type = '" . $this->db->escape_str($job_type) . "'";
        }
        if ($trigger_source) {
            $where .= " AND trigger_source = '" . $this->db->escape_str($trigger_source) . "'";
        }

        // Get total count
        $count_result = $this->mymodel->selectWithQuery("
            SELECT COUNT(id) as count FROM cronjob_logs WHERE $where
        ");
        $total_count = $count_result[0]['count'] ?? 0;
        $data['total_count'] = $total_count;
        $data['page'] = ceil($total_count / $limit);
        $data['current_page'] = $current_page;
        $data['limit'] = $limit;

        // Get summary stats
        $data['stats'] = $this->get_stats($where);

        $data['notif'] = '<p class="mb-1"><label class="text-notif">' . $this->template->separator_only($total_count) . ' log entries found</label></p>';

        $data['content'] = $this->load->view('cronjob_log/all', $data, true);
        $this->load->view('TemplateDashboard', $data);
    }

    /**
     * AJAX endpoint for table rows
     */
    public function item()
    {
        // Get filter parameters
        $job_name = $this->input->get('job_name');
        $status = $this->input->get('status');
        $job_type = $this->input->get('job_type');
        $trigger_source = $this->input->get('trigger_source');
        $start_date = $this->input->get('start_date') ?: date('Y-m-d', strtotime('-7 days'));
        $until_date = $this->input->get('until_date') ?: date('Y-m-d');

        // Pagination
        $limit = 30;
        $current_page = $this->input->get('page') ? intval($this->input->get('page')) : 1;
        if ($current_page < 1) {
            $current_page = 1;
        }
        $offset = ($current_page - 1) * $limit;

        // Sorting
        $allowed_sort = ['id', 'job_name', 'job_type', 'status', 'started_at', 'duration_seconds', 'processed_items'];
        $sort_by = $this->input->get('sort_by') ?: 'started_at';
        $sort_dir = strtoupper($this->input->get('sort_dir') ?: 'DESC');

        if (!in_array($sort_by, $allowed_sort)) {
            $sort_by = 'started_at';
        }
        if (!in_array($sort_dir, ['ASC', 'DESC'])) {
            $sort_dir = 'DESC';
        }

        // Build query conditions
        $where = "DATE(started_at) >= '" . $this->db->escape_str($start_date) . "'
                  AND DATE(started_at) <= '" . $this->db->escape_str($until_date) . "'";

        if ($job_name) {
            $where .= " AND job_name = '" . $this->db->escape_str($job_name) . "'";
        }
        if ($status) {
            $where .= " AND status = '" . $this->db->escape_str($status) . "'";
        }
        if ($job_type) {
            $where .= " AND job_type = '" . $this->db->escape_str($job_type) . "'";
        }
        if ($trigger_source) {
            $where .= " AND trigger_source = '" . $this->db->escape_str($trigger_source) . "'";
        }

        // Get data
        $data['data'] = $this->mymodel->selectWithQuery("
            SELECT
                cl.*,
                u.full_name as triggered_by_name
            FROM cronjob_logs cl
            LEFT JOIN user u ON cl.triggered_by = u.id
            WHERE $where
            ORDER BY $sort_by $sort_dir
            LIMIT $limit OFFSET $offset
        ");

        // Get total count
        $count_result = $this->mymodel->selectWithQuery("
            SELECT COUNT(id) as count FROM cronjob_logs WHERE $where
        ");
        $data['total_count'] = $count_result[0]['count'] ?? 0;
        $data['current_page'] = $current_page;
        $data['limit'] = $limit;
        $data['sort_by'] = $sort_by;
        $data['sort_dir'] = $sort_dir;

        $this->load->view('cronjob_log/item', $data);
    }

    /**
     * Get detail of a specific log entry (for modal)
     */
    public function detail()
    {
        $id = $this->input->get('id');

        if (!$id) {
            echo '<div class="alert alert-danger">Invalid log ID</div>';
            return;
        }

        $log = $this->mymodel->selectWithQuery("
            SELECT
                cl.*,
                u.full_name as triggered_by_name
            FROM cronjob_logs cl
            LEFT JOIN user u ON cl.triggered_by = u.id
            WHERE cl.id = " . intval($id) . "
            LIMIT 1
        ");

        if (empty($log)) {
            echo '<div class="alert alert-danger">Log entry not found</div>';
            return;
        }

        $data['log'] = $log[0];
        $this->load->view('cronjob_log/detail', $data);
    }

    /**
     * Clear old logs
     */
    public function clear_logs()
    {
        $days = $this->input->post('days') ?: 30;

        if (!is_numeric($days) || $days < 1) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Invalid number of days'
                ]));
            return;
        }

        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $this->db->where('created_at <', $cutoff_date);
        $deleted = $this->db->delete('cronjob_logs');

        $affected_rows = $this->db->affected_rows();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $deleted,
                'message' => $deleted ? "Deleted {$affected_rows} log entries older than {$days} days" : 'Failed to delete logs',
                'deleted_count' => $affected_rows
            ]));
    }

    /**
     * Get statistics summary
     */
    private function get_stats($where)
    {
        $stats = [
            'total' => 0,
            'completed' => 0,
            'failed' => 0,
            'running' => 0,
            'pending' => 0,
            'avg_duration' => 0,
            'total_processed' => 0,
            'total_failed_items' => 0
        ];

        $result = $this->mymodel->selectWithQuery("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                AVG(duration_seconds) as avg_duration,
                SUM(processed_items) as total_processed,
                SUM(failed_items) as total_failed_items
            FROM cronjob_logs
            WHERE $where
        ");

        if (!empty($result)) {
            $stats = [
                'total' => (int)($result[0]['total'] ?? 0),
                'completed' => (int)($result[0]['completed'] ?? 0),
                'failed' => (int)($result[0]['failed'] ?? 0),
                'running' => (int)($result[0]['running'] ?? 0),
                'pending' => (int)($result[0]['pending'] ?? 0),
                'avg_duration' => round((float)($result[0]['avg_duration'] ?? 0), 2),
                'total_processed' => (int)($result[0]['total_processed'] ?? 0),
                'total_failed_items' => (int)($result[0]['total_failed_items'] ?? 0)
            ];
        }

        return $stats;
    }
}
