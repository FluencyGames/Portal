Number.prototype.round = function(places) {
  return (Math.round(this + "e+" + places)  + "e-" + places);
}

Number.prototype.spread = function( mean, stddev ) {
	if(stddev!==0)
		return (this-mean)/stddev;
	else
		return undefined;
}

Number.prototype.spreadA = function( a, col=undefined ) {
	return this.spread(a.mean(col), a.stddev(xbar, col));
}

Array.prototype.mean = function( col=undefined ) {
	var useCol = col!==undefined;
	var sum=0;
	var cnt=0;
	
	if(useCol)
		this.forEach( function(a) { sum+=a[col]; cnt++; });
	else
		this.forEach( function(n) { sum+=n; cnt++;	});
	
	if(cnt==0)
		return 0;
		
	return sum/cnt;
	
}

Array.prototype.max = function( col=undefined ) {
	var useCol = col!==undefined;
	var maxValue = -1;
		
	if(useCol)
		this.forEach( function(a) { maxValue = Math.max(maxValue, a[col]); });
	else
		this.forEach( function(n) { maxValue = Math.max(maxValue, n);	});
	
	return maxValue;
}

Array.prototype.sumGrid = function( x0, y0, x1, y1 ) {
	var sum=0;
	
	if(x1<x0) { var t = x1; x1=x0; x0 = t; }
	if(y1<y0) { var t = y1; y1=y0; y0 = t; }
	
	for(var i=x0;i<=x1;i++)
		for(var j=y0;j<=y0;j++)
			sum += this[i][j];
			
	return sum;
}

Array.prototype.sum = function( field=undefined ) {
	var sum=0;
	
	if(field!==undefined)
		this.forEach( function(a) { sum+=a[field]; });
	else
		this.forEach( function(n) { sum+=n; });
	
	return sum;
}

Array.prototype.sumField = function( field, start=0, end=undefined ) {
	var last = end===undefined ? this.length : end;
	var sum=0;
	
	for(var i=start;i<last;i++) {
        sum += this[i][field];
    }
	
	return sum;
}

Array.prototype.mad = function( mean=undefined, col=undefined ) {
	var xbar = mean;
	
	if(mean===undefined)
		xbar = this.mean(col);
		
	var sum=0;
	var cnt=0;
	
	if(col!==undefined)
		this.forEach(function(a) { if(a[col]>0) { cnt++; sum+=Math.abs(a[col] - xbar); } });
	else
		this.forEach(function(n) { if(n>0)      { cnt++; sum+=Math.abs(n-xbar); } } );
	
	if(cnt==0)
		return 0;
		
	return sum/cnt;
}

Array.prototype.variance = function(mean=undefined, col=undefined) {
	var xbar = mean;
	
	if(mean===undefined)
		xbar = this.mean(col);
		
	var sum=0;
	var cnt=0;
	
	if(col!==undefined)
		this.forEach(function(a) { if(a[col] > 0) { cnt++; sum+=(a[col] - xbar)*(a[col] - xbar); } } );
	else
		this.forEach(function(n) { if(n>0)        { cnt++; sum+=(n-xbar)*(n - xbar); } });
	
	if(cnt==0)
		return 0;
		
	return sum/cnt;
}

Array.prototype.stddev = function( mean=undefined, col=undefined) {
	return Math.sqrt( this.variance(mean,col) );
}

function round2(n, p) { n.round(p); }

function mad_from_array_objects(a, key, xbar, ncludeZeros) {
	var sum=0;
	var cnt=0;
	
	a.forEach( function(o) {
		if(o[key]>0 || (o[key]==0 && ncludeZeros)) {
			cnt++;
			sum+=Math.abs(o[key]-xbar);
		}
	});
	
	if(cnt==0)
		return 0;
		
	return sum/cnt;
}

function decodeInt( bytes, offset, signBits, length, littleEndian) {
	var val = 0;
	var sign = 1;
	var bits;
	
	for(var i=0; i<length; i++) {
		if(bytes.length > offset + i) {
			bits = bytes[i+offset].charCodeAt(0);
			if(littleEndian)
				val = bits*Math.pow(16,i*2) + val;
			else
				val = val*256 + bits;
		}
	}
	

	
	// TODO: Fix the sign issue
	//if(signBits==1) {
	//	sign = (parseInt(val.charAt(0)) & 8)?-1:1; 
	//}
		
	return sign * parseInt(val);
}

function decodeUInt( bytes, offset ) 	{ return decodeInt( bytes, offset, 0, 2, true); }
function decodeUInt32( bytes, offset )	{ return decodeInt( bytes, offset, 0, 4, true); }
function decodeInt16( bytes, offset )	{ return decodeInt( bytes, offset, 1, 2, true); }
function decodeInt32( bytes, offset )	{ return decodeInt( bytes, offset, 1, 4, true); }

function decodeFloat(bytes, offset, signBits, exponentBits, fractionBits, eMin, eMax, littleEndian) {
	var totalBits = (signBits + exponentBits + fractionBits);
	var l = (totalBits)/8;
	var binary = "";
	
	for (var i = 0; i < l; i++) {
		var bits = parseInt(bytes[i+offset].charCodeAt(0)).toString(2);
		
		while (bits.length < 8) 
		  bits = "0" + bits;
		
		if (littleEndian)
		  binary = bits + binary;
		else
		  binary += bits;
	}

	var sign = (binary.charAt(0) == '1')?-1:1;
	var exponent = parseInt(binary.substr(signBits, exponentBits), 2) - eMax;
	var significandBase = binary.substr(signBits + exponentBits, fractionBits);
	var significandBin = '1'+significandBase;
	var i = 0;
	var val = 1;
	var significand = 0;
	
	if (exponent == -eMax) {
	  if (significandBase.indexOf('1') == -1)
	      return 0;
	  else {
	      exponent = eMin;
	      significandBin = '0'+significandBase;
	  }
	}
	
	while (i < significandBin.length) {
	  significand += val * parseInt(significandBin.charAt(i));
	  val = val / 2;
	  i++;
	}
	
	return sign * significand * Math.pow(2, exponent);
}

function decodeFloat32(bytes, offset) {
	return decodeFloat(bytes, offset, 1, 8, 23, -126, 127, true);
}


function decodeFloat64(bytes, offset ) {
	return decodeFloat(bytes, offset, 1, 11, 52, -1022, 1023, true);
}