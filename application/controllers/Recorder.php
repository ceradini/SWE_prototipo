<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recorder extends CI_Controller {
	function __construct()
    {
        parent::__construct();
    }

	function index()
	{
		$data['theme_js'] = array('recorder/recorder.js','recorder/mediaDevices.js');

		$content_data = array();

		$data['content']  = $this->load->view('recorder/main', $content_data, TRUE);

		$this->load->view('template', $data);
	}

	function save_audio()
	{
		if(isset($_FILES['file']) && !$_FILES['file']['error']){
			$fname     = date('Y-m-d_H-i-s');
			$path      = getcwd() . '/audio_files';
			$wav_file  = $path . '/' . $fname . ".wav";
			$flac_file = $path . '/' . $fname . ".FLAC";

			// save wav file
		    move_uploaded_file($_FILES['file']['tmp_name'], 'audio_files/' . $fname . '.wav');

		    // convert wav to FLAC
		    $command = '/usr/bin/ffmpeg -i ' . $wav_file . ' -ac 1 ' . $flac_file;
		    exec($command);

		    // delete old wav file
		    unlink($wav_file);

		    // upload file to google storage
		    $command = 'gsutil cp ' . $flac_file . ' gs://ajarvis-recorder';
		    exec($command); echo $command;
		}
	}
}
