<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class PerubahanKuliahRequest extends CI_Controller {

    public function __construct() {
        parent::__construct();
        try {
            $this->Auth_model->checkModuleAllowed(get_class());
        } catch (Exception $ex) {
            $this->session->set_flashdata('error', $ex->getMessage());
            header('Location: /');
        }
        $this->load->library('bluetape');
        $this->load->model('PerubahanKuliah_model');
        $this->load->database();
    }

    public function index() {
        // Retrieve logged in user data
        $userInfo = $this->Auth_model->getUserInfo();
        // Retrieve requests for this user
        $requests = $this->PerubahanKuliah_model->requestsBy($userInfo['email']);
        foreach ($requests as &$request) {
            if ($request->answer === NULL) {
                $request->status = 'TUNGGU';
                $request->labelClass = 'secondary';
            } else if ($request->answer === 'confirmed') {
                $request->status = 'TERKONFIRMASI';
                $request->labelClass = 'success';
            } else if ($request->answer === 'rejected') {
                $request->status = 'DITOLAK';
                $request->labelClass = 'alert';
            }
            $request->requestDateString = $this->bluetape->dbDateTimeToReadableDate($request->requestDateTime);
            $request->requestByName = $this->bluetape->getName($request->requestByEmail);
            $request->answeredDateString = $this->bluetape->dbDateTimeToReadableDate($request->answeredDateTime);
        }
        unset($request);

        $this->load->view('PerubahanKuliahRequest/main', array(
            'currentModule' => get_class(),
            'requestByEmail' => $userInfo['email'],
            'requestByName' => $userInfo['name'],
            'requests' => $requests,
        ));
    }

    public function add() {
        try {
            date_default_timezone_set("Asia/Jakarta");
            $userInfo = $this->Auth_model->getUserInfo();
            $this->db->insert('PerubahanKuliah', array(
                'requestByEmail' => $userInfo['email'],
                'requestDateTime' => strftime('%Y-%m-%d %H:%M:%S'),
                'mataKuliahName' => $this->input->post('mataKuliahName'),
                'mataKuliahCode' => $this->input->post('mataKuliahCode'),
                'class' => $this->input->post('class'),
                'changeType' => $this->input->post('changeType'),
                'fromDateTime' => $this->input->post('fromDateTime'),
                'fromRoom' => $this->input->post('fromRoom'),
                'toDateTime' => $this->input->post('toDateTime'),
                'toRoom' => $this->input->post('toRoom'),
                'remarks' => $this->input->post('remarks'),
            ));
            $this->session->set_flashdata('info', 'Permohonan perubahan kuliah sudah dikirim. Silahkan cek statusnya secara berkala di situs ini.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        header('Location: /PerubahanKuliahRequest');
    }

}
