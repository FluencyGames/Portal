// Application-specific game and progress data 

// includes math.js

//
//  Changes:
//
//  07-27-17:   Forced casting values from JSON structure to numeric
//
//

var ADBG = { PRACTICE:	0, NORMAL:	1, TIMED: 		2, CONTINOUS: 3, CHALLENGE: 4 };
var MX	 = { NORMAL:	1, TIMED:	1, PRACTICE:	2, CHALLENGE: 3 };

function num_keys(o) {
	var n = 0;
    for(var p in o) {
        if(o.hasOwnProperty(p))
            ++n;
    }	
	return n;
}

function get_game_mode(code) {
	return (code/10 - Math.floor(code/10))*10;
}

/*******************************************/
/*
/*		Addition Blocks Game Data
/*
/*******************************************/

function adbg_game_data(stream) {
    
    //
    // this is the previous way we handled -- as a binary file_exists
    // DEPRECATED, but we still have some data in our database that is in this format
    //
    
    // binary file of data
    var buff = Buffer.decodeB64(stream);
    
    // the array of games, to be sorted later
    var games = [];                 

	var ngames;
	var dt;
	var tm;
	var score;
	var gmCode;
	var endCode;
	
	//
	// read and decode the data
	//
	ngames = buff.readUInt32();
	if(ngames > 0) {
		for(var i=0;i<ngames;i++) {
			dt = buff.readFloat64();  // change this to readFloat64();
            // really old legacy data saved date with 32-bit float
            if(dt<9999 || dt>49999) {
                buff.seekRelative(-8);
                dt = buff.readFloat();
            }
			tm = buff.readUInt32();
			gmCode = buff.readUInt32();
			endCode = buff.readUInt32();
			score = buff.readUInt32();
			
			if( gmCode<0 || gmCode>999 || endCode<0 || endCode>5 || score<0 || score >100000) {
				console.log("Invalid data, rejecting");
				console.log("dt="+dt.toString()+", tm="+tm.toString()+", score="+score.toString());
			} else if(get_game_mode(gmCode) != ADBG.PRACTICE) {
				games[i] = {};
				games[i]['Date'] = dt;
				games[i]['Time'] = tm;
				games[i]['GameCode'] = gmCode;
				games[i]['EndCode'] = endCode;
                games[i]['Score'] = score;
                // did not have this data in binary file
				games[i]['Attempts'] = 0;
                games[i]['Success'] = 0;
			}
		}
	}
	
	//console.log(games);
	
	return games;
}

