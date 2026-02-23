<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/BaseController.php';
require_once APPPATH . 'helpers/env_helper.php';
require_once FCPATH . 'vendor/autoload.php';

class Kol_affiliator extends BaseController
{
    private $status_creator_options = [
        'Listing',
        'Dealing sample tiktok',
        'Cancel',
        'On process dealing',
        'dealing no sampel',
        'Output Video',
        'Kirim brief',
        'Pengiriman manual',
        'Sampel Refundable',
        'kadaluwarsa',
        'Done',
        'Utang Konten',
        'Tawaran Refundable',
        'Undang Scratch'
    ];

    private $spesifikasi_options = [
        'Review Mobil',
        'Modifikasi Mobil',
        'Touring & Travelling',
        'A Daily in My Live',
        'Edukasi dan Tips Otomotif',
        'Teknisi dan Mekanik Mesin',
        'Pedagang',
        'Jual beli mobil',
        'Cinematic Mobil',
        'Balap & Drifting',
        'Modif motor',
        'Daily Live',
        'Review produk',
        'Cinematic motor',
        'Fashion',
        'Beauty',
        'Mobil dan motor',
        'Place review',
        'Motor',
        'Internal',
        'Mobil awareness',
        'Motor Awareness'
    ];

    private $output_video_options = [
        '1x', '2x', '3x', '4x', '5x', '6x', '7x', '8x', '9x', '10x', '>10x'
    ];

    private $toko_options = ['Store', 'Mall', 'Autocare', 'Automotive'];
    private $platform_options = ['Tiktok', 'Instagram', 'Threads'];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('mymodel');
        $this->load->library('template');

