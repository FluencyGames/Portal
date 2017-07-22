<?php
	require_once(__DIR__ . '/../php/classes/Config.class.php');
	require_once(__DIR__ . '/../php/classes/Element.class.php');
	require_once(__DIR__ . '/../php/classes/User.class.php');
	
	Element::restrictAccess(EDUCATIONAL_ADMIN | TEACHER | TEACHER_ADMIN, 'manage');
	
	$user = User::getCurrentUser();
	$license = $user->getLicenseData();
	$products = $license['Products'];
	
	$documentroot = Config::get('documentroot');
	
	$teacherDomain = $license['DomainSuffix']; 
	$teacherID = $user->getColumn('Id');
	$groupName = $user->getColumn('Groups');
?>
<!DOCTYPE html>
<html>
<head>
	<?php Element::head('Fluency Games User Portal'); ?>
	
	<script src="<?php echo $documentroot; ?>js/math.js"></script>
	<script src="<?php echo $documentroot; ?>js/buffer.js"></script>
	
	<script type="text/javascript">
		var defaultScoreDivClass = 'score-circle';
		var defaultScoreTrendClass = 'trend-icon';
		var snapshots = [];
		var numScoresPerCol = 2;
		
		function createScorePair(score, scoreDiv, trendDiv, type) {
			return {
				score: score, // TODO(bret): Make this per game
				scoreDiv: scoreDiv,
				trendDiv: trendDiv,
				type: type,
			};
		}
		
		Snapshot = function(data) {
			var $this = this;
			this.data = data;
			
			this.scoreInfo = [
				//createScorePair(0, null, 'Average'),
				createScorePair(0, null, 'Points/Second'),
				createScorePair(0, null, 'Accuracy'),
				
				//createScorePair(0, null, 'Average'),
				createScorePair(0, null, 'Points/Second'),
				createScorePair(0, null, 'Accuracy'),
			];
			
			parentDiv = $('<div class="snapshot" data-id="' + data.id + '"></div>');
			
			parentRow = $('<div class="row"></div>');
			parentDiv.append(parentRow);
			
			nameCol = $('<div class="col-xs-3"><span class="name">' + data.name + '</span></div>');
			parentRow.append(nameCol);
			
			// NOTE(bret): We probably don't need this since we're not sending data back, but we may end up needing it anyway.
			/*<input id="username-' + data.username + '" type="hidden" name="username" value="' + data.username + '" />*/
			
			for (var colIndex = 0; colIndex < 2; ++colIndex) {
				scoreCol = $('<div class="col-xs-4"></div>');
				parentRow.append(scoreCol);
				
				scorePair = $('<div class="scores-3-pair"></div>');
				scoreCol.append(scorePair);
				
				scorePairRow = $('<div class="row"></div>');
				scorePair.append(scorePairRow);
				
				var colSize = 12 / numScoresPerCol;
				var col, scoreDiv, trendDiv;
				for (var scoreIndex = 0; scoreIndex < numScoresPerCol; ++scoreIndex) {
					col = $('<div class="col-xs-' + colSize + '"></div>');
					
					scoreDiv = $('<span class="' + defaultScoreDivClass + '" data-toggle="tooltip" title="XX%"></span>');
					scoreDiv.tipsy({ gravity: 's', html: true });
					col.append(scoreDiv);
					
					trendDiv = $('<span class="' + defaultScoreTrendClass + '"></span>');
					col.append(trendDiv);
					
					scorePairRow.append(col);
					
					$this.scoreInfo[(colIndex * numScoresPerCol) + scoreIndex].scoreDiv = scoreDiv;
					$this.scoreInfo[(colIndex * numScoresPerCol) + scoreIndex].trendDiv = trendDiv;
				}
			}
			
			scores = [];
			for (var i = 0; i < numScoresPerCol * 2; ++i) {
				scores.push(Math.random());
			}
			this.normalizeScores(scores);
			this.updateScores(scores);
			this.updateTrends();
			
			this.div = parentDiv;
			$("#snapshots").append(parentDiv);
		}
		
		Snapshot.prototype.normalizeScores = function(scores) {
			for (var i = 0; i < numScoresPerCol * 2; ++i) {
				//
			}
		}
		
		// TODO(bret): Find a better, cleaner way to write all this, jeesh
		Snapshot.prototype.updateScores = function(scores) {
			var scoreDiv;
			var classToUse;
			for (var s = 0; s < numScoresPerCol * 2; ++s) {
				scoreDiv = this.scoreInfo[s].scoreDiv;
				
				this.scoreInfo[s].score = scores[s];
				classToUse = defaultScoreDivClass;
				
				var percent = (scores[s] * 100).toFixed(2);
				var type = this.scoreInfo[s].type + ((s < numScoresPerCol) ? ' (Summary)' : ' (Last Game)');
				scoreDiv.attr('title', type + '<br />' + percent + '%');
				
				// TODO(bret): Make these use actual numbers
				if (scores[s] >= 0.7) {
					classToUse += ' green icon-circle';
				} else if (scores[s] < 0.5) {
					classToUse += ' red icon-circle';
				} else {
					classToUse += ' icon-circle-empty';
				}
				
				scoreDiv.attr('class', classToUse);
			}
		}
		
		Snapshot.prototype.updateTrends = function() {
			var trendDiv;
			var noTrendIcon = 'icon-minus'; // NOTE(bret): Feel free to change this to 'empty'
			var classToUse;
			for (var s = 0; s < numScoresPerCol * 2; ++s) {
				trendDiv = this.scoreInfo[s].trendDiv;
				classToUse = defaultScoreTrendClass;
				
				switch (Math.floor(Math.random() * 3)) {
					case 0: classToUse += ' green icon-up-dir'; break;
					case 1: classToUse += ' red icon-down-dir'; break;
					case 2: classToUse += ' icon-minus'; break;
				}
				
				trendDiv.attr('class', classToUse);
			}
		}
		
		$(window).ready(function() {
			snapshots[1] = new Snapshot({ id: 1, name: 'George' });
			snapshots[2] = new Snapshot({ id: 2, name: 'Lucas' });
			snapshots[3] = new Snapshot({ id: 3, name: 'Ian' });
		});
	</script>
	<!-- <script src="<?php echo $documentroot; ?>js/manage/users.js"></script> -->
