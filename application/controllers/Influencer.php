<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/BaseController.php';

class Influencer extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('mymodel');
        $this->load->library('template');

        // Set public methods (no permission required)
        $this->set_public_methods([]);

        // Override method-to-action mapping if needed
        $this->set_method_permissions([
            'remove' => 'delete',
            'sync' => 'create',
            'action' => 'edit',
            'action_process' => 'edit'
        ]);
    }

    public function index()
    {

        if ($_GET['keyword_category']) {
            $keyword_category = $_GET['keyword_category'];
        } else {
            $keyword_category = "Username";
        }
        $data['keyword_category'] = $keyword_category;
        $keyword = $_GET['keyword'];

        if ($_GET['start_date'] == "") {
            $start_date = DATE("Y-m-01");
        } else {
            $start_date = $_GET['start_date'];
        }
        if ($_GET['until_date'] == "") {
            $until_date = DATE("Y-m-d");
        } else {
            $until_date = $_GET['until_date'];
        }

        $data['start_date'] = $start_date;
        $data['until_date'] = $until_date;
        $data['brand'] = $brand;

        $data['title'] = 'Influencer - ' . $this->template->title();

        $qry = "";
        $qry = " 1=1 ";

        $ids = $_GET['ids'];
        $data['ids'] = $ids;
        if ($ids) {
            $qry .= " AND id  IN ($ids) ";
        }


        if ($brand) {
            $qry .= " AND brand = '$brand' ";
        }

        $status = $_GET['status'];
        $statusArray = $status ? explode(',', $status) : [];
        $text = '';
        foreach ($statusArray as $k => $v) {
            $text .= "'" . $v . "',";
        }
        $text = substr($text, 0, -1);

        if ($text) {
            $qry .= " AND status_reach IN ($text) ";
        }

        if ($keyword) {
            if ($keyword_category == "Nama Creator") {
                $qry .= " AND full_name LIKE '%$keyword%' ";
            } else if ($keyword_category == "Username") {
                $qry .= " AND influencer.username LIKE '%$keyword%' ";
            } else if ($keyword_category == "URL") {
                $qry .= " AND influencer.url LIKE '%$keyword%' ";
            } else if ($keyword_category == "Keterangan") {
                $qry .= " AND influencer.desc LIKE '%$keyword%' ";
            } else if ($keyword_category == "Platform") {
                $qry .= " AND influencer.type LIKE '%$keyword%' ";
            } else if ($keyword_category == "Niche") {
                $qry .= " AND influencer.niche LIKE '%$keyword%' ";
            } else if ($keyword_category == "PIC") {
                $qry .= " AND influencer.pic LIKE '%$keyword%' ";
            }
        }

        $query = $this->mymodel->selectWithQuery("SELECT COUNT(id) AS count
        FROM influencer
        WHERE $qry
        ");

        $data['page'] = CEIL($query[0]['count'] / 30);

        $data['notif'] = '<p class="mb-1"><label class="text-notif">' . $this->template->separator_only($query[0]['count']) . ' data ditemukan!</label></p>';

        $item = '';

        $current_page = intval($_GET['page']);
        if ($current_page <= 1) {
            $current_page = 1;
        }

        $url = base_url() . '/influencer/' . $this->template->get_param();
        $data['url_1'] = $this->template->get_param_without_status($url);
        $data['url_2'] = $this->template->get_param_without_keyword_category($url);
        $data['param'] = $this->template->get_param();
        $data['param_pagination'] = $this->template->get_param_without('page');
        $data['pagination'] = $this->template->pagination($data['page'], $current_page, $data['param_pagination']);

        $data['content'] = $this->load->view("influencer/all", $data, true);
        $this->load->view("TemplateDashboard", $data);
    }

    public function item()
    {
        $data['template'] = $this->template;

        $keyword_category = $_GET['keyword_category'] ?? "Username";
        $data['keyword_category'] = $keyword_category;
        $keyword = $_GET['keyword'] ?? '';

        $start_date = $_GET['start_date'] ?? date('Y-m-d');
        $until_date = $_GET['until_date'] ?? date('Y-m-d');
        $data['start_date'] = $start_date;
        $data['until_date'] = $until_date;

        $qry = "";
        $qry = " 1=1 ";

        $ids = $_GET['ids'];
        $data['ids'] = $ids;
        if ($ids) {
            $qry .= " AND id  IN ($ids) ";
        }


        if ($brand) {
            $qry .= " AND brand = '$brand' ";
        }

        $status = $_GET['status'];
        $statusArray = $status ? explode(',', $status) : [];
        $text = '';
        foreach ($statusArray as $k => $v) {
            $text .= "'" . $v . "',";
        }
        $text = substr($text, 0, -1);

        if ($text) {
            $qry .= " AND status_reach IN ($text) ";
        }

        if ($keyword) {
            if ($keyword_category == "Nama Creator") {
                $qry .= " AND full_name LIKE '%$keyword%' ";
            } else if ($keyword_category == "Username") {
                $qry .= " AND influencer.username LIKE '%$keyword%' ";
            } else if ($keyword_category == "URL") {
                $qry .= " AND influencer.url LIKE '%$keyword%' ";
            } else if ($keyword_category == "Keterangan") {
                $qry .= " AND influencer.desc LIKE '%$keyword%' ";
            } else if ($keyword_category == "Platform") {
                $qry .= " AND influencer.type LIKE '%$keyword%' ";
            } else if ($keyword_category == "Niche") {
                $qry .= " AND influencer.niche LIKE '%$keyword%' ";
            } else if ($keyword_category == "PIC") {
                $qry .= " AND influencer.pic LIKE '%$keyword%' ";
            }
        }

        $per_page_options = [10, 20, 30, 50, 100, 500];
        $limit = isset($_GET['limit']) && in_array($_GET['limit'], $per_page_options) 
                ? (int)$_GET['limit'] 
                : 10;
        $data['limit'] = $limit;
        $data['per_page_options'] = $per_page_options;

        $current_page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($current_page - 1) * $limit;

        $allowed_columns = ['id', 'username', 'follower', 'avg_view', 'cpm', 'avg_view_2', 'cpm_2', 'ratecard', 'pic'];
        $sort_column = in_array($_GET['sort_column'] ?? '', $allowed_columns) 
                    ? $_GET['sort_column'] 
                    : 'id';
        $sort_order = strtoupper($_GET['sort_order'] ?? '') === 'ASC' 
                    ? 'ASC' 
                    : 'DESC';

        $count_query = $this->mymodel->selectWithQuery("SELECT COUNT(*) as total FROM influencer WHERE $qry");
        $total_data = $count_query[0]['total'] ?? 0;
        $data['total_data'] = $total_data;
        $data['page'] = ceil($total_data / $limit);
        $data['current_page'] = $current_page;

        $query = $this->mymodel->selectWithQuery("SELECT * FROM influencer
            WHERE $qry 
            ORDER BY $sort_column $sort_order
            LIMIT $offset, $limit
        ");

        $data['data'] = $query;
        $data['start'] = $offset + 1;
        $data['end'] = min($offset + $limit, $total_data);

        $this->load->view("influencer/item", $data);
    }

    public function edit()
    {
        $id = $_GET['id'];

        $sqlInf = "
            SELECT i.*, u.full_name AS created_by_name
            FROM influencer i
            LEFT JOIN `user` u ON u.id = i.created_by
            WHERE i.id = '$id'
            LIMIT 1
        ";
        $rowsInf = $this->mymodel->selectWithQuery($sqlInf);
        if (empty($rowsInf)) {
            show_error('Data influencer tidak ditemukan', 404);
            return;
        }
        $inf = $rowsInf[0];
        $data['data'] = $inf;

        $username = $this->db->escape_str($inf['username'] ?? '');
        $sqlDummy = "
            SELECT d.created_at AS d_created_at, u2.full_name AS d_created_by_name
            FROM influencer_dummy d
            LEFT JOIN `user` u2 ON u2.id = d.created_by   -- join berdasarkan id
            WHERE d.username = '$username' AND d.is_generated = 1
            ORDER BY d.created_at DESC
            LIMIT 1
        ";
        $rowsDummy = $this->mymodel->selectWithQuery($sqlDummy);

        if (!empty($rowsDummy)) {
            $d   = $rowsDummy[0];

            if (empty($d['d_created_by_name']) || $d['d_created_by_name'] == 1) {
                $who = 'System';
            } else {
                $who = $d['d_created_by_name'];
            }

            $when = date('d M Y H:i', strtotime($d['d_created_at']));
            $data['log_text'] = "Data di <b>generate</b> dari listing oleh <b>{$who}</b> pada <b>{$when}</b>.";
        } else {
            if (empty($inf['created_by_name']) || $inf['created_by'] == 1) {
                $who = 'System';
            } else {
                $who = $inf['created_by_name'];
            }

            $when = date('d M Y H:i', strtotime($inf['created_at']));
            $data['log_text'] = "Data <b>ditambahkan</b> oleh <b>{$who}</b> pada <b>{$when}</b>.";
        }


        $data['brand'] = $this->mymodel->selectWithQuery("SELECT * FROM brand ORDER BY name ASC");

        $data['pic']   = $this->mymodel->selectWithQuery("SELECT * FROM `user` ORDER BY full_name ASC");

        $data['niche'] = $this->mymodel->selectWithQuery("SELECT DISTINCT niche FROM niche ORDER BY niche ASC");

        $this->load->view("influencer/edit", $data);
    }



    public function update()
    {
        $user = $_SESSION['user'];

        $id = $_POST['id'];
        $dt = $_POST['dt'];
        $dt['updated_at'] = DATE("Y-m-d H:i:s");
        $dt['updated_by'] = $user['id'];

        if (is_array($dt['pic'])) {
            $dt['pic'] = implode(', ', $dt['pic']);
        }

        if (is_array($dt['brand'])) {
            $dt['brand'] = implode(', ', $dt['brand']);
        }

        if ($dt['status_reach'] == 'Belum Reachout' && !empty($dt['ratecard'])) {
            $dt['status_reach'] = 'Sudah Reachout';
        }

        $nama_creator = $dt['username'];

        $count = $this->mymodel->selectWithQuery("SELECT COUNT(endorse.id) as total, title 
                FROM endorse 
                INNER JOIN endorse_campaign ON endorse.id_campaign = endorse_campaign.id
                WHERE nama_creator = '$nama_creator' AND status_endorse IN ('Posted Content', 'Draft Content') 
                GROUP BY title; ");

        if (count($count) == 1 && $dt['status_reach'] != 'Affiliate') {
            $dt['status_reach'] = 'Pernah Kerjasama';
        } else if (count($count) > 1 && $dt['status_reach'] != 'Affiliate') {
            $dt['status_reach'] = 'Repeat Kerjasama';
        }

        if ($this->db->update('influencer', $dt, array('id' => $id))) {
            $msg = 'Update data berhasil!';
            echo $this->template->alert_success($msg);
        } else {
            $msg = 'Update data tidak berhasil!';
            echo $this->template->alert_danger($msg);
        }
    }


    public function create()
    {
        $data['data'] = array();

        $query = $this->mymodel->selectWithQuery("SELECT * FROM user ORDER BY full_name ASC");

        $data['pic'] = $query;

        $query = $this->mymodel->selectWithQuery("SELECT * FROM brand ORDER BY name ASC");

        $data['brand'] = $query;

        $data['niche'] = $this->mymodel->selectWithQuery("SELECT DISTINCT niche FROM niche ORDER BY niche ASC");

        $data['user'] = $_SESSION['user'];

        $this->load->view("influencer/create", $data);
    }


    public function store()
    {
        $user = $_SESSION['user'];

        $id = $_POST['id'];
        $dt = $_POST['dt'];

        $dt['username']   = trim(strval($dt['username'] ?? ''));
        $dt['created_at'] = date("Y-m-d H:i:s");
        $dt['created_by'] = $user['id'];

        if (is_array($dt['pic']))   { $dt['pic']   = implode(', ', $dt['pic']); }
        if (is_array($dt['brand'])) { $dt['brand'] = implode(', ', $dt['brand']); }

        $username     = $dt['username']; 
        $bank         = $_POST['dt']['bank'] ?? '';
        $no_rekening  = $_POST['dt']['no_rekening'] ?? '';
        $desc         = $_POST['dt']['desc'] ?? '';
        $status_reach = $_POST['dt']['status_reach'] ?? '';

        $exists = $this->db->where('username', $username)
                        ->limit(1)
                        ->get('influencer')
                        ->num_rows() > 0;

        if ($exists) {
            $msg = 'Data dengan username tersebut sudah ada';
            echo $this->template->alert_danger($msg);
            return; 
        }

        $dt2 = [
            'nama_creator' => $username,
            'bank'         => $bank,
            'no_rekening'  => $no_rekening,
            'description'  => $desc,
        ];

        if ($status_reach === 'Belum Reachout' && !empty($dt['ratecard'])) {
            $dt['status_reach'] = 'Sudah Reachout';
        }

        $this->db->trans_begin();

        try {
            $this->db->insert('influencer', $dt);

            $this->db->where('nama_creator', $username);
            $query = $this->db->get('influencer_cost');

            if ($query->num_rows() == 0) {
                $this->db->insert('influencer_cost', $dt2);
            } else {
                $this->db->where('nama_creator', $username);
                $this->db->update('influencer_cost', $dt2);
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi gagal!');
            }

            $this->db->trans_commit();
            $msg = 'Tambah data berhasil!';
            echo $this->template->alert_success($msg);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $msg = 'Tambah data tidak berhasil: ' . $e->getMessage();
            echo $this->template->alert_danger($msg);
        }
    }


    public function sync_all()
    {
        $id = $_GET['id'];
        $data['param'] = json_encode($_GET, true);
        $this->load->view("influencer/sync_all", $data);
    }

    public function sync_all_process()
    {
        $user = $_SESSION['user'];
        $dt = json_decode($_POST['param'], true);

        $data['start_date'] = $start_date;
        $data['until_date'] = $until_date;
        $data['brand'] = $dt['brand'];

        if ($dt['keyword_category']) {
            $keyword_category = $dt['keyword_category'];
        } else {
            $keyword_category = "Username";
        }
        $data['keyword_category'] = $keyword_category;
        $keyword = $dt['keyword'];

        if ($_GET['start_date']) {
            $start_date = $dt['start_date'];
        } else {
            $start_date = DATE('Y-m-d');
        }
        if ($_GET['until_date']) {
            $until_date = $dt['until_date'];
        } else {
            $until_date = DATE('Y-m-d');
        }
        $qry = "";
        $qry = " 1=1 ";

        $ids = $_GET['ids'];
        $data['ids'] = $ids;
        if ($ids) {
            $qry .= " AND id  IN ($ids) ";
        }

        if ($brand) {
            $qry .= " AND brand = '$brand' ";
        }


        $status = $_GET['status'];
        $statusArray = $status ? explode(',', $status) : [];
        $text = '';
        foreach ($statusArray as $k => $v) {
            $text .= "'" . $v . "',";
        }
        $text = substr($text, 0, -1);

        if ($text) {
            $qry .= " AND status_reach IN ($text) ";
        }

        if ($keyword) {
            if ($keyword_category == "Nama Creator") {
                $qry .= " AND full_name LIKE '%$keyword%' ";
            } else if ($keyword_category == "Username") {
                $qry .= " AND influencer.username LIKE '%$keyword%' ";
            } else if ($keyword_category == "URL") {
                $qry .= " AND influencer.url LIKE '%$keyword%' ";
            } else if ($keyword_category == "Keterangan") {
                $qry .= " AND influencer.desc LIKE '%$keyword%' ";
            } else if ($keyword_category == "Platform") {
                $qry .= " AND influencer.type LIKE '%$keyword%' ";
            } else if ($keyword_category == "Niche") {
                $qry .= " AND influencer.niche LIKE '%$keyword%' ";
            } else if ($keyword_category == "PIC") {
                $qry .= " AND influencer.pic LIKE '%$keyword%' ";
            }
        }

        $mode = $_GET['mode'];
        $ids = $_GET['ids'];
        if ($mode == "refresh_data") {
            $qry = " id IN ($ids) ";
        }

        $list = $this->mymodel->selectWithQuery("SELECT * FROM influencer WHERE $qry AND status = 'Aktif' ");

        $dt = array();
        $syncedTiktok = 0;
        $failedTiktok = 0;
        $syncErrors = array();

        foreach ($list as $kl => $vl) {
            $id = $vl['id'];
            $query = $vl;

            $endorse = $this->mymodel->selectWithQuery("SELECT COUNT(id) as frequency, SUM(total_cost) as total_cost, SUM(views) as views, 
            AVG(views) as avg_views, 
            AVG(likes+comment+share_save) as avg_interaksi, 
            SUM(likes) as likes,
            SUM(share_save) as share,
            SUM(comment) as comment
            FROM endorse WHERE influencer = '$id'
            AND link_upload != ''
            ");
            $endorse = $endorse[0];

            $dt = array();
            $dt['sync_at'] = DATE("Y-m-d H:i:s");
            $dt['frequency'] = $endorse['frequency'];
            $dt['total_cost'] = $endorse['total_cost'];
            $dt['view'] = $endorse['views'];
            $dt['like'] = $endorse['likes'];
            $dt['comment'] = $endorse['comment'];
            $dt['collect'] = $endorse['collect'];
            $dt['share'] = $endorse['share'];
            $dt['avg_view'] = $endorse['avg_views'];
            $dt['avg_interaksi'] = $endorse['avg_interaksi'];
            if ($endorse['total_cost'] > 0 && $endorse['views'] > 0) {
                $dt['cpm'] = $endorse['total_cost'] / $endorse['views'] * 1000;
            } else {
                $dt['cpm'] = 0;
            }

            $url = $query['url'];
            if ($query['type'] == "Tiktok") {
                $result = $this->template->syncTiktokProfile('influencer', $id, 'Tiktok', $url);
                if (!empty($result['status'])) {
                    $this->db->update('influencer', $dt, array('id' => $id));
                    $syncedTiktok++;
                } else {
                    $failedTiktok++;
                    if (count($syncErrors) < 5) {
                        $syncErrors[] = "ID {$id}: " . ($result['msg'] ?? 'Gagal sinkronisasi TikTok');
                    }
                }
                continue;
            }

            $this->db->update('influencer', $dt, array('id' => $id));
            if ($query['type'] != "Tiktok") {
                $this->template->enqueue_scrape('influencer', $id, $query['type'], $url, 10);
                continue;
            }

            $response = $this->template->get_account_id($query['type'], $query['url']);
            // print_r($response);die;
            if ($response['status'] == false) {
                // $msg = $response['msg'];
                // echo $this->template->alert_danger($msg);
                // die;
            } else {
                $dt['updated_at'] = DATE("Y-m-d H:i:s");
                $dt['updated_by'] = strval($user['id']);
                $dt['account_id'] = $response['data']['account_id'];
                // print_r($response);die;
                $dt['img'] = $response['data']['img'];
                $dt['follower'] = $response['data']['follower'];
                $dt['media_count'] = $response['data']['media_count'];
                // print_r($dt);die;
                $this->db->update('influencer', $dt, array('id' => $id));

                if ($query['type'] == "Tiktok") {
                    $url = $query['url'];
                    preg_match('/@([a-zA-Z0-9_]+)/', $url, $matches);
                    $response = $this->template->get_post_list($query['type'], $response['data']['account_id'], $response['data']['username'] ?? '');
                } else {
                    $response = $this->template->get_post_list($query['type'], $response['data']['account_id'], $response['data']['username'] ?? '');
                }

                if ($response['status'] == false) {
                    // $msg = $response['msg'];
                    // echo $this->template->alert_danger($msg);
                    // die;
                } else {
                    $dt = array();
                    $dt['updated_at'] = DATE("Y-m-d H:i:s");
                    $dt['updated_by'] = strval($user['id']);
                    $dt['like'] = 0;
                    $dt['comment'] = 0;
                    $dt['collect'] = 0;
                    $dt['share'] = 0;
                    $dt['view'] = 0;
                    // print_r($response['data']);
                    $i = 0;
                    foreach ($response['data'] as $k => $v) {
                        $dt['like'] += $v['like'];
                        $dt['comment'] += $v['comment'];
                        $dt['collect'] += $v['collect'];
                        $dt['share'] += $v['share'];
                        $dt['view'] += $v['view'];
                        if ($i >= 10) {
                            break;
                        }
                        $i++;
                    }

                    if ($dt['view'] > 0) {
                        $dt['avg_view'] = $dt['view'] / $i;
                    }
                    if (($dt['like'] + $dt['comment'] + $dt['collect'] + $dt['share'])  > 0) {
                        $dt['avg_interaksi'] = ($dt['like'] + $dt['comment'] + $dt['collect'] + $dt['share']) / $i;
                    }
                    if ($dt['view'] > 0 && $dt['avg_interaksi'] > 0) {
                        $dt['er'] = $dt['avg_interaksi'] / $dt['avg_view'] * 100;
                    }
                    $dt['sync_at'] = DATE("Y-m-d H:i:s");
                    // $this->db->update('influencer', $dt, array('id' => $id));

                    $today = DATE("Y-m-d");
                    $logs = $this->mymodel->selectWithQuery("SELECT id FROM influencer_logs WHERE id_influencer = '$id' AND DATE(date) = '$today' ");
                    $logs = $logs[0];
                    if ($logs) {
                        $dt['updated_at'] = DATE("Y-m-d H:i:s");
                        $this->db->update('influencer_logs', $dt, array('id' => $logs['id']));
                    } else {
                        $dt['id_influencer'] = $id;
                        $dt['date'] = $today;
                        $dt['status'] = "Aktif";
                        $dt['created_at'] = DATE("Y-m-d H:i:s");
                        $this->db->update('influencer_logs', $dt);
                    }

                    $dt_2 = array();
                    $dt_2['sync_at'] = $dt['sync_at'];
                    $dt_2['frequency_2'] = $i;
                    $dt_2['er'] = $dt['er'];
                    $dt_2['updated_at'] = DATE("Y-m-d H:i:s");
                    $dt_2['updated_by'] = strval($user['id']);
                    $dt_2['view_2'] = $dt['view'];
                    $dt_2['like_2'] = $dt['like'];
                    $dt_2['collect_2'] = $dt['collect'];
                    $dt_2['share_2'] = $dt['share'];
                    $dt_2['comment_2'] = $dt['comment'];
                    $dt_2['avg_view_2'] = $dt['view'] / $i;
                    $dt_2['avg_interaksi_2'] = ($dt['like'] + $dt['comment'] + $dt['collect'] + $dt['share']) / $i;

                    if ($query['ratecard'] > 0 && $dt['view'] > 0) {
                        $dt_2['cpm_2'] = $query['ratecard'] / $dt_2['avg_view_2'] * 1000;
                    } else {
                        $dt_2['cpm_2'] = 0;
                    }

                    $this->db->update('influencer', $dt_2, array('id' => $id));

                    // $msg = "Refresh data berhasil!";
                    // echo $this->template->alert_success($msg);
                    // die;
                }
            }
        }
        if ($list) {
            $msg = "Refresh data selesai. TikTok berhasil: {$syncedTiktok}, TikTok gagal: {$failedTiktok}.";
            if (!empty($syncErrors)) {
                $msg .= "<br>Detail: " . implode('<br>', $syncErrors);
            }
            echo $this->template->alert_success($msg);
            die;
        } else {
            $msg = "Data tidak ditemukan!";
            echo $this->template->alert_danger($msg);
            die;
        }
    }

    public function sync()
    {
        $id = $_GET['id'];

        $query = $this->mymodel->selectWithQuery("SELECT * FROM influencer WHERE id = '$id'");

        $data['data'] = $query[0];
        $this->load->view("influencer/sync", $data);
    }

    public function sync_process()
    {


        $user = $_SESSION['user'];
        $id = $_POST['id'];

        $query = $this->mymodel->selectWithQuery("SELECT * FROM influencer WHERE id = '$id'");
        $query = $query[0];

        if ($query['status'] == "Tidak Aktif") {
            $msg = "Pastikan status influencer Aktif!";
            echo $this->template->alert_danger($msg);
            die;
        }

        if ($query['type'] == "Tiktok") {
            $result = $this->template->syncTiktokProfile('influencer', $id, 'Tiktok', $query['url']);
            if (empty($result['status'])) {
                echo $this->template->alert_danger($result['msg'] ?? 'Gagal sinkronisasi TikTok');
                return;
            }

            $aggregate = $this->get_endorse_aggregate_map(array($id));
            $internalUpdate = $this->build_internal_metrics_update(
                $aggregate[$id] ?? array(),
                strval($user['id']),
                DATE('Y-m-d H:i:s')
            );
            $internalUpdate['sync_at'] = DATE('Y-m-d H:i:s');
            $this->db->update('influencer', $internalUpdate, array('id' => $id));

            echo $this->template->alert_success("Refresh data berhasil!");
            return;
        }

        $endorse = $this->mymodel->selectWithQuery("SELECT COUNT(id) as frequency, SUM(total_cost) as total_cost, SUM(views) as views, 
        AVG(views) as avg_views, 
        AVG(likes+comment+share_save) as avg_interaksi, 
        SUM(likes) as likes,
        SUM(share_save) as share,
        SUM(comment) as comment
        FROM endorse WHERE influencer = '$id'
        AND link_upload != ''
        ");
        $endorse = $endorse[0];

        $dt = array();
        $dt['sync_at'] = DATE("Y-m-d H:i:s");
        $dt['frequency'] = $endorse['frequency'];
        $dt['total_cost'] = $endorse['total_cost'];
        $dt['view'] = $endorse['views'];
        $dt['like'] = $endorse['likes'];
        $dt['comment'] = $endorse['comment'];
        $dt['collect'] = $endorse['collect'];
        $dt['share'] = $endorse['share'];
        $dt['avg_view'] = $endorse['avg_views'];
        $dt['avg_interaksi'] = $endorse['avg_interaksi'];
        if ($endorse['total_cost'] > 0 && $endorse['views'] > 0) {
            $dt['cpm'] = $endorse['total_cost'] / $endorse['views'] * 1000;
        } else {
            $dt['cpm'] = 0;
        }

        $this->db->update('influencer', $dt, array('id' => $id));
        // die;
        $url = $query['url'];
        if ($query['type'] != "Tiktok") {
            $queue = $this->template->enqueue_scrape('influencer', $id, $query['type'], $url, 10);
            if ($queue['status']) {
                $msg = "Data internal berhasil diperbarui. Data eksternal sedang diproses, akan diperbarui dalam beberapa menit.";
                echo $this->template->alert_success($msg);
            } else {
                echo $this->template->alert_danger($queue['msg']);
            }
            die;
        }

        $response = $this->template->get_account_id($query['type'], $query['url']);
        if ($response['status'] == false) {
            if (($response['code'] ?? '') === 'rate_limited') {
                $msg = "Data internal berhasil diperbarui. Sinkronisasi TikTok ditunda karena batas API, silakan coba lagi dalam 1-2 menit.";
                echo $this->template->alert_success($msg);
                die;
            }

            $msg = $response['msg'];
            echo $this->template->alert_danger($msg);
            die;
        } else {
            $dt['updated_at'] = DATE("Y-m-d H:i:s");
            $dt['updated_by'] = strval($user['id']);
            $dt['account_id'] = $response['data']['account_id'];
            // print_r($response);die;
            $dt['img'] = $response['data']['img'];
            $dt['follower'] = $response['data']['follower'];
            $dt['media_count'] = $response['data']['media_count'];
            // print_r($dt);die;
            $this->db->update('influencer', $dt, array('id' => $id));

            if ($query['type'] == "Tiktok") {
                $url = $query['url'];
                $uri = explode("/", parse_url($url, PHP_URL_PATH));
                $username = $uri[1];
                $username = str_replace('@', '', $username);
                $response = $this->template->get_post_list($query['type'], $response['data']['account_id'], $response['data']['username'] ?? '');
            } else {
                $response = $this->template->get_post_list($query['type'], $response['data']['account_id'], $response['data']['username'] ?? '');
            }

            if ($response['status'] == false) {
                if (($response['code'] ?? '') === 'rate_limited') {
                    $msg = "Profil berhasil diperbarui, tetapi sinkronisasi posting TikTok ditunda karena batas API. Coba lagi dalam 1-2 menit.";
                    echo $this->template->alert_success($msg);
                    die;
                }

                $msg = $response['msg'];
                echo $this->template->alert_danger($msg);
                die;
            } else {
                $dt = array();
                $dt['updated_at'] = DATE("Y-m-d H:i:s");
                $dt['updated_by'] = strval($user['id']);
                $dt['like'] = 0;
                $dt['comment'] = 0;
                $dt['collect'] = 0;
                $dt['share'] = 0;
                $dt['view'] = 0;
                // print_r($response['data']);
                $i = 0;
                foreach ($response['data'] as $k => $v) {
                    $dt['like'] += $v['like'];
                    $dt['comment'] += $v['comment'];
                    $dt['collect'] += $v['collect'];
                    $dt['share'] += $v['share'];
                    $dt['view'] += $v['view'];
                    if ($i >= 10) {
                        break;
                    }
                    $i++;
                }


                if ($dt['view'] > 0) {
                    $dt['avg_view'] = $dt['view'] / $i;
                }
                if (($dt['like'] + $dt['comment'] + $dt['collect'] + $dt['share'])  > 0) {
                    $dt['avg_interaksi'] = ($dt['like'] + $dt['comment'] + $dt['collect'] + $dt['share']) / $i;
                }
                if ($dt['view'] > 0 && $dt['avg_interaksi'] > 0) {
                    $dt['er'] = $dt['avg_interaksi'] / $dt['avg_view'] * 100;
                }
                $dt['sync_at'] = DATE("Y-m-d H:i:s");
                // $this->db->update('influencer', $dt, array('id' => $id));

                $today = DATE("Y-m-d");
                $logs = $this->mymodel->selectWithQuery("SELECT id FROM influencer_logs WHERE id_influencer = '$id' AND DATE(date) = '$today' ");
                $logs = $logs[0];
                if ($logs) {
                    $dt['updated_at'] = DATE("Y-m-d H:i:s");
                    $this->db->update('influencer_logs', $dt, array('id' => $logs['id']));
                } else {
                    $dt['id_influencer'] = $id;
                    $dt['date'] = $today;
                    $dt['status'] = "Aktif";
                    $dt['created_at'] = DATE("Y-m-d H:i:s");
                    $this->db->insert('influencer_logs', $dt);
                }

                $dt_2 = array();
                $dt_2['sync_at'] = $dt['sync_at'];
                $dt_2['frequency_2'] = $i;
                $dt_2['er'] = $dt['er'];
                $dt_2['updated_at'] = DATE("Y-m-d H:i:s");
                $dt_2['updated_by'] = strval($user['id']);
                $dt_2['view_2'] = $dt['view'];
                $dt_2['like_2'] = $dt['like'];
                $dt_2['collect_2'] = $dt['collect'];
                $dt_2['share_2'] = $dt['share'];
                $dt_2['comment_2'] = $dt['comment'];
                $dt_2['avg_view_2'] = $dt['view'] / $i;
                $dt_2['avg_interaksi_2'] = ($dt['like'] + $dt['comment'] + $dt['collect'] + $dt['share']) / $i;

                if ($query['ratecard'] > 0 && $dt['view'] > 0) {
                    $dt_2['cpm_2'] = $query['ratecard'] / $dt_2['avg_view_2'] * 1000;
                } else {
                    $dt_2['cpm_2'] = 0;
                }

                $this->db->update('influencer', $dt_2, array('id' => $id));

                $msg = "Refresh data berhasil!";
                echo $this->template->alert_success($msg);
                die;
            }
        }
    }

    public function remove()
    {
        $id = $_GET['id'];
        $data['data']['id'] = $id;
        $this->load->view("influencer/delete", $data);
    }

    public function delete()
    {

        $user = $_SESSION['user'];

        $id = $_POST['id'];



        if ($this->db->delete('influencer', array('id' => $id))) {
            $msg = 'Hapus data berhasil!';
            echo $this->template->alert_success($msg);
        } else {
            $msg = 'Hapus data tidak berhasil!';
            echo $this->template->alert_danger($msg);
        }
    }

    public function action()
    {

        $id_selected_v2 = $_POST['id_selected_v2'];

        $id_selected = $_POST['id_selected'];
        if ($id_selected) {
            $id = explode(',', $id_selected);
        }

        $is_manual = $_POST['is_manual'];
        $marketplace = $_POST['marketplace'];
        $brand = $_POST['brand'];
        $order_id = $_POST['order_id'];
        $code = $_GET['code'];
        $data['data']['id'] = $id;
        $data['data']['code'] = $code;
        if ($code == "hapus_data") {
            $data['question'] = "Apakah kamu yakin ingin menghapus data influencer ini?";
            $data['btn'] = "Hapus Data";
        } else if ($code == "refresh_data") {
            $data['question'] = "Apakah kamu yakin ingin merefresh data influencer ini?";
            $data['btn'] = "Refresh Data";
        } else if ($code == "ubah_status") {
            $data['question'] = "Apakah kamu yakin ingin mengubah status data influencer ini?";
            $data['btn'] = "Ubah Status Influencer";
        } else if ($code == "ubah_status_data") {
            $data['question'] = "Apakah kamu yakin ingin mengubah status data ini?";
            $data['btn'] = "Ubah Status";
        }
        $this->load->view("influencer/action", $data);
    }

    public function action_process()
    {

        $list_id = "";
        $code = $_POST['code'];
        $user = $_SESSION['user'];

        $id_selected = $_POST['id_selected'];
        if ($id_selected) {
            $id = explode(',', $id_selected);
        }
        $is_manual = $_POST['is_manual'];
        $marketplace = $_POST['marketplace'];
        $brand = $_POST['brand'];
        $order_id = $_POST['order_id'];
        if ($code == "hapus_data") {
            foreach ($id as $k => $v) {
                $list_id .= "'" . $v . "',";
            }

            $list_id = substr($list_id, 0, -1);
            if ($list_id) {
                $dt = array();
                $this->db->delete('influencer', "id IN ($list_id)");
                $msg = 'Hapus data berhasil!';
                echo $this->template->alert_success($msg);
            } else {
                $msg = 'Pastikan kamu sudah memilih minimal 1 data!';
                echo $this->template->alert_danger($msg);
            }
        } else if ($code == "refresh_data") {
            foreach ($id as $k => $v) {
                if ($v > 0) {
                    $list_id .= "" . $v . ",";
                }
            }
            $list_id = substr($list_id, 0, -1);
            if ($list_id) {

                $url = base_url() . '/influencer/sync-all-process?mode=refresh_data&ids=' . $list_id;
                $curl = curl_init();

                // echo $url;die;
                // echo '<br>';

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                echo $response;
                die;
                curl_close($curl);
            } else {
                $msg = 'Pastikan kamu sudah memilih minimal 1 data!';
                echo $this->template->alert_danger($msg);
            }
        } else if ($code == "ubah_status") {
            $status = $_POST['status'];
            foreach ($id as $k => $v) {
                if ($v > 0) {
                    $list_id .= "" . $v . ",";
                }
            }
            $list_id = substr($list_id, 0, -1);
            if ($list_id) {
                $dtt = array();
                $dtt['status_reach'] = $status;
                $dtt['updated_at'] = DATE("Y-m-d H:i:s");
                $this->db->update('influencer', $dtt, "id IN ($list_id)");

                $msg = 'Ubah status influencer berhasil!';
                echo $this->template->alert_success($msg);
            } else {
                $msg = 'Pastikan kamu sudah memilih minimal 1 data!';
                echo $this->template->alert_danger($msg);
            }
        } else if ($code == "ubah_status_data") {
            $status = $_POST['status'];
            foreach ($id as $k => $v) {
                if ($v > 0) {
                    $list_id .= "" . $v . ",";
                }
            }
            $list_id = substr($list_id, 0, -1);
            if ($list_id) {
                $dtt = array();
                $dtt['status'] = $status;
                $dtt['updated_at'] = DATE("Y-m-d H:i:s");
                $this->db->update('influencer', $dtt, "id IN ($list_id)");

                $msg = 'Ubah status berhasil!';
                echo $this->template->alert_success($msg);
            } else {
                $msg = 'Pastikan kamu sudah memilih minimal 1 data!';
                echo $this->template->alert_danger($msg);
            }
        }
    }
    
    public function sync_external_process()
    {
        header('Content-Type: application/json; charset=utf-8');

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $user = $_SESSION['user'] ?? array();
        $userId = strval($user['id'] ?? '1');

        $cursor = intval($this->input->get('cursor'));
        if ($cursor < 0) {
            $cursor = 0;
        }

        $batch = intval($this->input->get('batch'));
        if ($batch <= 0) {
            $batch = 12;
        } else if ($batch > 100) {
            $batch = 100;
        }

        $maxRuntime = intval($this->input->get('max_runtime'));
        if ($maxRuntime <= 0) {
            $maxRuntime = 25;
        } else if ($maxRuntime > 50) {
            $maxRuntime = 50;
        }

        $startAt = microtime(true);

        $list = $this->get_daily_sync_batch($today, $cursor, $batch);
        if (empty($list)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Tidak ada data yang perlu di-update hari ini.',
                'data' => [
                    'sync_state' => 'idle',
                    'processed' => 0,
                    'synced_tiktok' => 0,
                    'queued_non_tiktok' => 0,
                    'deferred_rate_limited' => 0,
                    'failed' => 0,
                    'next_cursor' => $cursor,
                    'has_more' => false,
                    'remaining' => 0,
                    'batch_size' => $batch
                ]
            ]);
            exit;
        }

        $ids = array_map(function ($row) {
            return intval($row['id']);
        }, $list);
        $endorseMap = $this->get_endorse_aggregate_map($ids);

        $processed = 0;
        $syncedTiktok = 0;
        $queuedNonTiktok = 0;
        $deferredRateLimited = 0;
        $deferredSamples = array();
        $failed = 0;
        $nextCursor = $cursor;
        $errors = array();
        $stoppedByRuntime = false;

        foreach ($list as $row) {
            if ((microtime(true) - $startAt) >= $maxRuntime) {
                $stoppedByRuntime = true;
                break;
            }

            $id = intval($row['id']);
            $nextCursor = $id;
            $processed++;

            $agg = $endorseMap[$id] ?? array();
            $updateInternal = $this->build_internal_metrics_update($agg, $userId, $now);
            $this->db->update('influencer', $updateInternal, array('id' => $id));

            $platform = strval($row['type'] ?? '');
            $url = trim(strval($row['url'] ?? ''));

            if ($platform !== 'Tiktok') {
                $queue = $this->template->enqueue_scrape('influencer', $id, $platform, $url, 10);
                if (!empty($queue['status'])) {
                    $queuedNonTiktok++;
                    $this->db->update('influencer', array(
                        'sync_at' => $now,
                        'updated_at' => $now,
                        'updated_by' => $userId,
                    ), array('id' => $id));
                } else {
                    $failed++;
                    if (count($errors) < 5) {
                        $errors[] = "ID {$id}: " . ($queue['msg'] ?? 'Gagal enqueue');
                    }
                }
                continue;
            }

            $result = $this->template->syncTiktokProfile('influencer', $id, 'Tiktok', $url);
            if (!empty($result['status'])) {
                $syncedTiktok++;
                continue;
            }

            if (($result['code'] ?? '') === 'rate_limited') {
                $deferredRateLimited++;
                if (count($deferredSamples) < 5) {
                    $deferredSamples[] = array(
                        'id' => $id,
                        'platform' => 'Tiktok',
                        'reason' => strval($result['msg'] ?? 'Rate limited'),
                    );
                }
                continue;
            }

            $failed++;
            if (count($errors) < 5) {
                $errors[] = "ID {$id}: " . ($result['msg'] ?? 'Gagal sync TikTok');
            }
        }

        $remaining = $this->count_daily_sync_pending($today, $nextCursor);
        $hasMore = $remaining > 0;

        $syncState = 'completed';
        $cooldownSeconds = 0;

        if (
            $processed > 0
            && $deferredRateLimited === $processed
            && $syncedTiktok === 0
            && $queuedNonTiktok === 0
            && $failed === 0
        ) {
            $syncState = 'throttled';
            $cooldownSeconds = 120;
        } else if ($hasMore) {
            $syncState = 'running';
        }

        if ($syncState === 'throttled') {
            $message = "Sinkronisasi ditunda karena batas API TikTok. Diproses {$processed} data dan semuanya terkena rate limit. Coba lagi dalam 2 menit.";
        } else {
            $message = "Batch selesai. Diproses {$processed} data (TikTok {$syncedTiktok}, Queue {$queuedNonTiktok}, Ditunda {$deferredRateLimited}, Gagal {$failed}).";
            if ($stoppedByRuntime) {
                $message .= " Dihentikan karena batas waktu request, lanjutkan batch berikutnya.";
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'sync_state' => $syncState,
                'cooldown_seconds' => $cooldownSeconds,
                'processed' => $processed,
                'synced_tiktok' => $syncedTiktok,
                'queued_non_tiktok' => $queuedNonTiktok,
                'deferred_rate_limited' => $deferredRateLimited,
                'deferred_samples' => $deferredSamples,
                'failed' => $failed,
                'next_cursor' => $nextCursor,
                'has_more' => $hasMore,
                'remaining' => $remaining,
                'batch_size' => $batch,
                'stopped_by_runtime' => $stoppedByRuntime,
                'errors' => $errors
            ]
        ]);
        exit;
    }

    private function get_daily_sync_batch($today, $cursor, $limit)
    {
        $cursor = intval($cursor);
        $limit = intval($limit);

        $sql = "SELECT id, type, url, ratecard
        FROM influencer
        WHERE status = 'Aktif'
        AND COALESCE(url, '') != ''
        AND (sync_at IS NULL OR DATE(sync_at) < ?)
        AND id > ?
        ORDER BY id ASC
        LIMIT {$limit}";

        return $this->db->query($sql, array($today, $cursor))->result_array();
    }

    private function count_daily_sync_pending($today, $cursor)
    {
        $sql = "SELECT COUNT(1) as total
        FROM influencer
        WHERE status = 'Aktif'
        AND COALESCE(url, '') != ''
        AND (sync_at IS NULL OR DATE(sync_at) < ?)
        AND id > ?";

        $row = $this->db->query($sql, array($today, intval($cursor)))->row_array();
        return intval($row['total'] ?? 0);
    }

    private function get_endorse_aggregate_map($ids)
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return array();
        }

        $listId = implode(',', $ids);
        $sql = "SELECT influencer,
            COUNT(id) as frequency,
            COALESCE(SUM(total_cost), 0) as total_cost,
            COALESCE(SUM(views), 0) as views,
            COALESCE(AVG(views), 0) as avg_views,
            COALESCE(AVG(likes+comment+share_save), 0) as avg_interaksi,
            COALESCE(SUM(likes), 0) as likes,
            COALESCE(SUM(share_save), 0) as share,
            COALESCE(SUM(comment), 0) as comment
        FROM endorse
        WHERE influencer IN ({$listId})
        AND link_upload != ''
        GROUP BY influencer";

        $rows = $this->db->query($sql)->result_array();
        $map = array();
        foreach ($rows as $row) {
            $map[intval($row['influencer'])] = $row;
        }

        return $map;
    }

    private function build_internal_metrics_update($agg, $userId, $now)
    {
        $totalCost = floatval($agg['total_cost'] ?? 0);
        $totalViews = floatval($agg['views'] ?? 0);
        $cpm = ($totalCost > 0 && $totalViews > 0) ? ($totalCost / $totalViews * 1000) : 0;

        return array(
            'updated_at' => $now,
            'updated_by' => $userId,
            'frequency' => intval($agg['frequency'] ?? 0),
            'total_cost' => $totalCost,
            'view' => $totalViews,
            'like' => floatval($agg['likes'] ?? 0),
            'comment' => floatval($agg['comment'] ?? 0),
            'collect' => 0,
            'share' => floatval($agg['share'] ?? 0),
            'avg_view' => floatval($agg['avg_views'] ?? 0),
            'avg_interaksi' => floatval($agg['avg_interaksi'] ?? 0),
            'cpm' => $cpm,
        );
    }
    
}
