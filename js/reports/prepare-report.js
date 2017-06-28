// create a blank line in html document
function spacer() {
	return document.createElement('br');
}

/*
			<div class="header">
				<div class="container">
					<br/>
						<style type="text/css" >
							@media print { #print-button { display:none; visibility: hidden; } }
						</style>
						<div align="right">
							<input id="print-button" type="button" value="Print This Page" onClick="window.print();" />
						</div>
				</div>
				<div class="container">
					<span class="center">
						<h1>Student Progress Report</h1>
					</span>
					<div class="clear"></div>
				</div>
			</div>
*/

function reportHeader(showPrintButton) {
	var header = document.createElement('div');
	header.classList.add('header');
	
		var container = document.createElement('div');
		container.classList.add('container');
		container.appendChild( spacer() );
		
		if(showPrintButton) {
			var button = document.createElement('div');
			button.setAttribute('align', 'right');
			
			var nput = document.createElement('input');
			nput.id = 'print-button';
			nput.type = 'button';
			nput.value = 'Print This Page';
			nput.onclick = function() { window.print(); }
			
			button.appendChild(nput);
			container.appendChild(button);
		}
		
		var title = document.createElement('div');
		title.classList.add('container');
			
			var text = document.createElement('span');
			text.classList.add('center');
			var txt = document.createElement('h1');
			txt.innerText = "Student Progress Report";
			text.appendChild(txt);
				
		title.appendChild(text);
		//title.appendChild( spacer() );
			

	header.appendChild(container);
	header.appendChild(title);
	
	document.getElementsByTagName('body')[0].appendChild(header);
	
}

/*
	<div class="report-body" >
		<br/>
		<h1>STUDENT NAME GOES HERE</h1>
		<h2>PRODUCT GOES HERE</h2>
		<div class="container" id="student"></div>
		<br/>
	</div>
*/

function reportBody( student ) {
	var report = document.createElement('div');
	report.classList.add('report-body');
	report.id = student.reportId + '-' + 'body';
	
		report.appendChild( spacer() );
		
		var name = document.createElement('h1');
		name.innerText = student.name;
		report.appendChild(name);
		
		var product = document.createElement('h2');
		product.innerText = student.productName;
		report.appendChild(product);
		
		var container = document.createElement('div');
		container.classList.add('container');
		container.id = student.reportId;
		report.appendChild(container);
		
		report.appendChild(spacer());
		
	document.getElementsByTagName('body')[0].appendChild(report);		
}

/*
			<div class="footer">
				<div class="container">
					<span class="left">
						Report Generated on DATE.
					</span>
					<div class="clear"></div>
				</div>
			</div>

*/
function reportFooter() {
	var dt = new Date();
	
	var footer = document.createElement('div');
	footer.classList.add('footer');
	
	var container = document.createElement('div');
	var text = document.createElement('span');
	text.classList.add('left');	
	text.innerText = "Report Generated on " + dt.toDateString();
	container.appendChild(text);
	footer.appendChild( container );
	footer.appendChild( spacer() );
	
	//var pgBreak = document.createElement('p');
	//var comment = document.createComment('<!-- pagebreak -->');
	//pgBreak.appendChild(comment);
	
	document.getElementsByTagName('body')[0].appendChild(footer);	
	document.getElementsByTagName('body')[0].appendChild( document.createElement('p'));	
}


/*****************************************************
*
*	Reports  - Game And Data Formatting
*	
******************************************************/


function formatReportHtml(attr, v) {
	var _html= "";
	for(var index=0;index<attr.length;index++)
	{
		switch(attr.item(index).name.toLowerCase()) {
		
			//
			// fixed to 'n' decimal points
			//
			case "fixed":
				 _html += v.toFixed( parseInt(attr.item(index).value) ).toString();
				 break;
		
			// 
			// percent values		 
			//
			case "percent": 
				_html += (v * 100).toFixed(parseInt(attr.item(index).value) ).toString();
				_html += " %";
				break;
				
			//
			// prepend any text
			//
			case "prepend":	
				_html = attr.item(index).value + _html;
				break;
				
			//
			// postpend any text
			//
			case "postpend":	
				_html += attr.item(index).value;
				break;
				
			//
			// make it a hyperlink
			//
			case "href":
				_html ="<a href"+attr.item(index).value+">"+_html+"</a";
				break;	
				
			//
			// pad the data value
			//
			case "pad":
				_html += v.toString().padStart( parseInt(attr.item(index).value) );
				break;
				
			//
			// pad the data value
			//
			case "padZero":
				_html += v.toString().padStart( parseInt(attr.item(index).value), "0" );
				break;
				
			//
			// justify the text
			//	
			case "justify":
				_html = "<div text-align:"+ attr.item(index).value + ">"+v.toString()+"</div>";
				break;
				
			default: break;
		}
	}

	if(_html == "")
		_html= v.toString();
		
	return _html;	
}

