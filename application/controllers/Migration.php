<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
    }

    /**
     * Run a SQL file from /migrations by filename.
     * Example: /migration/run?file=add_marketing_overview_influencer_listing_modules.sql
     */
    public function run()
    {
        if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
            $this->output->set_status_header(401)->set_output('Unauthorized');
            return;
        }

        // Allow only super admin (role id 1)
        if (!in_array($_SESSION['user']['role'], array('1'))) {
            $this->output->set_status_header(403)->set_output('Forbidden');
            return;
        }

        $file = $this->input->get('file', true);
        if (empty($file)) {
            $this->output->set_status_header(400)->set_output('Missing file parameter');
            return;
        }

        // Basic filename validation
        if (!preg_match('/^[a-zA-Z0-9_\\-]+\\.sql$/', $file)) {
            $this->output->set_status_header(400)->set_output('Invalid file name');
            return;
        }

        $path = FCPATH . 'migrations/' . $file;
        if (!file_exists($path)) {
            $this->output->set_status_header(404)->set_output('File not found');
            return;
        }

        $sql = file_get_contents($path);
        if ($sql === false || trim($sql) === '') {
            $this->output->set_status_header(400)->set_output('Empty SQL file');
            return;
        }

        $statements = $this->split_sql_statements($sql);
        $this->db->trans_begin();

        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }
                $this->db->query($statement);
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $this->output->set_status_header(500)->set_output('Migration failed');
                return;
            }

            $this->db->trans_commit();
            $this->output->set_output('Migration completed');
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->output->set_status_header(500)->set_output('Migration error: ' . $e->getMessage());
        }
    }

    /**
     * Split SQL into statements; handles basic quoted strings.
     */
    private function split_sql_statements($sql)
    {
        $statements = [];
        $buffer = '';
        $in_single = false;
        $in_double = false;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if ($char === "'" && $prev !== '\\' && !$in_double) {
                $in_single = !$in_single;
            } elseif ($char === '"' && $prev !== '\\' && !$in_single) {
                $in_double = !$in_double;
            }

            if ($char === ';' && !$in_single && !$in_double) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }
}
