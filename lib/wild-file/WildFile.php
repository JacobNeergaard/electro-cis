<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/wild-file/blob/master/LICENSE
*/
declare(strict_types=1);

class WildFile {
	private $table;
	private $table_chunked, $chunks_subfolder = '.chunked_upload';
	private $dir;
	private $dbconn;
	private $storage;
	private string $idfield = 'id';
	private string $idfield_chunked = 'id';
	private $callback = [];

	public const NAME = 1;
	public const SIZE = 2;
	public const MIME = 3;
	public const CHECKSUM = 4;
	public const TRANSFER_TOKEN = 5;

	public function __construct($dbconn,$storage,$table,$dir = null){
		if(!is_int($dbconn->thread_id)) {
			$this->exception('No DB Connection');
		}
		$this->dbconn = $dbconn;
		if(!is_dir($storage)) {
			$this->exception('Invalid directory');
		}
		$this->storage = $storage;
		$table = explode('.',$table);
		$this->dir = empty($dir) ? str_replace('`','',end($table)) : $dir;
		foreach($table as &$value) {
			if(strpos($value,'`')===false) $value = '`'.$value.'`';
		}
		$this->table = implode('.',$table);
	}
	public function set_file_chunk_table($table_chunked = null){
		if(!isset($table_chunked)){
			$this->table_chunked = preg_replace("/`$/", "_chunked_upload`", $this->table);
		} else {
			$table_chunked = explode('.',$table_chunked);
			foreach($table_chunked as &$value) {
				if(strpos($value,'`')===false) $value = '`'.$value.'`';
			}
			$this->table_chunked = implode('.',$table_chunked);
		}
		if($this->table == $this->table_chunked){
			throw new \Exception("Database table for chunked upload can't be the same as file table.");
		}
	}
	public function set_idfield(string $idfield, ?string $idfield_chunked = null){
		$this->idfield = $idfield;
		$this->idfield_chunked = $idfield_chunked ?? $idfield;
	}
	public function set_callback($type,$function = null){
		if($function) {
			$this->callback[$type] = $function;
		}
		else {
			unset($this->callback[$type]);
		}
	}
	public function store_string($string,$field = [],$checksum_input = null){
		$checksum = hash('sha256',$string);
		if(func_num_args()===3) $this->checksum_check($checksum,$checksum_input);
		$this->auto_value($field, [
			self::SIZE => strlen($string),
			self::CHECKSUM => $checksum
		]);
		$id = $this->db_store($field);
		$this->validate_id($id);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		if(file_put_contents($path.$filename,$string)===false) {
			$this->exception('Error store_string: '.$path);
		}
		$this->checksum_store($path,$filename,$checksum);
		$this->log('store_string: '.$id.'|'.$path.$filename);
	}
	public function store_post($FILES,$field = [],$checksum_input = null){
		if(!isset($FILES['tmp_name']) || !is_array($FILES['tmp_name'])) {
			$this->exception('Invalid post array');
		}
		if(empty($FILES['tmp_name'][0])) {
			$this->exception('No files uploaded');
		}
		foreach($FILES['error'] as $key => $error) {
			$this->callback_execute('store',$FILES['tmp_name'][$key]);
			if($error!==UPLOAD_ERR_OK) continue;
			$checksum = hash_file('sha256',$FILES['tmp_name'][$key]);
			if(func_num_args()===3) $this->checksum_check($checksum,$checksum_input[$FILES['name'][$key]]);
			$this->auto_value($field, [
				self::NAME => $FILES['name'][$key],
				self::SIZE => $FILES['size'][$key],
				self::MIME => $FILES['type'][$key],
				self::CHECKSUM => $checksum
			]);
			$id = $this->db_store($field);
			$this->validate_id($id);
			$path = $this->create_path($id);
			$filename = $this->filename($id);
			move_uploaded_file($FILES['tmp_name'][$key],$path.$filename);
			$this->checksum_store($path,$filename,$checksum);
			$this->log('store_post: '.$id.'|'.$path.$filename);
		}
	}
	public function read_chunk_input_headers(){
		$range_match = preg_match("/^bytes ([0-9]+)-([0-9]+)\/([0-9]+)$/",$_SERVER['HTTP_CONTENT_RANGE'],$range_values);
		if($range_match !== 1){
			$this->exception("Invalid Content-Range header");
		}
		$disposition_match = preg_match("/^attachment; filename=\"([^\"]+)\"$/",$_SERVER['HTTP_CONTENT_DISPOSITION'],$disposition_values);
		if($disposition_match !== 1){
			$this->exception("Invalid Content-Disposition header");
		}
		return [
			'checksum' => $_SERVER['HTTP_X_WILDFILE_CHECKSUM'] ?? null,
			'transfer' => $_SERVER['HTTP_X_WILDFILE_TRANSFER'] ?? null,
			'mime' => $_SERVER['CONTENT_TYPE'],
			'name' => $disposition_values[1],
			'range_start' => intval($range_values[1]),
			'range_end' => intval($range_values[2]),
			'file_size' => intval($range_values[3]),
		];
	}
	public function store_chunk_input($field = [], $metadata = null){
		if(!isset($metadata)){
			$metadata = $this->read_chunk_input_headers();
		}
		$this->auto_value($field, [
			self::NAME => $metadata['name'],
			self::SIZE => $metadata['file_size'],
			self::MIME => $metadata['mime'],
		]);
		$chunk = file_get_contents('php://input');
		return $this->store_chunk($chunk,$metadata['range_start'],$metadata['range_end'],$metadata['file_size'],$field,$metadata['checksum'],$metadata['transfer']);
	}
	public function store_chunk(string $chunk, int $range_start, int $range_end, int $size_input, array $field, string $checksum_input, $transfer_token = null){
		$chunk_size = strlen($chunk);
		if($chunk_size == 0 || $range_start + $chunk_size - 1 != $range_end){
			$this->exception("Invalid file chunk size ($chunk_size bytes, $range_start to $range_end)");
		}

		if(!isset($this->table_chunked)){
			$this->set_file_chunk_table();
		}

		if(!isset($transfer_token)){
			[$transfer_id, $transfer_token] = $this->db_store_transfer($field, $checksum_input);
			$this->validate_id($transfer_id);
		} else {
			$transfer_id = $this->validate_transfer_id($transfer_token, $field);
		}
		$path_chunks = $this->get_path_chunks();
		$filename_chunks = $this->filename($transfer_id);
		$file_uri = $path_chunks.$filename_chunks;
		/*
		Open with mode 'c' (writing only, allows seeking)
		then seek to chunk offset before writing,
		so we aren't limited to sequential chunks
		*/
		$fail_if = fn($name,$failure_value,$value) => $value === $failure_value ? $this->exception('Error store_chunk: '.$path_chunks." ($name)") : $value;
		
		$file = $fail_if('fopen', false, fopen($file_uri, 'c'));
		$fail_if('fseek', -1, fseek($file, $range_start));
		$fail_if('fwrite', false, fwrite($file, $chunk));
		$fail_if('fclose', false, fclose($file));

		$this->log('store_chunk: '.$transfer_id."($range_start-$range_end/$size_input)|".$path_chunks);

		$file_size = filesize($file_uri);
		if($file_size < $size_input){
			return ['status'=>'incomplete', 'transfer'=>$transfer_token];
		} elseif($file_size > $size_input){
			$this->exception("Error store_chunked_upload: $file_uri (file size doesn't match expected size)");
		}

		$checksum = hash_file('sha256',$file_uri);
		$this->checksum_check($checksum,$checksum_input);

		$this->auto_value($field, [
			self::SIZE => $file_size,
			self::CHECKSUM => $checksum,
			self::TRANSFER_TOKEN => $transfer_token
		]);

		$id = $this->db_store($field);
		$this->validate_id($id);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		if(rename($file_uri, $path.$filename)===false) {
			$this->exception('Error store_chunked_upload: '.$path);
		}
		$this->checksum_store($path,$filename,$checksum);
		$this->log('store_chunked_upload: '.$transfer_id.'->'.$id.'|'.$path.$filename);

		$file_id = $field[$this->idfield]['value'] ?? $id;
		return ['status'=>'complete', 'transfer'=>$transfer_token, 'file'=>$file_id];
	}
	private function validate_transfer_id($transfer_token, array $field) {
		$transfer_token = $this->dbconn->real_escape_string($transfer_token);
		$id_field = $this->dbconn->real_escape_string($this->idfield_chunked);
		$sql = "SELECT * FROM $this->table_chunked WHERE `$id_field`='$transfer_token'";
		$query = $this->db_query($sql);
		if($query->num_rows != 1){
			$this->exception('Invalid transfer token');
		}
		$transfer = $query->fetch_assoc();
		foreach($field as $key => $value){
			if(!isset($value['value']) || ($value['noescape'] ?? false) && str_ends_with($value['value'],'()')){
				continue;
			}
			if($value['value'] != $transfer[$key]){
				$this->exception("File chunk metadata does not match ongoing transfer ($transfer_token)");
			}
		}
		return $transfer['id'];
	}
	private function auto_value(&$field, $auto){
		foreach($field as &$value) {
			if(isset($value['auto']) && isset($auto[$value['auto']])) {
				$value['value'] = $auto[$value['auto']];
			}
		}
	}
	private function db_store($dbfield){
		$fieldset = $this->fieldset($dbfield);
		$sql = "INSERT INTO $this->table SET $fieldset";
		$this->db_query($sql);
		return $this->dbconn->insert_id;
	}
	private function db_store_transfer($field, $checksum_input){
		$field['checksum'] = ['value'=>$checksum_input];
		$generate_id = $field[$this->idfield_chunked]['generate'] ?? null;
		if(is_callable($generate_id)){
			$field[$this->idfield_chunked]['value'] = $generate_id();
		}
		$fieldset = $this->fieldset($field);
		$sql = "INSERT INTO $this->table_chunked SET $fieldset";
		$this->db_query($sql);
		return [$this->dbconn->insert_id, $field[$this->idfield_chunked]['value'] ?? $this->dbconn->insert_id];
	}
	private function checksum_store($path,$filename,$checksum){
		$content = 'SHA256 ('.$filename.') = '.$checksum.PHP_EOL;
		if(file_put_contents($path.$filename.'.sha256', $content)===false) {
			$this->exception('Error checksum_store: '.$path.$filename.'.sha256');
		}
	}
	private function checksum_check($checksum,$checksum_input){
		if($checksum!==$checksum_input) {
			$this->exception('Error checksum_check');
		}
	}
	public function replace_string($id,$string,$field = [],$checksum_input = null){
		$checksum = hash('sha256',$string);
		if(func_num_args()===4) $this->checksum_check($checksum,$checksum_input);
		$this->auto_value($field, [
			self::SIZE => strlen($string),
			self::CHECKSUM => $checksum,
		]);
		$this->validate_id($id);
		$this->db_replace($id,$field);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		if(file_put_contents($path.$filename,$string)===false) {
			$this->exception('Error store_string: '.$path);
		}
		$this->checksum_store($path,$filename,$checksum);
		$this->log('replace_string: '.$id.'|'.$path.$filename);
	}
	public function replace_post($id,$FILES,$field = [],$checksum_input = null){
		if($FILES['error']!==UPLOAD_ERR_OK) $this->exception('Upload error');
		$this->callback_execute('store',$FILES['tmp_name']);
		$checksum = hash_file('sha256',$FILES['tmp_name']);
		if(func_num_args()===4) $this->checksum_check($checksum,$checksum_input[$FILES['name']]);
		$this->auto_value($field, [
			self::NAME => $FILES['name'][$key],
			self::SIZE => $FILES['size'][$key],
			self::MIME => $FILES['type'][$key],
			self::CHECKSUM => $checksum
		]);
		$this->validate_id($id);
		$this->db_replace($id,$field);
		$path = $this->create_path($id);
		$filename = $this->filename($id);
		move_uploaded_file($FILES['tmp_name'],$path.$filename);
		$this->checksum_store($path,$filename,$checksum);
		$this->log('replace_post: '.$id.'|'.$path.$filename);
	}
	private function db_replace($id,$dbfield){
		if(empty($dbfield)) return;
		$fieldset = $this->fieldset($dbfield);
		$sql = "UPDATE $this->table SET $fieldset WHERE `$this->idfield`='$id'";
		$this->db_query($sql);
	}
	public function get($id,$field = []){
		$this->validate_id($id);
		$path = $this->get_path($id);
		$filename = $this->filename($id);
		$out = new WildFileOut($path.$filename);
		if($field) {
			$this->db_get($out,$id,$field);
		}
		return $out;
	}
	private function db_get($out,$id,$dbfield){
		$field = [];
		foreach($dbfield as $var) {
			$field[] = '`'.$this->dbconn->real_escape_string($var).'`';
		}
		$field = implode(',',$field);
		$sql = "SELECT $field FROM $this->table WHERE `$this->idfield`='$id'";
		$query = $this->db_query($sql);
		if($rs = $query->fetch_assoc()) {
			foreach($rs as $key => $value) {
				$out->$key = $value;
			}
		}
	}
	public function delete($array){
		if(!is_array($array)) $array = [$array];
		foreach($array as $id) {
			$this->validate_id($id);
			$path = $this->get_path($id);
			$filename = $this->filename($id);
			$this->file_delete($path.$filename);
			$this->db_delete($id);
			$this->log('delete: '.$id.'|'.$path.$filename);
		}
	}
	private function db_delete($id){
		$sql = "DELETE FROM $this->table WHERE `$this->idfield`='$id'";
		$this->db_query($sql);
	}
	private function file_delete($file){
		if(file_exists($file)){
			if(file_exists($file.'.sha256')){
				if(!unlink($file.'.sha256')) {
					$this->exception('Error unlink checksum: '.$file.'.sha256');
				}
			}
			if(!unlink($file)) {
				$this->exception('Error unlink: '.$file);
			}
		}
	}
	public function evict($array,$field = []){
		if(!is_array($array)) $array = [$array];
		foreach($array as $id) {
			$this->validate_id($id);
			$path = $this->get_path($id);
			$filename = $this->filename($id);
			$this->file_delete($path.$filename);
			$this->db_replace($id,$field);
			$this->log('evict: '.$id.'|'.$path.$filename);
		}
	}
	public function zip(){
		return new WildFileZip($this);
	}
	private function db_query($sql){
		$query = $this->dbconn->query($sql);
		if($this->dbconn->errno) {
			$this->exception('SQL Error: '.$this->dbconn->error);
		}
		return $query;
	}
	private function fieldset($dbfield){
		$fieldset = [];
		foreach($dbfield as $key => $var) {
			if(!isset($var['value'])) $this->exception('Missing value: '.json_encode([$key=>$var]));
			$field = '`'.$this->dbconn->real_escape_string((string) $key).'`=';
			if(isset($var['noescape']) && $var['noescape']===true) {
				$field .= $var['value'];
			}
			else {
				$field .= "'".$this->dbconn->real_escape_string((string) $var['value'])."'";
			}
			$fieldset[] = $field;
		}
		return implode(',',$fieldset);
	}
	private function get_path($id){
		$storage = $this->storage.DIRECTORY_SEPARATOR;
		$folder = $this->folder($id);
		return $storage.$folder;
	}
	private function create_path($id){
		$storage = $this->storage.DIRECTORY_SEPARATOR;
		$folder = $this->folder($id);
		if(!is_dir($storage.$folder)) {
			$folder_arr = explode(DIRECTORY_SEPARATOR, $folder);
			$dir='';
			foreach($folder_arr as $part) {
				$dir .= $part.DIRECTORY_SEPARATOR;
				if(!is_dir($storage.$dir) && strlen($storage.$dir)>0) {
					if(!mkdir($storage.$dir)) {
						$this->exception('Error mkdir: '.$storage.$dir);
					}
				}
			}
		}
		return $storage.$folder;
	}
	private function get_path_chunks(){
		$path = $this->storage.DIRECTORY_SEPARATOR.$this->dir.DIRECTORY_SEPARATOR.$this->chunks_subfolder.DIRECTORY_SEPARATOR;
		if(!is_dir($path)){
			if(!mkdir($path)) {
				$this->exception('Error mkdir: '.$path);
			}
		}
		return $path;
	}
	private function filename($id){
		return $id.'.bin';
	}
	private function validate_id($id){
		if(empty($id)) {
			$this->exception('Invalid fileid');
		}
	}
	private function folder($id){
		$parts = [];
		$parts[] = $this->dir;
		$str = (string) $id;
		while(strlen($str) > 2) {
			$parts[] = substr($str, -2);
			$str = substr($str, 0, -2);
		}
		return implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR;
	}
	private function callback_execute($type,$param) {
		if(!empty($this->callback[$type])) {
			$this->callback[$type]($param);
		}
	}
	protected function exception($message){
		$this->log($message,LOG_ERR);
		throw new \Exception($message);
	}
	protected function log($message,$priority = LOG_INFO){
		syslog($priority,$message);
	}
}