function prepareReport( id, data ) {
    console.log("prepareReport");
    
	Object.keys(data).forEach(function(key, index) {
		var e = $("#"+ id + " " + key); //.getElementsByTagName(key);
		for(var i=0; i<e.length; i++)
			e[i].innerHTML = formatReportHtml(e[i].attributes, data[key]);
	});
}

/*****************************************************
*
*	Reports  - GRAPHS 
*	
******************************************************/

function createBarItems( data, labelField, dataField) {
	var dataset = {};
	var i=0;
	
	dataset.labels = [];
	dataset.datasets = [];

	dataset.datasets[0] = {};
	dataset.datasets[0].data = [];
	dataset.datasets[0].backgroundColor = 'rgba(255,255,128,1)';
	dataset.datasets[0].borderColor = 'rgba(0,0,0,1)';
	dataset.datasets[0].borderWidth = 1;
	
	data.forEach( function(d) {
		dataset.labels.push( d[labelField].toString() );
		dataset.datasets[0].data.push( d[dataField] );
	});
		
	return dataset;
}

function createOrderedPairs( data, field ) {
	var dataset = {};
	var i=0;
	
	dataset.datasets = [];
	dataset.datasets[0] = {};
	
	dataset.datasets[0].label = "";
	dataset.datasets[0].fill = false;
	dataset.datasets[0].showLine = false;
	dataset.datasets[0].pointBorderColor = "rgba(0,0,0,1)";
	dataset.datasets[0].data = [];

	data.forEach( function(game) {
		dataset.datasets[0].data.push( { x: ++i, y: game[field] } );
	});

	return dataset;
}

function prepareGraph( options ) {
	var e = options.element;
	
	if(e==null)
		return null;
	
	var g = null;
	var xAxisType;
	var yAxisType;
	var dataSet={};
	var ctx = e.getContext("2d");
	
	if(ctx!==undefined)
	{
		switch(options.type)
		{
			case 'scatter':
						xAxisType = 'linear'; 
						yAxisType = 'linear';
						dataSet = createOrderedPairs(options.dataSrc, options.field);
						break;
						
			case 'bar':	
						xAxisType = 'category';
						yAxisType = 'linear';
						dataSet = createBarItems(options.dataSrc, e.getAttribute('data-label'), e.getAttribute('data-col'));
						break;
						
			default: break;
		}

		if(dataSet.datasets[0].data.length == 0) {
			e.style.visible = "false";
			e.style.display = "none";
		} else {
			var parent = e.parentNode;
			//e.width = 325; //Math.round(.9 * parent.offsetWidth);
			e.height = 275; //Math.round(3*parent.offsetWidth/5);
			
			g = new Chart(ctx, {
							type: options.type,
							data: dataSet,
							options: {
								maintainAspectRatio: true,
								responsive: false,
								legend: {
									display: false,
								},
								tooltips: {
									enabled: false,
								},
								title: { 
									text: options.title,
									display: true
								},
								scales: {
									xAxes: [{
										type: xAxisType,
										position: 'bottom',
										gridLines: {
				                            zeroLineColor: 'rgba(0,0,0,1)',
				                        },
										scaleLabel: {
											display: true,
											labelString: options.xAxisLabel
										},
									}],
									yAxes: [{
										type: yAxisType,
										position: 'left',
										gridLines: {
				                            zeroLineColor: 'rgba(0,0,0,1)',
				                        },
										scaleLabel: {
											display: true,
											labelString: options.yAxisLabel
										}
									}]
								}
							
							}		
						});
		}
	}
	
	return g;
}

function prepareGraphs( id, student ) {
    
	window.graphs = {};
	
	var graphs = $("#" + id + " canvas");
	
	for(var i=0;i<graphs.length;i++) {
		var e = graphs[i];
		var data = student[e.getAttribute('data-obj')];
		window.graphs[e.id] = prepareGraph( {
										element:e,
										graph: e.id,
										type: e.getAttribute('type'), 
										dataSrc: student[e.getAttribute('data-src')],	
										field: e.getAttribute('data-col'),
										title: e.getAttribute('title'),
										xAxisLabel: e.getAttribute('x-label'),
										yAxisLabel: e.getAttribute('y-label'),
										align: 'left'
										});
	}
}