function adbg_process_game_data(theStudent, stream) {
	
	var summary = { NumberOfGames	:0,
					Avg				:0,
					PtsPerSec		:0,
                    Accuracy        :0,
                    Attempts        :0,
                    Success         :0,
					LastGameDate	:0,
					LastGameScore	:0,
					LastGamePtsPerSec	:0,
                    LastGameAccuracy:0,
					DevScore		:0,
					DevPtsPerSec	:0,
					DevLastScore	:0,
                    DevLastGameAccuracy: 0,
					DevLastPtsPerSec:0,
					OutliersAvg		:[],
					OutliersPtsPerSec:[]
					};
	
	var numGames = 0;
    var points = 0;
    var total_time = 0;
	
    var theGames = [];

    // if no data, just return empty structures
    if(stream.length==0) {
        theStudent.games = theGames;
        return summary;
    }
    
    // handle the type of data stream--
    // legacy data stream is base64 encoded stream
    // new format is just a JSON encoded string
    if(stream.charAt(0)!='{')
        theGames = adbg_game_data(stream);
    else  {
        var g = JSON.parse( stream );
        for(var game in g) theGames.push(g[ game ]); 
    }    
    
    //console.log(theGames);
    
    // sort by game date...
    theGames.sort(function(a,b) {
        var d1 = new Date(a['Date']);
        var d2 = new Date(b['Date']);
        return d1.valueOf() - d2.valueOf();
    });
    
    // save the unencoded data for later
    theStudent.games = theGames;

   theGames.forEach( function(game, i) {
        numGames++;
        points += Number(game['Score']);
        total_time += Number(game['Time']);
        summary.Attempts += Number(game['Attempts']);
        summary.Success += Number(game['Success']);

        // not in our origianl data, we will need this to calculate some data
        game['PtsPerSec'] = points / total_time;

    });
    
    if(numGames > 0) {
        summary.NumberOfGames = numGames;
        summary.PtsPerSec = theGames.mean('PtsPerSec');
        summary.Avg = (points / numGames);
        if(summary.Attempts>0)
            summary.Accuracy = (summary.Success / summary.Attempts);
        
        summary.LastGameDate = theGames[numGames-1]['Date'];
        summary.LastGameScore = Number(theGames[numGames-1]['Score']);
        summary.LastGamePtsPerSec = ( summary.LastGameScore / Number(theGames[numGames-1]['Time']));
        if(Number(theGames[numGames-1]['Attempts'])>0)        
            summary.LastGameAccuracy = ( Number(theGames[numGames-1]['Success'])/Number(theGames[numGames-1]['Attempts']));
    }
    
	//
	// analyze recent trends
	//
	if(numGames>3) {
        console.log(theGames);
        console.log(summary);
        
		var recentScore = theGames.sumField('Score', numGames-3);
		var recentTime  = theGames.sumField('Time', numGames-3);
        var recentAccuracy = 0.0;
        
		var recentScoreAvg = recentScore/3;
		var recentSpeed = recentScore/recentTime;
        if(theGames.sumField('Attempts', numGames-3)>0)
		      recentAccuracy  = theGames.sumField('Success', numGames-3) / theGames.sumField('Attempts', numGames-3);
		
		var madScore = theGames.mad( summary.Avg, 'Score');
		var madSpeed = theGames.mad( summary.PtsPerSec, 'PtsPerSec' );
		var madAccu  = theGames.mad( summary.Accuracy, 'Accuracy');
        
		summary.DevScore 		= recentScoreAvg.spread(summary.Avg, madScore);
		summary.DevPtsPerSec 	= recentSpeed.spread(summary.PtsPerSec, madSpeed);
        summary.DevAccuracy     = recentAccuracy.spread(summary.Accuracy, madAccu);
		
		summary.DevLastScore 	= summary.LastGameScore.spread(summary.Avg, madScore);
		summary.DevLastPtsPerSec = summary.LastGamePtsPerSec.spread(summary.PtsPerSec, madSpeed);
        summary.DevLastAccuracy = summary.LastGameAccuracy.spread(summary.Accuracy, madAccu);
	}
	
	//console.log(summary);
    
	return summary;
}

/*******************************************/
/*
/*		ADDITION BLOCKS Progress Data
/*
/*******************************************/

function adbg_progress_data(stream) {	
    
    //
    // this is the previous way we handled -- as a binary file_exists
    // DEPRECATED, but we still have some data in our database that is in this format
    //
    
    // binary file of data
	var buff = Buffer.decodeB64(stream);
	
	var sums = [];
	var count = 0;
	var tm,tenth;
	var s;
	
	while(!buff.isEOF()) {
		sum = buff.readUInt();
		if(sum >= 2 && sum<=45) {
			sums[count] = {};
			sums[count]['Sum'] = sum.toString();
			sums[count]['1'] = buff.readUInt();
			sums[count]['x'] = buff.readUInt();
			
			tm = buff.readUInt();
			tenth = buff.readUInt();
			if(tenth>9) {
				console.log("Invalid time data: tm = " + tm + ", tenth=" + tenth);
				tm = 0;
				tenth=0;
				buff.seekRelative(-1);
			}
			sums[count]['tt'] = (tm*10 + tenth)/10;
			
			s = buff.readString();
			if(s!="X")
				sums[count]['c'] = JSON.parse(s);
			else
				sums[count]['c'] = [];
				
			count++;
		}
	}
	
	return sums;
}

