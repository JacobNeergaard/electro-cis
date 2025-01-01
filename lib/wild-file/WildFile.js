/*
WildFile is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/wild-file/blob/master/LICENSE
*/

var WildFile = (function(){
	function checksum(field) {
		if(location.protocol != 'https:') {
			alert('WildFile client-side crypto function requires https protocol');
			return false;
		}
		let fieldname = field.name;
		if(fieldname.indexOf("[")!=-1) {
			fieldname = fieldname.slice(0,fieldname.indexOf("["));
		}
		field.parentElement.querySelectorAll('[name^="'+fieldname+'_checksum"]').forEach(function (e) {e.remove()});
		for(const file of field.files) {
			file_checksum(file).then(checksum => {
				const input = document.createElement("input");
				input.setAttribute("name", fieldname+"_checksum["+file.name+"]");
				input.setAttribute("type", "hidden");
				input.setAttribute("value", checksum);
				field.after(input);
			});
		}
		return true;
	}

	function file_checksum(file){
		return file.arrayBuffer()
			.then(buffer => crypto.subtle.digest('SHA-256', buffer))
			.then(digest => Array.from(new Uint8Array(digest)).map(x => x.toString(16).padStart(2, '0')).join(''))
	}

	function read_into_buffer(chunkedfile, primary_buffer, secondary_buffer){
		let offset = 0;
		let chunkStart = chunkedfile.bytesBuffered;
		const next_view = ()=>new Uint8Array(primary_buffer,offset,primary_buffer.byteLength-offset);
		const writeBuffer = ({done,value}) => {
			if(done && typeof value == 'undefined'){
				chunkedfile.stream_done = done;
				return [primary_buffer,secondary_buffer];
			}
			primary_buffer = value.buffer;
			offset += value.byteLength;
			chunkedfile.bytesBuffered += value.byteLength;
			if(done || offset >= primary_buffer.byteLength){
				primary_buffer.chunkSize = offset;
				primary_buffer.chunkStart = chunkStart;
				if(done) chunkedfile.stream_done = done;
				return [primary_buffer,secondary_buffer];
			}
			return chunkedfile.bytestream.read(next_view()).then(writeBuffer);
		}
		return chunkedfile.bytestream.read(next_view()).then(writeBuffer);
	}
	function upload_response(chunkedfile, url,response){
		if(!response.ok){
			return upload_complete(chunkedfile, false, 'Received upload response with HTTP status '+response.status);
		}
		if(chunkedfile.progress_callback_function){
			chunkedfile.progress_callback_function(chunkedfile.bytesSent, chunkedfile.file.size);
		}
		response.text().then(body=>{
			try {
				const msg = JSON.parse(body);
				chunkedfile.transfer = msg.transfer;
				chunkedfile.status = msg.status;
				if(chunkedfile.transfer && chunkedfile.bytesSent < chunkedfile.file.size){
					chunkedfile.upload(url)
				} else {
					chunkedfile.file_id = msg.file;
					upload_complete(chunkedfile, msg.status != 'error', msg.error);
				}
			} catch (error){
				upload_complete(chunkedfile, false, body);
			}
			
		})
	}
	function upload_complete(chunkedfile, success, msg){
		if(chunkedfile.buffers){
			chunkedfile.buffers = undefined;
			chunkedfile_buffer_count -= 1;
			try_call(chunkedfile_buffer_queue.shift());
		}
		if(success){
			try_call(chunkedfile.complete_resolve, chunkedfile);
		} else {
			try_call(chunkedfile.complete_reject, msg);
		}
		chunkedfile.complete_resolve = undefined;
		chunkedfile.complete_reject = undefined;
	}
	function try_call(func, ...arguments){
		if(func) func(...arguments);
	}
	function get_buffers(){
		return new Promise((resolve,reject)=>{
			const use_buffers = ()=>{
				chunkedfile_buffer_count += 1;
				resolve([
					new ArrayBuffer(ChunkedFile.buffer_size),
					new ArrayBuffer(ChunkedFile.buffer_size),
				]);
			}
			if(chunkedfile_buffer_count < ChunkedFile.buffer_limit){
				use_buffers();
			} else {
				chunkedfile_buffer_queue.push(use_buffers);
			}
		});
	}

	function ChunkedFile(file){
		this.file = file;
		this.checksum = file_checksum(file);
		if(typeof ReadableStreamBYOBReader == 'function'){
			// Safari doesn't support "byob" stream readers, which allows zero-copy reading.
			this.bytestream = file.stream().getReader({mode:"byob"});
		} else {
			// Fixme: this mode is very ineffecient currently, due to the implementation of read_into_buffer
			this.bytestream = file.stream().getReader();
		}
		this.buffers = get_buffers().then(([primary_buffer, secondary_buffer])=>read_into_buffer(this,primary_buffer, secondary_buffer));
		this.bytesBuffered = 0;
		this.bytesSent = 0;
		this.stream_done = false;
		this.complete = new Promise((resolve,reject)=>{
			this.complete_resolve = resolve;
			this.complete_reject = reject;
		})
	}

	ChunkedFile.prototype.register_progress_callback = function(callback){
		this.progress_callback_function = callback;
	}

	ChunkedFile.prototype.upload = function(url){
		Promise.all([this.checksum,this.buffers]).then(([checksum,[buffer,other_buffer]])=>{
			if(buffer && buffer.chunkSize > 0){
				const file_chunk = new Uint8Array(buffer,0,buffer.chunkSize);
				const chunkEnd = buffer.chunkStart + buffer.chunkSize - 1;
				const request_init = {
					method:'POST',
					body:file_chunk,
					headers:{
						'Content-Range': 'bytes '+buffer.chunkStart+'-'+chunkEnd+'/'+this.file.size,
						'Content-Type': this.file.type,
						'Content-Disposition': 'attachment; filename="'+encodeURI(this.file.name)+'"',
						'X-WildFile-Checksum': checksum,
					}
				}
				if(this.transfer){
					request_init.headers['X-WildFile-Transfer'] = this.transfer;
				} else if(buffer.chunkStart != 0){
					upload_complete(this, false, 'Missing transfer id');
				}
				this.bytesSent += buffer.chunkSize;
				const upload_promise = fetch(url,request_init).then(response=>upload_response(this,url,response));
				buffer.chunkSize = 0;
				if(!this.stream_done){
					this.buffers = read_into_buffer(this,other_buffer,buffer);
				}
			} else {
				upload_complete(this, false, "Can't to upload empty or missing buffer");
			}
		});
	}
	chunkedfile_buffer_count = 0;
	chunkedfile_buffer_queue = [];
	ChunkedFile.buffer_limit = 1;
	ChunkedFile.buffer_size = 2**20; // 2**20 = 1 Megabyte

	function chunked_upload(event, form, url, upload_start, upload_progress, upload_complete) {
		event.preventDefault();
		file_inputs = form.querySelectorAll('input[type=file]');
		for(let input of file_inputs){
			for(let raw_file of input.files){
				file = new ChunkedFile(raw_file);
				if(upload_start){
					upload_start(input, raw_file);
				}
				if(upload_progress){
					file.register_progress_callback((value,max)=>upload_progress(input, raw_file, value, max));
				}
				if(upload_complete){
					file.complete.then(chunkedfile=>upload_complete(chunkedfile,input, raw_file));
				}
				file.upload(url);
			}
		}
	}

	var exportobj = {
		checksum: checksum,
		upload: chunked_upload,
		ChunkedFile: ChunkedFile,
	};

	return exportobj;
})();