        $this->set_public_methods([]);
        $this->set_method_permissions([
            'remove' => 'delete',
            'import_excel' => 'create',
            'import_excel_process' => 'create',
            'sync_post_views' => 'edit',
            'sync_campaign_views' => 'edit'
        ]);
    }

    public function index()
    {
        $data['user'] = $_SESSION['user'];
        $permission_data = $this->get_permission_data('kol_affiliator');
        $data['can_create'] = $permission_data['can_create'];
        $data['can_edit'] = $permission_data['can_edit'];
        $data['can_delete'] = $permission_data['can_delete'];

        $keyword_category = trim((string)($this->input->get('keyword_category') ?? 'Username KOL'));
        $keyword = trim((string)($this->input->get('keyword') ?? ''));
        $status_filter = trim((string)($this->input->get('status_creator') ?? ''));

        $data['keyword_category'] = $keyword_category;
        $data['status_creator'] = $status_filter;

        $count_builder = $this->db->from('kol_campaigns');
        $this->apply_campaign_filters($count_builder, $keyword_category, $keyword, $status_filter);
        $total = (int) $count_builder->count_all_results();

        $data['page'] = (int) ceil($total / 10);
        $data['notif'] = '<p class="mb-1"><label class="text-notif">' . $this->template->separator_only($total) . ' data ditemukan!</label></p>';

        $current_page = max(1, (int)($this->input->get('page') ?? 1));
        $data['param'] = $this->template->get_param();
        $data['param_pagination'] = $this->template->get_param_without('page');
        $data['pagination'] = $this->template->pagination($data['page'], $current_page, $data['param_pagination']);
        $data['title'] = 'KOL & Affiliator - ' . $this->template->title();

        $data['content'] = $this->load->view('kol_affiliator/all', $data, true);
        $this->load->view('TemplateDashboard', $data);
    }

    public function item()
    {
        $data['template'] = $this->template;

        $permission_data = $this->get_permission_data('kol_affiliator');
        $data['can_edit'] = $permission_data['can_edit'];
        $data['can_delete'] = $permission_data['can_delete'];

        $keyword_category = trim((string)($this->input->get('keyword_category') ?? 'Username KOL'));
        $keyword = trim((string)($this->input->get('keyword') ?? ''));
        $status_filter = trim((string)($this->input->get('status_creator') ?? ''));

        $limit = 10;
        $current_page = max(1, (int)($this->input->get('page') ?? 1));
        $offset = ($current_page - 1) * $limit;

        $builder = $this->db
            ->select('kc.*, (SELECT COUNT(*) FROM kol_posts kp WHERE kp.campaign_id = kc.id) AS total_posts', false)
            ->from('kol_campaigns kc')
            ->order_by('kc.id', 'DESC')
            ->limit($limit, $offset);

        $this->apply_campaign_filters($builder, $keyword_category, $keyword, $status_filter, 'kc.');
        $data['data'] = $builder->get()->result_array();
        $data['start'] = $offset;

        $this->load->view('kol_affiliator/item', $data);
    }

    public function create_page()
    {
        $data['user'] = $_SESSION['user'];
        $data['data'] = [];
        $data['posts'] = [];

        $this->bind_form_options($data);

        $data['title'] = 'Create KOL & Affiliator - ' . $this->template->title();
        $data['content'] = $this->load->view('kol_affiliator/create_page', $data, true);
        $this->load->view('TemplateDashboard', $data);
    }

    public function store()
    {
        $dt = $this->input->post('dt');
        $campaign = $this->sanitize_campaign_payload($dt);

        if ($campaign['username_kol'] === '') {
            echo $this->template->alert_danger('Username KOL wajib diisi.');
            return;
        }

        $posts = $this->normalize_posts_payload($this->input->post('posts'), $campaign['platform']);

        $this->db->trans_begin();

        $ok = $this->db->insert('kol_campaigns', $campaign);
        if (!$ok) {
            $this->db->trans_rollback();
            echo $this->template->alert_danger('Gagal menambahkan data campaign.');
            return;
        }

        $campaign_id = (int)$this->db->insert_id();
        $this->replace_posts($campaign_id, $posts, false);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo $this->template->alert_danger('Gagal menambahkan data campaign.');
            return;
        }

        $this->db->trans_commit();
        echo $this->template->alert_success('Tambah data berhasil!');
    }

    public function edit_page()
    {
        $id = (int)($this->input->get('id') ?? 0);
        $campaign = $this->db->where('id', $id)->get('kol_campaigns')->row_array();

        if (empty($campaign)) {
            redirect(base_url('kol-affiliator'));
            return;
        }

        $data['user'] = $_SESSION['user'];
        $data['data'] = $campaign;
        $data['posts'] = $this->db
            ->where('campaign_id', $id)
            ->order_by('id', 'ASC')
            ->get('kol_posts')
            ->result_array();

        $this->bind_form_options($data);

        $data['title'] = 'Edit KOL & Affiliator - ' . $this->template->title();
        $data['content'] = $this->load->view('kol_affiliator/edit_page', $data, true);
        $this->load->view('TemplateDashboard', $data);
    }

    public function update()
    {
        $id = (int)($this->input->post('id') ?? 0);
        $exists = $this->db->where('id', $id)->get('kol_campaigns')->row_array();

        if (!$exists) {
            echo $this->template->alert_danger('Data tidak ditemukan.');
            return;
        }

        $campaign = $this->sanitize_campaign_payload($this->input->post('dt'));

        if ($campaign['username_kol'] === '') {
            echo $this->template->alert_danger('Username KOL wajib diisi.');
            return;
        }

        $posts = $this->normalize_posts_payload($this->input->post('posts'), $campaign['platform']);

        $this->db->trans_begin();

        $this->db->where('id', $id)->update('kol_campaigns', $campaign);
        $this->replace_posts($id, $posts, true);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo $this->template->alert_danger('Update data tidak berhasil.');
            return;
        }

        $this->db->trans_commit();
        echo $this->template->alert_success('Update data berhasil!');
    }

    public function detail()
    {
        $id = (int)($this->input->get('id') ?? 0);
        $campaign = $this->db->where('id', $id)->get('kol_campaigns')->row_array();

        if (empty($campaign)) {
            redirect(base_url('kol-affiliator'));
            return;
        }

        $permission_data = $this->get_permission_data('kol_affiliator');

        $data['user'] = $_SESSION['user'];
        $data['data'] = $campaign;
        $data['posts'] = $this->db
            ->where('campaign_id', $id)
            ->order_by('id', 'DESC')
            ->get('kol_posts')
            ->result_array();
        $data['can_edit'] = $permission_data['can_edit'];

        $data['title'] = 'Detail KOL & Affiliator - ' . $this->template->title();
        $data['content'] = $this->load->view('kol_affiliator/detail_page', $data, true);
        $this->load->view('TemplateDashboard', $data);
    }

    public function remove()
    {
        $id = (int)($this->input->get('id') ?? 0);
        $campaign = $this->db->where('id', $id)->get('kol_campaigns')->row_array();

        $data['data'] = [
            'id' => $id,
            'username_kol' => $campaign['username_kol'] ?? ''
        ];

        $this->load->view('kol_affiliator/delete', $data);
    }

    public function delete()
    {
        $id = (int)($this->input->post('id') ?? 0);

        if ($id <= 0) {
            echo $this->template->alert_danger('ID tidak valid.');
            return;
        }

        $ok = $this->db->delete('kol_campaigns', ['id' => $id]);

        if ($ok) {
            echo $this->template->alert_success('Hapus data berhasil!');
            return;
        }

        echo $this->template->alert_danger('Hapus data tidak berhasil!');
    }

    public function import_excel()
    {
        $this->load->view('kol_affiliator/import_excel');
    }

    public function download_template()
    {
        $file = FCPATH . 'Zerone Japan Database Product (1).xlsx';
        if (!is_file($file)) {
            show_error('Template tidak ditemukan.', 404);
            return;
        }

        $filename = basename($file);
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        readfile($file);
        exit;
    }

    public function import_excel_process()
    {
        $upload_dir = FCPATH . 'assets/webfile/excel/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0777, true);
        }

        $this->load->library('upload');
        $config = [
            'upload_path' => $upload_dir,
            'allowed_types' => 'xls|xlsx',
            'encrypt_name' => true,
            'max_size' => 10240
        ];
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            echo $this->template->alert_danger($this->upload->display_errors());
            return;
        }

        $upload_data = $this->upload->data();
        $filepath = $upload_data['full_path'];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        } catch (Exception $e) {
            @unlink($filepath);
            echo $this->template->alert_danger('File Excel tidak bisa dibaca. Pastikan format file valid.');
            return;
        }

        @unlink($filepath);

        if (empty($rows) || count($rows) < 2) {
            echo $this->template->alert_danger('File Excel kosong atau tidak memiliki data.');
            return;
        }

        $header_row = reset($rows);
        $first_row_key = key($rows);
        $header_map = [];
        foreach ($header_row as $col => $header) {
            $header_map[$col] = $this->normalize_excel_header($header);
        }

        $col_username = $this->find_excel_column($header_map, ['Username KOL', 'username_kol', 'username']);
        $col_pic = $this->find_excel_column($header_map, ['PIC Utama', 'pic_utama', 'pic']);
        $col_product = $this->find_excel_column($header_map, ['Product', 'product']);
        $col_status = $this->find_excel_column($header_map, ['Status Creator', 'status', 'creator_status']);
        $col_creator = $this->find_excel_column($header_map, ['Creator', 'creator']);
        $col_tanggal_blast = $this->find_excel_column($header_map, ['Tanggal Blast', 'tanggal_blast']);
        $col_tanggal_acc_sample = $this->find_excel_column($header_map, ['Tanggal Acc Sample', 'tanggal_acc_sample']);
        $col_keterangan = $this->find_excel_column($header_map, ['Keterangan', 'keterangan']);
        $col_niche = $this->find_excel_column($header_map, ['Niche', 'niche']);
        $col_spesifikasi = $this->find_excel_column($header_map, ['Spesifikasi', 'spesifikasi']);
        $col_platform = $this->find_excel_column($header_map, ['Platform', 'platform']);
        $col_kategori = $this->find_excel_column($header_map, ['Kategori', 'kategori']);
        $col_output_video = $this->find_excel_column($header_map, ['Output Video', 'output_video']);
        $col_toko = $this->find_excel_column($header_map, ['Toko', 'toko']);
        $col_nama_penerima = $this->find_excel_column($header_map, ['Nama Penerima', 'nama_penerima']);
        $col_no_hp = $this->find_excel_column($header_map, ['No HP', 'no_hp']);
        $col_alamat = $this->find_excel_column($header_map, ['Alamat', 'alamat']);
        $col_actual_posting = $this->find_excel_column($header_map, ['Actual Posting', 'actual_posting']);
        $col_tanggal_post = $this->find_excel_column($header_map, ['Tanggal Post', 'tanggal_post']);
        $col_link_post = $this->find_excel_column($header_map, ['Link Post', 'link_post']);
        $col_views = $this->find_excel_column($header_map, ['Views', 'views']);

        if (!$col_username) {
            echo $this->template->alert_danger('Header tidak cocok. Pastikan kolom Username KOL tersedia.');
            return;
        }

        $inserted = 0;
        $skipped = 0;
        $errors = [];

        $this->db->trans_begin();

        foreach ($rows as $row_number => $row) {
            if ($row_number == $first_row_key) {
                continue;
            }

            $username_kol = trim((string)($row[$col_username] ?? ''));
            if ($username_kol === '') {
                $has_any_data = false;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $has_any_data = true;
                        break;
                    }
                }
                if ($has_any_data) {
                    $skipped++;
                    $errors[] = "Baris {$row_number}: Username KOL kosong.";
                }
                continue;
            }

            $campaign = $this->sanitize_campaign_payload([
                'tanggal_blast' => $this->parse_excel_date($row[$col_tanggal_blast] ?? null),
                'tanggal_acc_sample' => $this->parse_excel_date($row[$col_tanggal_acc_sample] ?? null),
                'status' => $row[$col_status] ?? '',
                'creator' => $row[$col_creator] ?? '',
                'username_kol' => $username_kol,
                'pic_utama' => $row[$col_pic] ?? '',
                'product' => $row[$col_product] ?? '',
                'keterangan' => $row[$col_keterangan] ?? '',
                'niche' => $row[$col_niche] ?? '',
                'spesifikasi' => $row[$col_spesifikasi] ?? '',
                'platform' => $row[$col_platform] ?? '',
                'kategori' => $row[$col_kategori] ?? '',
                'output_video' => $row[$col_output_video] ?? '',
                'toko' => $row[$col_toko] ?? '',
                'nama_penerima' => $row[$col_nama_penerima] ?? '',
                'no_hp' => $row[$col_no_hp] ?? '',
                'alamat' => $row[$col_alamat] ?? '',
                'actual_posting' => $this->parse_excel_date($row[$col_actual_posting] ?? null),
            ]);

            $ok = $this->db->insert('kol_campaigns', $campaign);
            if (!$ok) {
                $skipped++;
                $errors[] = "Baris {$row_number}: gagal insert campaign.";
                continue;
            }

            $campaign_id = (int)$this->db->insert_id();
            $inserted++;

            $link_post = trim((string)($col_link_post ? ($row[$col_link_post] ?? '') : ''));
            $views = $col_views ? (int)round($this->parse_localized_number($row[$col_views] ?? 0)) : 0;
            $tanggal_post = $this->parse_excel_date($col_tanggal_post ? ($row[$col_tanggal_post] ?? null) : null);

            if ($link_post !== '' || $views > 0 || $tanggal_post !== null) {
                $platform = $this->detect_platform_from_url($link_post, $campaign['platform']);
                if ($views <= 0 && $link_post !== '') {
                    $social = $this->fetch_social_media($platform, $link_post);
                    if ($social['status'] && $social['view'] > 0) {
                        $views = $social['view'];
                        if ($tanggal_post === null && $social['created_at'] !== '') {
                            $tanggal_post = $social['created_at'];
                        }
                    }
                }

                $this->db->insert('kol_posts', [
                    'campaign_id' => $campaign_id,
                    'tanggal_post' => $tanggal_post,
                    'link_post' => $link_post,
                    'views' => max(0, (int)$views),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo $this->template->alert_danger('Import gagal diproses.');
            return;
        }

        $this->db->trans_commit();

        if ($inserted === 0) {
            $msg = 'Import gagal. Tidak ada data yang diproses.';
            if (!empty($errors)) {
                $msg .= ' ' . implode(' ', array_slice($errors, 0, 3));
            }
            echo $this->template->alert_danger($msg);
            return;
        }

        $msg = "Import berhasil! {$inserted} campaign ditambahkan, {$skipped} data dilewati.";
        if (!empty($errors)) {
            $msg .= ' Catatan: ' . implode(' ', array_slice($errors, 0, 3));
        }
        echo $this->template->alert_success($msg);
    }

    public function sync_post_views()
    {
        $post_id = (int)($this->input->post('post_id') ?? 0);
        if ($post_id <= 0) {
            return $this->json_response(false, 'Post ID tidak valid.');
        }

        $post = $this->db
            ->select('kp.*, kc.platform as campaign_platform')
            ->from('kol_posts kp')
            ->join('kol_campaigns kc', 'kc.id = kp.campaign_id', 'left')
            ->where('kp.id', $post_id)
            ->get()
            ->row_array();

        if (!$post) {
            return $this->json_response(false, 'Data post tidak ditemukan.');
        }

        if (trim((string)$post['link_post']) === '') {
            return $this->json_response(false, 'Link post belum diisi.');
        }

        $platform = $this->detect_platform_from_url($post['link_post'], $post['campaign_platform']);
        $social = $this->fetch_social_media($platform, $post['link_post']);

        if (!$social['status'] || $social['view'] <= 0) {
            $msg = $social['message'] !== '' ? $social['message'] : 'Views tidak ditemukan dari link post.';
            return $this->json_response(false, $msg, [
                'platform' => $platform
            ]);
        }

        $update = ['views' => (int)$social['view']];
        if (!empty($social['created_at'])) {
            $update['tanggal_post'] = $social['created_at'];
        }

        $this->db->where('id', $post_id)->update('kol_posts', $update);

        return $this->json_response(true, 'Views berhasil disinkronkan.', [
            'post_id' => $post_id,
            'views' => (int)$social['view'],
            'tanggal_post' => $update['tanggal_post'] ?? ($post['tanggal_post'] ?? null),
            'platform' => $platform
        ]);
    }

    public function sync_campaign_views()
    {
        $campaign_id = (int)($this->input->post('campaign_id') ?? 0);
        if ($campaign_id <= 0) {
            return $this->json_response(false, 'Campaign ID tidak valid.');
        }

        $campaign = $this->db->where('id', $campaign_id)->get('kol_campaigns')->row_array();
        if (!$campaign) {
            return $this->json_response(false, 'Campaign tidak ditemukan.');
        }

        $posts = $this->db->where('campaign_id', $campaign_id)->get('kol_posts')->result_array();
        if (empty($posts)) {
            return $this->json_response(false, 'Belum ada data post untuk disinkronkan.');
        }

        $updated = 0;
        $skipped = 0;

        foreach ($posts as $post) {
            $link_post = trim((string)($post['link_post'] ?? ''));
            if ($link_post === '') {
                $skipped++;
                continue;
            }

            $platform = $this->detect_platform_from_url($link_post, $campaign['platform']);
            $social = $this->fetch_social_media($platform, $link_post);

            if (!$social['status'] || $social['view'] <= 0) {
                $skipped++;
                continue;
            }

            $update = ['views' => (int)$social['view']];
            if (!empty($social['created_at'])) {
                $update['tanggal_post'] = $social['created_at'];
            }

            $this->db->where('id', (int)$post['id'])->update('kol_posts', $update);
            $updated++;
        }

        return $this->json_response(true, "Sync selesai: {$updated} post berhasil diupdate, {$skipped} post dilewati.", [
            'updated' => $updated,
            'skipped' => $skipped
        ]);
    }

    private function bind_form_options(&$data)
    {
        $data['status_creator_options'] = $this->status_creator_options;
        $data['spesifikasi_options'] = $this->spesifikasi_options;
        $data['output_video_options'] = $this->output_video_options;
        $data['toko_options'] = $this->toko_options;
        $data['platform_options'] = $this->platform_options;
    }

    private function apply_campaign_filters($builder, $keyword_category, $keyword, $status_filter, $prefix = '')
    {
        if ($keyword !== '') {
            $category_map = [
                'Username KOL' => $prefix . 'username_kol',
                'PIC Utama' => $prefix . 'pic_utama',
                'Product' => $prefix . 'product',
                'Status Creator' => $prefix . 'status'
            ];

            $column = $category_map[$keyword_category] ?? ($prefix . 'username_kol');
            $builder->like($column, $keyword);
        }

        if ($status_filter !== '') {
            $builder->where($prefix . 'status', $status_filter);
        }
    }

    private function sanitize_campaign_payload($dt)
    {
        $dt = is_array($dt) ? $dt : [];

        return [
            'tanggal_blast' => $this->normalize_date($dt['tanggal_blast'] ?? null),
            'tanggal_acc_sample' => $this->normalize_date($dt['tanggal_acc_sample'] ?? null),
            'status' => trim((string)($dt['status'] ?? '')),
            'creator' => trim((string)($dt['creator'] ?? '')),
            'username_kol' => trim((string)($dt['username_kol'] ?? '')),
            'pic_utama' => trim((string)($dt['pic_utama'] ?? '')),
            'product' => trim((string)($dt['product'] ?? '')),
            'keterangan' => trim((string)($dt['keterangan'] ?? '')),
            'niche' => trim((string)($dt['niche'] ?? '')),
            'spesifikasi' => trim((string)($dt['spesifikasi'] ?? '')),
            'platform' => $this->normalize_platform($dt['platform'] ?? ''),
            'kategori' => trim((string)($dt['kategori'] ?? '')),
            'output_video' => trim((string)($dt['output_video'] ?? '')),
            'toko' => trim((string)($dt['toko'] ?? '')),
            'nama_penerima' => trim((string)($dt['nama_penerima'] ?? '')),
            'no_hp' => trim((string)($dt['no_hp'] ?? '')),
            'alamat' => trim((string)($dt['alamat'] ?? '')),
            'actual_posting' => $this->normalize_date($dt['actual_posting'] ?? null),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function replace_posts($campaign_id, $posts, $delete_existing = true)
    {
        $campaign_id = (int)$campaign_id;
        if ($campaign_id <= 0) {
            return;
        }

        if ($delete_existing) {
            $this->db->delete('kol_posts', ['campaign_id' => $campaign_id]);
        }

        if (empty($posts)) {
            return;
        }

        foreach ($posts as $post) {
            $this->db->insert('kol_posts', [
                'campaign_id' => $campaign_id,
                'tanggal_post' => $this->normalize_date($post['tanggal_post'] ?? null),
                'link_post' => trim((string)($post['link_post'] ?? '')),
                'views' => max(0, (int)($post['views'] ?? 0)),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function normalize_posts_payload($posts, $campaign_platform = '')
    {
        if (!is_array($posts)) {
            return [];
        }

        $normalized = [];

        foreach ($posts as $row) {
            if (!is_array($row)) {
                continue;
            }

            $link_post = trim((string)($row['link_post'] ?? ''));
            $tanggal_post = $this->normalize_date($row['tanggal_post'] ?? null);
            $views = (int)round($this->parse_localized_number($row['views'] ?? 0));

            if ($views <= 0 && $link_post !== '') {
                $platform = $this->detect_platform_from_url($link_post, $campaign_platform);
                $social = $this->fetch_social_media($platform, $link_post);
                if ($social['status'] && $social['view'] > 0) {
                    $views = $social['view'];
                    if ($tanggal_post === null && $social['created_at'] !== '') {
                        $tanggal_post = $social['created_at'];
                    }
                }
            }

            if ($tanggal_post === null && $link_post === '' && $views <= 0) {
                continue;
            }

            $normalized[] = [
                'tanggal_post' => $tanggal_post,
                'link_post' => $link_post,
                'views' => max(0, $views)
            ];
        }

        return $normalized;
    }

    private function fetch_social_media($platform, $url)
    {
        $response = $this->template->get_social_media($platform, $url);

        return [
            'status' => (bool)($response['status'] ?? false),
            'message' => trim((string)($response['msg'] ?? '')),
            'view' => (int)($response['data']['view'] ?? 0),
            'created_at' => $this->normalize_date($response['data']['created_at'] ?? null)
        ];
    }

    private function normalize_platform($platform)
    {
        $platform = trim((string)$platform);
        if ($platform === '') {
            return '';
        }

        foreach ($this->platform_options as $option) {
            if (strtolower($option) === strtolower($platform)) {
                return $option;
            }
        }

        return $platform;
    }

    private function detect_platform_from_url($url, $fallback = '')
    {
        $url = strtolower(trim((string)$url));
        if ($url !== '') {
            if (strpos($url, 'tiktok.com') !== false || strpos($url, 'vt.tiktok.com') !== false) {
                return 'Tiktok';
            }
            if (strpos($url, 'instagram.com') !== false || strpos($url, 'instagr.am') !== false) {
                return 'Instagram';
            }
            if (strpos($url, 'threads.net') !== false) {
                return 'Threads';
            }
        }

        return $this->normalize_platform($fallback);
    }

    private function normalize_date($raw)
    {
        if ($raw === null) {
            return null;
        }

        $raw = trim((string)$raw);
        if ($raw === '') {
            return null;
        }

        $ts = strtotime($raw);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d', $ts);
    }

    private function normalize_excel_header($header)
    {
        $header = trim((string)$header);
        $header = str_replace("\xc2\xa0", ' ', $header);
        $header = preg_replace('/\s+/', ' ', $header);
        return strtolower(trim($header));
    }

    private function find_excel_column($header_map, $aliases)
    {
        foreach ($aliases as $alias) {
            $normalized_alias = $this->normalize_excel_header($alias);
            foreach ($header_map as $col => $normalized_header) {
                if ($normalized_header === $normalized_alias) {
                    return $col;
                }
            }
        }
        return null;
    }

    private function parse_excel_date($raw)
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                $date_obj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($raw);
                return $date_obj->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        return $this->normalize_date($raw);
    }

    private function parse_localized_number($raw)
    {
        if ($raw === null || $raw === '') {
            return 0;
        }

        if (is_numeric($raw)) {
            return (float)$raw;
        }

        $value = (string)$raw;
        $value = str_replace(["\xc2\xa0", 'Rp', 'rp', 'IDR', 'idr', ' '], '', $value);
        $value = preg_replace('/[^0-9,\.\-]/', '', $value);

        if ($value === '' || $value === '-' || $value === ',' || $value === '.') {
            return 0;
        }

        $last_comma = strrpos($value, ',');
        $last_dot = strrpos($value, '.');

        if ($last_comma !== false && $last_dot !== false) {
            if ($last_comma > $last_dot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($last_comma !== false) {
            $parts = explode(',', $value);
            if (count($parts) > 2) {
                $value = str_replace(',', '', $value);
            } else {
                $decimals = strlen($parts[1]);
                if ($decimals === 3 && strlen($parts[0]) > 0) {
                    $value = str_replace(',', '', $value);
                } else {
                    $value = str_replace(',', '.', $value);
                }
            }
        }

        return is_numeric($value) ? (float)$value : 0;
    }

    private function json_response($success, $message, $data = [])
    {
        $payload = [
            'success' => (bool)$success,
            'message' => (string)$message,
            'data' => $data
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