class WildFileOut {
	protected $file;
	protected $property = [];
	public function __construct($file){
		$this->file = $file;
	}
	public function __toString(){
		return file_get_contents($this->file);
	}
	public function output(){
		return readfile($this->file);
	}
	public function get_path(){
		return $this->file;
	}
	public function __set(string $name, string $value): void {
		$this->property[$name] = $value;
	}
	public function __get(string $name): string {
		return $this->property[$name];
	}
}

class WildFileHeader {
	public static function type($str){
		header('Content-Type: '.$str);
	}
	public static function filename($filename,$download = false){
		$download = $download ? 'attachment' : 'inline';
		header("Content-Disposition: ".$download."; filename*=UTF-8''".rawurlencode($filename));
	}
	public static function expires($datetime = null) {
		if(!$datetime) {
			$datetime = new DateTime('1 month');
		}
		$datetime->setTimezone(new DateTimeZone('UTC'));
		$seconds = (new DateTime())->diff($datetime)->format('%a') * 86400;
		header('Expires: '.$datetime->format('D, d M Y H:i:s \G\M\T'));
		header('Cache-Control: max-age='.$seconds);
		header_remove('Pragma');
	}
}

class WildFileZip extends WildFileOut {
	private $wf;
	private $archive;

	public function __construct($wf){
		$this->wf = $wf;
		$file = tempnam(sys_get_temp_dir(), 'wfzip_');
		$this->archive = new ZipArchive();
		$result = $this->archive->open($file, ZipArchive::OVERWRITE);
		if(!$result) {
			throw new \Exception('Error open ZipArchive: '.$file);
		}
		$this->file = $file;
	}
	public function add($id,$name = null){
		$file = $this->wf->get($id,$name ? [] : ['name']);
		$result = $this->archive->addFile($file->get_path(),$name ? $name : $file->name);
		if(!$result) {
			throw new \Exception('Error addFile ZipArchive: '.$file->get_path());
		}
	}
	public function close(){
		$result = $this->archive->close();
		if(!$result) {
			throw new \Exception('Error close ZipArchive');
		}
		$this->size = (string) filesize($this->file);
	}
	public function unlink(){
		$this->archive = null;
		unlink($this->file);
	}
}
