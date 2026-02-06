<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Cronjob Logger Library
 *
 * Handles logging of cronjob executions with detailed tracking
 * of job name, status, duration, items processed, and error messages.
 */
class Cronjob_logger
{
    protected $CI;
    protected $current_log_id = null;
    protected $start_time = null;
    protected $enabled = false;

    public function __construct()
    {
        $this->CI =& get_instance();
        if ($this->enabled) {
            $this->CI->load->model('mymodel');
        }
    }

    /**
     * Start logging a cronjob execution
     *
     * @param string $job_name Name of the cronjob
     * @param string $job_type Type of job (sync, generate, update, cleanup)
     * @param string $trigger_source How the job was triggered (scheduled, manual, webhook)
     * @return int|null Log ID if successful, null on failure
     */
    public function start($job_name, $job_type = 'sync', $trigger_source = 'scheduled')
    {
        $this->start_time = microtime(true);
        if (!$this->enabled) {
            $this->current_log_id = 1;
            return $this->current_log_id;
        }

        // Get triggered_by user ID if available
        $triggered_by = null;
        if (isset($_SESSION['user']['id'])) {
            $triggered_by = $_SESSION['user']['id'];
        }

        // Get IP address
        $ip_address = $this->CI->input->ip_address();

        $data = [
            'job_name' => $this->CI->db->escape_str($job_name),
            'job_type' => $this->CI->db->escape_str($job_type),
            'status' => 'running',
            'trigger_source' => $trigger_source,
            'started_at' => date('Y-m-d H:i:s'),
            'triggered_by' => $triggered_by,
            'ip_address' => $ip_address,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->CI->db->insert('cronjob_logs', $data);
            $this->current_log_id = $this->CI->db->insert_id();
            return $this->current_log_id;
        } catch (Exception $e) {
            log_message('error', 'Cronjob_logger: Failed to start log - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark cronjob as completed
     *
     * @param int $total_items Total items to process
     * @param int $processed_items Items successfully processed
     * @param int $failed_items Items that failed
     * @param int $skipped_items Items that were skipped
     * @param array|null $details Additional details to store as JSON
     * @return bool Success status
     */
    public function complete($total_items = 0, $processed_items = 0, $failed_items = 0, $skipped_items = 0, $details = null)
    {
        if (!$this->enabled) {
            $this->reset();
            return true;
        }
        if (!$this->current_log_id) {
            return false;
        }

        $duration = $this->start_time ? round(microtime(true) - $this->start_time, 3) : null;

        $data = [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'total_items' => (int)$total_items,
            'processed_items' => (int)$processed_items,
            'failed_items' => (int)$failed_items,
            'skipped_items' => (int)$skipped_items,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($details !== null) {
            $data['details'] = json_encode($details);
        }

        try {
            $this->CI->db->where('id', $this->current_log_id);
            $this->CI->db->update('cronjob_logs', $data);

            $log_id = $this->current_log_id;
            $this->reset();

            return true;
        } catch (Exception $e) {
            log_message('error', 'Cronjob_logger: Failed to complete log - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark cronjob as failed
     *
     * @param string $error_message Error message describing the failure
     * @param array|null $details Additional details to store as JSON
     * @return bool Success status
     */
    public function fail($error_message, $details = null)
    {
        if (!$this->enabled) {
            $this->reset();
            return true;
        }
        if (!$this->current_log_id) {
            return false;
        }

        $duration = $this->start_time ? round(microtime(true) - $this->start_time, 3) : null;

        $data = [
            'status' => 'failed',
            'completed_at' => date('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'error_message' => $error_message,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($details !== null) {
            $data['details'] = json_encode($details);
        }

        try {
            $this->CI->db->where('id', $this->current_log_id);
            $this->CI->db->update('cronjob_logs', $data);

            $log_id = $this->current_log_id;
            $this->reset();

            return true;
        } catch (Exception $e) {
            log_message('error', 'Cronjob_logger: Failed to mark log as failed - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update progress during execution
     *
     * @param int $processed_items Current count of processed items
     * @param int $failed_items Current count of failed items
     * @param int $total_items Total items (if known)
     * @param int $skipped_items Items skipped
     * @return bool Success status
     */
    public function update_progress($processed_items, $failed_items = 0, $total_items = null, $skipped_items = 0)
    {
        if (!$this->enabled) {
            return true;
        }
        if (!$this->current_log_id) {
            return false;
        }

        $data = [
            'processed_items' => (int)$processed_items,
            'failed_items' => (int)$failed_items,
            'skipped_items' => (int)$skipped_items,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($total_items !== null) {
            $data['total_items'] = (int)$total_items;
        }

        try {
            $this->CI->db->where('id', $this->current_log_id);
            $this->CI->db->update('cronjob_logs', $data);
            return true;
        } catch (Exception $e) {
            log_message('error', 'Cronjob_logger: Failed to update progress - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add details to current log
     *
     * @param array $details Details to merge with existing details
     * @return bool Success status
     */
    public function add_details($details)
    {
        if (!$this->enabled) {
            return true;
        }
        if (!$this->current_log_id || empty($details)) {
            return false;
        }

        try {
            // Get existing details
            $log = $this->CI->mymodel->selectWithQuery("
                SELECT details FROM cronjob_logs WHERE id = {$this->current_log_id} LIMIT 1
            ");

            $existing_details = [];
            if (!empty($log) && !empty($log[0]['details'])) {
                $existing_details = json_decode($log[0]['details'], true) ?: [];
            }

            // Merge details
            $merged_details = array_merge($existing_details, $details);

            $this->CI->db->where('id', $this->current_log_id);
            $this->CI->db->update('cronjob_logs', [
                'details' => json_encode($merged_details),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return true;
        } catch (Exception $e) {
            log_message('error', 'Cronjob_logger: Failed to add details - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current log ID
     *
     * @return int|null Current log ID
     */
    public function get_log_id()
    {
        return $this->current_log_id;
    }

    /**
     * Set log ID (for resuming logging on an existing entry)
     *
     * @param int $log_id Log ID to set
     * @return void
     */
    public function set_log_id($log_id)
    {
        $this->current_log_id = $log_id;
        $this->start_time = microtime(true);
    }

    /**
     * Reset the logger state
     *
     * @return void
     */
    public function reset()
    {
        $this->current_log_id = null;
        $this->start_time = null;
    }

    /**
     * Determine trigger source based on request
     *
     * @return string 'manual', 'webhook', or 'scheduled'
     */
    public function detect_trigger_source()
    {
        // Check for manual trigger via GET parameter
        if ($this->CI->input->get('mode') === 'true' || $this->CI->input->get('manual') === 'true') {
            return 'manual';
        }

        // Check if it's a webhook
        $content_type = $this->CI->input->get_request_header('Content-Type');
        if (strpos($content_type, 'application/json') !== false && $this->CI->input->method() === 'post') {
            return 'webhook';
        }

        // Default to scheduled
        return 'scheduled';
    }

    /**
     * Quick log for simple jobs without detailed tracking
     * Creates and completes a log entry in one call
     *
     * @param string $job_name Name of the cronjob
     * @param string $job_type Type of job
     * @param bool $success Whether the job succeeded
     * @param string|null $error_message Error message if failed
     * @param array|null $details Additional details
     * @return int|null Log ID if successful
     */
    public function quick_log($job_name, $job_type, $success = true, $error_message = null, $details = null)
    {
        $log_id = $this->start($job_name, $job_type, $this->detect_trigger_source());

        if (!$log_id) {
            return null;
        }

        if ($success) {
            $this->complete(0, 0, 0, 0, $details);
        } else {
            $this->fail($error_message, $details);
        }

        return $log_id;
    }
}