function adbg_process_progress_data(theStudent, stream) {
//	console.log("adbg_process_progess_data");
    
	var summary = { NumberOfItems:	0,
					AccuracyPerItem:    	0,
					secPerItem:		0,
					combosPerItem:	0,
					items:			[],
				};
				
	var totalSuccess = 0;
	var totalMissed = 0;
	var totalCombos = 0;
	var totalTime = 0;

    var theData = [];
    
    if(stream.length==0) {
        theStudent.progressData = theData;
        return summary;
    }
    
    // handle the type of data stream--
    // legacy data stream is base64 encoded stream
    // new format is just a JSON encoded string
    if(stream.charAt(0)!='{')
        theData = adbg_progress_data(stream);
    else  {
        var items = JSON.parse( stream );
        for(var item in items) { 
            items[item]['sum'] = item;
            theData.push(items[ item ]); 
        }
    }

    // console.log(theData);
    
    // save the data for later
    theStudent.progressData = theData;
    
    for(var key in theData) {
        
        if(theData.hasOwnProperty(key)) {

            var sum = theData[key];
            var index = Number(sum['sum']);

            var success = Number(sum['1']);
            var missed	= Number(sum['x']);
            var time 	= Number(sum['tt']);
            var combos	= sum['c'];
            var ncombos = num_keys(combos);

            totalSuccess += success;
            totalMissed += missed;
            totalCombos += ncombos;
            totalTime	+= time;

            summary.NumberOfItems += (success+missed>0?1:0);

            if( success > 0) {
                sum['Accuracy'] = success / (success+missed);
                sum['SecPer'] = (time/success);
                sum['NumCombos'] = ncombos;
            }

            summary.items[index] = sum;
        }
    }
    
	if(summary.NumberOfItems > 0) {
		summary.AccuracyPerItem = totalSuccess/(totalSuccess + totalMissed);
		summary.secPerItem	  = (totalTime/totalSuccess);
		summary.combosPerItem = (totalCombos/summary.NumberOfItems);
	
	}
    
	// find outliers
    //console.log(summary);
					
	return summary;
}


function adbg_find_outliers(games, summary) {

}

/*******************************************/
/*
/*		MUTLIPLICATION BLOCKS Game Data
/*
/*******************************************/


function mx_game_data(stream) {
    
    //
    // this is the previous way we handled -- as a binary file_exists
    // DEPRECATED, but we still have some data in our database that is in this format
    //
    
    // binary file of data
	var buff = Buffer.decodeB64(stream);

	var games = [];	// this is the list of games
	var game;
	var ngames;
	
	var dt;
	var tm;
	var score;
	var gmCode;
	var endCode;
	
	//
	// read and decode the data
	//
	ngames = buff.readUInt32();
	if(ngames > 0) {
		for(var i=0;i<ngames;i++) {
			dt = buff.readFloat64();  // change this to readFloat64();
            // really old legacy data saved date with 32-bit float
            if(dt<9999 || dt>49999) {
                buff.seekRelative(-8);
                dt = buff.readFloat();
            }
			tm = buff.readUInt32();
			gmCode = buff.readUInt32();
			endCode = buff.readUInt32();
			score = buff.readUInt32();
			
			if( gmCode<0 || gmCode>999 || endCode<0 || endCode>5 || score<0 || score >100000) {
				console.log("Invalid data, rejecting");
				console.log("dt="+dt.toString()+", tm="+tm.toString()+", score="+score.toString());
			} else if(get_game_mode(gmCode) != MX.PRACTICE) {
				games[i] = {};
				games[i]['Date'] = dt;
				games[i]['Time'] = tm;
				games[i]['GameCode'] = gmCode;
				games[i]['EndCode'] = endCode;
				games[i]['Score'] = score;
				games[i]['PtsPerSec'] = (tm>0)?(score/tm):0;
                // did not have this data in binary file
				games[i]['Attempts'] = endCode;
                games[i]['Success'] = score;
			}
		}
	}
	
	return games;

}

