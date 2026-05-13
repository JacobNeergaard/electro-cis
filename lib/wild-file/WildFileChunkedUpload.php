<?php
/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/wild-file/blob/master/LICENSE
*/
declare(strict_types=1);

class WildFileChunkedUpload {
	const STATUS_INCOMPLETE = 0;
	const STATUS_COMPLETE = 1;

	public readonly string $transfer_id;
	public readonly string $file_uri;
	public readonly int $file_size;
	public readonly string $checksum;

	private readonly string $progress_uri;
	private $progress_file;
	private int $status;
	private int $progress;
	private int $chunk_size;


	public static function read_input_headers(){
		$range_match = preg_match("/^bytes ([0-9]+)-([0-9]+)\/([0-9]+)$/",$_SERVER['HTTP_CONTENT_RANGE'],$range_values);
		if($range_match !== 1){
			self::exception("Invalid Content-Range header");
		}
		$disposition_match = preg_match("/^attachment; filename=\"([^\"]+)\"$/",$_SERVER['HTTP_CONTENT_DISPOSITION'],$disposition_values);
		if($disposition_match !== 1){
			self::exception("Invalid Content-Disposition header");
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
	public static function from_input(
		?string $storage = null,
		?string $dir = null,
		?array $metadata = null,
		?string $subfolder = null
	){
		if(!isset($metadata)){
			$metadata = self::read_input_headers();
		}
		$chunk = file_get_contents('php://input');
		return new self(
			$chunk,
			$metadata['range_start'],
			$metadata['range_end'],
			$metadata['file_size'],
			$metadata['checksum'],
			$storage,
			$dir,
			$subfolder,
			$metadata['transfer'],
			$metadata['name'],
			$metadata['mime']
		);
	}

	public function complete(){
		return $this->status == self::STATUS_COMPLETE;
	}

	public function to_output($stored_file_id = null){
		if($this->status == self::STATUS_COMPLETE){
			if(isset($stored_file_id)){
				return ['status'=>'complete', 'transfer'=>$this->transfer_id, 'file'=>$stored_file_id];
			} else {
				return ['status'=>'complete', 'transfer'=>$this->transfer_id];
			}
		} else {
			return ['status'=>'incomplete', 'transfer'=>$this->transfer_id];
		}
	}

	public function __construct(
		string $chunk,
		int $range_start,
		int $range_end,
		private int $size_input,
		private string $checksum_input,
		?string $storage = null,
		?string $dir = null,
		?string $subfolder = null,
		private ?string $transfer_input = null,
		public readonly ?string $name = null,
		public readonly ?string $mime = null,
	){
		$this->chunk_size = strlen($chunk);
		if($this->chunk_size == 0 || $range_start + $this->chunk_size - 1 != $range_end){
			self::exception("Error in WildFileChunkedUpload: Invalid file chunk size ($this->chunk_size bytes, $range_start to $range_end)");
		}
		if(!preg_match('/^[0-9A-Fa-f]+$/', $checksum_input)){
			self::exception("Error in WildFileChunkedUpload: Invalid checksum input (expected hexadecimal string)");
		}
		if(is_string($transfer_input) && !preg_match('/^[0-9A-Fa-f]+$/', $transfer_input)){
			self::exception("Error in WildFileChunkedUpload: Invalid transfer id (expected hexadecimal string)");
		}
		$this->transfer_id = strtoupper(substr($transfer_input ?? $checksum_input, 0, 20));

		$path_chunks = self::get_chunk_path($storage, $dir, $subfolder ?? '.chunked_upload');
		$this->file_uri = $path_chunks.self::buffer_filename($this->transfer_id);
		$this->progress_uri = $path_chunks.self::progress_filename($this->transfer_id);

		$this->progress_lock();

		$file = fopen($this->file_uri, 'c');
		if($file === false){
			self::exception("Error in WildFileChunkedUpload [fopen]");
		}
		self::file_write($file, $range_start, $chunk);

		$this->update_progress();

		self::log('WildFileChunkedUpload: '.$this->transfer_id."($range_start-$range_end/$size_input)|".$path_chunks);

		if($this->progress < $size_input){
			$this->status = self::STATUS_INCOMPLETE;
		} else {
			$this->validate_file();
			$this->status = self::STATUS_COMPLETE;
			if(unlink($this->progress_uri) === false){
				self::log("Warning in WildFileChunkedUpload: Failed unlinking obsolete progress file");
			}
		}
	}

	private function progress_lock() {
		// Open with mode 'c+' (read & write) so we can request advisory lock
		$file = fopen($this->progress_uri, 'c+');
		if($file === false){
			self::exception("Error progress_lock [fopen]");
		}
		// lock the progress file with an advisory lock, so we can avoid race conditions
		if(flock($file, LOCK_EX) === false){
			self::exception("Error progress_lock [flock]");
		}
		$this->progress_file = $file;
	}

	private function update_progress(){
		// read up to 64 bits of data
		$bytes = fread($this->progress_file, 8);
		if($bytes === false){
			self::exception('Error update_progress [fread]');
		} elseif($bytes === ''){
			$previous_progress = 0;
		} else {
			// J: unsigned long long (always 64 bit, big endian byte order)
			$progress_unpacked = unpack('J', $bytes);
			if($progress_unpacked === false){
				self::exception('Error update_progress [unpack]');
			} else {
				$previous_progress = $progress_unpacked[1];
			}
		}

		$this->progress = $previous_progress + $this->chunk_size;

		$bytes = pack('J', $this->progress);

		// closing the progress file also releases the advisory lock
		self::file_write($this->progress_file, 0, $bytes);
		$this->progress_file = null;
	}
	private function validate_file(){
		$this->file_size = filesize($this->file_uri);
		if($this->file_size > $this->size_input){
			self::exception("Error in WildFileChunkedUpload: $this->file_uri (file size doesn't match expected size, $file_size > $this->size_input)");
		}
		$this->checksum = hash_file('sha256',$this->file_uri);
		if($this->checksum !== $this->checksum_input){
			self::exception("Error in WildFileChunkedUpload: Checksum doesn't match input\n$this->checksum !== $this->checksum_input\n$this->size_input $this->file_size");
		}
		return true;
	}
	private static function file_write($file, int $seek, string $data) {
		if(fseek($file, $seek) === -1){
			self::exception("Error file_write [fseek]");
		}
		if(fwrite($file, $data) === false){
			self::exception("Error file_write [fwrite]");
		}
		if(fclose($file) === false){
			self::exception("Error file_write [fclose]");
		}
	}
	private static function buffer_filename($transfer_id){
		return $transfer_id.'.bin';
	}
	private static function progress_filename($transfer_id){
		return $transfer_id.'_progress.bin';
	}
	private static function get_chunk_path(?string $storage, ?string $dir, ?string $storage_subfolder){
		if(!isset($storage)){
			$storage_real = sys_get_temp_dir();
		} else {
			$storage_real = realpath($storage);
		}
		if(!is_dir($storage_real)){
			self::exception("Error is_dir: ".$storage_real);
		}
		$path = $storage_real.DIRECTORY_SEPARATOR;
		if(!empty($dir)){
			$path .= $dir.DIRECTORY_SEPARATOR;
		}
		if(!empty($storage_subfolder)){
			$path .= $storage_subfolder.DIRECTORY_SEPARATOR;
		}
		if(!is_dir($path)){
			if(!mkdir($path)) {
				self::exception('Error mkdir: '.$path);
			}
		}
		return $path;
	}
	protected static function exception($message){
		self::log($message,LOG_ERR);
		throw new \Exception($message);
	}
	protected static function log($message,$priority = LOG_INFO){
		syslog($priority,$message);
	}
}
