<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Disabled-by-default V2 rollout controls. Database control is deliberately opt-in. */
class EndorseV2Runtime
{
    private $CI;
    private $tableChecked = false;
    private $tableExists = false;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('env');
        $this->CI->load->database();
    }

    public function enabled(string $name): bool
    {
        $key = 'ENDORSE_V2_' . strtoupper($name);
        return in_array(strtolower((string) env($key, '0')), ['1', 'true', 'on', 'yes'], true);
    }

    public function analyticsMode(): string
    {
        $mode = strtolower((string) env('ENDORSE_V2_ANALYTICS', 'off'));
        return in_array($mode, ['off', 'shadow', 'visible'], true) ? $mode : 'off';
    }

    public function canEnqueue(): bool
    {
        return $this->enabled('READ') && $this->enabled('ENQUEUE') && $this->enabled('WRITER')
            && $this->enabled('ROLLUP') && $this->writerMode() === 'v2';
    }

    public function legacyWritesAllowed(): bool
    {
        // Absence of the additive schema must never alter the legacy application.
        return $this->writerMode() !== 'v2';
    }

    public function writerMode(): string
    {
        if (!$this->controlTableExists()) {
            return 'legacy';
        }
        $row = $this->CI->db->get_where('endorse_v2_runtime_control', ['control_key' => 'writer_mode'])->row_array();
        return strtolower((string) ($row['control_value'] ?? 'legacy')) === 'v2' ? 'v2' : 'legacy';
    }

    public function controlTableExists(): bool
    {
        if (!$this->tableChecked) {
            $this->tableChecked = true;
            $this->tableExists = $this->CI->db->table_exists('endorse_v2_runtime_control');
        }
        return $this->tableExists;
    }

    public function cronSignatureValid(): bool
    {
        $secret = (string) env('ENDORSE_V2_CRON_SECRET', '');
        $timestamp = (string) $this->CI->input->get_request_header('X-Endorse-Cron-Timestamp');
        $signature = (string) $this->CI->input->get_request_header('X-Endorse-Cron-Signature');
        if ($secret === '' || !ctype_digit($timestamp) || abs(time() - intval($timestamp)) > 300 || $signature === '') {
            return false;
        }
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $expected = hash_hmac('sha256', $timestamp . "\n" . ($_SERVER['REQUEST_METHOD'] ?? 'GET') . "\n" . $uri, $secret);
        return hash_equals($expected, $signature);
    }
}
