var Buffer = {
	_buffer: null,
	_pos: 0,
	_length: 0,
	_verbose: true,
	
	_log: function(str) 		{ if(this._verbose) console.log(str); },
	seek:	function(pos) 		{ this._pos = Math.min(this._length, Math.max(0, this._pos)); },
	seekRelative: function(pos) { this._pos = Math.min(this._length, Math.max(0, this._pos + pos)); },
	isEOF:	function() 			{ return this._pos > this._length; },
	size:	function()			{ return this._length; },
	
	_dump: function() {
		if(this._verbose) {
			var s = "";
			for(var i=0;i<this._length;i++) {
				s += this._buffer[i].charCodeAt(0).toString().padStart(3) + "  "; 
				if( (i+1) % 16 == 0) {
					this._log(s);
					s = "";
				}
			}
			this._log(s);
		}
	},
	
	_dumpArrayBuffer: function(dv) {
		if(this._verbose) {
			var s = "";
			var a = dv.buffer;
			var l = a.byteLength;
			console.log("_dumpArrayBuffer: ");
			console.log("Length: " + l.toString());
			console.log("Contents: ");
			for(var i=0;i<l;i++) {
				s += a[i].charCodeAt(0).toString().padStart(3) + "  "; 
				if( (i+1) % 16 == 0) {
					this._log(s);
					s = "";
				}
			}
			this._log(s);
			console.log(dv);
		}
	},

	CreateDataViewFromBuffer(size) {
		var buffer = new ArrayBuffer(8);
		var dv = new DataView(buffer, 0);
		var i=0;
		while(i<size) {
			dv.setUint8(i, this._buffer[this._pos]);
			this._pos++;
			i++;
		}
		this._dumpArrayBuffer(dv);
		return dv;
	},
			
	readByte: function() { 
		var b = null;
		if(!this.isEOF())
			b = this._buffer[this._pos++];
		return b;
	},
	
	readString: function() {
		var b,str = "";
		do {
			b = this.readByte().charCodeAt(0);
			if(b!=0)
				str += String.fromCharCode(b); 
		} while(!this.isEOF() && b!=0);
		return str;
	},
	
	readUInt: function() {
		var n = 0;
		if(!this.isEOF()) {
			n = decodeUInt( this._buffer, this._pos);
			this._pos += 2;
		}
		return n;
	},

	readUInt32: function() {
		var n = 0;
		if(!this.isEOF()) {
			n = decodeUInt32( this._buffer, this._pos);
			this._pos += 4;
		}
		return n;
	},
	
	readFloat: function() {
		var f = 0.0;
		if(!this.isEOF()) {
			f = decodeFloat32( this._buffer, this._pos);
			this._pos += 4;
		}
		return f;
	},
	
	readFloat64: function() {
		var f = 0.0;
		if(!this.isEOF()) {
			f = decodeFloat64( this._buffer, this._pos);
			this._pos += 8;
		}
		return f;
	},
	

	create: function(size) {
		var b = this._buffer = [];
		this._length = size;
		this._pos = 0;
		
		return this;
	},
		
	decodeB64: function(stream) {
		this._buffer = window.atob(stream); 
		this._pos = 0;
		this._length = this._buffer.length;
		//this._log("Length="+this._length);
		//this._dump();
		
		return this;//._buffer;
	}
    
    

}