function mx_process_game_data(theStudent, stream) {
	console.log("mx_process_game_data");
	
	var summary = { NumberOfGames	:0,
					Avg				:0,
					PtsPerSec		:0,
                    Accuracy        :0,
                    Attempts        :0,
                    Success         :0,
					LastGameDate	:0,
					LastGameScore	:0,
					LastGamePtsPerSec	:0,
                    LastGameAccuracy:0,
					DevScore		:0,
					DevPtsPerSec	:0,
                    DevAccuracy     :0,
					DevLastScore	:0,
					DevLastPtsPerSec:0,
                    DevLastAccuracy :0,
					OutliersAvg		:[],
					OutliersPtsPerSec:[]
					};
	
	var numGames = 0;
    var points = 0;
    var total_time = 0;
	
    var theGames = [];

    // if no data, just return empty structures
    if(stream.length==0) {
        theStudent.games = theGames;
        return summary;
    }
    
    // handle the type of data stream--
    // legacy data stream is base64 encoded stream
    // new format is just a JSON encoded string
    if(stream.charAt(0)!='{')
        theGames = mx_game_data(stream);
    else  {
        var g = JSON.parse( stream );
        for(var game in g) theGames.push(g[ game ]); 
    }    
    
    // sort by game date...
    theGames.sort(function(a,b) {
        var d1 = new Date(a['Date']);
        var d2 = new Date(b['Date']);
        return d1.valueOf() - d2.valueOf();
    });
    
    theStudent.games = theGames;

   theGames.forEach( function(game, i) {
        numGames++;
        points += Number(game['Score']);
        total_time += Number(game['Time']);
        summary.Attempts += Number(game['Attempts']);
        summary.Success += Number(game['Success']);

        // not in our origianl data, we will need this to calculate some data
        game['PtsPerSec'] = points / total_time;

    });
    
    if(numGames > 0) {
        summary.NumberOfGames = numGames;
        summary.PtsPerSec = theGames.mean('PtsPerSec');
        summary.Avg = (points / numGames);
        if(summary.Attempts>0)
            summary.Accuracy = (summary.Success / summary.Attempts);
        
        summary.LastGameDate = theGames[numGames-1]['Date'];
        summary.LastGameScore = Number(theGames[numGames-1]['Score']);
        summary.LastGamePtsPerSec = ( summary.LastGameScore / theGames[numGames-1]['Time']);
        if(Number(theGames[numGames-1]['Attempts'])>0)
            summary.LastGameAccuracy = ( Number(theGames[numGames-1]['Success'])/Number(theGames[numGames-1]['Attempts']));
    }
    
	//
	// analyze recent trends
	//
	if(numGames>3) {
		var recentScore = theGames.sumField('Score', numGames-3);
		var recentTime  = theGames.sumField('Time', numGames-3);
        var recentAccuracy = 0.0;
        
		var recentScoreAvg = recentScore/3;
		var recentSpeed = recentScore/recentTime;
        if(theGames.sumField('Attempts', numGames-3)>0)
		      recentAccuracy  = theGames.sumField('Success', numGames-3) / theGames.sumField('Attempts', numGames-3);
		
		var madScore = theGames.mad( summary.Avg, 'Score');
		var madSpeed = theGames.mad( summary.PtsPerSec, 'PtsPerSec' );
		var madAccu  = theGames.mad( summary.Accuracy, 'Accuracy');
        
		summary.DevScore 		= recentScoreAvg.spread(summary.Avg, madScore);
		summary.DevPtsPerSec 	= recentSpeed.spread(summary.PtsPerSec, madSpeed);
        summary.DevAccuracy     = recentAccuracy.spread(summary.Accuracy, madAccu);
		
		summary.DevLastScore 	= summary.LastGameScore.spread(summary.Avg, madScore);
		summary.DevLastPtsPerSec = summary.LastGamePtsPerSec.spread(summary.PtsPerSec, madSpeed);
        summary.DevLastAccuracy = summary.LastGameAccuracy.spread(summary.Accuracy, madAccu);
	}
	
	console.log(summary);
    
	return summary;
}

/*******************************************/
/*
/*		MULTIPLICATION BLOCKS Progress Data
/*
/*******************************************/

function mx_progress_data(theData) {
    
    //
    // this is the previous way we handled -- as a binary file_exists
    // DEPRECATED, but we still have some data in our database that is in this format
    //
    
    // binary file of data
	var buff = Buffer.decodeB64(theData);
	
	var product,products = [];
	var count = 0;
	var tm,tenth;
	var s;
	
	while(!buff.isEOF()) {
		product = buff.readUInt();
		if(product >= 1 && product<=144) {
			products[count] = {};
			products[count]['product'] = product;			
			products[count]['1'] = buff.readUInt();
			products[count]['x'] = buff.readUInt();
			
			tm = buff.readUInt();
			tenth = buff.readUInt();
			if(tenth>9) {
				console.log("Invalid time data: tm = " + tm + ", tenth=" + tenth);
				tm = 0;
				tenth=0;
				buff.seekRelative(-1);
			}
			products[count]['tt'] = (tm*10 + tenth)/10;
			
			s = buff.readString();
			if(s!="X")
				products[count]['c'] = JSON.parse(s);
			else
				products[count]['c'] = [];
				
			count++;
		}
	}
	
	return products;
}

