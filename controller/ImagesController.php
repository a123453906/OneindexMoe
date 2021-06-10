<?php 

class ImagesController{
	function generateRandomString($length) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $randomString;
        }
	
	function index(){
		if($this->is_image($_FILES["file"]) ){
		    $filename = $_FILES["file"]['name'];
			$content = file_get_contents( $_FILES["file"]['tmp_name']);
			$remotepath =  'images/'.date('Y/m/d/').$this->generateRandomString(10).'/';
			$remotefile = $remotepath.$filename;
			if( $_FILES["file"]['size'] < 4194304){
				$result = onedrive::upload(config('onedrive_root').$remotefile, $content);
			
				if($result){
					$root = get_absolute_path(dirname($_SERVER['SCRIPT_NAME'])).config('root_path');
					$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
					$url = $_SERVER['HTTP_HOST'].$root.'/'.$remotepath.rawurldecode($filename).((config('root_path') == '?')?'&s':'?s');
					$url = $http_type.str_replace('//','/', $url);
					view::direct($url);
				}
			}else{
				file_put_contents('./'.$_FILES["file"]['name'], $content);
				$request['headers'] = "Cookie: admin=".md5(config('password').config('refresh_token')).PHP_EOL;
				$request['headers'] .= "Host: ".$_SERVER['HTTP_HOST'];
				$request['curl_opt']=[CURLOPT_CONNECTTIMEOUT => 1,CURLOPT_TIMEOUT=>1,CURLOPT_FOLLOWLOCATION=>true];
				$http_type = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
				$request['url'] = $http_type.'127.0.0.1'.get_absolute_path(dirname($_SERVER['PHP_SELF'])).'?/admin/upload';
				$request['post_data'] = 'upload=1&delete=true&local='.urlencode('./'.$_FILES["file"]['name']).'&remote='.urlencode(config('onedrive_root').$remotepath.$filename);
				// UploadController::uploadImage(realpath('./'.$_FILES["file"]['name']), get_absolute_path('/'.$remotepath));
				// $request = UploadController::task_request();
				// $request['url'] = substr($request['url'],0,-4).'run';
				fetch::post($request);
				// $request['post_data'] = 'begin_task='.urlencode('/share/'.$remotepath.$filename);
				// fetch::post($request);
				$root = get_absolute_path(dirname($_SERVER['SCRIPT_NAME'])).config('root_path');
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				$url = $_SERVER['HTTP_HOST'].$root.'/'.$remotepath.rawurldecode($filename).((config('root_path') == '?')?'&s':'?s');
				$url = $http_type.str_replace('//','/', $url);
				return view::load('images/index')->with('message', $url);
			}
			
		}
		return view::load('images/index');
	}

	function is_image($file){
		$config = config('images@base');
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if(!in_array($ext,$config['exts'])){
			return false;
		}
		if($file['size'] > 104857600 || $file['size'] == 0){
			return false;
		}

		return true;
	}
}
