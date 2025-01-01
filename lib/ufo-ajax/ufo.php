<?php
/*
UfoAjax is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/ufo-ajax/blob/master/LICENSE
*/
declare(strict_types=1);

class Ufo {
	public static function get_clean(){
		$instance = self::get_instance();
		$messages = json_encode($instance->messages);
		$instance->messages = [];
		return $messages;
	}

	public static function log(...$args){
		self::add('log',['args'=>$args]);
	}

	public static function output($target, $content){
		self::add('output',['target'=>$target,'content'=>(string)$content]);
	}

	public static function attribute($target, $name, $content){
		self::add('attribute',['target'=>$target,'name'=>$name,'content'=>$content]);
	}

	public static function close($target){
		self::add('close',['target'=>$target]);
	}

	public static function post($id, $url){
		self::add('post',['id'=>$id,'url'=>$url]);
	}

	public static function get($id, $url){
		self::add('get',['id'=>$id,'url'=>$url]);
	}

	public static function interval($id, $interval){
		self::add('interval',['id'=>$id,'interval'=>$interval]);
	}

	public static function update($id){
		self::add('update',['id'=>$id]);
	}

	public static function stop($id){
		self::add('stop',['id'=>$id]);
	}

	public static function remove($id){
		self::add('unset',['id'=>$id]);
	}

	public static function abort($id){
		self::add('abort',['id'=>$id]);
	}

	public static function callbackadd($id,$point,$func,...$args){
		self::add('callbackadd',['id'=>$id,'point'=>$point,'func'=>$func,'args'=>$args]);
	}

	public static function callbackremove($id,$point,$func){
		self::add('callbackremove',['id'=>$id,'point'=>$point,'func'=>$func]);
	}

	public static function callbackclear($id){
		self::add('callbackclear',['id'=>$id]);
	}

	public static function call($func,...$args){
		self::add('call',['func'=>$func,'args'=>$args]);
	}

	public static function dataset($key, $value){
		self::add('dataset',['key'=>$key,'value'=>$value]);
	}

	public static function nop(){
		self::add('nop');
	}

	public static function websocket_permission($uid, $permissions){
		self::add_websocket('permission',['uid'=>$uid,'permissions'=>$permissions]);
	}

	public static function websocket_message($channel, $message){
		self::add_websocket('message',['channel'=>$channel,'message'=>$message]);
	}

	public static function websocket_subscribe($channel){
		self::add('websocket_subscribe',['channel'=>$channel]);
	}

	public static function websocket_accept_uid($permissions){
		if(empty($_POST['ufo_websocket_uid']) || !is_string($_POST['ufo_websocket_uid'])){
			return;
		}
		$uid = $_POST['ufo_websocket_uid'];
		self::websocket_permission($uid, $permissions);
		return $uid;
	}

	private static $instance;
	private static function get_instance(){
		if(!isset(self::$instance)) self::$instance = new self();
		return self::$instance;
	}

	private static function add($type, $data = []){
		$data['type'] = $type;
		$instance = self::get_instance();
		$instance->messages[] = $data;
	}

	private static function add_websocket($type, $data = []){
		$data['type'] = $type;
		$instance = self::get_instance();
		$instance->websocket_messages[] = $data;
	}

	private $messages = [];
	private $websocket_messages = [];
	private function __construct(){
		$this->handle_connection_close_request();
		register_shutdown_function([$this,'write']);
	}

	private function handle_connection_close_request(){
		if(!headers_sent() && !empty($_SERVER['HTTP_UFO_CONNECTION']) && strtolower($_SERVER['HTTP_UFO_CONNECTION']) == 'close'){
			header('Connection: close');
		}
	}

	public function write(){
		if(!empty($this->websocket_messages)
			&& defined('UFO_WEBSOCKET_HOST')
			&& defined('UFO_WEBSOCKET_BACKEND_PORT')
		){
			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			socket_bind($socket, UFO_WEBSOCKET_HOST);
			if(socket_last_error()) self::log(socket_strerror(socket_last_error()));
			socket_connect($socket, UFO_WEBSOCKET_HOST, UFO_WEBSOCKET_BACKEND_PORT);
			if(socket_last_error()) self::log(socket_strerror(socket_last_error()));

			foreach($this->websocket_messages as $msg){
				socket_write($socket, json_encode($msg).PHP_EOL);
			}
		}
		if(!empty($this->messages)){
			$error = error_get_last();
			if(!headers_sent()) {
				header('Content-Type: application/json; charset=utf-8');
			}
			if(headers_sent() || isset($error)) echo "\x02";
			echo json_encode($this->messages);
		}
	}
}