function mx_process_progress_data(theStudent, stream) {
	console.log("mx_process_progess_data");
    
	var summary = { NumberOfItems:	0,
					AccuracyPerItem:    	0,
					secPerItem:		0,
					combosPerItem:	0,
					items:			[],
				};
				
	var totalSuccess = 0;
	var totalMissed = 0;
	var totalCombos = 0;
	var totalTime = 0;

    var theData = [];
    
    if(stream.length==0) {
        theStudent.progressData = theData;
        return summary;
    }
    
    // handle the type of data stream--
    // legacy data stream is base64 encoded stream
    // new format is just a JSON encoded string
    if(stream.charAt(0)!='{')
        theData = mx_progress_data(stream);
    else  {
        var items = JSON.parse( stream );
        for(var item in items) {
            items[item]['product'] = item; 
            theData.push(items[ item ]); 
        }
    }

    // save the data for later
    theStudent.progressData = theData;
    console.log(theData);
    
    for(var key in theData) {
        
        if(theData.hasOwnProperty(key)) {

            var product = theData[key];
            var index = Number(product['product']);

            //console.log(sum);

            //product['Product'] = index;

            var success = Number(product['1']);
            var missed	= Number(product['x']);
            var time 	= Number(product['tt']);
            var combos	= product['c'];
            var ncombos = num_keys(combos);

            totalSuccess += success;
            totalMissed += missed;
            totalCombos += ncombos;
            totalTime	+= time;

            summary.NumberOfItems += (success+missed>0?1:0);

            if( success > 0) {
                product['Accuracy'] = success / (success+missed);
                product['SecPer'] = (time/success);
                product['NumCombos'] = ncombos;
            }

//            summary.products[index] = product;
            summary.items.push( product );
              
        }
    }
    
	if(summary.NumberOfItems > 0) {
		summary.AccuracyPerItem   = totalSuccess/(totalSuccess + totalMissed);
		summary.secPerItem        = (totalTime/totalSuccess);
		summary.combosPerItem     = (totalCombos/summary.NumberOfItems);
	
	}
    
	// find outliers
    console.log(summary);
					
	return summary;
}

/*******************************************/
/*
/*		PERCENT BINGO Game Data
/*
/*******************************************/

function bingo_game_data(stream) {
	
    var games = [];
    
    if(stream.length==0) {
        return games;
    }
    
    var o, map;
	var i=0;
    
    if(stream.charAt(0)!='{') { 
        map = window.atob(stream);
        o = JSON.parse(map.substr(0,map.length-1));
    } else {
        o = JSON.parse(stream);
    }
	
	//
	// convert the object to an array of game objects;
	// we need to do this because we need to sort the array
	//
	
	Object.keys(o).map(function(key) { 
		games[i] = {};
		games[i].Date             = o[key].Date;
		games[i].Time             = Number(o[key].Time);
		games[i].GameCode         = Number(o[key].GameCode);
		games[i].EndCode          = Number(o[key].EndCode);
		games[i].Score            = Number(o[key].Score);
		games[i].AdaptivePlayLevel = Number(o[key].AdaptivePlayLevel);
		games[i].FractionAttempts = Number(o[key].FractionAttempts);
		games[i].FractionCorrect  = Number(o[key].FractionCorrect);
		games[i].FractionTime     = Number(o[key].FractionTime);
		games[i].DecimalAttempts  = Number(o[key].DecimalAttempts);
		games[i].DecimalCorrect   = Number(o[key].DecimalCorrect);
		games[i].DecimalTime      = Number(o[key].DecimalTime);
		i++;
	});
		
	return games;
}

