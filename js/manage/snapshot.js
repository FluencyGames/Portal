var defaultScoreDivClass = 'score-circle';
var defaultScoreTrendClass = 'trend-icon';
var snapshots = [];
var numScoresPerCol = 2;

function createScorePair(scoreDiv, trendDiv, type) {
	return {
		scoreDiv: scoreDiv,
		trendDiv: trendDiv,
		type: type,
	};
}

Snapshot = function(data) {
	var $this = this;
	this.data = data;
	
	this.scoreGroups = [
		null,
		null,
	];
	
	this.scoreInfo = [
		//createScorePair(null, null, 'Average'),
		createScorePair(null, null, 'Points/Second'),
		createScorePair(null, null, 'Accuracy'),
		
		//createScorePair(null, null, 'Average'),
		createScorePair(null, null, 'Points/Second'),
		createScorePair(null, null, 'Accuracy'),
	];
	
	parentDiv = $('<div class="snapshot" data-id="' + data.Id + '"></div>');
	
	parentRow = $('<div class="row"></div>');
	parentDiv.append(parentRow);
	
	this.name = data.Lname + ', ' + data.Fname;
	nameCol = $('<div class="col-xs-3"><span class="name">' + this.name + '</span></div>');
	parentRow.append(nameCol);
	
	// NOTE(bret): We probably don't need this since we're not sending data back, but we may end up needing it anyway, so I decided to keep this here for potential future use
	/*<input id="username-' + data.Username + '" type="hidden" name="username" value="' + data.Username + '" />*/
	
	for (var colIndex = 0; colIndex < 2; ++colIndex) {
		scoreCol = $('<div class="col-xs-4"></div>');
		parentRow.append(scoreCol);
		
		scoreGroup = $('<div class="scores-3-group no-data"></div>');
		this.scoreGroups[colIndex] = scoreGroup;
		scoreCol.append(scoreGroup);
		
		scoreGroupRow = $('<div class="row"></div>');
		scoreGroup.append(scoreGroupRow);
		
		var colSize = 12 / numScoresPerCol;
		var col, scoreDiv, trendDiv;
		for (var scoreIndex = 0; scoreIndex < numScoresPerCol; ++scoreIndex) {
			col = $('<div class="col-xs-' + colSize + '"></div>');
			
			scoreDiv = $('<span class="' + defaultScoreDivClass + '" data-toggle="tooltip" title="XX%"></span>');
			scoreDiv.tipsy({ gravity: 's', html: true });
			col.append(scoreDiv);
			
			trendDiv = $('<span class="' + defaultScoreTrendClass + '"></span>');
			col.append(trendDiv);
			
			scoreGroupRow.append(col);
			
			pairIndex = (colIndex * numScoresPerCol) + scoreIndex;
			
			$this.scoreInfo[pairIndex].scoreDiv = scoreDiv;
			$this.scoreInfo[pairIndex].trendDiv = trendDiv;
		}
	}
	
	this.div = parentDiv;
	$("#snapshots").append(parentDiv);
}

// TODO(bret): Find a better, cleaner way to write all this, jeesh
Snapshot.prototype.updateScores = function(scores, product) {
	var rangeInfo = ranges[product];
	
	var scoreDiv;
	var classToUse;
	for (var s = 0; s < numScoresPerCol * 2; ++s) {
		scoreDiv = this.scoreInfo[s].scoreDiv;
		classToUse = defaultScoreDivClass;
		
		var isPPS = (s % 2) == 0;
		
		var type = this.scoreInfo[s].type + ((s < numScoresPerCol) ? ' (Summary)' : ' (Last Game)');
		
		var postfix = ' PPS';
		if (!isPPS) {
			scores[s] *= 100;
			postfix = '%';
		}
		
		var theScore = (scores[s]).toFixed(2);
		scoreDiv.attr('title', type + '<br />' + theScore + postfix);
		
		var min = (isPPS) ? rangeInfo.ppsmin : rangeInfo.accmin;
		var max = (isPPS) ? rangeInfo.ppsmax : rangeInfo.accmax;
		
		// NOTE(bret): This is currently hardcoded, gonna need to find a way to make it adjustable per product
		var pointsAreSpeed = (product == 4);
		var green = ' green icon-circle';
		var red = ' red icon-circle';
		
		if (scores[s] >= max) {
			classToUse += (pointsAreSpeed) ? red : green;
		} else if (scores[s] < min) {
			classToUse += (pointsAreSpeed) ? green : red;
		} else {
			classToUse += ' icon-circle-empty';
		}
		
		scoreDiv.attr('class', classToUse);
	}
}

Snapshot.prototype.updateTrends = function(trends) {
	var trendDiv;
	var noTrendIcon = 'icon-minus'; // NOTE(bret): Feel free to change this to 'empty'
	var classToUse;
	for (var s = 0; s < numScoresPerCol * 2; ++s) {
		trendDiv = this.scoreInfo[s].trendDiv;
		classToUse = defaultScoreTrendClass;
		
		// NOTE(bret): These are hardcoded, they shouldn't need to be saved in a database, since they are universal
		if (trends[s] >= 1.0) {
			classToUse += ' green icon-up-dir';
		} else if (trends[s] <= -1.0) {
			classToUse += ' red icon-down-dir';
		} else {
			classToUse += ' icon-minus';
		}
		
		trendDiv.attr('class', classToUse);
	}
}

Snapshot.prototype.update = function(newData, product) {
	if ((newData == "") || (newData == "\"\"")) {
		this.scoreGroups[0].addClass('no-data');
		this.scoreGroups[1].addClass('no-data');
	} else {
		this.scoreGroups[0].removeClass('no-data');
		this.scoreGroups[1].removeClass('no-data');
		
		var reportId = 'student-' + this.data.Id;
		var theStudent = {
			reportId: 		reportId,
			name: 	 		this.name,
			product: 		product,
			games: 			[],
			progressData: 	[],
			summary: 		{},
			progress: 		{}
		};
		
		var processedGameData = processGameData(theStudent, newData);
		
		var scores = [
			theStudent.summary.PtsPerSec,
			theStudent.summary.Accuracy,
			theStudent.summary.LastGamePtsPerSec,
			theStudent.summary.LastGameAccuracy,
		];
		
		var trends = [
			theStudent.summary.DevPtsPerSec,
			theStudent.summary.DevScore,
			theStudent.summary.DevLastPtsPerSec,
			theStudent.summary.DevLastScore,
		];
		
		console.log('student: ', theStudent);
		//console.log("Scores: ", scores);
		//console.log("Trends: ", trends);
		
		this.updateScores(scores, product);
		this.updateTrends(trends);
	}
}

function createSnapshot(data) {
	data.Id = parseInt(data.Id);
	snapshots[data.Id] = new Snapshot(data);
}

function loadSnapshots(product) {
	sendAjax({
		url: "php/ajax/manage/get-snapshots.php",
		data: {
			product: product
		},
		success: function(result) {
			students = result.students;
			for (var key in students) {
				var snap = snapshots[parseInt(key)];
				if (snap !== undefined)
					snap.update(students[key], product);
			}
		},
		error: function(result) {
			//alert("Index.php: Line 27, " + result['responseText']);
			console.log(result);
		},
	});
}