</head>
<body>
	<?php Element::header(5); ?>
	<div class="body">
		<div class="container">
			
			<div class="row">
				<?php Element::sidebarManage(6); ?>
				<div class="col-xs-12 col-sm-8 col-lg-9">
					
					<div class="card">
						<div class="head center">
							<?php echo $user->getFullname() . ' (Group: ' . $user->getColumn('Groups') . ')'; ?>
						</div>
						
						<div class="snapshot product" data-id="0">
							<span class="name">Select Product:</span>
							<input type="hidden" name="username" value="<?php echo '*.' . $groupName . '.' . $teacherDomain; ?>" />
							<select id="product" class="right-aligned-products">
								<?php if($products & 0x01) { ?> <option value="1" >Addition Blocks</option> <?php } ?>
								<?php if($products & 0x02) { ?> <option value="2" >Multiplication Blocks</option> <?php } ?>
								<?php if($products & 0x04) { ?> <option value="4" >Percent Bingo</option> <?php } ?>
								<?php if($products & 0x08) { ?> <option value="8" >Subtraction Blocks</option> <?php } ?>
								<?php if($products & 0x10) { ?> <option value="16">Integer Blocks</option> <?php } ?>
                                <option value="128">Facts Assessment</option>
							</select>
						</div>
						<div class="snapshot">
							<div class="row">
								<div class="col-xs-3">
									<span class="name" style="height: 50px !important; line-height: 60px;">Name (Games)</span>
								</div>
								<div class="col-xs-4" style="text-align: center;">
									Summary
								</div>
								<div class="col-xs-4" style="text-align: center;">
									Last Game
								</div>
									<?php
										for ($i = 0; $i < 2; ++$i) {
									?>
								<div class="col-xs-4">
									<div class="scores-3-pair">
										<div class="row">
											<!--<div class="col-xs-4">
												<div class="score-title" data-toggle="tooltip"title="Average">Avg</div>
											</div>-->
											<div class="col-xs-6">
												<div class="score-title" data-toggle="tooltip" title="Points Per Second">PPS</div>
											</div>
											<div class="col-xs-6">
												<div class="score-title" data-toggle="tooltip" title="Accuracy">Acc</div>
											</div>
										</div>
									</div>
								</div>
									<?php } ?>
							</div>
						</div>
						<div id="snapshots"></div>
						<div class="footer center">
							End of students
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php Element::footer(); ?>
</body>
</html>