function bingo_process_game_data(theStudent, stream) {
	var summary = { NumberOfGames      :0,
					Avg                :0,
					PtsPerSec          :0,
					MaxLevel		   :0,
					Accuracy		   :0,
					FracAccuracy	   :0,
					DecAccuracy		   :0,
					LastGameDate		:0,
					LastGameScore		:0,
					LastGamePtsPerSec	:0,
					LastGameAccuracy	:0,
					LastGameFracAccuracy:0,
					LastGa,eDecAccuracy :0,
					DevScore		:0,
					DevPtsPerSec	:0,
                    DevAccuracy     :0,
					DevFracAccuracy	:0,
					DevDecAccuracy	:0,
					DevLastScore	:0,
					DevLastPtsPerSec:0,
                    DevLastAccuracy :0,
					DevLastFracAccuracy:0,
					DevLastDecAccuracy:0,
					OutliersAvg		:[],
					OutliersPtsPerSec:[],
                    games           :[]
					};
					
	summary.games = bingo_game_data(stream);
	summary.NumberOfGames = summary.games.length;
	
	var totalScore = 0;
	var totalTime = 0;
	var totalFractionAttempts = 0;
	var totalFractionCorrect = 0;
	var totalFractionTime = 0;
	var totalDecimalAttempts = 0;
	var totalDecimalCorrect = 0;
	var totalDecimalTime = 0;

	summary.games.sort(function(a,b) {
		return a.Date-b.Date;
	});
    
    theStudent.games = summary.games;
	
	totalScore = summary.games.sum("Score");
	totalTime = summary.games.sum("Time");
	
	totalFractionAttempts	= summary.games.sum("FractionAttempts");
	totalFractionCorrect 	= summary.games.sum("FractionCorrect");
	totalFractionTime 		= summary.games.sum("FractionTime");
	totalDecimalAttempts 	= summary.games.sum("DecimalAttempts");	
	totalDecimalCorrect 	= summary.games.sum("DecimalCorrect");
	totalDecimalTime 		= summary.games.sum("DecimalTime");

	summary.MaxLevel = summary.games.max("AdaptivePlayLevel")	

	if(summary.NumberOfGames > 0) {	
		summary.Avg = (totalScore / summary.NumberOfGames );
		summary.PtsPerSec = (totalScore / totalTime );
			
		if(totalFractionAttempts + totalDecimalAttempts > 0)
			summary.Accuracy	= (totalFractionCorrect + totalDecimalCorrect) / (totalFractionAttempts + totalDecimalAttempts);
			
		if(totalFractionAttempts > 0)
			summary.FracAccuracy = (totalFractionCorrect) / (totalFractionAttempts);
			
		if(totalDecimalAttempts > 0)			
			summary.DecAccuracy	= (totalDecimalCorrect) / (totalDecimalAttempts);
		
		var game = summary.games[summary.NumberOfGames-1];
		summary.LastGameDate = game.Date;
		summary.LastGameScore = game.Score;
		summary.LastGamePtsPerSec = game.Score / game.Time;
		
		if(game.FractionAttempts + game.DecimalAttempts > 0)		
			summary.LastGameAccuracy = (game.FractionCorrect + game.DecimalCorrect)/(game.FractionAttempts + game.DecimalAttempts);
			
		if(game.FractionAttempts > 0)		
			summary.LastGameFracAccuracy = (game.FractionCorrect)/(game.FractionAttempts);
			
		if(game.DecimalAttempts > 0)		
			summary.LastGameDecAccuracy = (game.DecimalCorrect)/(game.DecimalAttempts);
	}
	
	if(summary.NumberOfGames > 3) {
		var recentScore = 0;
		var recentTime  = 0;
		var fAtt=0,fCorr=0;
		var dAtt=0,dCorr=0;

		for(var j=n-1; j>=n-3; j-=1) {
			recentScore += summary.games[j].Score;
			recentTime += summary.games[j].Time;
			fAtt += summary.games[j].FractionAttempts;
			dAtt += summary.games[j].DecimalAttempts;
			fCorr += summary.games[j].FractionCorrect;
			dCorr += summary.games[j].DecimalCorrect;
		}		
		
		var recentAvg = recentScore / 3;
		var recentPtsPerSec = recentScore / recentTime;
        var recentAccuracy = 0; 
		var recentFractionAccuracy = 0;
		var recentDecimalAccuracy = 0;
		
		if(fAtt>0) recentFractionAccuracy = fCorr/fAtt;
		if(dAtt>0) recentDecimalAccuracy = dCorr/dAtt;
		if(fAtt + dAtt>0) recentAccuracy = fCorr + dCorr / (fAtt +dCorr);
        
		var madScore = summary.games.mad(summary.Avg, "Score");
		var madSpeed = summary.games.mad(summary.PtsPerSec, "Time");
        var madAccu  = summary.games.mad(summary.Accuracy, "Accuracy");
		
		summary.DevScore = recentAvg.spread( summary.Avg, madScore );
		summary.DevPtsPerSec = recentPtsPerSec.spread( summary.PtsPerSec, madSpeed );
		summary.DevAccuracy = recentAccuracy.spread(summary.Accuracy, madAccuracy);
        
		summary.DevLastScore = summary.LastScore.spread( summary.Avg, madScore);
		summary.DevLastPtsPerSec = summary.LastPtsPerSec.spread( summary.PtsPerSec, madSpeed);
        summary.DevLastAccuracy = summary.LastAccuracy.spread( summary.Accuracy, madAccuracy);
		
	}		
	
	console.log(summary);
	
	return summary;					
}

/*******************************************/
/*
/*		PERCENT BINGO Progress Data
/*
/*******************************************/
var percents = [ 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 
				55, 60, 65, 70, 75, 80, 85, 90, 95, 100,
				105, 110, 115, 120, 125, 130, 135, 140, 145, 150, 
				155, 160, 165, 170, 175, 180, 185, 190, 195, 200,
				12.5, 33.3, 37.5, 62.5, 66.7, 87.5 ];

var AdaptivePlayLevels = [ "Beginner",
						 "Grasshopper",
						 "Grasshopper (2)",
						 "Master",
						 "Master (2)",
						 "Wizard",
						 "Wizard (2)" ]
						 
				
function bingo_process_progress_data(theStudent, stream) {
	
	var summary = { NumberOfItems:      0,
                    AccuracyPerItem:    0,
					SecPerItem:			0,
                    combosPerItem:      0,
					decAccuracy:		0,
					decSpeed:			0,
					fracAccuracy:		0,
					fracSpeed:			0,
					combosFractions:	0,
					fractions:			[],
					decimals:			[]
				};
				
	var map = window.atob(stream);
	var o = JSON.parse(map.substr(0,map.length-1));
	var i=0,values = [];				

    if(stream.length==0) {
        return games;
    }
    
    var o, map;
	var i=0;
    
    if(stream.charAt(0)!='{') { 
        map = window.atob(stream);
        o = JSON.parse(map.substr(0,map.length-1));
    } else {
        o = JSON.parse(stream);
    }
    
    theStudent.progressData = o;
    
	//console.log(o);
	
	percents.forEach( function(percent) {
		var key = percent.toString();
		if(key.includes('.')) key += '0';
		
		summary.fractions[i] = o[key + 'f'];
		summary.decimals[i] = o[key + 'd'];
		
		i++;
	});
	
    summary.NumberOfItems = i-1;
    
	var totalFracAttempts = summary.fractions.sum("ATT");
	var totalFracSuccess = summary.fractions.sum("SUCCESS");
	var totalFracTime = summary.fractions.sum("TIME");
	
	var totalDecAttempts = summary.decimals.sum("ATT");
	var totalDecSuccess = summary.decimals.sum("SUCCESS");
	var totalDecTime = summary.decimals.sum("TIME");
	
	//console.log(summary.fractions);
	//console.log(summary.decimals);
	
	summary.AccuracyPerItem = (totalFracSuccess + totalDecSuccess)/(totalFracAttempts + totalDecAttempts);
	summary.SecPerItem = (totalFracTime + totalDecTime) / (totalFracSuccess + totalDecSuccess);
	
	if(totalFracAttempts>0) summary.fracAccuracy = (totalFracSuccess/totalFracAttempts);
	if(totalFracSuccess>0)	summary.fracSpeed 	= (totalFracTime/totalFracSuccess);
	if(totalDecAttempts>0)	summary.decAccuracy = (totalDecSuccess/totalDecAttempts);
	if(totalDecSuccess>0)	summary.decSpeed 	= (totalDecTime/totalDecSuccess);
	
	console.log(summary);
	
	return summary;
}

function processGameData( theStudent, stream )
{
	var summary = null;
    
	switch(theStudent.product) {
		default:
			console.log('processGameData: Invalid product:' + theStudent.product);
			break;
			
		case 1:	//AdditionBlocks
			summary = adbg_process_game_data(theStudent, stream);
			break;
		case 2:	// Mx Blocks
			summary = mx_process_game_data(theStudent, stream);
			break;
		case 4:
			summary = bingo_process_game_data(theStudent, stream);
			break;
		
	}

	theStudent['summary']  = summary;
}


function processProgressData( theStudent, stream )
{
	var progress = null;
    
	switch(theStudent.product) {
		default:
			console.log('processGameData: Invalid product: ' + theStudent.product);
			break;
			
		case 1:	//AdditionBlocks
			progress = adbg_process_progress_data(theStudent, stream)
			break;
			
		case 2:	// Mx Blocks
			progress = mx_process_progress_data(theStudent, stream);
			break;
		case 4:
			progress = bingo_process_progress_data(theStudent, stream);
			break;
	}
	
	theStudent.progress = progress;

}